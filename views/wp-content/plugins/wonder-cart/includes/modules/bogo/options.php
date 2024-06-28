<?php
/**
 * Module BOGO Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'bogo';

$options = array(
	'trigger'       => array(
		'menuItem' => __( 'Trigger', 'wonder-cart' ),
		'title'    => __( 'Step 1 - Trigger', 'wonder-cart' ),
		'fields'   => array(
			array(
				'id'          => 'campaign_name',
				'name'        => __( 'Campaign name:', 'wonder-cart' ),
				'description' => __( 'Customers will see this in their cart and during checkout.', 'wonder-cart' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Example: “Black Friday Discount”', 'wonder-cart' ),
				'default'     => '',
				'style'       => array(
					'size'      => 'xl',
					'fullWidth' => true,
				),
			),
			array(
				'id'               => 'trigger_product',
				'name'             => __( 'Trigger when the user add to cart:', 'wonder-cart' ),
				'type'             => 'selectTerms',
				'required'         => true,
				'options'          => array(
					array(
						'value' => 'all',
						'label' => __( 'All products', 'wonder-cart' ),
					),
					array(
						'value' => 'products',
						'label' => __( 'Specific products', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Specific categories', 'wonder-cart' ),
					),
				),
				'style'            => array(
					'size'  => 'xl',
					'width' => '100%',
				),
				'buttonLabels'     => array(
					array(
						'value' => 'products',
						'label' => __( 'Select products', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Select categories', 'wonder-cart' ),
					),
				),
				'editButtonLabels' => array(
					array(
						'value' => 'products',
						'label' => __( 'Select products', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Select categories', 'wonder-cart' ),
					),
				),
				'searchModalTerms' => array(
					array(
						'value'       => 'products',
						'term'        => 'product',
						'placeholder' => __( 'Search products', 'wonder-cart' ),
					),
					array(
						'value'       => 'categories',
						'term'        => 'product_cat',
						'placeholder' => __( 'Search category', 'wonder-cart' ),
					),
				),
				'default'          => array(
					'type' => 'all',
					'ids'  => array(),
				),
			),

		),
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
						'default' => yith_sales_block_editor_paragraph( __( 'Enjoy our special BOGO on this product. Buy 1 and Get 1 FOR FREE!', 'wonder-cart' ) ),
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
						'default'  => __( 'BUY 1 GET 1', 'wonder-cart' ),
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
								'default' => yith_sales_get_default_setting( '#4b4b4b', 'yith_sales_badge_colors', 'background_color' ),
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
								'align'   => 'right',
								'type'    => 'colorpicker',
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
		array(
			'text_to_show_popup'  => __( 'Enjoy our special BOGO. Buy 1 and Get 1 FOR FREE!', 'wonder-cart' ),
			'text_to_show_banner' => __( 'Enjoy our special BOGO. Buy 1 and Get 1 FOR FREE!', 'wonder-cart' ),
		),
		$campaign_type
	),
);

return apply_filters( 'yith_sales_bogo_options', $options );
