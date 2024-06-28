<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Upsell Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Upsell_Campaign' ) ) {

	/**
	 * The Upsell campaign
	 */
	class YITH_Sales_Upsell_Campaign extends Abstract_YITH_Sales_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'upsell';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'            => '{\"type\":\"all\",\"ids\":[]}',
			'title'                      => '',
			'full_size_background_color' => '',
			'close_icon_color'           => '',
			'checkbox_color'             => '',
			'saving_color'               => '',
			'products_to_show'           => '',
			'num_products_to_show'       => 2,
			'show_product_price'         => 'yes',
			'apply_discount'             => 'yes',
			'discount_to_apply'          => '{"discount_type":"percentage","discount_value":10}',
			'show_saving'                => 'yes',
			'show_product_name'          => 'yes',
			'redirect_to_checkout'       => 'no',
			'add_to_cart_button_label'   => '',
		);

		/**
		 * Initialize frequently bought together campaign.
		 *
		 * @param YITH_Sales_Upsell_Campaign|int $campaign Campaign instance or ID.
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the add to cart button label
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
		 * Get if show saving
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_saving( $context = 'view' ) {
			return $this->get_prop( 'show_saving', $context );
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
		 * Get the full size background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_full_size_background_color( $context = 'view' ) {
			return $this->get_prop( 'full_size_background_color', $context );
		}

		/**
		 * Get the close icon color of the modal
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_close_icon_color( $context = 'view' ) {
			return $this->get_prop( 'close_icon_color', $context );
		}

		/**
		 * Get the checkbox color.
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_checkbox_color( $context = 'view' ) {
			return $this->get_prop( 'checkbox_color', $context );
		}

		/**
		 * Get the saving color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_saving_color( $context = 'view' ) {
			return $this->get_prop( 'saving_color', $context );
		}

		/**
		 * Get the products to show inside the modal
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
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
		 */
		public function get_num_products_to_show( $context = 'view' ) {
			return $this->get_prop( 'num_products_to_show', $context );
		}

		/**
		 * Get if show the product price
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_product_price( $context = 'view' ) {
			return $this->get_prop( 'show_product_price', $context );
		}

		/**
		 * Get if apply a discount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
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
		 * Get if show the product name
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_product_name( $context = 'view' ) {
			return $this->get_prop( 'show_product_name', $context );
		}

		/**
		 * Get if redirect to the checkout after add to cart
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_redirect_to_checkout( $context = 'view' ) {
			return $this->get_prop( 'redirect_to_checkout', $context );
		}

		/**
		 * Get the add to cart button label
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_add_to_cart_button_label( $context = 'view' ) {
			return $this->get_prop( 'add_to_cart_button_label', $context );
		}

		/**
		 * Get the add to cart button label
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
		 * Set show saving
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
		 * Set the full size background color of modal
		 *
		 * @param string $full_size_background_color Full size background color of modal.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_full_size_background_color( $full_size_background_color ) {
			$this->set_prop( 'full_size_background_color', $full_size_background_color );
		}

		/**
		 * Set the close icon color of modal
		 *
		 * @param string $close_icon_color Color of close icon.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_close_icon_color( $close_icon_color ) {
			$this->set_prop( 'close_icon_color', $close_icon_color );
		}

		/**
		 * Set the checkbox color
		 *
		 * @param string $checkbox_color Color of the checkbox.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_checkbox_color( $checkbox_color ) {
			$this->set_prop( 'checkbox_color', $checkbox_color );
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
		 * Set the products to show inside the modal
		 *
		 * @param array $products_to_show Products to show inside the modal.
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
		 * Set he discount to apply
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
		 * Set if redirect to the checkout after add to cart
		 *
		 * @param string $redirect_to_checkout Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_redirect_to_checkout( $redirect_to_checkout ) {
			$this->set_prop( 'redirect_to_checkout', $redirect_to_checkout );
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
		 * Check if the campaign is valid
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return bool
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
		 * Add the rule in the cart
		 *
		 * @param array  $cart_item     The cart item.
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
				$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key );
				$quantity          = intval( $cart_item['quantity'] );
				$discounted_price  = $this->get_discounted_price( $price_to_discount, $quantity, $cart_item['data'] );
				$discount          = $this->get_discount_to_apply_to_product( $cart_item['data'] );
				$applied           = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
			} else {
				WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
			}

			return $applied;
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float $price_to_discount The price to discount.
		 * @param int   $quantity          The quantity.
		 * @param array $cart_item_data           The product object.
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
		 * Check if the campaign should be updated for the current item
		 *
		 * @param string $cart_item_key The item key.
		 * @param array  $cart_item     The item.
		 * @param int    $quantity      the quantity.
		 *
		 * @return array
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {
			$item_keys_to_check = array();
			if ( isset( $cart_item['yith_sales_main_product'] ) && $this->get_id() === intval( $cart_item['yith_sales_main_product'] ) ) {
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
