<?php
/**
 * Module Cart Discount Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'cart-discount';

$options = array(
	'trigger'       => array(
		'menuItem' => __( 'Trigger', 'wonder-cart' ),
		'title'    => __( 'Step 1 - Trigger', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'          => 'campaign_name',
				'name'        => __( 'Campaign name:', 'wonder-cart' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Example: “10% Cart Discount”', 'wonder-cart' ),
				'default'     => '',
				'style'       => array(
					'size'      => 'xl',
					'fullWidth' => true,
				),
			),
			array(
				'id'                    => 'cart_rules',
				'name'                  => __( 'Trigger:', 'wonder-cart' ),
				'type'                  => 'cartDiscountRules',
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
	'configuration' => array(
		'menuItem' => __( 'Configuration', 'wonder-cart' ),
		'title'    => __( 'Step 2 - Configuration', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'     => 'behaviour_toggle',
				'name'   => __( 'Behaviour', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'discount_to_apply',
						'name'    => __( 'Discount to apply:', 'wonder-cart' ),
						'type'    => 'discountField',
						'default' => array(
							'discount_type'  => 'percentage',
							'discount_value' => 10,
						),
					),
				),
			),
		),
	),
	'customization' => array(
		'menuItem' => __( 'Customization', 'wonder-cart' ),
		'title'    => __( 'Step 3 - Customization', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'     => 'discount_toggle',
				'name'   => __( 'Discount label', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'title',
						'name'    => '',
						'type'    => 'text',
						'default' => __( 'Loyalty discount', 'wonder-cart' ),
						'style'   => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
					),
					array(
						'id'     => 'colors',
						'name'   => __( 'Label colors', 'wonder-cart' ),
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'label_background_color',
								'name'    => __( 'Background', 'wonder-cart' ),
								'default' => '#FFF1CE',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'label_text_color',
								'name'    => __( 'Text', 'wonder-cart' ),
								'default' => '#B40000',
								'style'   => array(
									'size' => 'lg',
								),
								'align'   => 'right',
								'type'    => 'colorpicker',
							),
						),
					),
				),
			),
		),
	),
);

return apply_filters( 'yith_sales_cart_discount_options', $options );
