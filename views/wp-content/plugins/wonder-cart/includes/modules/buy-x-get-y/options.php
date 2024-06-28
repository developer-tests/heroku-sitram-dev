<?php
/**
 * Module BUY X GET Y options
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
				'placeholder' => __( 'Example: “Buy Shoes, Get Socks”', 'wonder-cart' ),
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
				'multiple'         => false,
				'options'          => array(
					array(
						'value' => 'products',
						'label' => __( 'Specific product', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Specific category', 'wonder-cart' ),
					),
				),
				'style'            => array(
					'size'  => 'xl',
					'width' => '100%',
				),
				'buttonLabels'     => array(
					array(
						'value' => 'products',
						'label' => __( 'Select product', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Select category', 'wonder-cart' ),
					),
				),
				'editButtonLabels' => array(
					array(
						'value' => 'products',
						'label' => __( 'Select product', 'wonder-cart' ),
					),
					array(
						'value' => 'categories',
						'label' => __( 'Select category', 'wonder-cart' ),
					),
				),
				'searchModalTerms' => array(
					array(
						'value'       => 'products',
						'term'        => 'product',
						'placeholder' => __( 'Search product', 'wonder-cart' ),
					),
					array(
						'value'       => 'categories',
						'term'        => 'product_cat',
						'placeholder' => __( 'Search category', 'wonder-cart' ),
					),
				),
				'default'          => array(
					'type' => 'categories',
					'ids'  => array(),
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
						'id'               => 'products_to_offer',
						'name'             => __( 'Create a promotion for the purchase of:', 'wonder-cart' ),
						'type'             => 'selectTerms',
						'required'         => true,
						'multiple'         => false,
						'options'          => array(
							array(
								'value' => 'same',
								'label' => __( 'Same items used for trigger', 'wonder-cart' ),
							),
							array(
								'value' => 'products',
								'label' => __( 'Different product', 'wonder-cart' ),
							),
							array(
								'value' => 'categories',
								'label' => __( 'Different product category', 'wonder-cart' ),
							),
						),
						'buttonLabels'     => array(
							array(
								'value' => 'products',
								'label' => __( 'Select product', 'wonder-cart' ),
							),
							array(
								'value' => 'categories',
								'label' => __( 'Select category', 'wonder-cart' ),
							),
						),
						'editButtonLabels' => array(
							array(
								'value' => 'products',
								'label' => __( 'Select product', 'wonder-cart' ),
							),
							array(
								'value' => 'categories',
								'label' => __( 'Select category', 'wonder-cart' ),
							),
						),
						'searchModalTerms' => array(
							array(
								'value'       => 'products',
								'term'        => 'product',
								'placeholder' => __( 'Search product', 'wonder-cart' ),
							),
							array(
								'value'       => 'categories',
								'term'        => 'product_cat',
								'placeholder' => __( 'Search category', 'wonder-cart' ),
							),
						),
						'default'          => array(
							'type' => 'same',
							'ids'  => array(),
						),
						'style'            => array(
							'size'  => 'xl',
							'width' => '100%',
						),
					),
					array(
						'id'   => 'info_same_x_y',
						'type' => 'html',
						'html' => sprintf(/* translators: %1$s is the trigger product %2$s is the html tag */
							__(
								'Example: The user buy %1$s2 pair of Shoes%2$s and get the %1$s3rd pair of Shoes%2$s with a 20%3$s off',
								'wonder-cart'
							),
							'<strong>',
							'</strong>',
							'%'
						),
						'deps' => array(
							'id'    => 'products_to_offer',
							'value' => array(
								'index' => 'type',
								'value' => 'same',
							),
							'type'  => 'show',
						),
					),
					array(
						'id'   => 'info_categories_x_y',
						'type' => 'html',
						'html' => sprintf(/* translators: %1$s is the trigger product %2$s html tag */
							__(
								'Example: The user buy %1$s2 pair of Shoes%2$s and get a %1$s1 pair of Socks%2$s with a 20%3$s off.',
								'wonder-cart'
							),
							'<strong>',
							'</strong>',
							'%'
						),
						'deps' => array(
							'id'    => 'products_to_offer',
							'value' => array(
								'index' => 'type',
								'value' => 'categories',
							),
							'type'  => 'show',
						),
					),
					array(
						'id'   => 'info_products_x_y',
						'type' => 'html',
						'html' => sprintf( /* translators: %1$s is the trigger product %2$s html tag */
							__(
								'Example: The user buy %1$s2 pair of Shoes%2$s and get a %1$s1 pair of Socks%2$s with a 20%3$s off.',
								'wonder-cart'
							),
							'<strong>',
							'</strong>',
							'%'
						),
						'deps' => array(
							'id'    => 'products_to_offer',
							'value' => array(
								'index' => 'type',
								'value' => 'products',
							),
							'type'  => 'show',
						),
					),
					array(
						'id'              => 'x_y_promo_rule',
						'name'            => __( 'Promo details', 'wonder-cart' ),
						'buttonLabel'     => __( 'Set promo rule', 'wonder-cart' ),
						'editButtonLabel' => __( 'Edit promo rule', 'wonder-cart' ),
						'type'            => 'xyPromoRule',
						'required'        => true,
						'default'         => array(),
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
				'id'     => 'heading_add_to_cart',
				'name'   => __( 'Add to cart', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'add_in_cart',
						'name'    => __( 'Automatically add free items in cart', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'no',
						'style'   => array(
							'size'  => 'md',
						),
						'deps'    => array(
							'conditions' => array(
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'same',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'or',
						),
					),
					array(
						'id'   => 'add_in_cart_desc',
						'html' => __( 'This option is valid, only for simple products offered as free', 'wonder-cart' ),
						'type' => 'html',
						'deps' => array(
							'conditions' => array(
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'same',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'or',
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
							'conditions' => array(
								array(
									array(
										'id'    => 'add_in_cart',
										'value' => 'no',
										'type'  => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '<',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '<',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'fixed',
										),
										'condition' => '===',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'or',
						),
					),
				),
			),
			array(
				'id'     => 'heading_toggle',
				'name'   => __( 'Notices', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'      => 'title',
						'name'    => __( 'Modal window:', 'wonder-cart' ),
						'type'    => 'textEditor',
						'default' => yith_sales_block_editor_paragraph( __( 'Promo unblocked! Get this item with a special discount.', 'wonder-cart' ) ),
						'deps'    => array(
							'conditions' => array(
								array(
									array(
										'id'    => 'add_in_cart',
										'value' => 'no',
										'type'  => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '<',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'percentage',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_value',
											'value' => 100,
										),
										'condition' => '<',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'trigger_product',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '===',
										'type'      => 'show',
									),
									array(
										'id'        => 'products_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '!==',
										'type'      => 'show',
									),
									array(
										'id'        => 'x_y_promo_rule',
										'value'     => array(
											'index' => 'discount_type',
											'value' => 'fixed',
										),
										'condition' => '===',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'or',
						),
					),
					array(
						'id'      => 'show_product_notice',
						'name'    => __( 'Show notice in product pages', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'no',
					),
					array(
						'id'      => 'product_notice',
						'name'    => __( 'Product page:', 'wonder-cart' ),
						'type'    => 'textEditor',
						'default' => yith_sales_block_editor_paragraph( __( 'New Promo! Get 1 pair of socks with a 70% off!', 'wonder-cart' ) ),
						'deps'    => array(
							'id'    => 'show_product_notice',
							'value' => 'yes',
							'type'  => 'show',
						),
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
				),
			),
		),
	),
	'promotion'     => yith_sales_get_promotion_options(
		array(
			'title'               => __( 'Step 4 - Promotion', 'wonder-cart' ),
			'text_to_show_popup'  => __( 'Unblock our promo! Buy X and get Y with a special discount!', 'wonder-cart' ),
			'text_to_show_banner' => __( 'Unblock our promo! Buy X and get Y with a special discount!', 'wonder-cart' ),
			'cta_button_label'    => __( 'Get product X', 'wonder-cart' ),
		),
		$campaign_type
	),
);

return apply_filters( 'yith_sales_bogo_options', $options );
