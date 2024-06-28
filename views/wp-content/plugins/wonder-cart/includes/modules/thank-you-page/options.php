<?php
/**
 * Module Thank you page Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;


$campaign_type = 'thank-you-page';
$options       = array(
	'trigger'       => yith_sales_get_trigger_options(
		array(
			'campaign_name' => __( 'Example: “TY Page - Best seller Promotion”', 'wonder-cart' ),
			'trigger_title' => __( 'Trigger when the user purchased:', 'wonder-cart' ),
		),
		$campaign_type,
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
						'id'               => 'products_to_show',
						'name'             => __( 'Products to suggest:', 'wonder-cart' ),
						'type'             => 'selectTerms',
						'required'         => true,
						'options'          => yith_sales_get_product_to_show_options(),
						'buttonLabels'     => array(
							array(
								'value' => 'products',
								'label' => __( 'Select products', 'wonder-cart' ),
							),
						),
						'editButtonLabels' => array(
							array(
								'value' => 'products',
								'label' => __( 'Select products', 'wonder-cart' ),
							),
						),
						'searchModalTerms' => array(
							array(
								'value'       => 'products',
								'term'        => 'product',
								'placeholder' => __( 'Search products', 'wonder-cart' ),
							),
						),
						'default'          => array(
							'type' => 'featured',
							'ids'  => array(),
						),
						'style'            => array(
							'size'  => 'xl',
							'width' => '100%',
						),
					),
					yith_sales_get_box_info_option(),
					yith_sales_get_box_info_option( 'cross-sell' ),
					yith_sales_get_box_info_option( 'upsell' ),
					yith_sales_get_box_info_option( 'related' ),
					yith_sales_get_box_info_option( 'best-seller' ),
					array(
						'id'      => 'num_products_to_show',
						'name'    => __( 'Number of products:', 'wonder-cart' ),
						'type'    => 'number',
						'min'     => 1,
						'max'     => 10,
						'step'    => 1,
						'default' => 2,
						'style'   => array(
							'size'      => 'xl',
							'fullWidth' => false,
						),
						'deps'    => array(
							'id'    => 'products_to_show',
							'value' => array(
								'index' => 'type',
								'value' => 'products',
							),
							'type'  => 'hide',
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
						'id'      => 'show_product_price',
						'name'    => __( 'Show product price', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'apply_discount',
						'name'    => __( 'Apply discount', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'discount_to_apply',
						'name'    => __( 'Discount to apply:', 'wonder-cart' ),
						'type'    => 'discountField',
						'default' => array(
							'discount_type'  => 'percentage',
							'discount_value' => 10,
						),
						'deps'    => array(
							'id'    => 'apply_discount',
							'value' => 'yes',
							'type'  => 'show',
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
						'deps'    => array(
							'id'    => 'apply_discount',
							'value' => 'yes',
							'type'  => 'show',
						),
					),
				),
			),
			array(
				'id'     => 'product_info',
				'name'   => __( 'Product info', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'show_product_name',
						'name'    => __( 'Show product name', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
				),
			),
			array(
				'id'     => 'add_to_cart',
				'name'   => __( 'Add to cart', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'redirect_to_checkout',
						'name'    => __( 'Redirect user to Checkout', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'no',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'      => 'show_add_to_cart',
						'name'    => __( 'Show add to cart', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'       => 'add_to_cart_button_label',
						'name'     => __( 'Button label', 'wonder-cart' ),
						'type'     => 'text',
						'required' => true,
						'default'  => __( 'Buy now', 'wonder-cart' ),
						'style'    => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
						'deps'     => array(
							'id'    => 'show_add_to_cart',
							'value' => 'yes',
							'type'  => 'show',
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
				'id'     => 'text_toggle',
				'name'   => __( 'Text before products', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'title',
						'name'    => __( 'Title', 'wonder-cart' ),
						'type'    => 'textEditor',
						'default' => yith_sales_block_editor_paragraph( __( 'You may also like...', 'wonder-cart' ) ),
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
						'default'  => __( '-20%', 'wonder-cart' ),
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
								'align'   => 'left',
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
);

return apply_filters( 'yith_sales_thank_you_page_options', $options );
