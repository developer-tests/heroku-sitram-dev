<?php
/**
 * Free Shipping Controller
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
class YITH_Sales_Free_Shipping_Controller {

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
		$this->campaigns = $this->sort_campaigns( $campaigns );
		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_method' ), 10 );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'register_shipping_method' ) );
		add_action( 'woocommerce_before_cart_table', array( $this, 'show_free_shipping_banner' ), 15 );
		add_action( 'render_block_woocommerce/cart', array( $this, 'display_free_shipping_banner_block' ), 10, 2 );
		add_action( 'rest_insert_yith_campaign', array( $this, 'invalidate_shipping_cache' ) );
	}

	/**
	 * Load the class that manage the shipping method
	 *
	 * @return void
	 */
	public function load_shipping_method() {
		require_once YITH_SALES_INC . '/modules/free-shipping/class-yith-sales-wc-shipping-free-shipping.php';
	}

	/**
	 * Register the shipping method
	 *
	 * @param array $methods The registered shipping methods.
	 *
	 * @return array
	 */
	public function register_shipping_method( $methods ) {
		$methods['yith_sales_free'] = 'YITH_Sales_WC_Shipping_Free_Shipping';

		return $methods;
	}

	/**
	 * Check if there is a shipping campaign valid to apply the free shipping
	 *
	 * @param array $package The package.
	 *
	 * @return bool
	 */
	public function can_add_free_shipping( $package ) {
		$cart_details = array(
			'cart_total' => yith_sales_get_cart_subtotal(),
			'state'      => $package['destination']['country'] . ':' . $package['destination']['state'],
			'country'    => $package['destination']['country'],
		);

		foreach ( $this->campaigns as $campaign ) {
			if ( $campaign->is_valid( $cart_details ) ) {
				return true;
			}
		}

		return false;
	}

    public function get_free_shipping_banner_args() {
	    $cart_total = yith_sales_get_cart_subtotal();
        $args = array();
	    foreach ( $this->campaigns as $campaign ) {
		    $banner_text = $campaign->get_formatted_banner( $cart_total );
		    if ( $banner_text ) {
                $args[] = array( 'id'=> $campaign->get_id(), 'text'=>$banner_text );
		    }
	    }
        return $args;
    }

	/**
	 * Show the banner in the cart page
	 *
	 * @return void
	 */
	public function show_free_shipping_banner() {

		$cart_total = yith_sales_get_cart_subtotal();

		foreach ( $this->campaigns as $campaign ) {
			$banner_text = $campaign->get_formatted_banner( $cart_total );
			if ( $banner_text ) {
				$options = array(
					'campaignID'  => $campaign->get_id(),
					'bannerText'  => $banner_text,
					'bannerColor' => $campaign->get_notice_background_color(),
				);
				?>
				<div class="yith-sales-cart-banner"
						data-options="<?php echo yith_sales_get_json( $options ); // phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
				>
				</div>
				<?php
			}
		}
	}

	/**
	 * Invalidate the WC Cache if a free-shipping campaign is upated/created
	 *
	 * @param WP_Post $post The post.
	 *
	 * @return void
	 */
	public function invalidate_shipping_cache( $post ) {
		$campaign = yith_sales_get_campaign( $post->ID );
		if ( $campaign && 'free-shipping' === $campaign->get_type() ) {
			WC_Cache_Helper::get_transient_version( 'shipping', true );
		}
	}

	/**
	 * Sort the campaigns so the rule more specific will be applied before
	 *
	 * @param array $campaigns List of campaigns.
	 *
	 * @return array
	 */
	public function sort_campaigns( $campaigns ) {
		$ordered_campaigns = array();
		if ( isset( $campaigns['free-shipping'] ) ) {
			$ordered_campaigns = array_merge( $ordered_campaigns, $campaigns['free-shipping'] );
		}
		return $ordered_campaigns;
	}

	/**
	 * Print the campaign in blocks
	 *
	 * @param string $content The content.
	 * @param array $parsed_block The parsed block.
	 *
	 * @return string
	 */
	public function display_free_shipping_banner_block( $content, $parsed_block ) {

            ob_start();
            $this->show_free_shipping_banner();
            $banner = ob_get_clean();
            return $banner.$content;

	}
}
