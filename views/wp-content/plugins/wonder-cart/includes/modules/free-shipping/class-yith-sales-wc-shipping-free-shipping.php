<?php
/**
 * This class add the free shipping if there are Free shipping rules valid
 *
 * @package YITH\Sales
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class add a free shipping method under specific rules
 *
 * @author YITH
 */
class YITH_Sales_WC_Shipping_Free_Shipping extends WC_Shipping_Method {
	/**
	 * The construct method
	 *
	 * @param int $instance_id The instance id.
	 */
	public function __construct( $instance_id = 0 ) {

		$this->id          = 'yith_sales_free_shipping';
		$this->title       = __( 'Free shipping', 'wonder-cart' );
		$this->instance_id = absint( $instance_id );
		$this->supports    = array();

		$hide_other_shipping = get_option( 'yith_sales_hide_other_shipping', 'no' );

		if ( 'yes' === $hide_other_shipping ) {
			add_filter( 'woocommerce_package_rates', array( $this, 'hide_other_shipping_methods' ) );
		}
	}

	/**
	 * Check if the method is enabled
	 *
	 * @return bool
	 */
	public function is_enabled() {

		return true;
	}

	/**
	 * Check if is possible add the free shipping
	 *
	 * @param array $package The package.
	 *
	 * @return bool
	 */
	public function is_available( $package ) {

		$controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Free_Shipping_Controller' );

		return $controller->can_add_free_shipping( $package );

	}

	/**
	 * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
	 *
	 * @param array $package Shipping package.
	 *
	 * @uses WC_Shipping_Method::add_rate()
	 */
	public function calculate_shipping( $package = array() ) {
		$this->add_rate(
			array(
				'label'   => $this->title,
				'cost'    => 0,
				'taxes'   => false,
				'package' => $package,
			)
		);
	}

	/**
	 * Remove all other rates
	 *
	 * @param array $rates The rates
	 *
	 * @return array
	 */
	public function hide_other_shipping_methods( $rates ) {

		if ( isset( $rates[ $this->id ] ) ) {
			$new_rates[ $this->id ] = $rates[ $this->id ];
			return $new_rates;
		}

		return $rates;
	}
}
