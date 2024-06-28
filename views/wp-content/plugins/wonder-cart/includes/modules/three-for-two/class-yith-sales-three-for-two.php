<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Shop 3x2 Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Three_For_Two_Campaign' ) ) {
	/**
	 * The Three x Two Campaign
	 */
	class YITH_Sales_Three_For_Two_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {


		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'three-for-two';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'        => array(),
			'title'                  => '',
			'default_qty'            => 'yes',
			'show_badge'             => 'yes',
			'badge_text'             => '',
			'badge_background_color' => '',
			'badge_text_color'       => '',
		);


		/**
		 * Initialize 3x2 campaign.
		 *
		 * @param YITH_Sales_Three_For_Two_Campaign|int $campaign Campaign instance or ID.
		 *
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the trigger products
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_trigger_product( $context = 'view' ) {
			return $this->get_prop( 'trigger_product', $context );
		}


		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get if set the default quantity
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_default_qty( $context = 'view' ) {
			return $this->get_prop( 'default_qty', $context );
		}

		/**
		 * Get if show the badge
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_badge( $context = 'view' ) {
			return $this->get_prop( 'show_badge', $context );
		}

		/**
		 * Get the badge text
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_text( $context = 'view' ) {
			return $this->get_prop( 'badge_text', $context );
		}

		/**
		 * Get the badge background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_background_color( $context = 'view' ) {
			return $this->get_prop( 'badge_background_color', $context );
		}

		/**
		 * Get the badge text color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_text_color( $context = 'view' ) {
			return $this->get_prop( 'badge_text_color', $context );
		}

		/**
		 * Set the trigger products
		 *
		 * @param array $trigger_product Array type, ids.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_trigger_product( $trigger_product ) {
			$this->set_prop( 'trigger_product', $trigger_product );
		}


		/**
		 * Set title
		 *
		 * @param string $title Set the title.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_title( $title ) {
			$this->set_prop( 'title', $title );
		}

		/**
		 * Set if set the default quantity to 3
		 *
		 * @param string $default_qty Set the title.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_default_qty( $default_qty ) {
			$this->set_prop( 'default_qty', $default_qty );
		}

		/**
		 * Set the badge text
		 *
		 * @param string $badge_text Badge text.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_text( $badge_text ) {
			$this->set_prop( 'badge_text', $badge_text );
		}

		/**
		 * Set the badge background color
		 *
		 * @param string $badge_background_color Badge background color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_background_color( $badge_background_color ) {
			$this->set_prop( 'badge_background_color', $badge_background_color );
		}

		/**
		 * Set the badge text color
		 *
		 * @param string $badge_text_color Badge text color.
		 *
		 * @return void
		 * @since  1.0.0
		 * @author YITH
		 */
		public function set_badge_text_color( $badge_text_color ) {
			$this->set_prop( 'badge_text_color', $badge_text_color );
		}

		/**
		 * Check if the campaign is valid
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return bool
		 * @since  1.0.0
		 * @author YITH
		 */
		public function is_valid_for( $product ) {
			$trigger  = $this->get_trigger_product();
			$is_valid = false;
			if ( isset( $trigger['type'], $trigger['ids'] ) ) {
				switch ( $trigger['type'] ) {
					case 'all':
						$is_valid = true;
						break;
					case 'products':
						if ( is_array( $trigger['ids'] ) && yith_sales_is_product_in_list( $product, $trigger['ids'] ) ) {
							$is_valid = true;
						}
						break;
					case 'categories':
						$is_valid = yith_sales_is_product_in_taxonomy_list( $product, $trigger['ids'] );
						break;
				}
			}

			return $is_valid;
		}


		/**
		 *  Show the campaign title on product page.
		 *
		 * @return void
		 * @since  1.0.0
		 * @author YITH
		 */
		public function show_title_on_single_page() {
			$title = do_blocks( $this->get_title() );
			if ( $title ) {
				echo sprintf( '<div class="yith-sales-campaign-title">%s</div>', wp_kses_post( $title ) );
			}
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float $price_to_discount The price to discount.
		 * @param int   $quantity The quantity.
		 * @param array $cart_item_data The product object.
		 *
		 * @return float
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_discounted_price( $price_to_discount, $quantity, $cart_item_data ) {
			$price_to_discount = floatval( $price_to_discount );
			if ( $quantity < 3 ) {
				return $price_to_discount;
			}

			$discount         = $this->get_discount_to_apply_to_product( $price_to_discount, $quantity );
			$discounted_price = $price_to_discount - $discount['discount_value'];

			return max( $discounted_price, 0 );
		}

		/**
		 * Add the rule in the cart
		 *
		 * @param array  $cart_item The cart item.
		 * @param string $cart_item_key The item key that allow the apply.
		 *
		 * @return bool
		 *
		 * @since  1.0.0
		 * @author YITH
		 */
		public function apply_rule_in_cart( $cart_item, $cart_item_key ) {
			$quantity                = intval( $cart_item['quantity'] );
			$can_be_applied          = $quantity >= 3;
			$is_discount_not_applied = true;
			$applied                 = false;

			if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
				$cart_item_adj           = isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : false;
				$discounts               = $cart_item_adj['yith_sales_discounts']['applied_discounts'];
				$is_discount_not_applied = array_search( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			}

			if ( $can_be_applied && $is_discount_not_applied ) {
				$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key, 'view' );
				$discounted_price  = $this->get_discounted_price( $price_to_discount, $quantity, $cart_item['data'] );

				$discount = $this->get_discount_to_apply_to_product( $price_to_discount, $quantity );
				$applied  = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
			} else {
				if ( isset( $cart_item_adj ) ) {
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
				} else {
					$product                                            = wc_get_product( WC()->cart->cart_contents[ $cart_item_key ]['data'] );
					WC()->cart->cart_contents[ $cart_item_key ]['data'] = $product;
				}
			}

			return $applied;
		}

		/**
		 * Calculate the discount based on the quantity on cart.
		 *
		 * @param float $price Price.
		 * @param int   $quantity Quantity.
		 *
		 * @return array
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_discount_to_apply_to_product( $price, $quantity ) {
			$discount = array(
				'discount_value' => 0,
				'discount_type'  => 'fixed',
			);
			if ( $quantity < 3 ) {
				return $discount;
			}

			$discount['discount_value'] = ( floor( $quantity / 3 ) * $price ) / $quantity;

			return $discount;
		}


		/**
		 * Check the cart when an update is made.
		 *
		 * Refresh the cart items rule
		 *
		 * @param int   $cart_item_key Cart item key.
		 * @param array $cart_item Cart item.
		 * @param int   $quantity Quantity added to cart.
		 *
		 * @return array
		 * @since  1.0.0
		 * @author YITH
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {
			unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );

			return array();
		}

	}
}
