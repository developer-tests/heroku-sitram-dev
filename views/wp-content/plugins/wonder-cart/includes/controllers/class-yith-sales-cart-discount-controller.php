<?php
/**
 * Cart Discount Controller
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
class YITH_Sales_Cart_Discount_Controller {

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $campaigns = array();

	/**
	 * The current valid campaign
	 *
	 * @var bool|YITH_Sales_Cart_Discount_Campaign
	 */
	protected $valid_campaign = false;

	/**
	 * Construct function of controller
	 *
	 * @param array $campaigns Campaigns ordered by priority.
	 */
	public function __construct( $campaigns ) {
		$this->campaigns = $campaigns['cart-discount'] ?? array();
		add_action( 'woocommerce_cart_updated', array( $this, 'apply_coupon_cart_discount' ), 99 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'apply_coupon_cart_discount' ), 101 );
		add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'customize_label_coupon' ), 10, 2 );
		add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'customize_coupon_cart_html' ), 10, 2 );
	}

	/**
	 * Return the extra args to validate the cart discount campaign
	 *
	 * @param false|float $cart_subtotal The subtotal.
	 *
	 * @return array
	 */
	public function get_cart_condition_args( $cart_subtotal = false ) {
		$subtotal      = false !== $cart_subtotal ? $cart_subtotal : yith_sales_get_cart_subtotal();
		$user_id       = get_current_user_id();
		$num_of_orders = wc_get_customer_order_count( $user_id );
		$past_expense  = is_user_logged_in() ? wc_get_customer_total_spent( $user_id ) : - 1;

		return array(
			'cart_total'    => $subtotal,
			'num_of_orders' => $num_of_orders,
			'past_expense'  => $past_expense,
		);
	}

	/**
	 * Get the valid cart discount campaign
	 *
	 * @param false|float $cart_subtotal The subtotal.
	 *
	 * @return false|mixed
	 */
	public function get_valid_campaign( $cart_subtotal = false ) {
		$extra_args = $this->get_cart_condition_args( $cart_subtotal );

		foreach ( $this->campaigns as $campaign ) {
			if ( is_object( $campaign ) && $campaign->is_valid( $extra_args ) ) {
				$this->valid_campaign = $campaign;
				break;
			}
		}

		return $this->valid_campaign;
	}

	/**
	 * Get the current coupon code
	 *
	 * @return string
	 */
	public function get_coupon_code() {
		$coupon_code_prefix = 'yith_sales_cart_discount-';
		$coupon_in_session  = WC()->session->get( 'yith_sales_cart_discount_coupon_code', '' );
		if ( is_user_logged_in() ) {
			$coupon_code = $coupon_code_prefix . get_current_user_id();
			if ( ! empty( $coupon_in_session ) ) {
				$coupon = new WC_Coupon( $coupon_in_session );
				$coupon->delete( true );
				WC()->session->set( 'yith_sales_cart_discount_coupon_code', '' );
				WC()->session->save_data();
				WC()->cart->remove_coupon( $coupon_in_session );
			}
		} else {
			$coupon_code = $coupon_in_session;
			if ( empty( $coupon_code ) ) {
				$coupon_code = uniqid( $coupon_code_prefix . '_' );
				WC()->session->set( 'yith_sales_cart_discount_coupon_code', $coupon_code );
				WC()->session->save_data();
			}
		}

		return $coupon_code;
	}

	/**
	 * Wrapper in the html the coupon details
	 *
	 * @param string $html The coupon html.
	 *
	 * @return string
	 */
	protected function get_coupon_details_html( $html ) {
		$options = array(
			'textColor' => $this->valid_campaign->get_label_text_color(),
			'bgColor'   => $this->valid_campaign->get_label_background_color(),
		);

		$data = yith_sales_get_json( $options );

		return '<div id="yith-sales-coupon-row-wrapper" data-options="' . $data . '">' . $html . '</div>';
	}

	/**
	 * Add the coupon in the cart
	 *
	 * @return void
	 * @throws Exception Error message.
	 */
	public function apply_coupon_cart_discount() {
		$is_rest = isset( $_REQUEST['code'], $_REQUEST['context'] ) && 'yith_sales_block' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ) && $this->get_coupon_code() === sanitize_text_field( wp_unslash( $_REQUEST['code'] ) );
		if ( ! $is_rest ) {
			$campaign_valid = $this->get_valid_campaign();

			$coupon_code = $this->get_coupon_code();
			if ( $campaign_valid ) {
				$wc_discount       = new WC_Discounts( WC()->cart );
				$coupon            = new WC_Coupon( $coupon_code );
				$valid             = $wc_discount->is_coupon_valid( $coupon );
				$valid             = is_wp_error( $valid ) ? false : $valid;
				$discount_to_apply = $campaign_valid->get_discount_to_apply();
				$coupon_data       = array(
					'discount_type'  => 'percentage' === $discount_to_apply['discount_type'] ? 'percent' : 'fixed_cart',
					'amount'         => $discount_to_apply['discount_value'],
					'individual_use' => false,
					'usage_limit'    => 0,
				);
				if ( $valid ) {
					foreach ( $coupon_data as $data_key => $data_value ) {
						if ( is_callable( array( $coupon, 'set_' . $data_key ) ) ) {
							$method = 'set_' . $data_key;
							$coupon->$method( $data_value );
						}
					}
				} else {
					$coupon->add_meta_data( 'yith_sales_coupon', 1 );
					$coupon->add_meta_data( 'yith_sales_coupon_version', '1.0.0' );
					$coupon->read_manual_coupon( $coupon_code, $coupon_data );
				}

				if ( ! $valid || $coupon->get_changes() ) {
					$coupon->save();
				}
				if ( ! WC()->cart->has_discount( $coupon_code ) ) {
					WC()->cart->add_discount( $coupon_code );
				}
			} else {
				if ( WC()->cart->has_discount( $coupon_code ) ) {
					WC()->cart->remove_coupon( $coupon_code );
				}
			}
		}

	}

	/**
	 * Change the dynamic coupon label
	 *
	 * @param string $coupon_code Coupon code.
	 * @param WC_Coupon $coupon The coupon.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author YITH
	 */
	public function customize_label_coupon( $coupon_code, $coupon ) {

		if ( $coupon instanceof WC_Coupon ) {
			$is_our_coupon = $coupon->get_meta( 'yith_sales_coupon', true );
			if ( $is_our_coupon && $this->valid_campaign ) {
				$coupon_code = $this->get_coupon_details_html( $this->valid_campaign->get_title() );
			}
		}

		return $coupon_code;
	}

	/**
	 * Hide the remove link in the coupon and add custom style
	 *
	 * @param string $value The old html value.
	 * @param WC_Coupon $coupon The coupon.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author YITh
	 */
	public function customize_coupon_cart_html( $value, $coupon ) {
		if ( $coupon instanceof WC_Coupon ) {
			$is_our_coupon = $coupon->get_meta( 'yith_sales_coupon', true );
			if ( $is_our_coupon && $this->valid_campaign ) {
				$value = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_prices_including_tax() );
				$value = '-' . wc_price( $value );
			}
		}

		return $value;
	}

}
