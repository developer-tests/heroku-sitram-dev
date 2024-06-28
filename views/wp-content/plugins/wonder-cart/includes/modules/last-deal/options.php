<?php
/**
 * Module Last deal Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;


$campaign_type = 'last-deal';
$options       = array(
	'trigger'       => yith_sales_get_trigger_options(
		array(
			'campaign_name' => __( 'Example: “Last deal! 50% discount”', 'wonder-cart' ),
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
						'id'               => 'products_to_offer',
						'name'             => __( 'Products to offer a deal:', 'wonder-cart' ),
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
							'type' => 'products',
							'ids'  => array(),
						),
						'style'            => array(
							'size'  => 'xl',
							'width' => '100%',
						),
					),
					yith_sales_get_box_info_option( 'featured', 'products_to_offer' ),
					yith_sales_get_box_info_option( 'cross-sell', 'products_to_offer' ),
					yith_sales_get_box_info_option( 'upsell', 'products_to_offer' ),
					yith_sales_get_box_info_option( 'related', 'products_to_offer' ),
					yith_sales_get_box_info_option( 'best-seller', 'products_to_offer' ),
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
							'id'    => 'products_to_offer',
							'value' => array(
								'index' => 'type',
								'value' => 'products',
							),
							'type'  => 'hide',
						),
					),
					array(
						'id'      => 'show_on',
						'name'    => __( 'Show on', 'wonder-cart' ),
						'type'    => 'select',
						'default' => 'cart',
						'options' => array(
							array(
								'value' => 'cart',
								'label' => __( 'Cart', 'wonder-cart' ),
							),
							array(
								'value' => 'checkout',
								'label' => __( 'Checkout', 'wonder-cart' ),
							),
							array(
								'value' => 'both',
								'label' => __( 'Both Cart and Checkout', 'wonder-cart' ),
							),
						),
						'style'   => array(
							'size'  => 'xl',
							'width' => '100%',
						),
					),
					array(
						'id'      => 'show_countdown',
						'name'    => __( 'Show countdown', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'yes',
						'style'   => array(
							'size'  => 'md',
						),
					),
					array(
						'id'     => 'countdown_option',
						'name'   => '',
						'type'   => 'inline-fields',
						'fields' => array(
							array(
								'id'      => 'countdown_value',
								'name'    => '',
								'type'    => 'number',
								'min'     => 1,
								'step'    => 1,
								'default' => 60,
								'style'   => array(
									'size'   => 'xl',
									'isMini' => true,
								),
							),
							array(
								'id'      => 'countdown_type',
								'name'    => '',
								'type'    => 'select',
								'class'   => 'yith-sales-discount-type',
								'options' => array(
									array(
										'value' => 'seconds',
										'label' => __( 'seconds', 'wonder-cart' ),
									),
									array(
										'value' => 'minutes',
										'label' => __( 'minutes', 'wonder-cart' ),
									),
								),
								'default' => 'seconds',
								'style'   => array(
									'size'  => 'xl',
									'width' => '178px',
								),
							),
						),
						'deps'   => array(
							'id'    => 'show_countdown',
							'value' => 'yes',
							'type'  => 'show',
						),

					),
				),
			),
			array(
				'id'     => 'pricing',
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
				'id'     => 'product_info_toggle',
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
						'default'  => __( 'Yes, I want it!', 'wonder-cart' ),
						'required' => true,
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
						'default'             => yith_sales_block_editor_paragraph( __( 'Last minute deal! Buy this item for just $19.99', 'wonder-cart' ), 'class="has-text-align-center"' ),
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
								'default' => yith_sales_get_default_setting( '#ffffff', 'yith_sales_modal_colors', 'background_color' ),
								'style'   => array(
									'size' => 'lg',
								),
								'type'    => 'colorpicker',
							),
							array(
								'id'      => 'close_icon_color',
								'name'    => __( 'Close icon', 'wonder-cart' ),
								'default' => yith_sales_get_default_setting( '#ebebeb', 'yith_sales_modal_colors', 'close_color' ),
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

return apply_filters( 'yith_sales_last_deal_options', $options );
