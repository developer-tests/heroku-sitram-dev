<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Bogo Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Bogo_Campaign' ) ) {

	/**
	 * The BOGO Campaign class
	 */
	class YITH_Sales_Bogo_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {


		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'bogo';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'        => array(),
			'title'                  => '',
			'show_badge'             => 'yes',
			'badge_text'             => '',
			'badge_background_color' => '',
			'badge_text_color'       => '',
		);


		/**
		 * Initialize bogo campaign.
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
		 * Get if show the badge
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
		 */
		public function set_title( $title ) {
			$this->set_prop( 'title', $title );
		}

		/**
		 * Set type
		 *
		 * @param string $bogo_type Bogo type (same|any).
		 *
		 * @return void
		 * @since  1.0.0
		 * @author YITH
		 */
		public function set_bogo_type( $bogo_type ) {
			$this->set_prop( 'bogo_type', $bogo_type );
		}

		/**
		 * Set the badge text
		 *
		 * @param string $badge_text Badge text.
		 *
		 * @return void
		 * @since  1.0.0
		 * @author YITH
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
		 * @since  1.0.0
		 * @author YITH
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
					case 'default':
						break;
				}
			}

			return $is_valid;
		}


		/**
		 * Add custom data in the cart item
		 *
		 * Check if on cart there are products with the same campaign applied and update the quantity rule.
		 *
		 * @param array     $cart_item_data The cart item data.
		 * @param int       $product_id     Product id added to cart.
		 * @param int       $variation_id   Variation id added to cart.
		 * @param int|float $quantity       Quantity added to cart.
		 *
		 * @return array
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
			// disable the campaign to the list of FBT.
			if ( isset( $cart_item_data['yith_sales_main_cart_item_key'] ) ) {
				return $cart_item_data;
			}
			$cart_item_data['yith_sales_bogo_main_product'] = $this->get_id();

			return $cart_item_data;
		}

		/**
		 * Apply the discount on cart.
		 *
		 * @param array  $cart_item     Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return array|false
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
				$discounted_price  = 0;
				$discount          = array(
					array(
						'discount_value' => 100,
						'discount_type'  => 'percentage',
					),
				);

				$applied = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
			} else {
				WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
			}

			return $applied;
		}

		/**
		 * Update the cart when a product is added
		 *
		 * @param string $cart_item_key  Cart item key.
		 * @param int    $product_id     Product id.
		 * @param int    $quantity       Quantity of product.
		 * @param int    $variation_id   Variation id.
		 * @param array  $variation      Variation list.
		 * @param array  $cart_item_data Additional info.
		 *
		 * @return void
		 * @throws Exception The exception.
		 * @since  1.0.0
		 * @author YITH
		 */
		public function update_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( isset( $cart_item_data['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item_data['yith_sales']['campaigns'] ) ) {
				return;
			}

			foreach ( WC()->cart->cart_contents as $item ) {
				if ( isset( $item['yith_sales_main_cart_bogo_item_key'] ) && $item['yith_sales_main_cart_bogo_item_key'] === $cart_item_key ) {
					return;
				}
			}

			$cart_item      = WC()->cart->get_cart_item( $cart_item_key );
			$product        = wc_get_product( $cart_item['data']->get_id() );
			$stock_quantity = $product->get_stock_quantity();

			if ( ! $product->has_enough_stock( 2 * $quantity ) ) {
				$quantity = (int) $stock_quantity / 2;
				WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = (int) $stock_quantity / 2;
				/* translators: %1$s is the quantity. */
				wc_add_notice( sprintf( __( 'You cannot add that amount to the cart &mdash; we have %1$s in stock', 'wonder-cart' ), $stock_quantity ), 'error' );
			}

			$cart_item_data = array(
				'yith_sales'                         => array(
					'campaigns' => array(
						$this->get_id(),
					),
				),
				'yith_sales_main_cart_bogo_item_key' => $cart_item_key,
			);

			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

		}


		/**
		 * Check the cart content when the cart is updated.
		 *
		 * @param string $cart_item_key Cart item key.
		 * @param array  $cart_item     Cart item.
		 * @param int    $quantity      Quantity updated.
		 *
		 * @return array
		 * @since  1.0.0
		 * @author YITH
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {

			if ( isset( $cart_item['yith_sales_bogo_main_product'] ) && $this->get_id() === $cart_item['yith_sales_bogo_main_product'] ) {
				$product        = wc_get_product( $cart_item['data']->get_id() );
				$stock_quantity = $product->get_stock_quantity();

				foreach ( WC()->cart->cart_contents as $item_key => $item ) {
					if ( isset( $item['yith_sales_main_cart_bogo_item_key'] ) && $item['yith_sales_main_cart_bogo_item_key'] === $cart_item_key ) {
						if ( 0 === $quantity ) {
							WC()->cart->remove_cart_item( $item_key );
						} else {
							if ( $product->has_enough_stock( 2 * $quantity ) ) {
								WC()->cart->cart_contents[ $item_key ]['quantity'] = $quantity;
							} else {
								WC()->cart->cart_contents[ $item_key ]['quantity']      = (int) $stock_quantity / 2;
								WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = (int) $stock_quantity / 2;
								/* translators: %1$s is the stock quantity */
								wc_add_notice( sprintf( __( 'You cannot add that amount to the cart &mdash; we have %1$s in stock', 'wonder-cart' ), $stock_quantity ), 'error' );
							}
						}
					}
				}
			}

			return array();

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

		/**
		 * Hide the remove link from cart for single cart item
		 *
		 * @param string $remove_link   HTML code of remove item link.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function hide_remove_link( $remove_link, $cart_item_key ) {
			return '';
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


	}
}
