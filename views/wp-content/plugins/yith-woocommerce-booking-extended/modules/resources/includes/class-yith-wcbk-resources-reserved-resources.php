<?php
/**
 * Class YITH_WCBK_Resources_Reserved_Resources
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Booking\Modules\Resources
 */

defined( 'YITH_WCBK' ) || exit;

if ( ! class_exists( 'YITH_WCBK_Resources_Reserved_Resources' ) ) {
	/**
	 * Class YITH_WCBK_Resources_Reserved_Resources
	 * handle reserved resources during Checkout
	 *
	 * @since 5.8.1
	 * @internal
	 */
	class YITH_WCBK_Resources_Reserved_Resources {
		use YITH_WCBK_Singleton_Trait;

		/**
		 * The constructor.
		 */
		private function __construct() {
			add_action( 'yith_wcbk_reserved_bookings_reserve_for_order_item', array( $this, 'handle_reserve_for_order_item' ), 10, 3 );

			add_action( 'yith_wcbk_reserved_bookings_release_for_order', array( $this, 'handle_release_for_order' ) );

			add_filter( 'yith_wcbk_booking_helper_count_booked_bookings_in_period', array( $this, 'filter_count_booked_bookings_in_period' ), 10, 2 );

			add_action( 'yith_wcbk_reserved_resources_clean_expired', array( $this, 'clean_expired' ) ); // used in Cron Schedules.
		}

		/**
		 * Is booking reservation enabled?
		 *
		 * @return boolean
		 */
		private function is_enabled(): bool {
			static $enabled = null;
			if ( is_null( $enabled ) ) {
				$has_needed_db_table = version_compare( YITH_WCBK_Install::get_db_version(), '5.8.1', '>=' );

				$enabled = ! ! apply_filters( 'yith_wcbk_reserved_bookings_enabled', false ) && $has_needed_db_table;
			}

			return $enabled;
		}

		/**
		 * Handle reserve bookings for an order.
		 *
		 * @param WC_Order_Item_Product $item    The order item.
		 * @param WC_Order              $order   The order.
		 * @param int                   $minutes True to sum persons instead of counting booking number.
		 *
		 * @throws Exception If the booking resources in the order cannot be reserved.
		 */
		public function handle_reserve_for_order_item( WC_Order_Item_Product $item, WC_Order $order, int $minutes ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$this->reserve_for_order_item( $item, $order, 10 );
		}

		/**
		 * Handle release for order.
		 *
		 * @param int $order_id The order ID.
		 */
		public function handle_release_for_order( $order_id ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$this->release_for_order( $order_id );
		}

		/**
		 * Reserve booking for product.
		 *
		 * @param WC_Order_Item_Product $item    The order item.
		 * @param WC_Order              $order   The order.
		 * @param int                   $minutes True to sum persons instead of counting booking number.
		 *
		 * @throws Exception If the booking for the specified product cannot be reserved.
		 */
		private function reserve_for_order_item( WC_Order_Item_Product $item, WC_Order $order, int $minutes ) {
			/* @var WC_Product_Booking $product The bookable product. */
			$product = $item->get_product();

			$booking_data = $item->get_meta( 'yith_booking_data' );
			$props        = YITH_WCBK_Cart::get_booking_props_from_booking_data( $booking_data );

			/**
			 * Extract booking props.
			 *
			 * @var int   $from         The 'from' timestamp.
			 * @var int   $to           The 'to' timestamp.
			 * @var int   $persons      The number of persons.
			 * @var int[] $resource_ids The resource IDs of persons.
			 */
			list ( $from, $to, $persons, $resource_ids ) = yith_plugin_fw_extract( $props, 'from', 'to', 'persons', 'resource_ids' );

			$persons   = max( $persons ?? 1, 1 );
			$resources = array_filter( array_map( 'yith_wcbk_get_resource', $resource_ids ?? array() ) );

			if ( ! ! $resources ) {
				foreach ( $resources as $resource ) {
					$this->reserve_for_resource( $resource, $product, $order, $item->get_id(), $from, $to, $persons, $minutes );
				}
			}
		}

		/**
		 * Release temporary reserved bookings for an order.
		 *
		 * @param int $order_id The order ID.
		 */
		private function release_for_order( $order_id ) {
			global $wpdb;

			if ( ! $this->is_enabled() ) {
				return;
			}

			$wpdb->delete(
				$wpdb->yith_wcbk_reserved_resources,
				array(
					'order_id' => $order_id,
				)
			);
		}

		/**
		 * Reserve booking for product.
		 *
		 * @param YITH_WCBK_Resource $resource The resource.
		 * @param WC_Product_Booking $product  The bookable product.
		 * @param WC_Order           $order    The order.
		 * @param int                $item_id  The order item.
		 * @param int                $from     The 'from' timestamp.
		 * @param int                $to       The 'to' timestamp.
		 * @param int                $persons  Optional order to exclude from the results.
		 * @param int                $minutes  True to sum persons instead of counting booking number.
		 *
		 * @throws Exception If the booking for the specified resource cannot be reserved.
		 */
		private function reserve_for_resource( $resource, $product, $order, $item_id, $from, $to, $persons, $minutes ) {
			global $wpdb;
			$persons = max( $persons, 1 );
			$weight  = ! ! $product->has_count_people_as_separate_bookings_enabled() ? $persons : 1;

			$count_args         = array(
				'resources'                 => $resource->get_id(),
				'from'                      => $from,
				'to'                        => $to,
				'unit'                      => $product->get_duration_unit(),
				'include_externals'         => $product->has_external_calendars(),
				'count_persons_as_bookings' => $product->has_count_people_as_separate_bookings_enabled(),
				'exclude_order_id'          => $order->get_id(),
				'exclude_reserved'          => true,
				'return'                    => $resource->get_available_quantity() < 2 ? 'total' : 'max_by_unit',
			);
			$number_of_bookings = yith_wcbk_booking_helper()->count_max_booked_bookings_per_unit_in_period( $count_args );

			$query_for_reserved = $this->get_query_for_reserved_resources( $resource->get_id(), $from, $to, $order->get_id(), $product->has_count_people_as_separate_bookings_enabled(), true );

			$date_from = is_numeric( $from ) ? $from : wc_string_to_timestamp( $from );
			$date_from = gmdate( 'Y-m-d H:i:s', $date_from );
			$date_to   = is_numeric( $to ) ? $to : wc_string_to_timestamp( $to );
			$date_to   = gmdate( 'Y-m-d H:i:s', $date_to );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$the_query = $wpdb->prepare(
				"
				INSERT INTO {$wpdb->yith_wcbk_reserved_resources} ( `order_id`, `order_item_id`, `product_id`, `resource_id`, `from`,`to`,`persons`, `timestamp`, `expires` )
				SELECT %d, %d, %d, %d, %s, %s, %d, NOW(), ( NOW() + INTERVAL %d MINUTE ) FROM DUAL
				WHERE %d + ( $query_for_reserved FOR UPDATE ) + %d <= %d
				ON DUPLICATE KEY UPDATE `product_id` = VALUES( `product_id` ), `resource_id` = VALUES( `resource_id` ), `from` = VALUES( `from` ), `to` = VALUES( `to` ), `persons` = VALUES( `persons` ), `expires` = VALUES( `expires` )
				",
				$order->get_id(),
				$item_id,
				$product->get_id(),
				$resource->get_id(),
				$date_from,
				$date_to,
				$persons,
				$minutes,
				$number_of_bookings,
				$weight,
				$resource->get_available_quantity()
			);

			$result = $wpdb->query( $the_query );
			// phpcs:enable

			if ( ! $result ) {
				throw new Exception(
					sprintf(
					// translators: %s is the name of the product.
						__( '%s is not available in the dates you selected.', 'yith-booking-for-woocommerce' ),
						$product->get_name()
					)
				);
			}
		}

		/**
		 * Returns query statement for getting reserved bookings of a product.
		 *
		 * @param int          $resource_id      The resource ID.
		 * @param int          $from             The 'from' timestamp.
		 * @param int          $to               The 'to' timestamp.
		 * @param int          $exclude_order_id Optional order to exclude from the results.
		 * @param bool         $sum_persons      True to sum persons instead of counting booking number.
		 * @param string|false $max_by_unit      The unit to get the max (month, day, hour, minute). False to not use group-by.
		 *
		 * @return string Query statement.
		 */
		private function get_query_for_reserved_resources( $resource_id, $from, $to, $exclude_order_id = 0, $sum_persons = false, $max_by_unit = false ) {
			global $wpdb;

			$join         = "$wpdb->posts posts ON reserved_table.`order_id` = posts.ID";
			$where_status = "posts.post_status IN ( 'wc-checkout-draft', 'wc-pending' )";
			if ( \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$join         = "{$wpdb->prefix}wc_orders orders ON reserved_table.`order_id` = orders.id";
				$where_status = "orders.status IN ( 'wc-checkout-draft', 'wc-pending' )";
			}

			$date_from = is_numeric( $from ) ? $from : wc_string_to_timestamp( $from );
			$date_from = gmdate( 'Y-m-d H:i:s', $date_from );
			$date_to   = is_numeric( $to ) ? $to : wc_string_to_timestamp( $to );
			$date_to   = gmdate( 'Y-m-d H:i:s', $date_to );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( $max_by_unit ) {
				$table_where = $wpdb->prepare(
					'
						reserved_table.`expires` > NOW()
						AND reserved_table.`resource_id` = %d
						AND reserved_table.`order_id` != %d
						AND reserved_table.`to` > %s
						AND reserved_table.`from` < %s
						',
					$resource_id,
					$exclude_order_id,
					$date_from,
					$date_to
				);

				$table_query = "
						$wpdb->yith_wcbk_reserved_resources reserved_table
						LEFT JOIN $join
						WHERE $where_status
						AND $table_where
						";

				$from_table_query     = str_replace( 'reserved_table', 'reserved_table_from', $table_query );
				$to_table_query       = str_replace( 'reserved_table', 'reserved_table_to', $table_query );
				$bookings_table_query = str_replace( 'reserved_table', 'reserved_table_bookings', $table_query );

				$select_value       = 'MAX( booking_count )';
				$inner_select_value = 'COUNT(*) AS booking_count';

				if ( $sum_persons ) {
					$select_value       = 'MAX( persons )';
					$inner_select_value = 'SUM( persons ) AS persons';
				}

				return "SELECT COALESCE( $select_value, 0) FROM 
						(
							SELECT $inner_select_value
							FROM 
								(		
									SELECT DISTINCT `from` as bucket FROM $from_table_query
									UNION DISTINCT
									SELECT DISTINCT `to` as bucket FROM $to_table_query
								) as buckets
							JOIN (SELECT reserved_table_bookings.* FROM $bookings_table_query) AS bookings ON bookings.from <= buckets.bucket AND bookings.to > buckets.bucket
							GROUP BY bucket
						) as bucket_counting";
			}

			$select = 'COUNT( DISTINCT reserved_table.`id` )';
			if ( $sum_persons ) {
				$select = 'COALESCE( SUM( reserved_table.`persons` ), 0 )';
			}

			return $wpdb->prepare(
				"
			SELECT $select 
			FROM $wpdb->yith_wcbk_reserved_resources reserved_table
			LEFT JOIN $join
			WHERE $where_status
			AND reserved_table.`expires` > NOW()
			AND reserved_table.`resource_id` = %d
			AND reserved_table.`order_id` != %d
			AND reserved_table.`to` > %s
			AND reserved_table.`from` < %s
			",
				$resource_id,
				$exclude_order_id,
				$date_from,
				$date_to
			);
			// phpcs:enable
		}

		/**
		 * Get the reserved number of resources for a specific product.
		 *
		 * @param int  $resource_id      The resource ID.
		 * @param int  $from             The 'from' timestamp.
		 * @param int  $to               The 'to' timestamp.
		 * @param int  $exclude_order_id Optional order to exclude from the results.
		 * @param bool $sum_persons      True to sum persons instead of counting booking number.
		 *
		 * @return int
		 */
		private function get_reserved_count( $resource_id, $from, $to, $exclude_order_id = 0, $sum_persons = false ) {
			global $wpdb;

			if ( ! $this->is_enabled() ) {
				return 0;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $this->get_query_for_reserved_resources( $resource_id, $from, $to, $exclude_order_id, $sum_persons, true ) );
		}

		/**
		 * Filter count booked bookings in period to include counting reserved resources.
		 *
		 * @param int   $count The actual count.
		 * @param array $args  Arguments.
		 *
		 * @return int
		 */
		public function filter_count_booked_bookings_in_period( $count, $args ) {
			if ( $this->is_enabled() && ! empty( $args['resources'] ) && empty( $args['exclude_reserved'] ) ) {
				$resources = (array) $args['resources'];
				$resources = array_filter( array_map( 'absint', $resources ) );

				/**
				 * Extract arguments.
				 *
				 * @var int  $from                      The 'from' timestamp.
				 * @var int  $to                        The 'to' timestamp.
				 * @var bool $count_persons_as_bookings True to count persons as bookings.
				 * @var int  $exclude_order_id          Order ID to exclude.
				 */
				list ( $from, $to, $count_persons_as_bookings, $exclude_order_id ) =
					yith_plugin_fw_extract( $args, 'from', 'to', 'count_persons_as_bookings', 'exclude_order_id' );

				foreach ( $resources as $resource_id ) {
					$count += $this->get_reserved_count( $resource_id, $from, $to, $exclude_order_id, $count_persons_as_bookings );
				}
			}

			return $count;
		}

		/**
		 * Clean expired.
		 */
		public function clean_expired() {
			global $wpdb;

			if ( ! $this->is_enabled() ) {
				return;
			}

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DELETE FROM $wpdb->yith_wcbk_reserved_resources WHERE `expires` < NOW()" );
			// phpcs:enable
		}
	}
}
