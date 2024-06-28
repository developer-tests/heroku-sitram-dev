<?php
/**
 * Module Upsell Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$campaign_type = 'upsell';

$options = array(
	'trigger'       => yith_sales_get_trigger_options(
		array(
			'campaign_name' => __( 'Example: “Upsell shoes”', 'wonder-cart' ),
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
						'id'               => 'products_to_show',
						'name'             => __( 'Products to upsell:', 'wonder-cart' ),
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
							'discount_value' => 20,
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
						'id'       => 'add_to_cart_button_label',
						'name'     => __( 'Button label', 'wonder-cart' ),
						'type'     => 'text',
						'required' => true,
						'default'  => __( 'Buy now', 'wonder-cart' ),
						'style'    => array(
							'size'      => 'xl',
							'fullWidth' => true,
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
				'id'     => 'heading_toggle',
				'name'   => __( 'Heading', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'                  => 'title',
						'name'                => __( 'Title', 'wonder-cart' ),
						'type'                => 'textEditor',
						'backgroundColorMeta' => 'full_size_background_color',
						'default'             => yith_sales_block_editor_paragraph( __( 'Don\'t miss out! 50% off this item today only.', 'wonder-cart' ) ),
					),
				),
			),
			array(
				'id'     => 'style_toggle',
				'name'   => __( 'Style', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => yith_sales_get_popup_options(),
			),
			array(
				'id'     => 'colors_toggle',
				'name'   => __( 'Colors', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'     => 'full_size_colors',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'full_size_background_color',
								'name'    => __( 'Background', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#fff', 'yith_sales_modal_colors', 'background_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'close_icon_color',
								'name'    => __( 'Close icon', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#ebebeb', 'yith_sales_modal_colors', 'close_icon_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
							),
						),
					),
					array(
						'id'     => 'additional_color',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'checkbox_color',
								'name'    => __( 'Check icons', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#a1c746', 'yith_sales_form_colors', 'check_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'deps'    => array(
									'conditions' => array(
										array(
											array(
												'id'    => 'products_to_show',
												'value' => array(
													'index' => 'type',
													'value' => 'products',
												),
												'condition' => '===',
											),
											array(
												'id'    => 'products_to_show',
												'value' => array(
													'index' => 'ids',
													'value' => 1,
												),
												'condition' => '>',
											),
										),
										array(
											array(
												'id'    => 'products_to_show',
												'value' => array(
													'index' => 'type',
													'value' => 'products',
												),
												'condition' => '!==',
											),
											array(
												'id'    => 'num_products_to_show',
												'value' => 1,
												'condition' => '>',
											),
										),
									),
									'relation'   => 'or',
								),
							),
							array(
								'id'      => 'saving_color',
								'name'    => __( 'Saving', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#d10000', 'yith_sales_price_colors', 'saving_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
								'align'   => 'right',
								'deps'    => array(
									'id'    => 'apply_discount',
									'value' => 'yes',
									'condition' > '==',
									'type'  => 'show',
								),
							),
						),
					),
				),
			),
		),
	),
);

return apply_filters( 'yith_sales_upsell_options', $options );
