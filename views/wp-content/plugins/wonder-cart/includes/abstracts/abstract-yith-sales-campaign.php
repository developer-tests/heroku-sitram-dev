<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Abstract YITH Sales Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Abstracts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Abstract_YITH_Sales_Campaign' ) ) {
	/**
	 * Shippo generic object
	 *
	 * @since 1.0.0
	 */
	abstract class Abstract_YITH_Sales_Campaign extends Abstract_YITH_Sales_Data {

		/**
		 * This is the name of this object type.
		 *
		 * @var string
		 */
		protected $object_type = 'campaign';

		/**
		 * Post type.
		 *
		 * @var string
		 */
		protected $post_type = 'yith_campaign';

		/**
		 * Cache group.
		 *
		 * @var string
		 */
		protected $cache_group = 'yith_campaign';

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = '';

		/**
		 * Stores campaign data.
		 *
		 * @var array
		 */
		protected $data = array(
			'campaign_name'      => '',
			'type'               => 'frequently-bought-together',
			'priority'           => 1,
			'campaign_status'    => 'inactive',
			'schedule_date_from' => null,
			'schedule_date_to'   => null,
		);

		/**
		 * Get the campaign if ID is passed, otherwise the campaign is new and empty.
		 * This class should NOT be instantiated, but the yith_get_campaign() function
		 * should be used.
		 *
		 * @param int|Abstract_YITH_Sales_Campaign|object $campaign Campaign to init.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			if ( is_numeric( $campaign ) && $campaign > 0 ) {
				$this->set_id( $campaign );
			} elseif ( $campaign instanceof self ) {
				$this->set_id( absint( $campaign->get_id() ) );
			} elseif ( ! empty( $campaign->ID ) ) {
				$this->set_id( absint( $campaign->ID ) );
			} else {
				$this->set_object_read( true );
			}
		}


		/**
		 * Get internal type. Should return string and *should be overridden* by child classes.
		 *
		 * The campaign_type property is deprecated but is used here for BW compatibility with child classes which may be defining campaign_type and not have a get_type method.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_type() {
			return isset( $this->type ) ? $this->type : false;
		}


		/**
		 * Get post type. Should return string and *should be overridden* by child classes.
		 *
		 * The campaign_type property is deprecated but is used here for BW compatibility with child classes which may be defining campaign_type and not have a get_type method.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_post_type() {
			return $this->post_type;
		}

		/**
		 * Get campaign name.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_campaign_name( $context = 'view' ) {
			return $this->get_prop( 'campaign_name', $context );
		}

		/**
		 * Get campaign status.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_campaign_status( $context = 'view' ) {
			return $this->get_prop( 'campaign_status', $context );
		}


		/**
		 * Get campaign status.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_schedule_date_from( $context = 'view' ) {
			return $this->get_prop( 'schedule_date_from', $context );
		}


		/**
		 * Get campaign status.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_schedule_date_to( $context = 'view' ) {
			return $this->get_prop( 'schedule_date_to', $context );
		}


		/**
		 * Get campaign preview.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_preview( $context = 'view' ) {
			return $this->get_prop( 'preview', $context );
		}

		/**
		 * Get campaign group.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_group( $context = 'view' ) {
			return $this->get_prop( 'group', $context );
		}


		/**
		 * Get campaign priority.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_priority( $context = 'view' ) {
			return $this->get_prop( 'priority', $context );
		}


		/**
		 * Set campaign name.
		 *
		 * @param string $name Campaign name.
		 *
		 * @since 1.0.0
		 */
		public function set_campaign_name( $name ) {
			$this->set_prop( 'campaign_name', $name );
		}

		/**
		 * Set campaign status.
		 *
		 * @param string $status Campaign status.
		 *
		 * @since 1.0.0
		 */
		public function set_status( $status ) {
			$this->set_prop( 'status', $status );
		}

		/**
		 * Set campaign post status.
		 *
		 * @param string $post_status Campaign status.
		 *
		 * @since 1.0.0
		 */
		public function post_status( $post_status ) {
			$this->set_prop( 'post_status', $post_status );
		}

		/**
		 * Set campaign status.
		 *
		 * @param string $group Campaign status.
		 *
		 * @since 1.0.0
		 */
		public function set_group( $group ) {
			$this->set_prop( 'group', $group );
		}

		/**
		 * Set campaign status.
		 *
		 * @param string $campaign_type Campaign status.
		 *
		 * @since 1.0.0
		 */
		public function set_campaign_type( $campaign_type ) {
			$this->set_prop( 'campaign_type', $campaign_type );
		}

		/**
		 * Set campaign schedule date from.
		 *
		 * @param string $schedule_date_from Campaign schedule date from.
		 *
		 * @since 1.0.0
		 */
		public function set_schedule_date_from( $schedule_date_from ) {
			$this->set_prop( 'schedule_date_from', $schedule_date_from );
		}

		/**
		 * Set campaign schedule date to.
		 *
		 * @param string $schedule_date_to Campaign schedule date from.
		 *
		 * @since 1.0.0
		 */
		public function set_schedule_date_to( $schedule_date_to ) {
			$this->set_prop( 'schedule_date_to', $schedule_date_to );
		}

		/**
		 * Set campaign preview.
		 *
		 * @param string $preview Campaign preview.
		 *
		 * @since 1.0.0
		 */
		public function set_preview( $preview ) {
			$this->set_prop( 'preview', $preview );
		}

		/**
		 * Set campaign priority.
		 *
		 * @param string $priority Campaign priority.
		 *
		 * @since 1.0.0
		 */
		public function set_priority( $priority ) {
			$this->set_prop( 'priority', $priority );
		}

		/**
		 * Set all props to default values.
		 *
		 * @since 1.0.0
		 */
		public function set_defaults() {
			$this->data    = $this->default_data;
			$this->changes = array();
			$this->set_object_read( false );
		}


		/**
		 * Read the rule object.
		 *
		 * @throws Exception If invalid rule.
		 * @since 3.0.0
		 */
		protected function read() {
			$this->set_defaults();
			$post_object = get_post( $this->get_id() );

			if ( ! $this->get_id() || ! $post_object || $this->get_post_type() !== $post_object->post_type ) {
				throw new Exception( __( 'Invalid campaign.', 'wonder-cart' ) );
			}

			foreach ( $this->data as $prop => $default_value ) {
				$meta_prop = $this->get_meta( $prop );
				if ( $meta_prop ) {
					$value = $meta_prop;
				} else {
					$value = metadata_exists( 'post', $this->get_id(), $prop ) ? get_post_meta( $this->get_id(), $prop, true ) : $default_value;
				}

				$method_name = str_replace( '-', '_', $prop );
				$setter      = "set_{$method_name}";

				if ( method_exists( $this, $setter ) ) {
					$this->$setter( $value );
				} else {
					$this->set_prop( $prop, $value );
				}
			}
			$this->set_object_read( true );

			/**
			 * DO_ACTION: yith_sales_campaign_read
			 *
			 * The action is triggered after to read the campaign from db.
			 *
			 * @param int $id the rule id.
			 */
			do_action( 'yith_sales_campaign_read', $this->get_id() );
		}

		/**
		 * Check if the campaign is valid to apply.
		 *
		 * It should be active and not scheduled or ended.
		 *
		 * @return bool
		 */
		public function is_active() {
			$status = $this->get_campaign_status();

			$is_valid = false;
			if ( 'active' !== $status ) {
				return $is_valid;
			}

			$now                = time();
			$schedule_date_from = $this->get_schedule_date_from();
			$schedule_date_to   = $this->get_schedule_date_to();

			if ( '' !== $schedule_date_from && $schedule_date_from < $now ) {
				if ( ( '' !== $schedule_date_to && $now < $schedule_date_to ) || '' === $schedule_date_to ) {
					$is_valid = true;
				}
			}

			return $is_valid;

		}


		/**
		 * Return the price to discount
		 *
		 * @param array  $cart_item     The cart item.
		 * @param string $cart_item_key The cart item key.
		 * @param string $context       Context.
		 *
		 * @return float|bool
		 * @since 3.0.0
		 */
		public function get_price_to_discount( $cart_item, $cart_item_key, $context = '' ) {
			$default_price = false;

			/**
			 * DO_ACTION: yith_sales_before_get_price_to_discount
			 *
			 * The action is triggered before get the price to discount.
			 *
			 * @param array  $cart_item     the cart item.
			 * @param string $cart_item_key the cart item key.
			 */
			do_action( 'yith_sales_before_get_price_to_discount', $cart_item, $cart_item_key );
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
				$product = $cart_item['data'];

				if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['price_adjusted'] ) ) {
					$default_price = floatval( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['price_adjusted'] );
				} else {
					$wc_product    = wc_get_product( $cart_item['data']->get_id() );
					$default_price = 'view' === $context ? floatval( $wc_product->get_price() ) : floatval( $product->get_regular_price() );
				}
			}

			/**
			 * DO_ACTION: ywdpd_after_get_price_to_discount
			 *
			 * The action is triggered after get the price to discount.
			 *
			 * @param array  $cart_item     the cart item.
			 * @param string $cart_item_key the cart item key.
			 */
			do_action( 'yith_sales_after_get_price_to_discount', $cart_item, $cart_item_key );

			/**
			 * APPLY_FILTERS: yith_sales_get_price_to_discount
			 *
			 * Filter the price to discount.
			 *
			 * @param float                        $default_price The value to filter.
			 * @param array                        $cart_item     The cart item.
			 * @param string                       $cart_item_key The cart item key.
			 * @param Abstract_YITH_Sales_Campaign $object        The current object.
			 *
			 * @return float
			 */
			return apply_filters( 'yith_sales_get_price_to_discount', $default_price, $cart_item, $cart_item_key, $this );
		}

		/**
		 * Save the discount rule in the cart.
		 *
		 * @param string $cart_item_key    The item to add the discount.
		 * @param float  $original_price   The original price.
		 * @param float  $discounted_price The new price.
		 * @param array  $discount         The discount settings.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function save_discount_in_cart( $cart_item_key, $original_price, $discounted_price, $discount = array() ) {

			$saved_on_cart = false;

			if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
				/**
				 * APPLY_FILTERS: yith_sales_save_discount_in_cart
				 *
				 * Filter the price to discount.
				 *
				 * @param bool  $save_discount_in_cart The value to filter.
				 * @param float $discounted_price      The discounted price.
				 * @param float $original_price        The original price.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_sales_save_discount_in_cart', $discounted_price !== $original_price, $discounted_price, $original_price ) ) {

					$product       = WC()->cart->cart_contents[ $cart_item_key ]['data'];
					$quantity      = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
					$tax_mode      = WC()->cart->get_tax_price_display_mode();
					$display_price = 'excl' === $tax_mode ? wc_get_price_excluding_tax( $product, array( 'price' => $discounted_price ) ) : wc_get_price_including_tax( $product, array( 'price' => $discounted_price ) );
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $display_price );

					$applied_discount = array(
						'id'             => $this->get_id(),
						'type'           => $this->get_type(),
						'price_base'     => $original_price,
						'price_adjusted' => $discounted_price,
						'discount'       => $discount,
					);

					if ( ! isset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] ) ) {
						$discount_data                                                      = array(
							'id'                => $this->get_id(),
							'type'              => $this->get_type(),
							'price_base'        => $original_price,
							'display_price'     => $display_price,
							'price_adjusted'    => $discounted_price,
							'applied_discounts' => array( $applied_discount ),
						);
						WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] = $discount_data;
					} else {
						$existing     = WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'];
						$add_discount = true;
						if ( isset( $existing['applied_discounts'] ) && is_array( $existing['applied_discounts'] ) && count( $existing['applied_discounts'] ) > 0 ) {
							foreach ( $existing['applied_discounts'] as $existent_discount ) {
								if ( maybe_serialize( $existent_discount ) === maybe_serialize( $applied_discount ) ) {
									$add_discount = false;
									break;
								}
							}
							if ( $add_discount ) {
								$existing['applied_discounts'][] = $applied_discount;
							}
						}
						$discount_data                                                      = array(
							'type'              => $existing['type'],
							'id'                => $this->get_id(),
							'price_base'        => $existing['price_base'],
							'display_price'     => $existing['display_price'],
							'price_adjusted'    => $discounted_price,
							'applied_discounts' => $existing['applied_discounts'],

						);
						WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] = $discount_data;
					}

					$saved_on_cart = true;
				}
			}

			return $saved_on_cart;
		}

		/**
		 * Return if the badge is enabled for the campaign in the current product
		 *
		 * @param WC_Product $product Product.
		 *
		 * @return bool
		 */
		public function show_badge_for_product( $product ) {
			$show_badge = false;

			if ( method_exists( $this, 'get_show_badge' ) && method_exists( $this, 'is_valid_for', ) && $this->is_valid_for( $product ) ) {
				$show_badge = $this->get_show_badge() === 'yes';
			}

			return $show_badge;
		}

		/**
		 * Check if the campaign needs to hide the product form in single product page
		 *
		 * @return bool
		 */
		public function hide_product_form() {
			return false;
		}

		/**
		 * Check if the campaign need to show the promotion
		 *
		 * @return bool
		 */
		public function show_promotion() {
			return false;
		}

		/**
		 * Add custom data in the cart item
		 *
		 * @param array $cart_item_data The cart item data.
		 * @param int   $product_id     The product id.
		 * @param int   $variation_id   the variation id.
		 * @param int   $quantity       The quantity.
		 *
		 * @return array
		 */
		public function get_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
			if ( isset( $cart_item_data['yith_sales']['campaigns'] ) && ! in_array( $this->get_id(), $cart_item_data['yith_sales']['campaigns'] ) || ! isset( $cart_item_data['yith_sales']['campaigns'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$cart_item_data['yith_sales']['campaigns'][] = $this->get_id();
			}

			return $cart_item_data;
		}

		/**
		 * Check if the campaign should be updated for the current item and return the keys of items resetted
		 *
		 * @param string $cart_item_key The item key.
		 * @param array  $cart_item     The item.
		 * @param int    $quantity      the quantity.
		 *
		 * @return array
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {

			return array();
		}

		public function can_be_applied_with_other_campaigns( $cart_item ) {
			$types = yith_sales()->get_order_of_campaigns();
			if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
				$index = array_search( $this->get_type(), $types, true );
				$slice = array_slice( $types, 0, $index );
				$campaigns = $cart_item['yith_sales']['campaigns'];
				foreach ( $campaigns as $campaign ) {
					$campaign = yith_sales_get_campaign( $campaign );
					if ( $campaign && in_array( $campaign->get_type(), $slice, true ) ) {
						return false;
					}
				}
			}

			return true;
		}
	}
}
