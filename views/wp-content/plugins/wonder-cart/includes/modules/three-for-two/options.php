<?php
/**
 * Module Three for Two Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'three-for-two';
$options       = array(
	'trigger'       => yith_sales_get_trigger_options(
		array(
			'campaign_name' => __( 'Example: “3x2 on Parfumes”', 'wonder-cart' ),
		),
		$campaign_type
	),
	'customization' => array(
		'menuItem' => __( 'Customization', 'wonder-cart' ),
		'title'    => __( 'Step 2 - Customization', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'     => 'heading_toggle',
				'name'   => __( 'Notice', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'title',
						'name'    => __( 'Title:', 'wonder-cart' ),
						'type'    => 'textEditor',
						'default' => yith_sales_block_editor_paragraph( __( 'Buy two units and get the third for free!', 'wonder-cart' ) ),
					),
				),
			),
			array(
				'id'     => 'extra_toggle',
				'name'   => __( 'Extra', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'default_qty',
						'name'    => __( 'Set default quantity as 3', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'show_badge',
						'name'    => __( 'Show badge', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'       => 'badge_text',
						'name'     => __( 'Badge text', 'wonder-cart' ),
						'type'     => 'text',
						'required' => true,
						'default'  => __( '3x2', 'wonder-cart' ),
						'style'    => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
						'deps'     => array(
							'id'    => 'show_badge',
							'value' => 'yes',
							'type'  => 'show',
						),
					),
					array(
						'id'     => 'colors',
						'name'   => __( 'Badge colors', 'wonder-cart' ),
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'badge_background_color',
								'name'    => __( 'Background', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#CF8300', 'yith_sales_badge_colors', 'background_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'badge_text_color',
								'name'    => __( 'Text', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#ffffff', 'yith_sales_badge_colors', 'text_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
							),
						),
						'deps'   => array(
							'id'    => 'show_badge',
							'value' => 'yes',
							'type'  => 'show',
						),
					),

				),
			),
		),
	),
	'promotion'     => yith_sales_get_promotion_options(
		array(),
		$campaign_type,
	),
);

return apply_filters( 'yith_sales_three_for_two_options', $options );
