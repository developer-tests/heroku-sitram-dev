<?php
/**
 * Dynamic Controller
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Controllers
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Class
 */
class YITH_Sales_Dynamic_Controller {

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $campaigns = array();


	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $valid_campaign_by_product = array();


	/**
	 * Construct function of controller
	 *
	 * @param array $campaigns Campaigns ordered by priority.
	 */
	public function __construct( $campaigns ) {

		$this->campaigns = $this->process_campaigns( $campaigns );

		if ( yith_sales_is_using_block_template_in_single_product() ) {

			add_filter( 'render_block', array( $this, 'display_campaign_block' ), 10, 2 );
		} else {
			add_action( 'woocommerce_single_product_summary', array( $this, 'display_campaign' ), 20 );
			add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'display_campaign_title' ), 20 );
		}

		add_filter( 'woocommerce_get_price_html', array( $this, 'get_html_product_price' ), 99, 2 );
		add_filter( 'woocommerce_product_is_on_sale', array( $this, 'maybe_remove_is_on_sale_discount' ), 99, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'change_add_to_cart' ), 1000, 2 );
		add_filter( 'woocommerce_before_add_to_cart_button', array( $this, 'add_fields_to_add_to_cart_form' ), 1000 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_fields_to_add_to_cart_url' ), 1000, 2 );

		add_action( 'woocommerce_add_to_cart', array( $this, 'update_cart' ), 1000, 6 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'remove_quantity' ), 1000, 3 );
		add_filter(
			'woocommerce_store_api_product_quantity_editable',
			array(
				$this,
				'remove_quantity_on_wc_blocks',
			),
			1000,
			3
		);
		add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'remove_quantity_on_widget' ), 1000, 3 );
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'hide_remove_link' ), 1000, 2 );

		add_filter( 'woocommerce_quantity_input_args', array( $this, 'change_input_quantity_value' ), 100, 2 );
		add_filter(
			'yith_sales_price_valid_campaign_by_product',
			array(
				$this,
				'check_sales_price_valid_campaign',
			),
			10,
			2
		);
		add_filter( 'yith_sales_store_api_check_campaign_on_cart_item', array( $this, 'check_add_to_cart_store_api' ), 10, 2 );
	}

	/**
	 * Disable the price campaigns if a quantity discount campaign is applied to the same product.
	 *
	 * @param bool $is_valid If the price campaigns.
	 * @param WC_Product $product Current product to check.
	 *
	 * @return boolean
	 */
	public function check_sales_price_valid_campaign( $is_valid, $product ) {
		if ( $is_valid ) {
			$dynamic_campaigns = $this->get_valid_campaign_by_product( $product );
			if ( $dynamic_campaigns && 'quantity-discount' === $dynamic_campaigns->get_type() && $dynamic_campaigns->is_active() ) {
				$is_valid = false;
			}
		}

		return $is_valid;
	}

	/**
	 * Update the cart when a product is added
	 *
	 * @param string $cart_item_key Cart item key.
	 * @param int $product_id Product id.
	 * @param int $quantity Quantity of product.
	 * @param int $variation_id Variation id.
	 * @param array $variation Variation list.
	 * @param array $cart_item_data Additional info.
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	public function update_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

		if ( isset( $cart_item['yith_sales_main_cart_item_key'] ) ) {
			return;
		}

		if ( isset( $cart_item['yith_sales_bogo_main_product'] ) ) {
			$campaign = yith_sales_get_campaign( $cart_item['yith_sales_bogo_main_product'] );
			if ( in_array( $campaign, $this->campaigns ) && method_exists( $campaign, 'update_cart' ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$campaign->update_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );
			}
		}
	}

	/**
	 * Show campaign
	 */
	public function display_campaign( $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			global $product;
		}
		$campaign_html = '';
		foreach ( $this->campaigns as $campaign ) {
			if ( $campaign->is_valid_for( $product ) && is_callable( array( $campaign, 'show_campaign' ) ) ) {
				ob_start();
				$campaign->show_campaign( $product );
				$campaign_html = ob_get_clean();
				break;
			}
		}

		if ( $echo ) {
			echo $campaign_html;
		} else {
			return $campaign_html;
		}

	}

	/**
	 * Print the campaign in blocks
	 *
	 * @param string $content The content.
	 * @param array $parsed_block The parsed block.
	 *
	 * @return string
	 */
	public function display_campaign_block( $content, $parsed_block ) {

		if ( 'core/post-excerpt' === $parsed_block['blockName'] && ( isset( $parsed_block['attrs']['__woocommerceNamespace'] ) && 'woocommerce/product-query/product-summary' === $parsed_block['attrs']['__woocommerceNamespace'] ) ) {
			$after   = $this->display_campaign( null, false );
			$content = $content . $after;
		}
		if ( 'woocommerce/add-to-cart-form' === $parsed_block['blockName'] ) {
			$before  = $this->display_campaign_title( null, false );
			$content = $before . $content;
		}

		return $content;
	}

	/**
	 * Add campaign id to add to cart.
	 *
	 * @param array $args The argument.
	 * @param WC_Product $product Product.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	public function change_add_to_cart( $args, $product ) {
		$campaign = $this->get_valid_campaign_by_product( $product );

		if ( $campaign ) {
			$args['attributes']['data-campaign_id'] = $campaign->get_id();
		}

		return $args;
	}

	/**
	 * Add an additional field id to the add to cart form.
	 *
	 * @return void
	 *
	 * @since  1.0.0
	 * @author YITH
	 */
	public function add_fields_to_add_to_cart_form() {
		global $product;
		$campaign = $this->get_valid_campaign_by_product( $product );

		if ( $campaign ) {
			echo '<input type="hidden" name="campaign_ids[]" value="' . esc_attr( $campaign->get_id() ) . '"/>';
		}
	}


	/**
	 * Add an additional field id to the add to cart form.
	 *
	 * @param string $url URL to change.
	 * @param WC_Product $product Product to change.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function add_fields_to_add_to_cart_url( $url, $product ) {
		global $product;
		$campaign = $this->get_valid_campaign_by_product( $product );

		if ( $campaign ) {
			$url = add_query_arg( array( 'campaign_id' => $campaign->get_id() ), $url );
		}

		return $url;
	}

	/**
	 * Return a valid campaign for a product
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return YITH_Sales_Shop_Discount_Campaign|YITH_Sales_Category_Discount_Campaign|bool
	 * @since  1.0.0
	 * @author YITH
	 */
	public function get_valid_campaign_by_product( $product ) {
		if ( ! $product ) {
			return false;
		}
		$product_id     = $product->get_id();
		$valid_campaign = false;

		foreach ( $this->campaigns as $campaign ) {

			if ( $campaign->is_valid_for( $product ) ) {
				$this->valid_campaign_by_product[ $product_id ] = $campaign;
				$valid_campaign                                 = $campaign;
				break;
			}
		}

		return $valid_campaign;
	}

	/**
	 * Return the list of campaign as one level
	 *
	 * @param array $grouped_campaings Campaigns grouped.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	public function process_campaigns( $grouped_campaings ) {
		$ordered_campaigns = array();
		foreach ( $grouped_campaings as $group ) {
			$ordered_campaigns = array_merge( $ordered_campaigns, $group );
		}

		return $ordered_campaigns;
	}

	/**
	 * Disable on sale discount if on product there is a quantity discount
	 *
	 * @param bool $onsale if the product is onsale.
	 * @param WC_Product $product The product.
	 *
	 * @return bool
	 * @since  1.0.0
	 * @author YITH
	 */
	public function maybe_remove_is_on_sale_discount( $onsale, $product ) {
		$campaign = $this->get_valid_campaign_by_product( $product );
		if ( $campaign && method_exists( $campaign, 'find_the_best_rule_for_this_quantity' ) ) {
			$rule = $campaign->find_the_best_rule_for_this_quantity( 1 );
			if ( $rule ) {
				$onsale = false;
			}
		}

		return $onsale;
	}

	/**
	 * Override the HTML product price
	 *
	 * @param string $html_price HTML price.
	 * @param WC_Product $product Product.
	 *
	 * @return float
	 * @since  1.0.0
	 * @author YITH
	 */
	public function get_html_product_price( $html_price, $product ) {
		$campaign = $this->get_valid_campaign_by_product( $product );
		if ( $campaign && method_exists( $campaign, 'get_html_product_price' ) ) {
			$html_price = $campaign->get_html_product_price( $html_price, $product );

		}

		return $html_price;
	}

	/**
	 * Remove the quantity input from cart
	 *
	 * @param string $product_quantity HTML code to show product quantity.
	 * @param string $cart_item_key Cart item key.
	 * @param array $cart_item Cart item.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function remove_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
			foreach ( $cart_item['yith_sales']['campaigns'] as $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );
				if ( $campaign && method_exists( $campaign, 'remove_quantity' ) ) {
					$product_quantity = $campaign->remove_quantity( $product_quantity, $cart_item_key, $cart_item );
				}
			}
		}

		return $product_quantity;
	}

	/**
	 * Remove the quantity input on wc blocks cart
	 *
	 * @param string $value HTML code to show product quantity.
	 * @param WC_Product $product The product.
	 * @param array $cart_item Cart item.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function remove_quantity_on_wc_blocks( $value, $product, $cart_item ) {
		if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
			foreach ( $cart_item['yith_sales']['campaigns'] as $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );
				if ( $campaign && method_exists( $campaign, 'remove_quantity_on_wc_blocks' ) ) {
					$value = $campaign->remove_quantity_on_wc_blocks( $product, $cart_item );
				}
			}
		}

		return $value;
	}

	/**
	 * Hide the remove link from cart for single cart item
	 *
	 * @param string $remove_link HTML code of remove item link.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function hide_remove_link( $remove_link, $cart_item_key ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );
		if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
			foreach ( $cart_item['yith_sales']['campaigns'] as $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );
				if ( $campaign && method_exists( $campaign, 'hide_remove_link' ) ) {
					$remove_link = $campaign->hide_remove_link( $remove_link, $cart_item_key );
				}
			}
		}

		return $remove_link;
	}

	/**
	 * Remove the quantity input from cart
	 *
	 * @param string $product_quantity HTML code to show product quantity.
	 * @param array $cart_item Cart item.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function remove_quantity_on_widget( $product_quantity, $cart_item, $cart_item_key ) {
		return $this->remove_quantity( $product_quantity, $cart_item_key, $cart_item );
	}


	/**
	 * Show the campaign title on product page.
	 *
	 * @return void|string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function display_campaign_title( $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			global $product;
		}
		$campaign       = $this->get_valid_campaign_by_product( $product );
		$campaign_title = '';
		if ( is_callable( array( $campaign, 'show_title_on_single_page' ) ) ) {
			ob_start();
			$campaign->show_title_on_single_page();
			$campaign_title = ob_get_clean();
		}

		if ( $echo ) {
			echo $campaign_title;
		} else {
			return $campaign_title;
		}
	}


	/**
	 * Change the input quantity value on single product
	 *
	 * @param array $args Params to filter.
	 * @param WC_Product $wc_product Product.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	public function change_input_quantity_value( $args, $wc_product ) {
		$campaign = $this->get_valid_campaign_by_product( $wc_product );
		global $product;
		if ( $product && is_callable( array( $campaign, 'get_default_qty' ) ) ) {
			$args['input_value'] = $campaign->get_default_qty() === 'yes' ? 3 : $args['input_value'];
		}

		return $args;
	}

	/**
	 * @param $cart_data
	 * @param $product
	 *
	 * @return mixed
	 */
	public function check_add_to_cart_store_api( $cart_data, $product ) {

		$campaign = $this->get_valid_campaign_by_product( $product );

		if( 'bogo' === $campaign->get_type() ){
			$cart_item_data['yith_sales_bogo_main_product'] = $campaign->get_id();
			$cart_data['cart_item_data'] = array_merge( $cart_data['cart_item_data'], $cart_item_data );
		}elseif( 'three-for-two' === $campaign->get_type() ){
			$cart_item_data              = array(
				'yith_sales' => array(
					'campaigns' => array( $campaign->get_id() )
				)
			);
			$cart_data['cart_item_data'] = array_merge( $cart_data['cart_item_data'], $cart_item_data );
		}

		return $cart_data;
	}
}
