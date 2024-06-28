<?php
/**
 * Upsell Controller
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
class YITH_Sales_Upsell_Controller {

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $campaigns = array();


	/**
	 * Construct function of controller
	 *
	 * @param array $campaigns Campaigns ordered by priority.
	 */
	public function __construct( $campaigns ) {
		$this->campaigns = empty( $campaigns['upsell'] ) ? array() : $campaigns['upsell'];
		add_action( 'woocommerce_add_to_cart', array( $this, 'save_latest_item_added' ), 20 );
		add_action( 'wp_footer', array( $this, 'add_upsell_modal' ), 99 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'clear_upsell_cookies' ) );
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
		$action = isset( $_REQUEST['yith_sales_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith_sales_action'] ) ) : false;

		if ( $action && method_exists( $this, $action ) ) {
			$this->$action();
		}
	}

	/**
	 * Find if exist a valid campaign
	 *
	 * @return void
	 */
	public function find_valid_campaign_details() {
		$valid_campaign_details = $this->get_valid_campaign_details();
		$data                   = array(
			'campaignID' => false,
			'productID'  => false,
		);
		if ( $valid_campaign_details['valid_campaign'] ) {
			$campaign            = $valid_campaign_details['valid_campaign'];
			$data['campaignID']  = $campaign->get_id();
			$data['productID']   = $valid_campaign_details['product_id'];
			$data['cartItemKey'] = $valid_campaign_details['cart_item_key'];
			yith_sales_set_modal_as_visited( $campaign, 'upsell' );
		}

		wp_send_json_success( $data );
	}


	/**
	 * Add to cart the upsell product
	 *
	 * @throws Exception The exception.
	 */
	public function add_upsell() {
		if ( ! empty( $_REQUEST['yith_sales_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$data               = wp_unslash( $_REQUEST['yith_sales_data'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$campaign_id        = $data['campaign_id'];
			$campaign           = yith_sales_get_campaign( $campaign_id );
			$main_cart_item_key = $data['cart_item_key'];
			$main_cart_item     = WC()->cart->get_cart_item( $main_cart_item_key );
			if ( $campaign->is_valid_for( $main_cart_item['data'] ) ) {
				remove_action( 'woocommerce_add_to_cart', array( $this, 'save_latest_item_added' ), 20 );

				WC()->cart->cart_contents[ $main_cart_item_key ]['yith_sales_main_product'] = $campaign_id;

				$products       = $data['products'];
				$cart_item_data = array(
					'yith_sales'                    => array(
						'campaigns' => array(
							$campaign_id,
						),
					),
					'yith_sales_main_cart_item_key' => $main_cart_item_key,
				);

				foreach ( $products as $product ) {
					$variations = array();
					if ( ! empty( $product['variations'] ) ) {
						foreach ( $product['variations'] as $variation ) {
							$variations[ $variation['key'] ] = $variation['option'];
						}
					}
					$cart_item_key = WC()->cart->add_to_cart( $product['product_id'], 1, $product['variation_id'], $variations, $cart_item_data );
				}

				add_action( 'woocommerce_add_to_cart', array( $this, 'save_latest_item_added' ), 20 );
				if ( 'yes' === $campaign->get_redirect_to_checkout() ) {
					$data = array(
						'redirect_to' => wc_get_checkout_url(),
					);
					wp_send_json( $data );
				} else {
					WC_AJAX::get_refreshed_fragments();
				}
			}
		}
	}

	/**
	 * Add in a transient the latest product added in the cart
	 *
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return void
	 */
	public function save_latest_item_added( $cart_item_key ) {
		set_transient( 'yith_sales_latest_cart_item_key', $cart_item_key );
	}

	/**
	 * Return the details of the current valid campaign
	 *
	 * @return array
	 */
	public function get_valid_campaign_details() {
		$cart_item_key  = get_transient( 'yith_sales_latest_cart_item_key' );
		$valid_campaign = false;
		$product_id     = false;
		if ( $cart_item_key ) {
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );
			if ( $cart_item ) {
				foreach ( $this->campaigns as $campaign ) {
					if ( ! yith_sales_is_modal_visited( $campaign, 'upsell' ) && $campaign->is_valid_for( $cart_item['data'] ) ) {
						$valid_campaign = $campaign;
						$product_id     = $cart_item['data']->get_id();
						break;
					}
				}
			}
		}
		delete_transient( 'yith_sales_latest_cart_item_key' );

		return array(
			'valid_campaign' => $valid_campaign,
			'product_id'     => $product_id,
			'cart_item_key'  => $cart_item_key,
		);
	}

	/**
	 * Add the template for the upsell campaigns
	 *
	 * @return void
	 */
	public function add_upsell_modal() {
		// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
		// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		$valid_campaign_details = $this->get_valid_campaign_details();
		$valid_campaign         = $valid_campaign_details['valid_campaign'];
		$options                = array(
			'campaignID' => false,
			'productID'  => false,
		);
		if ( false !== $valid_campaign && ! yith_sales_is_modal_visited( $valid_campaign, 'upsell' ) ) {
			$options = array(
				'campaignID'  => $valid_campaign->get_id(),
				'productID'   => $valid_campaign_details['product_id'],
				'cartItemKey' => $valid_campaign_details['cart_item_key'],
			);
		}
		?>
        <div class="yith-sales-upsell-modal"
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
	 * Clear campaign modal cookie
	 *
	 * @return void
	 */
	public function clear_upsell_cookies() {
		yith_sales_clear_modal_cookie( 'upsell' );
	}

}
