<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Buy X Get Y Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Buy_X_Get_Y_Campaign' ) ) {
	/**
	 * The Buy X Get Y Class
	 */
	class YITH_Sales_Buy_X_Get_Y_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'buy-x-get-y';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'   => array(),
			'title'             => '',
			'add_in_cart'       => 'no',
			'products_to_offer' => array(),
			'x_y_promo_rule'    => array(),
			'product_notice'    => '',
		);

		/**
		 * Initialize buy X get Y campaign.
		 *
		 * @param YITH_Sales_Bogo_Campaign|int $campaign Campaign instance or ID.
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
		 * Get the products to offer
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_products_to_offer( $context = 'view' ) {
			return $this->get_prop( 'products_to_offer', $context );
		}

		/**
		 * Get the buy x get y rule
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_x_y_promo_rule( $context = 'view' ) {
			return $this->get_prop( 'x_y_promo_rule', $context );
		}

		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get the notice inside the single product page
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_product_notice( $context = 'view' ) {
			return $this->get_prop( 'product_notice', $context );
		}

		/**
		 * Get if possible add the product automatically
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_add_in_cart( $context = 'view' ) {
			return $this->get_prop( 'add_in_cart', $context );
		}

		/**
		 * Check if the campaign is valid
		 *
		 * @param WC_Product $product The product.
		 * @param string     $param   The trigger type.
		 *
		 * @return bool
		 * @since  1.0.0
		 * @author YITH
		 */
		public function is_valid_for( $product, $param = 'trigger_product' ) {
			$trigger = $this->get_trigger_product();

			if ( 'trigger_product' !== $param ) {
				$offer   = $this->get_products_to_offer();
				$trigger = 'same' === $offer['type'] ? $trigger : $offer;
			}

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
					case 'default':
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
			$product_notice = do_blocks( $this->get_product_notice() );
			if ( $product_notice ) {
				echo sprintf( '<div class="yith-sales-campaign-title">%s</div>', wp_kses_post( $product_notice ) );
			}
		}

		/**
		 * Check if the campaign is valid on cart
		 *
		 * @param array $cart_item_added Last item added to cart.
		 *
		 * @return bool
		 */
		public function check_is_valid_by_cart_item( $cart_item_added ) {

			if ( ! $this->is_valid_for( $cart_item_added['data'] ) ) {
				return false;
			}

			$rule = $this->get_x_y_promo_rule();

			if ( ! isset( $rule['itemsToBuy'] ) ) {
				return false;
			}

			if ( $rule['itemsToBuy'] <= $cart_item_added['yith_sales_av_quantity'] ) {
				return true;
			}

			$trigger_product_on_cart = $this->get_products_on_cart();
			$total_quantity          = array_sum( array_column( $trigger_product_on_cart, 'yith_sales_av_quantity' ) );

			if ( $rule['itemsToBuy'] <= $total_quantity ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if the trigger product are in the cart
		 *
		 * @param array $cart_item The cart item.
		 * @param bool  $same      The rule is for the same product.
		 *
		 * @return bool
		 */
		public function check_is_trigger_valid_by_cart_item( $cart_item, $same = false ) {

			$rule = $this->get_x_y_promo_rule();

			if ( ! isset( $rule['itemsToBuy'] ) ) {
				return false;
			}

			$trigger_product_on_cart = $this->get_products_on_cart();
			$total_quantity          = array_sum( array_column( $trigger_product_on_cart, 'yith_sales_av_quantity' ) );
			$total_product_offer     = $this->get_total_item_to_offer();
			if ( $total_quantity > $rule['itemsToBuy'] && $same ) {
				$total_quantity -= $total_product_offer;
			}
			if ( $rule['itemsToBuy'] <= $total_quantity ) {
				return true;
			}

			return false;

		}

		/**
		 * Check if the campaign is valid on cart
		 *
		 * @param array $cart_item_added Last item added to cart.
		 *
		 * @return bool
		 */
		public function check_rule_on_cart( $cart_item_added ) {

			$rule = $this->get_x_y_promo_rule();

			if ( ! $this->is_valid_for( $cart_item_added['data'], 'product_to_offer' ) ) {
				return false;
			}

			$trigger_product_on_cart = $this->get_products_on_cart();
			$total_quantity          = array_sum( array_column( $trigger_product_on_cart, 'quantity' ) );

			if ( $rule['itemsToBuy'] <= $total_quantity ) {
				$product_offer_on_cart = $this->get_products_on_cart( 'product_to_offer' );
				$total_quantity        = array_sum( array_column( $product_offer_on_cart, 'quantity' ) );

			}

			return false;
		}

		/**
		 * Return the list of cart items valid for this campaign.
		 *
		 * @param string $param The type of product to check.
		 *
		 * @return array
		 */
		public function get_products_on_cart( $param = 'trigger_product' ) {
			$product_on_cart = array();
			if ( WC()->cart->cart_contents ) {
				foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
					if ( $this->is_valid_for( $cart_item['data'], $param ) ) {
						$product_on_cart[ $cart_item_key ] = $cart_item;
					}
				}
			}

			return $product_on_cart;
		}

		/**
		 * Get the total product in the cart for the rule
		 *
		 * @param array $cart_item The cart item.
		 * @param bool  $clean     Clean calculation.
		 *
		 * @return int|mixed
		 */
		public function get_total_valid_product( $cart_item, $clean = false ) {
			$num = 0;
			if ( ! is_null( WC()->cart ) && ! WC()->cart->is_empty() ) {
				foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_it ) {
					if ( $clean ) {
						if ( $this->is_valid_for( $cart_it['data'] ) && ! $this->is_valid_for( $cart_it['data'], 'product_to_offer' ) ) {
							$num += $cart_it['yith_sales_av_quantity'];
						}
					} else {
						if ( $this->is_valid_for( $cart_it['data'] ) && $this->is_valid_for( $cart_it['data'], 'product_to_offer' ) ) {
							$num += $cart_it['yith_sales_av_quantity'];
						}
					}
				}
			}

			return $num;
		}

		/**
		 * Return ho many product are available for the rule
		 *
		 * @return int
		 */
		public function get_total_trigger() {
			$products_on_cart = $this->get_products_on_cart();
			$total            = array_sum( array_column( $products_on_cart, 'yith_sales_av_quantity' ) );
			$product_offer    = $this->get_products_to_offer();
			$rule             = $this->get_x_y_promo_rule();
			if ( 'same' === $product_offer['type'] ) {
				$total = $total - $rule['itemsToBuy'];
			}

			return $total;
		}

		/**
		 * Calculate how many product offer for this rule
		 *
		 * @return int
		 */
		public function get_total_item_to_offer() {

			$products_on_cart = $this->get_products_on_cart( 'product_to_offer' );
			$total            = array_sum( array_column( $products_on_cart, 'yith_sales_av_quantity' ) );

			$product_offer = $this->get_products_to_offer();
			$rule          = $this->get_x_y_promo_rule();
			if ( 'same' === $product_offer['type'] ) {
				$total = $total - $rule['itemsToBuy'];
			}

			return max( $total, 0 );
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float $price_to_discount The price to discount.
		 * @param int   $quantity          The quantity.
		 * @param array $cart_item_data    The cart item.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function get_discounted_price( $price_to_discount, $quantity, $cart_item_data ) {
			$discounted_price = floatval( $price_to_discount );
			$discount         = $this->get_x_y_promo_rule();
			$discount_type    = $discount['discount_type'];
			$discount_amount  = floatval( str_replace( ',', '.', $discount['discount_value'] ) );

			if ( 'percentage' === $discount_type ) {
				$percent          = $discount_amount / 100;
				$discounted_price = $discounted_price - ( $discounted_price * $percent );
			} else {
				$discounted_price = $discounted_price - $discount_amount;
			}

			return max( $discounted_price, 0 );
		}

		/**
		 * Check if is possible add the product automatically
		 *
		 * @return bool
		 */
		public function can_add_to_cart() {

			$can_add = $this->get_add_in_cart() === 'yes';

			if ( $can_add ) {

				$can_add          = false;
				$trigger_opts     = $this->get_trigger_product();
				$product_to_offer = $this->get_products_to_offer();
				$config_to_check  = 'same' === $product_to_offer['type'] ? $trigger_opts : $product_to_offer;

				if ( 'products' === $config_to_check['type'] ) {
					$rule       = $this->get_x_y_promo_rule();
					$product_id = current( $config_to_check['ids'] );
					$product    = wc_get_product( $product_id );

					$can_add = 'simple' === $product->get_type() && 'percentage' === $rule['discount_type'] && 100 === intval( $rule['discount_value'] );
				}
			}

			return $can_add;
		}

		/**
		 * Add the product automatically.
		 *
		 * @return void
		 * @throws Exception The error.
		 */
		public function add_to_cart() {
			$trigger_opts     = $this->get_trigger_product();
			$product_to_offer = $this->get_products_to_offer();
			$rule             = $this->get_x_y_promo_rule();
			$config_to_check  = 'same' === $product_to_offer['type'] ? $trigger_opts : $product_to_offer;
			$product_id       = current( $config_to_check['ids'] );

			$cart_item_key = yith_sales_check_product_on_cart( $product_id, 0, array() );
			if ( $cart_item_key ) {
				$old_qty    = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				$counter    = $old_qty;
				$tot_target = 0;
				$repeats    = floor( ( $old_qty ) / $rule['itemsToBuy'] );
				for ( $x = 1; $x <= $repeats; $x ++ ) {
					if ( $counter - $rule['itemsToBuy'] >= 0 ) {
						$counter    -= $rule['itemsToBuy'];
						$tot_target += $rule['itemsToGet'];
					}
				}
				WC()->cart->set_quantity( $cart_item_key, $old_qty + $tot_target );

			} else {
				$cart_item_data = array(
					'yith_sales' => array(
						'campaigns' => array(
							$this->get_id(),
						),
					),
				);
				WC()->cart->add_to_cart( $product_id, $rule['itemsToGet'], 0, array(), $cart_item_data );
			}
		}

		/**
		 * Apply the discount on cart.
		 *
		 * @param array  $cart_item     Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return bool
		 */
		public function apply_rule_in_cart( $cart_item, $cart_item_key ) {
			$can_be_applied = true;
			$applied        = false;
			if ( $this->is_valid_for( $cart_item['data'], 'product_to_add' ) ) {

				if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
					$discounts      = $cart_item['yith_sales_discounts']['applied_discounts'];
					$can_be_applied = in_array( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				}
				if ( $can_be_applied ) {

					$total_target = isset( $cart_item[ $this->get_id() ]['total_target'] ) ? $cart_item[ $this->get_id() ]['total_target'] : 0;

					$rule              = $this->get_x_y_promo_rule();
					$discount_type     = $rule['discount_type'];
					$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key, 'view' );
					$discounted_price  = $this->get_discounted_price( $price_to_discount, 1, $cart_item );
					$quantity          = $cart_item['quantity'];

					$counter  = YITH_Sales_Buy_X_Get_Y_Counter::get_counter( $this->get_id(), $total_target );
					$base_qty = ( $counter > $quantity ) ? $quantity : $counter;

					if ( $base_qty > 0 ) {

						$real_quantity = $cart_item['yith_sales_av_quantity'] - $base_qty;
						$line_subtotal = $quantity * $price_to_discount;
						if ( 'percentage' === $discount_type ) {
							$line_total         = ( $base_qty * $discounted_price ) + ( $real_quantity * $price_to_discount );
							$current_difference = ( $line_subtotal - $line_total ) / $quantity;
							$price              = $current_difference > 0 ? $price_to_discount - $current_difference : $price_to_discount;
						} else {
							$real_quantity      = $quantity - $base_qty;
							$line_total         = ( $base_qty * $discounted_price ) + ( $real_quantity * $price_to_discount );
							$current_difference = ( $line_subtotal - $line_total ) / $quantity;
							$price              = $current_difference > 0 ? $price_to_discount - $current_difference : $price_to_discount;
						}

						if ( $total_target > $quantity ) {
							YITH_Sales_Buy_X_Get_Y_Counter::update_counter( $this->get_id(), $quantity );
							WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_av_quantity'] = 0;
						} else {
							$new_av = $quantity - $base_qty;
							$new_av = max( $new_av, 0 );

							WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_av_quantity'] = $new_av;
							YITH_Sales_Buy_X_Get_Y_Counter::reset_counter( $this->get_id(), false );
						}

						$applied = $this->save_discount_in_cart(
							$cart_item_key,
							$price_to_discount,
							$price,
							array(
								'discount_type'  => $discount_type,
								'discount_value' => $rule['discount_value'],
							)
						);
					}
				} else {
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item['yith_sales_discounts']['price_adjusted'] );
				}
			}

			return $applied;
		}

		/**
		 * Check the product in cart , after a cart update.
		 *
		 * @param string $cart_item_key The key.
		 * @param array  $cart_item     The item.
		 * @param int    $quantity      The quantity.
		 *
		 * @return array
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {
			$item_keys_to_check = array();
			if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
				$discounts  = $cart_item['yith_sales_discounts']['applied_discounts'];
				$is_applied = in_array( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( $is_applied ) {
					$totals = $this->get_total_trigger();
					$rule   = $this->get_x_y_promo_rule();
					if ( $rule['itemsToBuy'] > $totals ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );
						$item_keys_to_check[] = $cart_item_key;
					}
				}
			}

			return $item_keys_to_check;

		}
	}
}
