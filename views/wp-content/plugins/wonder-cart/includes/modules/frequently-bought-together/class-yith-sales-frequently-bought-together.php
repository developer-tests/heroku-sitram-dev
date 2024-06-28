<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Frequently Bought Together Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Frequently_Bought_Together_Campaign' ) ) {
	/**
	 * The FBT campaign
	 */
	class YITH_Sales_Frequently_Bought_Together_Campaign extends Abstract_YITH_Sales_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'frequently-bought-together';


		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'          => array(),
			'products_to_show'         => array(),
			'num_products_to_show'     => 2,
			'checked_by_default'       => 'yes',
			'show_product_price'       => 'yes',
			'apply_discount'           => 'yes',
			'discount_to_apply'        => array(),
			'show_saving'              => 'yes',
			'show_total'               => 'yes',
			'show_product_name'        => 'yes',
			'add_to_cart_button_label' => '',
			'title'                    => '',
			'border_radius_item'       => array(),
			'item_background_color'    => '',
			'item_border_color'        => '',
			'saving_color'             => '',
		);


		/**
		 * Initialize frequently bought together campaign.
		 *
		 * @param YITH_Sales_Frequently_Bought_Together_Campaign|int $campaign Campaign instance or ID.
		 *
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the products where show the offer
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_trigger_product( $context = 'view' ) {
			return $this->get_prop( 'trigger_product', $context );
		}

		/**
		 * Get the products to show inside the offer
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_products_to_show( $context = 'view' ) {
			return $this->get_prop( 'products_to_show', $context );
		}

		/**
		 * Get the number of products to show
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 *
		 * @since 1.0.0
		 */
		public function get_num_products_to_show( $context = 'view' ) {
			return (int) $this->get_prop( 'num_products_to_show', $context );
		}

		/**
		 * Get if the products are checked by default
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_checked_by_default( $context = 'view' ) {
			return $this->get_prop( 'checked_by_default', $context );
		}


		/**
		 * Get if to show the product price
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_show_product_price( $context = 'view' ) {
			return $this->get_prop( 'show_product_price', $context );
		}

		/**
		 * Get if a discount is enabled for the campaign
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_apply_discount( $context = 'view' ) {
			return $this->get_prop( 'apply_discount', $context );
		}

		/**
		 * Get the discount to apply
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_discount_to_apply( $context = 'view' ) {
			return $this->get_prop( 'discount_to_apply', $context );
		}

		/**
		 * Get if to show the saving price
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_show_saving( $context = 'view' ) {
			return $this->get_prop( 'show_saving', $context );
		}

		/**
		 * Get if show the total amount of products selected
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_show_total( $context = 'view' ) {
			return $this->get_prop( 'show_total', $context );
		}

		/**
		 * Get if to show the product name
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_show_product_name( $context = 'view' ) {
			return $this->get_prop( 'show_product_name', $context );
		}


		/**
		 * Get the add to cart button label
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_add_to_cart_button_label( $context = 'view' ) {
			return $this->get_prop( 'add_to_cart_button_label', $context );
		}

		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}


		/**
		 * Get the border radius of each item
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_border_radius_item( $context = 'view' ) {
			return $this->get_prop( 'border_radius_item', $context );
		}

		/**
		 * Get the background color of each item
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_item_background_color( $context = 'view' ) {
			return $this->get_prop( 'item_background_color', $context );
		}

		/**
		 * Get the checkbox color of each item
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_item_border_color( $context = 'view' ) {
			return $this->get_prop( 'item_border_color', $context );
		}


		/**
		 * Get the color of saving price
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_saving_color( $context = 'view' ) {
			return $this->get_prop( 'saving_color', $context );
		}

		/**
		 * Get the trigger products
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
		 * Set the products to show  on page product
		 *
		 * @param array $products_to_show Products to show on page product.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_products_to_show( $products_to_show ) {
			$this->set_prop( 'products_to_show', $products_to_show );
		}

		/**
		 * Set the number of products to show
		 *
		 * @param int $num_products_to_show Number of products to show.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_num_products_to_show( $num_products_to_show ) {
			$this->set_prop( 'num_products_to_show', $num_products_to_show );
		}

		/**
		 * Set if the products are checked by default
		 *
		 * @param string $checked_by_default Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_checked_by_default( $checked_by_default ) {
			$this->set_prop( 'checked_by_default', $checked_by_default );
		}

		/**
		 * Set if show the product price
		 *
		 * @param string $show_product_price Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_product_price( $show_product_price ) {
			$this->set_prop( 'show_product_price', $show_product_price );
		}

		/**
		 * Set if apply a discount
		 *
		 * @param string $apply_discount Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_apply_discount( $apply_discount ) {
			$this->set_prop( 'apply_discount', $apply_discount );
		}

		/**
		 * Set the discount to apply
		 *
		 * @param array $discount_to_apply Discount to apply.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_discount_to_apply( $discount_to_apply ) {
			$this->set_prop( 'discount_to_apply', $discount_to_apply );
		}

		/**
		 * Set if show saving
		 *
		 * @param string $show_saving Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_saving( $show_saving ) {
			$this->set_prop( 'show_saving', $show_saving );
		}

		/**
		 * Set if show the total of items
		 *
		 * @param string $show_total Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_total( $show_total ) {
			$this->set_prop( 'show_total', $show_total );
		}


		/**
		 * Set if show the product name
		 *
		 * @param string $show_product_name Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_product_name( $show_product_name ) {
			$this->set_prop( 'show_product_name', $show_product_name );
		}


		/**
		 * Set the add to cart button label
		 *
		 * @param string $add_to_cart_button_label Button label.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_add_to_cart_button_label( $add_to_cart_button_label ) {
			$this->set_prop( 'add_to_cart_button_label', $add_to_cart_button_label );
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
		 * Set the border radius of the items
		 *
		 * @param array $border_radius_item Border radius.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_border_radius_item( $border_radius_item ) {
			$this->set_prop( 'border_radius_item', $border_radius_item );
		}

		/**
		 * Set the item background color
		 *
		 * @param string $item_background_color Color of background.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_item_background_color( $item_background_color ) {
			$this->set_prop( 'item_background_color', $item_background_color );
		}

		/**
		 * Set the border color of items
		 *
		 * @param string $item_border_color Color of the border.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_item_border_color( $item_border_color ) {
			$this->set_prop( 'item_border_color', $item_border_color );
		}

		/**
		 * Set the saving color
		 *
		 * @param string $saving_color Color of saving text.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_saving_color( $saving_color ) {
			$this->set_prop( 'saving_color', $saving_color );
		}


		/**
		 * Check if the campaign is valid
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return bool
		 */
		public function is_valid_for( $product ) {

			$trigger = $this->get_trigger_product();

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

			if ( $is_valid ) {
				$products_to_show = $this->get_products_to_show();
				$products         = array();
				switch ( $products_to_show['type'] ) {
					case 'best-seller':
						$products = wc_get_products(
							array(
								'orderby' => 'popularity',
								'limit'   => 10,
							)
						);
						break;
					case 'featured':
						$products = wc_get_featured_product_ids();
						break;
					case 'upsell':
						$products = $product->get_upsell_ids();
						break;
					case 'cross-sell':
						$products = $product->get_cross_sell_ids();
						break;
					case 'related':
						$products = wc_get_related_products( $product->get_id() );
						break;
					case 'products':
						$products = wc_get_products(
							array(
								'post__in' => $products_to_show['ids'],
							)
						);
						break;
				}

				if ( ! $products ) {
					$is_valid = false;
				}
			}

			return $is_valid;
		}

		/**
		 * Check if the product with the discount is still valid
		 *
		 * @param array $cart_item The cart item to check.
		 *
		 * @return bool
		 */
		public function is_valid_for_cart( $cart_item ) {
			$main_cart_key = $cart_item['yith_sales_main_cart_item_key'] ?? false;
			$is_valid      = false;
			if ( $main_cart_key ) {
				$main_item        = WC()->cart->get_cart_item( $main_cart_key );
				$product          = $main_item['data'];
				$products_to_show = $this->get_products_to_show();
				$products         = array();
				switch ( $products_to_show['type'] ) {
					case 'best-seller':
						$products = wc_get_products(
							array(
								'orderby' => 'popularity',
								'return'  => 'ids',
							)
						);
						break;
					case 'featured':
						$products = wc_get_featured_product_ids();
						break;
					case 'upsell':
						$products = $product->get_upsell_ids();
						break;
					case 'cross-sell':
						$products = $product->get_cross_sell_ids();
						break;
					case 'related':
						$products = wc_get_related_products( $product->get_id(), $this->get_num_products_to_show() );
						break;
					case 'products':
						$products = $products_to_show['ids'];
						break;
				}

				$is_valid = in_array( $cart_item['product_id'], $products );
			}


			return $is_valid;
		}

		/**
		 * Add the rule in the cart
		 *
		 * @param array  $cart_item The cart item.
		 * @param string $cart_item_key The item key that allow the apply.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public function apply_rule_in_cart( $cart_item, $cart_item_key ) {

			$can_be_applied = true;
			$applied        = false;
			if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
				$cart_item_adj  = isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : false;
				$discounts      = $cart_item_adj['yith_sales_discounts']['applied_discounts'];
				$can_be_applied = in_array( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			}

			if ( $can_be_applied ) {
				$check = $this->is_valid_for_cart( $cart_item );

				if ( $check ) {
					$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key );
					$quantity          = intval( $cart_item['quantity'] );
					$discounted_price  = $this->get_discounted_price( $price_to_discount, $quantity, $cart_item['data'] );
					$discount          = $this->get_discount_to_apply_to_product( $cart_item['data'] );
					$applied           = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
				}

			} else {
				WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
			}

			return $applied;
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float $price_to_discount The price to discount.
		 * @param int   $quantity The quantity.
		 * @param array $cart_item_data The cart item.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function get_discounted_price( $price_to_discount, $quantity, $cart_item_data ) {

			if ( 'yes' !== $this->get_apply_discount() ) {
				return $price_to_discount;
			}

			$discounted_price = floatval( $price_to_discount );
			$discount         = $this->get_discount_to_apply();
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
		 * Get the discount to apply to a product
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return array
		 */
		public function get_discount_to_apply_to_product( $product ) {
			return $this->get_discount_to_apply();
		}

		/**
		 * Custom add to cart for FBT
		 *
		 * @param array $request Request parameters.
		 *
		 * @return bool
		 * @throws Exception The exception.
		 */
		public function add_to_cart( $request ) {

			$main_product  = WC()->cart->get_cart_item( $request['cart_item_key'] );
			$main_product  = $main_product['data'];
			$added_to_cart = array();
			if ( $this->is_valid_for( $main_product ) ) {

				WC()->cart->cart_contents[ $request['cart_item_key'] ]['yith_sales_main_product'] = $this->get_id();

				$cart_item_data = array(
					'yith_sales'                    => array(
						'campaigns' => array(
							$this->get_id(),
						),
					),
					'yith_sales_main_cart_item_key' => $request['cart_item_key'],
				);

				foreach ( $request['product_list'] as $product ) {
					$product_id   = $product['product_id'];
					$variation_id = ! empty( $product['variation_id'] ) ? $product['variation_id'] : 0;
					$variation    = ! empty( $product['variation'] ) ? $product['variation'] : array();

					$result = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation, $cart_item_data );

					if ( $result ) {

						$added_to_cart[ $product_id ] = 1;
					}
				}

				wc_add_to_cart_message( $added_to_cart, false );
				wp_safe_redirect( $main_product->get_permalink() );
				exit();
			}

			return false;

		}

		/**
		 * Check if the campaign should be updated for the current item
		 *
		 * @param string $cart_item_key The item key.
		 * @param array  $cart_item The item.
		 * @param int    $quantity the quantity.
		 *
		 * @return array
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {
			$item_keys_to_check = array();
			if ( isset( $cart_item['yith_sales_main_product'] ) && $this->get_id() === $cart_item['yith_sales_main_product'] ) {
				if ( 0 === $quantity ) { // if is a remove action.
					foreach ( WC()->cart->cart_contents as $item_key => $item ) {
						if ( $cart_item_key !== $item_key && ( isset( $item['yith_sales_main_cart_item_key'] ) && $item['yith_sales_main_cart_item_key'] === $cart_item_key ) ) {
							unset( WC()->cart->cart_contents[ $item_key ]['yith_sales'] );
							unset( WC()->cart->cart_contents[ $item_key ]['yith_sales_discounts'] );
							$item_keys_to_check[] = $item_key;
						}
					}
				} else {
					foreach ( WC()->cart->cart_contents as $item_key => $item ) {
						if ( $cart_item_key !== $item_key && ( isset( $item['yith_sales_main_cart_item_key'] ) && $item['yith_sales_main_cart_item_key'] === $cart_item_key ) ) {
							WC()->cart->cart_contents[ $item_key ]['yith_sales']['campaigns'][] = $this->get_id();
							unset( WC()->cart->cart_contents[ $item_key ]['yith_sales_discounts'] );
						}
					}
				}
			}

			return $item_keys_to_check;
		}
	}
}
