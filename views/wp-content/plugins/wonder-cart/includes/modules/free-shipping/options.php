<?php
/**
 * Module Free Shipping Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'free-shipping';
$options       = array(
	'trigger'       => array(
		'menuItem' => __( 'Trigger', 'wonder-cart' ),
		'title'    => __( 'Step 1 - Trigger', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'          => 'campaign_name',
				'name'        => __( 'Campaign name:', 'wonder-cart' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Example: “Free Shipping promotion”', 'wonder-cart' ),
				'default'     => '',
				'style'       => array(
					'size'      => 'xl',
					'fullWidth' => true,
				),
			),
			array(
				'id'                    => 'free_shipping_rules',
				'name'                  => __( 'Trigger:', 'wonder-cart' ),
				'type'                  => 'freeShippingRules',
				'required'              => true,
				'default'               => array(
					'type'   => 'always',
					'handle' => 'any',
					'rules'  => array(),
				),
				'buttonLabel'           => __( 'Set conditions', 'wonder-cart' ),
				'editButtonLabel'       => __( 'Set conditions', 'wonder-cart' ),
				'addNewRuleButtonLabel' => __( 'Add condition', 'wonder-cart' ),
			),
		),
	),
	'customization' => array(
		'menuItem' => __( 'Customization', 'wonder-cart' ),
		'title'    => __( 'Step 2 - Customization', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'     => 'cart_toggle',
				'name'   => __( 'Cart notice', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'show_amount_left',
						'name'    => __( 'Show amount left for free shipping', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'no',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'          => 'title',
						'name'        => __( 'Notice text', 'wonder-cart' ),
						'type'        => 'text',
						'default'     => __( 'Only add %value% more to get FREE SHIPPING!', 'wonder-cart' ),
						'description' => __( 'Use the placeholder %value% to automatically show the amount left to get free shipping.', 'wonder-cart' ),
						'deps'        => array(
							'id'    => 'show_amount_left',
							'value' => 'yes',
							'type'  => 'show',
						),
						'style'       => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
					),
				),
			),
			array(
				'id'     => 'colors_toggle',
				'name'   => __( 'Colors', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'     => 'colors',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'notice_background_color',
								'name'    => __( 'Notice Background', 'wonder-cart' ),
								'default' => '#F9F6DA',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
						),
					),
				),
				'deps'   => array(
					'id'    => 'show_amount_left',
					'value' => 'yes',
					'type'  => 'show',
				),
			),
		),
	),
	'promotion'     => yith_sales_get_promotion_options(
		array(
			'title'               => __( 'Step 3 - Promotion', 'wonder-cart' ),
			'text_to_show_popup'  => __( 'FREE Shipping on orders $30+', 'wonder-cart' ),
			'text_to_show_banner' => __( 'FREE Shipping on orders $30+', 'wonder-cart' ),
		),
		$campaign_type,
	),
);

return apply_filters( 'yith_sales_free_shipping_options', $options );
