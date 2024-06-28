<?php
/**
 * Module Quantity Discount Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;


$campaign_type = 'quantity-discount';
$options       = array(
	'trigger'       => yith_sales_get_trigger_options(
		array(
			'campaign_name' => __( 'Example: “Business Card - 100/250 units”', 'wonder-cart' ),
			'trigger_title' => __( 'Show on:', 'wonder-cart' ),
		),
		$campaign_type
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
						'id'                    => 'discount_rules',
						'required'              => true,
						'name'                  => __( 'Discount for quantity', 'wonder-cart' ),
						'buttonLabel'           => __( 'Set rules', 'wonder-cart' ),
						'editButtonLabel'       => __( 'Edit rules', 'wonder-cart' ),
						'addNewRuleButtonLabel' => __( 'Add rule', 'wonder-cart' ),
						'type'                  => 'quantityDiscountRules',
						'default'               => array(),
					),
				),
			),
			array(
				'id'     => 'pricing_toggle',
				'name'   => __( 'Pricing', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'show_price_for_unit',
						'name'    => __( 'Show price for unit', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'show_saving',
						'name'    => __( 'Show saving', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'show_total_amount',
						'name'    => __( 'Show total amount', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
				),
				'deps'   => array(
					'id'        => 'discount_rules',
					'value'     => 0,
					'condition' => '>',
				),
			),
			array(
				'id'     => 'product_info_toggle',
				'name'   => __( 'Product info', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'show_product_image',
						'name'    => __( 'Show product image', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'show_quantity_badge',
						'name'    => __( 'Show quantity badge', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
						'deps'    => array(
							'id'    => 'show_product_image',
							'value' => 'yes',
							'type'  => 'show',
						),
					),

				),
				'deps'   => array(
					'id'        => 'discount_rules',
					'value'     => 0,
					'condition' => '>',
				),
			),
		),
	),
	'customization' => array(
		'menuItem' => __( 'Customization', 'wonder-cart' ),
		'title'    => __( 'Step 3 - Customization', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'     => 'heading_toggle',
				'name'   => __( 'Heading', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'title',
						'name'    => __( 'Title', 'wonder-cart' ),
						'type'    => 'textEditor',
						'default' => yith_sales_block_editor_paragraph( __( 'Buy more, pay less', 'wonder-cart' ) ),
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
								'id'      => 'item_background_color',
								'name'    => __( 'Background', 'wonder-cart' ),
								'default' => '#ffffff',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'item_border_color',
								'name'    => __( 'Borders', 'wonder-cart' ),
								'default' => '#ebebeb',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
							),
						),
					),
					array(
						'id'     => 'badge_color',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'badge_text_color',
								'name'    => __( 'Badge text', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#ffffff', 'yith_sales_badge_colors', 'text_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'badge_background_color',
								'name'    => __( 'Badge background', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#4b4b4b', 'yith_sales_badge_colors', 'background_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
							),
						),
					),
					array(
						'id'     => 'ribbon_color',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'ribbon_text_color',
								'name'    => __( 'Ribbon text', 'wonder-cart' ),
								'default' => '#2c2c2c',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'ribbon_background_color',
								'name'    => __( 'Ribbon background', 'wonder-cart' ),
								'default' => '#ebebeb',
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
							),
						),
					),
				),
			),
		),
	),
);

return apply_filters( 'yith_sales_quantity_discount_options', $options );
