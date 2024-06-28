<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Gift on Cart Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Gift_On_Cart_Campaign' ) ) {
	/**
	 * The Gift Campaign
	 */
	class YITH_Sales_Gift_On_Cart_Campaign extends Abstract_YITH_Sales_Campaign {


		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'gift-on-cart';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'gift_rules'                 => array(),
			'product_to_offer'           => array(),
			'add_in_cart'                => 'no',
			'num_products_to_show'       => 2,
			'max_products_to_add'        => 2,
			'show_product_price'         => 'yes',
			'show_product_name'          => 'yes',
			'add_to_cart_button_label'   => 'yes',
			'title'                      => '',
			'full_size_background_color' => '',
			'close_icon_color'           => '',
		);

		/**
		 * Initialize gift on cart campaign.
		 *
		 * @param YITH_Sales_Gift_On_Cart_Campaign|int $campaign Campaign instance or ID.
		 *
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}


		/**
		 * Get the gift rules
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_gift_rules( $context = 'view' ) {
			return $this->get_prop( 'gift_rules', $context );
		}

		/**
		 * Get the products to offer
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_product_to_offer( $context = 'view' ) {
			return $this->get_prop( 'product_to_offer', $context );
		}

		/**
		 * Get if add in cart automaticallu
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_add_in_cart( $context = 'view' ) {
			return $this->get_prop( 'add_in_cart', $context );
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
		 * Get the number of products that can be added in the cart
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 */
		public function get_max_products_to_add( $context = 'view' ) {
			return $this->get_prop( 'max_products_to_add', $context );
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
		 * Set the list of rules
		 *
		 * @param array $gift_rules List of rules.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_gift_rules( $gift_rules ) {
			$this->set_prop( 'gift_rules', $gift_rules );
		}

		/**
		 * Set the product to offer
		 *
		 * @param array $product_to_offer List of product to offer.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_product_to_offer( $product_to_offer ) {
			$this->set_prop( 'product_to_offer', $product_to_offer );
		}

		/**
		 * Set if add the product automatically on cart
		 *
		 * @param string $add_in_cart Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_add_in_cart( $add_in_cart ) {
			$this->set_prop( 'add_in_cart', $add_in_cart );
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
		 * Set the number of products that can be added in the cart
		 *
		 * @param int $max_products_to_add What the value is for. Valid values are view and edit.
		 */
		public function set_max_products_to_add( $max_products_to_add ) {
			$this->set_prop( 'max_products_to_add', $max_products_to_add );
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
		 * Check if the campaign is valid
		 *
		 * @param array $extra_args The args to validate the campaign.
		 *
		 * @return bool
		 */
		public function is_valid( $extra_args ) {

			$is_valid = false;
			$rules    = $this->get_gift_rules();

			if ( 'always' === $rules['type'] ) {
				return $this->valid_products_found_in_cart();
			}

			$handle_rule     = $rules['handle'];
			$gift_conditions = $rules['rules'];
			foreach ( $gift_conditions as $gift_condition ) {
				$condition_type = $gift_condition['type'];

				switch ( $condition_type ) {
					case 'past_expense':
						$current_condition_valid = $this->is_valid_total_amount_spent( $gift_condition['value'], $extra_args['past_expense'], $gift_condition['condition'] );
						break;
					case 'products':
					case 'categories':
						$current_condition_valid = $this->is_valid_terms( $gift_condition['value'], $extra_args[ $condition_type ], $gift_condition['condition'] );
						break;
					default:
						$current_condition_valid = $this->is_valid_cart_total( $gift_condition['value'], $extra_args['cart_total'], $gift_condition['condition'] );
						break;
				}
				if ( ! $current_condition_valid && 'all' === $handle_rule ) {
					$is_valid = false;
					break;
				} else {
					$is_valid = $is_valid || $current_condition_valid;
				}
			}

			return $is_valid;
		}

		/**
		 * Check if in the cart there are no gift products
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function valid_products_found_in_cart() {
			foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
				if ( ! isset( $cart_item['yith_sales_gift_product'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if the condition on cart total is valid
		 *
		 * @param float  $total_to_check The total to check.
		 * @param float  $cart_total     The cart total.
		 * @param string $condition      The condition.
		 *
		 * @return bool
		 */
		public function is_valid_cart_total( $total_to_check, $cart_total, $condition ) {
			switch ( $condition ) {
				case 'at_least':
					$is_valid = $cart_total >= $total_to_check;
					break;
				case 'at_most':
					$is_valid = $cart_total < $total_to_check;
					break;
				default:
					$is_valid = floatval( $total_to_check ) === floatval( $cart_total );
					break;
			}

			return $is_valid;
		}

		/**
		 * Check if the condition on total amount spent is valid
		 *
		 * @param float  $total_to_check The total to check.
		 * @param float  $amount_total   The total amount spent.
		 * @param string $condition      The condition.
		 *
		 * @return bool
		 */
		public function is_valid_total_amount_spent( $total_to_check, $amount_total, $condition ) {

			if ( $total_to_check < 0 ) {
				return true;
			}

			switch ( $condition ) {
				case 'at_least':
					$is_valid = $amount_total >= $total_to_check;
					break;
				case 'at_most':
					$is_valid = $amount_total < $total_to_check;
					break;
				default:
					$is_valid = floatval( $total_to_check ) === floatval( $amount_total );
					break;
			}

			return $is_valid;
		}

		/**
		 * Check if the condition for the terms in cart is valid
		 *
		 * @param array $term_ids_to_check The term ids to check.
		 * @param array $term_ids_in_cart  The term ids in cart.
		 * @param bool  $condition         The condition check.
		 *
		 * @return bool
		 */
		public function is_valid_terms( $term_ids_to_check, $term_ids_in_cart, $condition = 'at_least' ) {

			$term_ids_in_cart  = array_map( 'intval', $term_ids_in_cart );
			$term_ids_to_check = array_map( 'intval', $term_ids_to_check );
			$is_valid          = true;
			foreach ( $term_ids_to_check as $term_id ) {

				$is_valid = in_array( $term_id, $term_ids_in_cart, true );
				if ( ! $is_valid && 'all' === $condition ) {
					break;
				} elseif ( $is_valid ) {
					break;
				}
			}

			return $is_valid;
		}

		/**
		 * Check if is possible add the gift product automatically
		 *
		 * @return bool
		 */
		public function can_add_to_cart_automatically() {
			$add_to_cart      = $this->get_add_in_cart();
			$product_to_offer = $this->get_product_to_offer();
			$num_of_product   = count( $product_to_offer['ids'] );
			$can_add_to_cart  = 'yes' === $add_to_cart;
			if ( $can_add_to_cart ) {
				if ( 'categories' === $product_to_offer['type'] ) {
					$can_add_to_cart = false;
				} elseif ( 1 === $num_of_product ) {
					$product_id      = current( $product_to_offer['ids'] );
					$product         = wc_get_product( $product_id );
					$can_add_to_cart = 'simple' === $product->get_type();
				}
			}

			return $can_add_to_cart;
		}

		/**
		 * Get the product configuration
		 *
		 * @return array
		 * @throws Exception The exception.
		 */
		protected function get_product_configuration_to_add_automatically() {
			$product          = array(
				'product_id'   => '',
				'variation_id' => 0,
				'variation'    => array(),
			);
			$product_to_offer = $this->get_product_to_offer();
			$num_of_product   = count( $product_to_offer['ids'] );

			if ( 1 === $num_of_product && 'products' === $product_to_offer['type'] ) {
				$product_id  = current( $product_to_offer['ids'] );
				$product_obj = wc_get_product( $product_id );
				if ( $product_obj->is_purchasable() && $product_obj->is_in_stock() ) {
					if ( $product_obj->get_type() === 'simple' ) {
						$product['product_id'] = $product_id;
					}
				}
			}

			return $product;
		}


		/**
		 * Add to cart the main product automatically
		 *
		 * @return void
		 * @throws Exception The exception.
		 */
		public function add_to_cart() {
			$product_config = $this->get_product_configuration_to_add_automatically();
			if ( ! empty( $product_config['product_id'] ) ) {
				$cart_item_data = array(
					'yith_sales'              => array(
						'campaigns' => array(
							$this->get_id(),
						),
					),
					'yith_sales_gift_product' => $this->get_id(),
				);
				$cart_key       = WC()->cart->add_to_cart( $product_config['product_id'], 1, $product_config['variation_id'], $product_config['variation'], $cart_item_data );
				if ( $cart_key ) {
					wc_add_to_cart_message( array( $product_config['product_id'] => 1 ), false );
				}
			}
		}

		/**
		 * Get the max product that can be added in the cart as gift
		 *
		 * @return int
		 */
		public function total_products_to_add() {
			$product_to_offer = $this->get_product_to_offer();
			if ( 'categories' === $product_to_offer['type'] ) {
				$total_products = $this->get_max_products_to_add();
			} else {
				$total_products = count( $product_to_offer['ids'] ) > 1 ? $this->get_max_products_to_add() : 1;
			}

			return $total_products;
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
				$discount          = $this->get_discount_to_apply_to_product( $cart_item['data'] );
				$applied           = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, 0, $discount );
			} else {
				WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
			}

			return $applied;
		}

		/**
		 * Get the discount to apply to a product
		 *
		 * @param WC_Product $product the product.
		 *
		 * @return array
		 */
		public function get_discount_to_apply_to_product( $product ) {
			return array(
				'type'  => 'percentage',
				'value' => 100,
			);
		}

		/**
		 * Remove the quantity from the product.
		 *
		 * @param string $product_quantity The quantity html.
		 * @param string $cart_item_key    The cart item key.
		 * @param array  $cart_item        The cart item.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function remove_quantity( $product_quantity, $cart_item_key, $cart_item ) {
			return sprintf( '<p class="yith-sales-cart-item-quantity">%d</p>', $cart_item['quantity'] );
		}

		/**
		 * Remove the quantity from the product on woocommerce block.
		 *
		 * @param WC_Product $product   Product.
		 * @param array      $cart_item The cart item.
		 *
		 * @return bool
		 * @since  1.0.0
		 * @author YITH
		 */
		public function remove_quantity_on_wc_blocks( $product, $cart_item ) {
			return false;
		}
	}
}
