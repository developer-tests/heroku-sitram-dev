<?php
/**
 * Sales Price Controller
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
class YITH_Sales_Price_Controller {

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
	 *
	 * @since  1.0.0
	 * @author YITH
	 */
	public function __construct( $campaigns ) {

		$this->campaigns = $this->process_campaigns( $campaigns );

		add_filter( 'woocommerce_get_price_html', array( $this, 'add_saving' ), 99, 2 );

		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'change_add_to_cart' ), 1000, 2 );
		add_filter( 'woocommerce_before_add_to_cart_button', array( $this, 'add_fields_to_add_to_cart_form' ), 1000 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_fields_to_add_to_cart_url' ), 1000, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_data' ), 15, 3 );
		// add title of campaigns inside the category page.
		add_action( 'woocommerce_archive_description', array( $this, 'add_campaign_title' ) );

		add_filter( 'yith_sales_store_api_check_campaign_on_cart_item', array( $this,'check_add_to_cart_store_api' ), 10, 2 );
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
	 * Add additional field id to the add to cart form.
	 *
	 * @param string     $url URL to change.
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
	 * Add campaign id to add to cart.
	 *
	 * @param array      $args The args.
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
	 * Get the right product price
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return string
	 */
	public function get_price( $product ) {
		if ( $product->get_type() !== 'variable' ) {
			$campaign = $this->get_valid_campaign_by_product( $product );

			if ( $campaign ) {
				$base_price = $product->get_regular_price();

				return $campaign->get_discounted_price( $base_price, $product );
			}
		}

		return $product->get_price();
	}

	/**
	 * Add saving to the price
	 *
	 * @param string     $html_price HTML price.
	 * @param WC_Product $product Product.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function add_saving( $html_price, $product ) {
		$campaign = $this->get_valid_campaign_by_product( $product );
		$saving   = '';
		if ( $campaign ) {
			if ( $product->get_type() !== 'variable' ) {
				$base_price = $product->get_regular_price();
				$new_price  = $campaign->get_discounted_price( $base_price, $product );
				$html_price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $base_price ) ), wc_get_price_to_display( $product, array( 'price' => $new_price ) ) ) . $product->get_price_suffix();

				if ( $campaign->get_show_saving() === 'yes' ) {
					$saving = $campaign->get_saving( $product );
					$saving = ' <span class="yith-sales-saving">' . esc_html( $saving ) . '</span>';
				}
				if ( $saving ) {
					$html_price .= $saving;
					$html_price = ' <span class="yith-sales-price">' . $html_price . '</span>';
				}
			}
		}

		return $html_price;
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

		return apply_filters( 'yith_sales_price_valid_campaign_by_product', $valid_campaign, $product, $this );
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
	 * Show the title of the campaign applied to this page
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	public function add_campaign_title() {
		foreach ( $this->campaigns as $campaign ) {
			$title = $campaign->get_title_to_display();
			if ( false !== $title ) {
				printf( '<div class="yith-sales-campaign-title">%s</div>', wp_kses_post( $title ) );
				break;
			}
		}
	}

	/**
	 * Add in the variation data the right price
	 *
	 * @param array                $variation_data The data.
	 * @param WC_Product_Variable  $variable_product The variable product.
	 * @param WC_Product_Variation $variation_product The variation.
	 *
	 * @return array
	 */
	public function add_variation_data( $variation_data, $variable_product, $variation_product ) {

		$price                                      = $this->get_price( $variation_product );
		$variation_data['yith_sales_display_price'] = wc_get_price_to_display( $variation_product, array( 'price' => $price ) );

		return $variation_data;
	}

	/**
	 * @param $cart_data
	 * @param $product
	 *
	 * @return mixed
	 */
	public function check_add_to_cart_store_api( $cart_data, $product ) {

		$campaign = $this->get_valid_campaign_by_product( $product );
		if ( $campaign ) {
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
