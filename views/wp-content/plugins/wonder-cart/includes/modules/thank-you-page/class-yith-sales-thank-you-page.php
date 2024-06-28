<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Shop Thank you page Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Thank_You_Page_Campaign' ) ) {
	/**
	 * The "Thank You" Campaign
	 */
	class YITH_Sales_Thank_You_Page_Campaign extends Abstract_YITH_Sales_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'thank-you-page';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'          => array(),
			'products_to_show'         => '',
			'title'                    => '',
			'num_products_to_show'     => 3,
			'show_product_price'       => 'yes',
			'apply_discount'           => 'yes',
			'discount_to_apply'        => array(),
			'show_saving'              => 'yes',
			'show_product_name'        => 'yes',
			'show_add_to_cart'         => 'yes',
			'add_to_cart_button_label' => '',
			'show_badge'               => 'yes',
			'badge_text'               => '',
			'badge_background_color'   => '',
			'badge_text_color'         => '',
			'redirect_to_checkout'       => 'no',
		);


		/**
		 * Initialize thank you page campaign.
		 *
		 * @param   YITH_Sales_Thank_You_Page_Campaign|int $campaign  Campaign instance or ID.
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the trigger products
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_trigger_product( $context = 'view' ) {
			return $this->get_prop( 'trigger_product', $context );
		}

		/**
		 * Get the products to show inside the thank-you page
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_products_to_show( $context = 'view' ) {
			return $this->get_prop( 'products_to_show', $context );
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
		 * Get the title
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get if show the product price
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_product_price( $context = 'view' ) {
			return $this->get_prop( 'show_product_price', $context );
		}

		/**
		 * Get if apply a discount
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_apply_discount( $context = 'view' ) {
			return $this->get_prop( 'apply_discount', $context );
		}

		/**
		 * Get the discount to apply
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_discount_to_apply( $context = 'view' ) {
			return $this->get_prop( 'discount_to_apply', $context );
		}

		/**
		 * Get if show saving
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_saving( $context = 'view' ) {
			return $this->get_prop( 'show_saving', $context );
		}

		/**
		 * Get if show the product name
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_product_name( $context = 'view' ) {
			return $this->get_prop( 'show_product_name', $context );
		}

		/**
		 * Get if show add to cart button
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_add_to_cart( $context = 'view' ) {
			return $this->get_prop( 'show_add_to_cart', $context );
		}

		/**
		 * Get the add to cart button label
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_add_to_cart_button_label( $context = 'view' ) {
			return $this->get_prop( 'add_to_cart_button_label', $context );
		}

		/**
		 * Get if show the badge
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_badge( $context = 'view' ) {
			return $this->get_prop( 'show_badge', $context );
		}

		/**
		 * Get the badge text
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_text( $context = 'view' ) {
			return $this->get_prop( 'badge_text', $context );
		}

		/**
		 * Get the badge background color
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_background_color( $context = 'view' ) {
			return $this->get_prop( 'badge_background_color', $context );
		}

		/**
		 * Get the badge text color
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_text_color( $context = 'view' ) {
			return $this->get_prop( 'badge_text_color', $context );
		}

		/**
		 * Get the number of products to show
		 *
		 * @param   string $context  What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 */
		public function get_num_products_to_show( $context = 'view' ) {
			return $this->get_prop( 'num_products_to_show', $context );
		}

		/**
		 * Set the trigger products
		 *
		 * @param   array $trigger_product  Array type, ids.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_trigger_product( $trigger_product ) {
			$this->set_prop( 'trigger_product', $trigger_product );
		}

		/**
		 * Set the products to show inside the thank-you page
		 *
		 * @param   array $products_to_show  Products to show .
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_products_to_show( $products_to_show ) {
			$this->set_prop( 'products_to_show', $products_to_show );
		}

		/**
		 * Set title
		 *
		 * @param   string $title  Set the title.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_title( $title ) {
			$this->set_prop( 'title', $title );
		}

		/**
		 * Set if show the product price
		 *
		 * @param   string $show_product_price  Yes or no.
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
		 * @param   string $apply_discount  Yes or no.
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
		 * @param   array $discount_to_apply  Discount to apply.
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
		 * @param   string $show_product_name  Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_product_name( $show_product_name ) {
			$this->set_prop( 'show_product_name', $show_product_name );
		}


		/**
		 * Set if show the add to cart button
		 *
		 * @param   string $show_add_to_cart  Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_add_to_cart( $show_add_to_cart ) {
			$this->set_prop( 'show_add_to_cart', $show_add_to_cart );
		}

		/**
		 * Set the add to cart button label
		 *
		 * @param   string $add_to_cart_button_label  Button label.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_add_to_cart_button_label( $add_to_cart_button_label ) {
			$this->set_prop( 'add_to_cart_button_label', $add_to_cart_button_label );
		}

		/**
		 * Set show badge
		 *
		 * @param   string $show_badge  Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_badge( $show_badge ) {
			$this->set_prop( 'show_badge', $show_badge );
		}


		/**
		 * Set the badge text
		 *
		 * @param   string $badge_text  Badge text.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_text( $badge_text ) {
			$this->set_prop( 'badge_text', $badge_text );
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
		 * Set the badge background color
		 *
		 * @param   string $badge_background_color  Badge background color.
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
		 * @param   string $badge_text_color  Badge text color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_text_color( $badge_text_color ) {
			$this->set_prop( 'badge_text_color', $badge_text_color );
		}

		/**
		 * Set the number of products to show
		 *
		 * @param   int $num_products_to_show  Number of products to show.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_num_products_to_show( $num_products_to_show ) {
			$this->set_prop( 'num_products_to_show', $num_products_to_show );
		}

		/**
		 * Set show saving
		 *
		 * @param   string $show_saving  Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_saving( $show_saving ) {
			$this->set_prop( 'show_saving', $show_saving );
		}

		/**
		 * Check if the campaign can be applied
		 *
		 * @param int|WC_Product $product The product.
		 *
		 * @return bool
		 */
		public function can_be_applied( $product ) {
			$product          = is_numeric( $product ) ? wc_get_product( $product ) : $product;
			$can_be_applied   = true;
			$products_to_show = $this->get_products_to_show();
			if ( 'products' === $products_to_show['type'] ) {
				$can_be_applied = yith_sales_is_product_in_list( $product, $products_to_show['ids'] );
			}

			return $can_be_applied;
		}

		/**
		 * Check if the campaign is valid
		 *
		 * @param   int|WC_Product $product  Product to check.
		 *
		 * @return bool
		 */
		public function is_valid_for( $product ) {
			$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

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
		 * Return the new price for the specific quantity
		 *
		 * @param   float $price_to_discount  The price to discount.
		 * @param   int   $quantity           The quantity.
		 * @param   array $cart_item_data     The product object.
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
		 * Add the rule in the cart
		 *
		 * @param   array  $cart_item      The cart item.
		 * @param   string $cart_item_key  The item key that allow the apply.
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
				$can_be_applied = array_search( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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
		 * Return if the badge is enabled for the campaign in the current product
		 *
		 * @param   WC_Product $product  Product.
		 *
		 * @return bool
		 */
		public function show_badge_for_product( $product ) {
			return false;
		}

		/**
		 * Add an additional field id to the add to cart form.
		 *
		 * @param   string     $url      URL to change.
		 * @param   WC_Product $product  Product to change.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function add_fields_to_add_to_cart_url( $url, $product ) {
			if ( $this->can_be_applied( $product ) ) {
				$url = add_query_arg( array( 'campaign_id' => $this->get_id() ), $url );
			}

			return $url;
		}

		/**
		 * Add the variation on cart
		 *
		 * @param   array $request      URL to change.
		 *
		 * @return string|boolean
		 * @since  1.0.0
		 * @author YITH
		 */
		public function add_thank_you_page_on_cart( $request ) {
			if ( isset( $request['products'] ) ) {
				if ( isset( $request['products'][0]['product_id'] ) ) {
					$wc_product = wc_get_product( $request['products'][0]['product_id'] );
					$product    = $request['products'][0];
					if ( $wc_product && $this->can_be_applied( $product['product_id'] ) ) {
						$variations = array();
						if ( ! empty( $product['variations'] ) ) {
							foreach ( $product['variations'] as $variation ) {
								$variations[ $variation['key'] ] = $variation['option'];
							}
						}
						$cart_item_data = array( 'yith_sales' => array( 'campaigns' => array( $this->get_id() ) ) );
						try {
							$cart_item_key = WC()->cart->add_to_cart( $product['product_id'], 1, $product['variation_id'], $variations, $cart_item_data );
							return $cart_item_key;
						} catch ( Exception $e ) {
							return false;
						}
					}
				}
			}
		}
	}
}
