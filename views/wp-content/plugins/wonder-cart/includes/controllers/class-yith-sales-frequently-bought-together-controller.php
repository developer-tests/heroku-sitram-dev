<?php
/**
 * Frequently Bought Together Controller
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
class YITH_Sales_Frequently_Bought_Together_Controller {

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

		$this->campaigns = empty( $campaigns['frequently-bought-together'] ) ? array() : $campaigns['frequently-bought-together'];

		add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'display_campaign' ), 20 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_fbt_products' ), 3000, 2 );
	}

	/**
	 * Show campaign
	 */
	public function display_campaign() {
		global $product;
		$price_controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Price_Controller' );
		$price            = $price_controller ? $price_controller->get_price( $product ) : $product->get_price();
		foreach ( $this->campaigns as $campaign ) {

			/**
			 * The campaign
			 *
			 * @var $campaign YITH_Sales_Frequently_Bought_Together_Campaign.
			 */
			if ( $campaign->is_valid_for( $product ) ) {
				$options = array(
					'campaignID'   => $campaign->get_id(),
					'productID'    => $product->get_id(),
					'productPrice' => $price,
				);
				?>
                <div class="yith-sales-frequently-bought-together"
                     data-options="<?php echo yith_sales_get_json( $options ); // phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
                </div>
				<?php
				break;
			}
		}
	}

	/**
	 * Add in the cart the products with the frequently bought together campaign
	 *
	 * @param string $cart_item_key The cart item key.
	 * @param int    $product_id    The product id.
	 *
	 * @return void
	 */
	public function add_fbt_products( $cart_item_key, $product_id ) {
		remove_action( 'woocommerce_add_to_cart', array( $this, 'add_fbt_products' ), 3000 );
		if ( isset( $_POST['yith_sales_fbt'], $_POST['yith_sales_product_list'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$campaign_id = sanitize_text_field( wp_unslash( $_POST['yith_sales_fbt'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$campaign    = yith_sales_get_campaign( $campaign_id );

            if ( $campaign ) {
				$request_list = wp_unslash( $_POST['yith_sales_product_list'] ); // phpcs:ignore WordPress.Security.NonceVerification , WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				// Filter the selected items.
				$product_list = array();

				foreach ( $request_list as $product_in_list ) {
					if ( 'yes' === $product_in_list['selected'] ) {
						$product_list[] = $product_in_list;
					}
				}

				if ( ! empty( $product_list ) ) {
					$request = array(
						'cart_item_key' => $cart_item_key,
						'product_list'  => $product_list,
					);

					$result = $campaign->add_to_cart( $request );
				}
			}
		}
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_fbt_products' ), 3000, 2 );
	}
}
