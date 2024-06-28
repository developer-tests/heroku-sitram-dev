<?php
/**
 * This class register the special meta for the cart
 *
 * @package YITH/Sales
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

class YITH_Sales_WC_Blocks_Register {
	use YITH_Sales_Trait_Singleton;

	protected function __construct() {
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_endpoint' ) );
	}

	/**
	 * Register the endpoint
	 *
	 * @return void
	 */
	public function register_endpoint() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => 'yith_sales_wc_block_manager',
				'data_callback'   => array( $this, 'my_data_callback' ),
				'schema_callback' => array( $this, 'my_schema_callback' ),
				'schema_type'     => ARRAY_A,
			)
		);
	}

	/**
	 * Return the data
	 *
	 * @return string[]
	 */
	public function my_data_callback() {
		$controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Cart_Discount_Controller' );
		$campaign = false;
		$free_shipping_options = array();
		if( $controller instanceof YITH_Sales_Cart_Discount_Controller ) {
			$campaign   = $controller->get_valid_campaign();
		}
		$free_shipping_controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Free_Shipping_Controller' );

		if( $free_shipping_controller instanceof YITH_Sales_Free_Shipping_Controller ) {
			$free_shipping_options = $free_shipping_controller->get_free_shipping_banner_args();
		}

		return array(
			'yith_sales_coupon_label' => $campaign instanceof YITH_Sales_Cart_Discount_Campaign ? $campaign->get_title() : '',
			'yith_sales_remain_for_free_shipping' => $free_shipping_options
		);
	}

	/**
	 * The schema callback
	 *
	 * @return array[]
	 */
	public function my_schema_callback() {
		return array(
			'yith_sales_coupon_label' => array(
				'description' => _x( 'The coupon label applied in cart', 'The coupon label', 'wonder-cart' ),
				'type'        => 'string',
				'readonly'    => true,
			)
		);
	}
}

YITH_Sales_WC_Blocks_Register::get_instance();