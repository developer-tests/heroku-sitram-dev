<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Quantity Discount Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Quantity_Discount_Campaign' ) ) {
	/**
	 * The Quantity campaign
	 */
	class YITH_Sales_Quantity_Discount_Campaign extends Abstract_YITH_Sales_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'quantity-discount';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'         => array(),
			'title'                   => '',
			'discount_rules'          => array(),
			'show_price_for_unit'     => 'yes',
			'show_saving'             => 'yes',
			'show_total_amount'       => 'yes',
			'show_product_image'      => 'yes',
			'show_quantity_badge'     => 'yes',
			'item_background_color'   => '',
			'item_border_color'       => '',
			'badge_text_color'        => '',
			'badge_background_color'  => '',
			'ribbon_text_color'       => '',
			'ribbon_background_color' => '',
		);

		/**
		 * Initialize quantity discount campaign.
		 *
		 * @param YITH_Sales_Quantity_Discount_Campaign|int $campaign Campaign instance or ID.
		 *
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the products where apply the quantity discount
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
		 * Get the discount rules
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_discount_rules( $context = 'view' ) {
			return $this->get_prop( 'discount_rules', $context );
		}

		/**
		 * Get if show price for unit
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_show_price_for_unit( $context = 'view' ) {
			return $this->get_prop( 'show_price_for_unit', $context );
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
		 * Get if show the total amount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_show_total_amount( $context = 'view' ) {
			return $this->get_prop( 'show_total_amount', $context );
		}

		/**
		 * Get if show the product image
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_show_product_image( $context = 'view' ) {
			return $this->get_prop( 'show_product_image', $context );
		}

		/**
		 * Get if show the quantity badge
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_quantity_badge( $context = 'view' ) {
			return $this->get_prop( 'show_quantity_badge', $context );
		}

		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get the item background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_item_background_color( $context = 'view' ) {
			return $this->get_prop( 'item_background_color', $context );
		}

		/**
		 * Get the item border color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_item_border_color( $context = 'view' ) {
			return $this->get_prop( 'item_border_color', $context );
		}

		/**
		 * Get the quantity badge background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_badge_background_color( $context = 'view' ) {
			return $this->get_prop( 'badge_background_color', $context );
		}

		/**
		 * Get the ribbon text color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_ribbon_text_color( $context = 'view' ) {
			return $this->get_prop( 'ribbon_text_color', $context );
		}

		/**
		 *  Get the ribbon background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_ribbon_background_color( $context = 'view' ) {
			return $this->get_prop( 'ribbon_background_color', $context );
		}

		/**
		 * Get the quantity badge text color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_badge_text_color( $context = 'view' ) {
			return $this->get_prop( 'badge_text_color', $context );
		}

		/**
		 * Set the products where apply the quantity discount
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
		 * Set the discount rules
		 *
		 * @param array $discount_rules Discount rules.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_discount_rules( $discount_rules ) {
			$this->set_prop( 'discount_rules', $discount_rules );
		}

		/**
		 * Set if show price for unit
		 *
		 * @param string $show_price_for_unit Yes or not.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_price_for_unit( $show_price_for_unit ) {
			$this->set_prop( 'show_price_for_unit', $show_price_for_unit );
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
		 * Set if show the total amount
		 *
		 * @param string $show_total_amount Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_total_amount( $show_total_amount ) {
			$this->set_prop( 'show_total_amount', $show_total_amount );
		}

		/**
		 * Set if show the product image
		 *
		 * @param string $show_product_image Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_product_image( $show_product_image ) {
			$this->set_prop( 'show_product_image', $show_product_image );
		}

		/**
		 * Set if show the quantity badge
		 *
		 * @param string $show_quantity_badge Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_quantity_badge( $show_quantity_badge ) {
			$this->set_prop( 'show_quantity_badge', $show_quantity_badge );
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
		 * Set the item background color
		 *
		 * @param string $item_background_color Color to set.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_item_background_color( $item_background_color ) {
			$this->set_prop( 'item_background_color', $item_background_color );
		}

		/**
		 * Set the item border color
		 *
		 * @param string $item_border_color Color to set.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_item_border_color( $item_border_color ) {
			$this->set_prop( 'item_border_color', $item_border_color );
		}

		/**
		 * Set the quantity badge background color
		 *
		 * @param string $badge_background_color Color to set.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_badge_background_color( $badge_background_color ) {
			$this->set_prop( 'badge_background_color', $badge_background_color );
		}

		/**
		 * Set the ribbon text color
		 *
		 * @param string $ribbon_text_color Color to set.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_ribbon_text_color( $ribbon_text_color ) {
			$this->set_prop( 'ribbon_text_color', $ribbon_text_color );
		}

		/**
		 * Set the ribbon background color
		 *
		 * @param string $ribbon_background_color Color to set.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_ribbon_background_color( $ribbon_background_color ) {
			$this->set_prop( 'ribbon_background_color', $ribbon_background_color );
		}

		/**
		 * Check if the campaign needs to hide the product form in single product page
		 *
		 * @return bool
		 */
		public function hide_product_form() {
			return true;
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
		 * Show the campaign on product
		 *
		 * @param WC_Product $product Current product.
		 *
		 * @return void
		 */
		public function show_campaign( $product ) {
			$options = array(
				'campaignID' => $this->get_id(),
				'productID'  => $product->get_id(),
			);
			// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
			// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
			?>
            <div class="yith-sales-quantity-discount"
                 data-options="
					<?php
					echo yith_sales_get_json( $options );
					?>
			">
            </div>
			<?php
            // phpcs:enable
		}


		/**
		 * Discount to apply to the product.
		 *
		 * @param array $cart_item Cart item.
		 *
		 * @return array|false
		 */
		public function get_discount_to_apply_to_product( $cart_item ) {
			if ( ! isset( $cart_item['yith_quantity_discount']['rule'] ) || ! $cart_item['yith_quantity_discount']['rule'] ) {
				return false;
			}
			$rule = $cart_item['yith_quantity_discount']['rule'];

			return $rule ? array(
				'discount_type'  => $rule['discountType'],
				'discount_value' => $rule['discount'],
			) : array(
				'discount_type'  => 'fixed',
				'discount_value' => 0,
			);
		}

		/**
		 * Return the new price for the specific quantity
		 *
		 * @param float      $price_to_discount The price to discount.
		 * @param int        $quantity          The quantity.
		 * @param WC_Product $cart_item_data    Carti item data.
		 * @param array      $cart_item         Cart item.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function get_discounted_price( $price_to_discount, $quantity, $cart_item_data, $cart_item ) {

			$discount = $this->get_discount_to_apply_to_product( $cart_item );

			if ( ! $discount ) {
				return $price_to_discount;
			}
			$rule                    = $this->find_the_best_rule_for_this_quantity( $quantity );
			$fixed_discount_quantity = ( 'fixed' === $discount['discount_type'] && 0 !== $discount['discount_value'] ) ? $rule['unit'] : 1;
			$discounted_price        = $this->calculate_discount( $price_to_discount, $discount );
			$price                   = $price_to_discount - ( $discounted_price / $fixed_discount_quantity );

			return max( $price, 0 );
		}

		/**
		 * Calculate discount
		 *
		 * @param float $price    Price.
		 * @param array $discount Discount to apply.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public function calculate_discount( $price, $discount ) {
			return yith_sales_calculate_discount( $price, $discount );
		}

		/**
		 * Find the best rule of campaign based on the quantity
		 *
		 * @param int $quantity Quantity to check.
		 *
		 * @return array|false
		 */
		public function find_the_best_rule_for_this_quantity( $quantity ) {
			$discount_rules = $this->get_discount_rules();
			if ( empty( $discount_rules ) ) {
				return false;
			}
			$best_rule = $quantity >= $discount_rules[0]['unit'] ? $discount_rules[0] : false;
			if ( $best_rule ) {
				foreach ( $discount_rules as $rule ) {
					if ( $rule['unit'] > $best_rule['unit'] && $quantity >= $rule['unit'] ) {
						$best_rule = $rule;
					}
				}
			}

			return $best_rule;
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
		 * @throws Exception The exception.
		 */
		public function get_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {

			$cart_item_data = parent::get_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity );
			$qty_on_cart    = $this->get_total_quantity_on_cart( $product_id );
			if ( $qty_on_cart > 0 ) {
				$new_qty = $quantity + $this->get_total_quantity_on_cart( $product_id );
				$rule    = $this->find_the_best_rule_for_this_quantity( $new_qty );

				if ( $rule ) {
					foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $product_id ) {
							WC()->cart->remove_cart_item( $cart_item_key );
							$this->add_rule_to_cart( $rule, $cart_item['data'], $cart_item['quantity'], $cart_item['variation_id'], $cart_item['variation'] );
						}
					}

					$cart_item_data['yith_quantity_discount']['rule'] = $rule;
				}
			}

			return $cart_item_data;
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
			if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
				$discounts      = $cart_item['yith_sales_discounts']['applied_discounts'];
				$can_be_applied = in_array( $this->get_id(), array_column( $discounts, 'id' ) ) !== false; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			}

			if ( ! isset( $cart_item['yith_quantity_discount'] ) || ! $cart_item['yith_quantity_discount']['rule'] ) {
				return $applied;
			}

			if ( $can_be_applied ) {
				add_filter( 'yith_sales_save_discount_in_cart', '__return_true' );
				$price_to_discount = $this->get_price_to_discount( $cart_item, $cart_item_key );
				$discounted_price  = $this->get_discounted_price( $price_to_discount, $cart_item['quantity'], $cart_item['data'], $cart_item );
				$discount          = $this->get_discount_to_apply_to_product( $cart_item );
				$applied           = $this->save_discount_in_cart( $cart_item_key, $price_to_discount, $discounted_price, $discount );
				remove_filter( 'yith_sales_save_discount_in_cart', '__return_true' );
			} else {
				WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item['yith_sales_discounts']['price_adjusted'] );
			}

			return $applied;
		}

		/**
		 * Counts how many cart items with this campaign and products are on cart.
		 *
		 * @param int         $product_id    Product id added to cart.
		 * @param string|bool $cart_item_key Cart item Key.
		 *
		 * @return int
		 */
		public function get_total_quantity_on_cart( $product_id, $cart_item_key = false ) {
			$total = 0;

			foreach ( WC()->cart->cart_contents as $cart_key => $cart_item ) {
				if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $product_id ) {
					if ( ! $cart_item_key || $cart_key !== $cart_item_key ) {
						$total += $cart_item['quantity'];
					}
				}
			}

			return $total;
		}

		/**
		 * Add the product on cart.
		 *
		 * @param array $request Request arguments.
		 *
		 * @return array
		 * @throws Exception The exception.
		 */
		public function add_to_cart( $request ) {
			$product           = sanitize_text_field( $request['yith-sales-add-to-cart'] );
			$rule              = $request['rule'];
			$quantity_to_add   = $rule['unit'];
			$new_cart_item_key = false;

			$data = array();
			if ( $product ) {
				$product = wc_get_product( $product );
			}
			if ( ! $product || ! $this->is_valid_for( $product ) ) {
				return new WP_Error( 'add_to_cart', 'Campaign not valid for this product' );
			}
			if ( $product->get_type() !== 'variable' ) {
				$new_cart_item_key = false;
				if ( WC()->cart->cart_contents ) {
					foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $product->get_id() ) {
							$products_in_cart = $quantity_to_add + $cart_item['quantity'];
							$rule             = $this->find_the_best_rule_for_this_quantity( $products_in_cart );
							WC()->cart->set_quantity( $cart_item_key, $products_in_cart, true );
							WC()->cart->cart_contents[ $cart_item_key ]['yith_quantity_discount'] = array(
								'rule' => $rule,
							);
							$quantity_to_add   = $products_in_cart;
							$new_cart_item_key = true;
							break;
						}
					}
				}
				if ( ! $new_cart_item_key ) {
					$new_cart_item_key = $this->add_rule_to_cart( $rule, $product, $quantity_to_add );
				}
			} else {
				if ( WC()->cart->cart_contents ) {
					$products_in_cart = $quantity_to_add;
					foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $product->get_id() ) {
							$products_in_cart += $cart_item['quantity'];
						}
					}

					$rule = $this->find_the_best_rule_for_this_quantity( $products_in_cart );
				}

				if ( isset( $request['list'] ) ) {
					foreach ( $request['list'] as $single_variation ) {
						if ( isset( $single_variation['selectedVariation'] ) ) {
							$variation = array();
							foreach ( $single_variation['selectedAttributes'] as $attribute ) {
								$variation[ $attribute['key'] ] = $attribute['option'];
							}
							$quantity     = $single_variation['quantity'];
							$variation_id = $single_variation['selectedVariation']['variationID'];

							foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
								if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $product->get_id() ) {
									if ( $cart_item['variation_id'] === $variation_id && serialize( $cart_item['variation'] ) === serialize( $variation ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
										$quantity += $cart_item['quantity'];
										WC()->cart->remove_cart_item( $cart_item_key );
									} else {
										WC()->cart->remove_cart_item( $cart_item_key );
										$this->add_rule_to_cart( $rule, $cart_item['data'], $cart_item['quantity'], $cart_item['variation_id'], $cart_item['variation'] );
									}
								}
							}
							$new_cart_item_key = $this->add_rule_to_cart( $rule, $product, $quantity, $variation_id, $variation );
						}
					}
				}
			}

			if ( false !== $new_cart_item_key ) {
				$added_to_cart[ $product->get_id() ] = 1;
				wc_add_to_cart_message( $added_to_cart, false );
				$data = array(
					'redirect_to' => $product->get_permalink(),
				);
			}

			return $data;
		}

		/**
		 * Check the cart when an update is made.
		 *
		 * Refresh the cart items rule
		 *
		 * @param int   $cart_item_key Cart item key.
		 * @param array $cart_item     Cart item.
		 * @param int   $quantity      Quantity added to cart.
		 *
		 * @return array
		 */
		public function check_cart_on_update( $cart_item_key, $cart_item, $quantity = 0 ) {
			$product_id = $cart_item['product_id'];
			$new_qty    = $quantity + $this->get_total_quantity_on_cart( $product_id, $cart_item_key );
			$rule       = $this->find_the_best_rule_for_this_quantity( $new_qty );
			foreach ( WC()->cart->cart_contents as $cart_key => $item ) {
				if ( isset( $cart_item['yith_sales']['campaigns'] ) && in_array( $this->get_id(), $cart_item['yith_sales']['campaigns'], true ) && $cart_item['product_id'] === $item['data']->get_id() ) {
					WC()->cart->cart_contents[ $cart_key ]['yith_quantity_discount']['rule'] = $rule;
				}
			}

			return array();
		}

		/**
		 * Discount to apply to the product.
		 *
		 * @param array      $rule         Rule to apply.
		 * @param WC_Product $product      Main product to add to cart.
		 * @param int|float  $quantity     Quantity of product to add.
		 * @param int        $variation_id Variation id.
		 * @param array      $variation    Variations list.
		 *
		 * @return string|bool
		 * @throws Exception The exception.
		 */
		public function add_rule_to_cart( $rule, $product, $quantity, $variation_id = 0, $variation = array() ) {
			$cart_item_data = array(
				'yith_sales'             => array(
					'campaigns' => array(
						$this->get_id(),
					),
				),
				'yith_quantity_discount' => array(
					'rule' => $rule,
				),
			);

			$cart_item_key = WC()->cart->add_to_cart(
				$product->get_id(),
				$quantity,
				$variation_id,
				$variation,
				$cart_item_data,
			);

			return $cart_item_key;
		}

		/**
		 * Override the HTML product price
		 *
		 * @param string     $html_price HTML price.
		 * @param WC_Product $product    Product.
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITH
		 */
		public function get_html_product_price( $html_price, $product ) {
			$rule = $this->find_the_best_rule_for_this_quantity( 1 );
			if ( ! $rule ) {
				return $html_price;
			}
			if ( $product->get_type() !== 'variable' ) {
				$base_price = (float) $product->get_regular_price();

				if ( $rule && 1 === $rule['unit'] ) {
					$discount = $this->calculate_discount(
						$base_price,
						array(
							'discount_type'  => $rule['discountType'],
							'discount_value' => $rule['discount'],
						)
					);

					$new_price = $base_price - $discount;

					if ( $new_price < $base_price ) {
						$html_price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $base_price ) ), wc_get_price_to_display( $product, array( 'price' => $new_price ) ) ) . $product->get_price_suffix();
					} else {
						$html_price = wc_price( wc_get_price_to_display( $product, array( 'price' => $base_price ) ) );
					}
				} else {
					$html_price = wc_price( wc_get_price_to_display( $product, array( 'price' => $base_price ) ) );
				}
			} else {
				$prices        = $product->get_variation_prices( true );
				$min_reg_price = current( $prices['regular_price'] );
				$max_reg_price = end( $prices['regular_price'] );
				if ( $rule && 1 === $rule['unit'] ) {
					$min_reg_price = $min_reg_price - $this->calculate_discount(
						$min_reg_price,
						array(
							'discount_type'  => $rule['discountType'],
							'discount_value' => $rule['discount'],
						)
					);
					$max_reg_price = $max_reg_price - $this->calculate_discount(
						$max_reg_price,
						array(
							'discount_type'  => $rule['discountType'],
							'discount_value' => $rule['discount'],
						)
					);
				}
				if ( $min_reg_price !== $max_reg_price ) {
					$html_price = sprintf( '%s - %s', wc_price( wc_get_price_to_display( $product, array( 'price' => $min_reg_price ) ) ), wc_price( wc_get_price_to_display( $product, array( 'price' => $max_reg_price ) ) ) );
				} else {
					$html_price = wc_price( wc_get_price_to_display( $product, array( 'price' => $min_reg_price ) ) );
				}
			}

			return $html_price;
		}

		/**
		 * Remove add to cart on single product
		 *
		 * @return true
		 */
		public function remove_add_to_cart() {
			return true;
		}
	}


}
