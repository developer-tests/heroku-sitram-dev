<?php
/**
 * Options to configure the content of the BH Onboarding
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH PayPal Payments for WooCommerce
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$onboarding_tabs = array(
	'slug'               => YITH_PAYPAL_PAYMENTS_SLUG,
	'enqueue_script'     => array( 'yith-ppwc-backend', 'yith-ppwc-login', 'yith-ppwc-login-handler' ),
	'enqueue_style'      => array( 'yith-ppwc-backend-css' ),
	'logo'               => YITH_PAYPAL_PAYMENTS_URL . 'assets/images/paypal.svg',
	'plugin-description' => __( 'Securely accept PayPal Digital Payments and credit/debit card payments on your store.', 'yith-paypal-payments-for-woocommerce' ),
	'claim'              => __( 'Set up PayPal Payments on your store', 'yith-paypal-payments-for-woocommerce' ),
	'tabs'               => array(
		'main'        => array(
			'title'            => __( 'I already have a business account on PayPal', 'yith-paypal-payments-for-woocommerce' ),
			'description'      => sprintf( __( '%1$sIf you already have an account on PayPal%2$s, enter the info below to connect your store.', 'yith-paypal-payments-for-woocommerce' ), '<strong>', '</strong>' ),
			'options'          => array(
				'title'             => array(
					'id'        => 'yith_ppwc_gateway_options[title]',
					'title'     => _x( 'Title', 'Admin panel option title', 'yith-paypal-payments-for-woocommerce' ),
					'desc'      => _x( 'Enter a title to identify this payment method during checkout.', 'Admin panel option description', 'yith-paypal-payments-for-woocommerce' ),
					'type'      => 'yith-field',
					'yith-type' => 'text',
					'default'   => _x( 'Pay with PayPal', 'Default value for PayPal Payment title.', 'yith-paypal-payments-for-woocommerce' ),
				),
				'environment'       => array(
					'id'        => 'yith_ppwc_gateway_options[environment]',
					'title'     => _x( 'Environment', 'Admin panel option title', 'yith-paypal-payments-for-woocommerce' ),
					'desc'      => _x( 'Choose if you want to take live payment transactions or test payments using a sandbox environment.', 'Admin panel option description', 'yith-paypal-payments-for-woocommerce' ),
					'type'      => 'yith-field',
					'yith-type' => 'radio',
					'options'   => array(
						'production' => _x( 'Live', 'Admin panel option. Production environment.', 'yith-paypal-payments-for-woocommerce' ),
						'sandbox'    => _x( 'Sandbox', 'Admin panel option. Development environment.', 'yith-paypal-payments-for-woocommerce' ),
					),
					'default'   => 'production',
				),
				'connection-button' => array(
					'id'        => 'yith_ppwc_gateway_options[button]',
					'title'     => _x( 'Connect account', 'Admin panel option title', 'yith-paypal-payments-for-woocommerce' ),
					'desc'      => '',
					'type'      => 'yith-field',
					'yith-type' => 'yith-ppwc-login-button',
					'default'   => '',
				),
				'intent'            => array(
					'id'        => 'yith_ppwc_gateway_options[intent]',
					'title'     => _x( 'Payment action', 'Admin title option', 'yith-paypal-payments-for-woocommerce' ),
					'desc'      => _x( 'Choose whether to capture funds immediately or authorize the payment only.', 'Admin option', 'yith-paypal-payments-for-woocommerce' ),
					'type'      => 'yith-field',
					'yith-type' => 'radio',
					'default'   => 'capture',
					'options'   => array(
						// translators:placeholders are html tags.
						'capture'   => sprintf( _x( 'Sale %1$sFunds are transferred instantly from customer account to merchant account.%2$s', 'Admin option, the placeholder are tags', 'yith-paypal-payments-for-woocommerce' ), '<small>', '</small>' ),
						// translators:placeholders are html tags.
						'authorize' => sprintf( esc_html__( 'Authorize and capture %1$sFunds will be reserved for 3 days and the order will be set as "On hold" until the admin completes it.%2$s', 'yith-paypal-payments-for-woocommerce' ), '<small>', '</small>' ),
					),
				),
			),
			'show_save_button' => true,
		),
		'shippo-info' => array(
			'title'            => __( 'Create an account', 'yith-paypal-payments-for-woocommerce' ),
			'description'      => __( 'Create a free business account on PayPal and allow your customers to pay with PayPal or credit cards.', 'yith-paypal-payments-for-woocommerce' ),
			'options'          => array(
				'text' => array(
					'id'               => 'yith-paypal-text',
					'title'            => '',
					'type'             => 'yith-field',
					'yith-type'        => 'bh-onboarding-create-an-account',
					'yith-display-row' => false,
				),
			),
			'show_save_button' => false,
		),
	),
);

/**
 * APPLY_FILTERS: yith_ppwc_bh_onboarding_tab
 *
 * This filter allow to manage the PayPal Payments subtabs.
 *
 * @return array
 */
return apply_filters( 'yith_ppwc_bh_onboarding_tab', $onboarding_tabs );
