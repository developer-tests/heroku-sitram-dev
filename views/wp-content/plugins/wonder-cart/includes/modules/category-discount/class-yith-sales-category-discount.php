<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Category Discount Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Category_Discount_Campaign' ) ) {

	/**
	 * Category Discount Campaign
	 */
	class YITH_Sales_Category_Discount_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'category-discount';


		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'        => array(),
			'category_discount'      => '[]',
			'show_saving'            => 'no',
			'title'                  => '',
			'show_badge'             => 'yes',
			'badge_text'             => '',
			'badge_background_color' => '',
			'badge_text_color'       => '',
		);


		/**
		 * Initialize category discount campaign.
		 *
		 * @param YITH_Sales_Category_Discount_Campaign|int $campaign Campaign instance or ID.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}


		/**
		 * Get the category discount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_category_discount( $context = 'view' ) {
			return $this->get_prop( 'category_discount', $context );
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
		 *
		 * @since 1.0.0
		 */
		public function set_badge_text_color( $badge_text_color ) {
			$this->set_prop( 'badge_text_color', $badge_text_color );
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
		 * Set the category discount
		 *
		 * @param string $category_discount Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_category_discount( $category_discount ) {
			$this->set_prop( 'category_discount', $category_discount );
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
					case 'categories':
						$is_valid = yith_sales_is_product_in_taxonomy_list( $product, $trigger['ids'] );
						break;
				}
			}

			return apply_filters( 'yith_sales_price_valid_campaign_by_product', $is_valid, $product, $this );

		}

		/**
		 * Calculate discount
		 *
		 * @param float      $price   Price.
		 * @param WC_Product $product Product to discount.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function calculate_discount( $price, $product ) {

			$discount = $this->get_discount_to_apply_to_product( $product );
			if ( $discount ) {
				$discount = yith_sales_calculate_discount( $price, $discount );
			}

			return $discount;
		}

		/**
		 * Get the saving string
		 *
		 * @param WC_Product $product Product.
		 *
		 * @return string
		 */
		public function get_saving( $product ) {
			return yith_sales_get_saving( $product->get_price( 'edit' ), $this->calculate_discount( $product->get_price( 'edit' ), $product ) );
		}

		/**
		 * Get the text of badge based on product
		 *
		 * @param WC_Product $product Product.
		 *
		 * @return string
		 */
		public function get_badge_text_by_product( $product ) {
			$badge_text = $this->get_badge_text();

			if ( false === strpos( $badge_text, '%discount%' ) ) {
				return $badge_text;
			}

			$category_discount = $this->get_category_discount();
			$discount          = 0;
			foreach ( $category_discount as $discount ) {
				if ( yith_sales_is_product_in_taxonomy_list( $product, array( $discount['category'] ) ) ) {
					$discount_text = 'fixed' === $discount['discount_type'] ? wc_price( $discount['discount_value'] ) : $discount['discount_value'] . '%';

					return str_replace( '%discount%', $discount_text, $badge_text );

				}
			}
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float      $price_to_discount The price to discount.
		 * @param WC_Product $cart_item_data    Carti item data.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function get_discounted_price( $price_to_discount, $cart_item_data ) {

			$discounted_price = $this->calculate_discount( $price_to_discount, $cart_item_data );

			$price = (float) $price_to_discount - $discounted_price;

			return max( 0, $price );
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

			return $applied;
		}


		/**
		 * Get the discount to apply to a product
		 *
		 * @param WC_Product $product The product.
		 *
		 * @return bool|array
		 */
		public function get_discount_to_apply_to_product( $product ) {
			$category_discount = $this->get_category_discount();
			$discount          = false;
			foreach ( $category_discount as $discount ) {
				if ( yith_sales_is_product_in_taxonomy_list( $product, array( $discount['category'] ) ) ) {
					return $discount;
				}
			}

			return $discount;
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

			if ( is_tax() ) {
				$term = get_queried_object();
				if ( $term ) {
					$categories     = $this->get_trigger_product();
					$campaign_title = $this->get_title();
					if ( $categories && ! empty( $campaign_title ) && ! empty( $categories['ids'] ) && in_array( $term->term_id, $categories['ids'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$discounts = $this->get_category_discount();
						foreach ( $discounts as $discount ) {
							if ( $term->term_id === $discount['category'] ) {
								$discount_text = 'fixed' === $discount['discount_type'] ? wc_price( $discount['discount_value'] ) : $discount['discount_value'] . '%';
								$title         = str_replace( '%discount%', $discount_text, $campaign_title );
								break;
							}
						}
					}
				}
			}

			return do_blocks( $title );
		}

	}
}
