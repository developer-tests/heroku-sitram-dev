<?php
/**
 * Thank you page Controller
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
class YITH_Sales_Thank_You_Page_Controller {

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
	 * @param   array $campaigns  Campaigns ordered by priority.
	 */
	public function __construct( $campaigns ) {
		$this->campaigns = $this->sort_campaigns( $campaigns );

		add_action( 'woocommerce_thankyou', array( $this, 'show_campaign' ), 10 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_fields_to_add_to_cart_url' ), 1000, 2 );

		add_action( 'wp_ajax_yith_sales_action', array( $this, 'ajax_actions' ) );
		add_action( 'wp_ajax_nopriv_yith_sales_action', array( $this, 'ajax_actions' ) );

	}


	/**
	 * Execute the ajax actions for the upsell campaign
	 *
	 * @return void
	 */
	public function ajax_actions() {
		check_ajax_referer( 'yith-sales-action', 'security' );

		$action      = isset( $_REQUEST['yith_sales_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith_sales_action'] ) ) : false;
		$campaign_id = isset( $_REQUEST['yith_sales_data']['campaign_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith_sales_data']['campaign_id'] ) ) : false;

		if ( $action && $campaign_id ) {
			$campaign = yith_sales_get_campaign( $campaign_id );
			if ( $campaign && method_exists( $campaign, 'add_thank_you_page_on_cart' ) ) {
				$result = $campaign->add_thank_you_page_on_cart( wp_unslash( $_REQUEST['yith_sales_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				if ( $result ) {
					if ( 'yes' === $campaign->get_redirect_to_checkout() ) {
						$data = array(
							'redirect_to' => wc_get_checkout_url(),
						);
						wp_send_json( $data );
					} else {
						WC_AJAX::get_refreshed_fragments();
					}
				} else {
					$data = array(
						'error' => 'Error during add to cart',
					);
				}
				wp_send_json( $data );
			}
		}
	}

	/**
	 * Show the campaign on thank you page.
	 *
	 * @param   int $order_id  Order id.
	 *
	 * @return void
	 */
	public function show_campaign( $order_id ) {
		$order          = wc_get_order( $order_id );
		$order_items    = $order->get_items();
		$valid_campaign = false;
		foreach ( $order_items as $order_item ) {
			$product = $order_item->get_product();
			if ( $product ) {
				$valid_campaign = $this->get_valid_campaign_by_product( $product );
				if ( $valid_campaign ) {
					break;
				}
			}
		}

		if ( $valid_campaign ) {

			$options = array(
				'campaignID' => $valid_campaign->get_id(),
			)
			?>
			<div
					class="yith-sales-thank-you-page"
					data-options="<?php echo yith_sales_get_json( $options ); // phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			>
			</div>
			<?php
		}
	}

	/**
	 * Return a valid campaign for a product
	 *
	 * @param   WC_Product $product  Product.
	 *
	 * @return YITH_Sales_Thank_You_Page_Campaign
	 */
	public function get_valid_campaign_by_product( $product ) {

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
	 * Sort the campaigns so the rule more specific will be applied before
	 *
	 * @param   array $campaigns  List of campaigns.
	 *
	 * @return array
	 */
	public function sort_campaigns( $campaigns ) {
		$ordered_campaigns = array();
		if ( isset( $campaigns['thank-you-page'] ) ) {
			$ordered_campaigns = array_merge( $ordered_campaigns, $campaigns['thank-you-page'] );
		}

		return $ordered_campaigns;
	}

	/**
	 * Show badge for product
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return bool
	 */
	public function show_badge_for_product( $product ) {
		return false;
	}


	/**
	 * Add additional field id to the add to cart form.
	 *
	 * @param   string     $url      URL to change.
	 * @param   WC_Product $product  Product to change.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function add_fields_to_add_to_cart_url( $url, $product ) {

		foreach ( $this->campaigns as $campaign ) {
			if ( method_exists( $campaign, 'add_fields_to_add_to_cart_url' ) ) {
				$url = $campaign->add_fields_to_add_to_cart_url( $url, $product );
			}
		}

		return $url;
	}
}
