<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Shop Discount Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Shop_Discount_Campaign' ) ) {
	/**
	 * The Shop discount campaign
	 */
	class YITH_Sales_Shop_Discount_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'shop-discount';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'        => array(),
			'exclude_products'       => 'no',
			'exclude_products_list'  => array(),
			'discount_to_apply'      => array(),
			'show_badge'             => 'yes',
			'badge_text'             => '',
			'badge_background_color' => '',
			'badge_text_color'       => '',
			'show_saving'            => 'no',
			'title'                  => '',
		);


		/**
		 * Initialize shop discount campaign.
		 *
		 * @param YITH_Sales_Shop_Discount_Campaign|int $campaign Campaign instance or ID.
		 *
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
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
			$badge_text     = $this->get_prop( 'badge_text', $context );
			$discount       = $this->get_discount_to_apply();
			$discount_value = 'fixed' === $discount['discount_type'] ? wc_price( $discount['discount_value'] ) : $discount['discount_value'] . '%';

			return str_replace( '%discount%', $discount_value, $badge_text );

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
		 * Get if there are products to exclude
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_exclude_products( $context = 'view' ) {
			return $this->get_prop( 'exclude_products', $context );
		}


		/**
		 * Get the list of products to exclude from discount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_exclude_products_list( $context = 'view' ) {
			return $this->get_prop( 'exclude_products_list', $context );
		}


		/**
		 * Get the trigger products
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
		 * Get the discount to apply
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_discount_to_apply( $context = 'view' ) {
			return $this->get_prop( 'discount_to_apply', $context );
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
		 * Set if there are products to exclude
		 *
		 * @param string $exclude_products Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_exclude_products( $exclude_products ) {
			$this->set_prop( 'exclude_products', $exclude_products );
		}

		/**
		 * Set the list of products to exclude from discount
		 *
		 * @param array $exclude_products_list Array type, ids.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_exclude_products_list( $exclude_products_list ) {
			$this->set_prop( 'exclude_products_list', $exclude_products_list );
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
		 * Set show badge
		 *
		 * @param string $show_badge Yes or no.
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
		 * @param string $background_color Badge background color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_background_color( $background_color ) {
			$this->set_prop( 'badge_background_color', $background_color );
		}

		/**
		 * Set the badge text color
		 *
		 * @param string $text_color Badge text color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_text_color( $text_color ) {
			$this->set_prop( 'badge_text_color', $text_color );
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
		 * Check if the campaign is valid
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return bool
		 */
		public function is_valid_for( $product ) {

			if ( 'no' === $this->get_exclude_products() ) {
				return true;
			}

			$product_excluded = $this->get_exclude_products_list();
			$is_valid         = false;
			if ( isset( $product_excluded['type'], $product_excluded['ids'] ) ) {
				switch ( $product_excluded['type'] ) {
					case 'products':
						if ( is_array( $product_excluded['ids'] ) && ! yith_sales_is_product_in_list( $product, $product_excluded['ids'] ) ) {
							$is_valid = true;
						}
						break;
					case 'categories':
						$is_valid = ! yith_sales_is_product_in_taxonomy_list( $product, $product_excluded['ids'] );
						break;
				}
			}

			return apply_filters( 'yith_sales_price_valid_campaign_by_product', $is_valid, $product, $this );
		}

		/**
		 * Calculate discount
		 *
		 * @param float      $price   Product Price.
		 * @param WC_Product $product Product.
		 *
		 * @return float
		 */
		public function calculate_discount( $price, $product ) {
			$discount = $this->get_discount_to_apply();

			return yith_sales_calculate_discount( $price, $discount );
		}

		/**
		 * Get the saving string
		 *
		 * @param WC_Product $product Product.
		 *
		 * @return string
		 */
		public function get_saving( $product ) {
			return yith_sales_get_saving( $product->get_price( 'edit' ), yith_sales_calculate_discount( $product->get_price( 'edit' ), $this->get_discount_to_apply() ) );
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float $price_to_discount The price to discount.
		 * @param array $cart_item_data    The product object.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function get_discounted_price( $price_to_discount, $cart_item_data ) {

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
			if ( $this->can_be_applied_with_other_campaigns( $cart_item ) ) {
				if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
					$cart_item_adj  = isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : false;
					$discounts      = $cart_item_adj['yith_sales_discounts']['applied_discounts'];
					$can_be_applied = array_search( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				}
				if ( $can_be_applied ) {
					$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key, 'edit' );
					$discounted_price  = $this->get_discounted_price( $price_to_discount, $cart_item['data'] );
					$discount          = $this->get_discount_to_apply_to_product( $cart_item['data'] );
					$applied           = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
				} else {
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_adj['yith_sales_discounts']['price_adjusted'] );
				}
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
		 * Return the title to display on category page
		 *
		 * @return string|bool
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_title_to_display() {
			$title = false;

			if ( is_shop() ) {
				$campaign_title = $this->get_title();
				$title          = wp_kses_post( $campaign_title );
			} elseif ( is_tax() ) {
				$term = get_queried_object();
				if ( 'yes' === $this->get_exclude_products() ) {
					$list = $this->get_exclude_products_list();
					if ( 'categories' === $list['type'] && ! in_array( $term->term_id, $list['ids'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$campaign_title = $this->get_title();
						$title          = wp_kses_post( $campaign_title );
					}
				}
			}

			if ( $title ) {
				$discount       = $this->get_discount_to_apply();
				$discount_value = 'fixed' === $discount['discount_type'] ? wc_price( $discount['discount_value'] ) : $discount['discount_value'] . '%';
				$title          = str_replace( '%discount%', $discount_value, $title );
			}

			return do_blocks( $title );
		}
	}
}
