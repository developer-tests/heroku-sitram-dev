<?php
/**
 *  YITH Sales Cart Discount Action Schedule
 *
 * @author  YITH
 * @package YITH\Sales\
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class manage action schedule to clear old coupons
 */
class YITH_Sales_Cart_Discount_Action_Schedule {

	/**
	 * The construct
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_action_schedule' ) );
		add_action( 'yith_sales_clear_coupons_action', array( $this, 'clear_coupons_action_per_page' ), 10, 2 );
	}

	/**
	 * Init the recurring scheduled action to clear coupons
	 *
	 * @return void
	 */
	public function init_action_schedule() {

		$ve                 = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
		$time_start         = strtotime( '00:00 ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' );
		$args               = array(
			'limit' => 30,
			'page'  => 0,
		);
		$has_hook_scheduled = WC()->queue()->get_next( 'yith_sales_clear_coupons_action' );
		! $has_hook_scheduled && WC()->queue()->schedule_recurring( $time_start, DAY_IN_SECONDS, 'yith_sales_clear_coupons_action', $args, 'yith-sales-actions' );
	}

	/**
	 * Schedule the single clear coupon action
	 *
	 * @param int $limit The limit.
	 * @param int $page The current page.
	 *
	 * @return void
	 */
	public function clear_coupons_action_per_page( $limit, $page ) {

		$coupons_to_clear = $this->get_coupons_to_clear( $limit, $page );

		if ( $coupons_to_clear ) {
			$this->clear_coupons( $coupons_to_clear );
			$args = array(
				'limit' => $limit,
				'page'  => $page ++,
			);
			WC()->queue()->schedule_single( strtotime( '+30 second' ), 'yith_sales_clear_coupons_action', $args, 'yith-sales-actions' );
		}
	}

	/**
	 * Get the coupons to delete
	 *
	 * @param int $limit The limit.
	 * @param int $page The current page.
	 *
	 * @return array|false
	 */
	protected function get_coupons_to_clear( $limit, $page ) {
		global $wpdb;
		$query         = "SELECT DISTINCT ( pm.post_id ) FROM {$wpdb->postmeta} AS pm WHERE pm.meta_key= %s AND pm.meta_value = %s ORDER BY pm.post_id DESC LIMIT %d OFFSET %d";
		$query         = $wpdb->prepare( $query, 'yith_sales_coupon', '1', $limit, $page * $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results       = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		$results       = wp_list_pluck( $results, 'post_id' );
		$ids_to_remove = array();
		foreach ( $results as $result ) {
			$post_type = get_post_type( $result );
			$date_gmt  = get_post_datetime( $result, 'date', 'gmt' );

			/**
			 * APPLY_FILTERS: yith_sales_remove_coupon_older_than
			 *
			 * Change the day ago to delete older coupons.
			 *
			 * @param int $day_ago The day ago.
			 *
			 * @return int
			 */
			$day_ago = apply_filters( 'yith_sales_remove_coupon_older_than', strtotime( '-1 day', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

			if ( 'shop_coupon' === $post_type && $date_gmt && $date_gmt->getTimestamp() < $day_ago ) {
				$ids_to_remove[] = $result;
			}
		}

		return count( $ids_to_remove ) > 0 ? $ids_to_remove : false;
	}

	/**
	 * Delete the coupons from db
	 *
	 * @param array $coupon_ids The ids to delete from db.
	 *
	 * @return void
	 */
	protected function clear_coupons( $coupon_ids ) {
		global $wpdb;
		$non_cached_ids        = esc_sql( $coupon_ids );
		$non_cached_ids_string = implode( ',', $non_cached_ids );
		$query                 = "DELETE FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id IN ( $non_cached_ids_string ); ";
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		$query = "DELETE FROM {$wpdb->posts} WHERE {$wpdb->posts}.ID IN ( $non_cached_ids_string );";
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
	}
}

new YITH_Sales_Cart_Discount_Action_Schedule();
