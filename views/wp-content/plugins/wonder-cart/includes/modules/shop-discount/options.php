<?php
/**
 * Module Shop Discount Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'shop-discount';
$options       = array(
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
				'id'       => 'trigger_product',
				'name'     => __( 'Trigger when the user add to cart:', 'wonder-cart' ),
				'type'     => 'text',
				'disabled' => 'yes',
				'default'  => __( 'All Products', 'wonder-cart' ),
				'style'    => array(
					'size'      => 'xl',
					'fullWidth' => true,
				),
			),
			array(
				'id'      => 'exclude_products',
				'name'    => __( 'Exclude products or categories', 'wonder-cart' ),
				'type'    => 'onoff',
				'default' => 'no',
				'style'   => array(
					'size'  => 'md',
				),
			),
			array(
				'id'               => 'exclude_products_list',
				'type'             => 'selectTerms',
				'options'          => array(
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
				'required'    => true,
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
					'type' => 'products',
					'ids'  => array(),
				),
				'deps'             => array(
					'id'    => 'exclude_products',
					'value' => 'yes',
					'type'  => 'show',
				),
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
			array(
				'id'     => 'pricing_toggle',
				'name'   => __( 'Pricing', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'show_saving',
						'name'    => __( 'Show saving', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
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
				'id'     => 'notice_toggle',
				'name'   => __( 'Notice in shop page', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'          => 'title',
						'name'        => __( 'Title', 'wonder-cart' ),
						'type'        => 'textEditor',
						/* translators: %discount% is the discount value that will see the customers */
						'default'     => yith_sales_block_editor_paragraph( __( 'Black Friday Campaign! Enjoy a special %discount% on all products!', 'wonder-cart' ) ),
						/* translators: %discount% is the discount value that will see the customers */
						'description' => __( 'It is possible to use %discount% to show the discount applied to the product.', 'wonder-cart' ),
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
						'id'          => 'badge_text',
						'name'        => __( 'Badge text', 'wonder-cart' ),
						'type'        => 'text',
						'required'    => true,
						'default'     => '-%discount%',
						/* translators: %discount% is the discount value that will see the customers */
						'description' => __( 'It is possible to use %discount% to show the discount applied to the product.', 'wonder-cart' ),
						'style'       => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
						'deps'        => array(
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
								'default' => yith_sales_get_default_setting( '#000000', 'yith_sales_badge_colors', 'badge_background_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'badge_text_color',
								'name'    => __( 'Text', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#ffffff', 'yith_sales_badge_colors', 'badge_text_color' ),
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
		array(
			'title'               => __( 'Step 4 - Promotion', 'wonder-cart' ),
			'text_to_show_popup'  => __( 'Enjoy a special 20% on all products', 'wonder-cart' ),
			'text_to_show_banner' => __( 'Enjoy a special 20% on all products', 'wonder-cart' ),
		),
		$campaign_type
	),
);


return apply_filters( 'yith_sales_shop_discount_options', $options );
