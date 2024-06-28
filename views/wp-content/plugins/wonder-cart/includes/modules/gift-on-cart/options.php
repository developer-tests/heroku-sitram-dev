<?php
/**
 * Module Gift on Cart Options
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Module
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;


$campaign_type = 'gift-on-cart';
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
				'placeholder' => __( 'Example: “Gift Watch”', 'wonder-cart' ),
				'default'     => '',
				'style'       => array(
					'size'      => 'xl',
					'fullWidth' => true,
				),
			),
			array(
				'id'                    => 'gift_rules',
				'name'                  => __( 'Trigger:', 'wonder-cart' ),
				'type'                  => 'giftDiscountRules',
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
						'id'               => 'product_to_offer',
						'name'             => __( 'Products to offer as gift:', 'wonder-cart' ),
						'type'             => 'selectTerms',
						'required'         => true,
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
						'style'            => array(
							'size'  => 'xl',
							'width' => '100%',
						),
					),
					array(
						'id'      => 'num_products_to_show',
						'name'    => __( 'Number of products to show:', 'wonder-cart' ),
						'type'    => 'number',
						'min'     => 2,
						'max'     => 10,
						'step'    => 1,
						'default' => 2,
						'style'   => array(
							'size'      => 'xl',
							'fullWidth' => false,
						),
						'deps'    => array(
							'conditions' => array(
								array(
									'id'        => 'product_to_offer',
									'value'     => array(
										'index' => 'type',
										'value' => 'categories',
									),
									'condition' => '==',
									'type'      => 'show',
								),
								array(
									'id'        => 'product_to_offer',
									'value'     => array(
										'index' => 'ids',
										'value' => 1,
									),
									'condition' => '>=',
									'type'      => 'show',
								),
							),
						),
					),
					array(
						'id'      => 'add_in_cart',
						'name'    => __( 'Automatically add in cart', 'wonder-cart' ),
						'type'    => 'onoff',
						'default' => 'no',
						'style'   => array(
							'size'  => 'md',
						),
						'deps'    => array(
							'conditions' => array(
								array(
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'ids',
											'value' => 1,
										),
										'condition' => '==',
										'type'      => 'show',
									),
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'OR',
						),
					),
					array(
						'id'   => 'add_in_cart_desc',
						'html' => __( 'This option is valid only for simple products offered as gift', 'wonder-cart' ),
						'type' => 'html',
						'deps' => array(
							'conditions' => array(
								array(
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'ids',
											'value' => 1,
										),
										'condition' => '==',
										'type'      => 'show',
									),
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'products',
										),
										'condition' => '==',
										'type'      => 'show',
									),
								),
							),
							'relation'   => 'OR',
						),
					),
					array(
						'id'      => 'max_products_to_add',
						'name'    => __( 'Number of gift(s) the user can pick:', 'wonder-cart' ),
						'type'    => 'number',
						'min'     => 1,
						'max'     => 10,
						'step'    => 1,
						'default' => 1,
						'style'   => array(
							'size'      => 'xl',
							'fullWidth' => false,
						),
						'deps'    => array(
							'conditions' => array(
								array(
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'ids',
											'value' => 1,
										),
										'condition' => '>',
										'type'      => 'show',
									),
								),
								array(
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'type',
											'value' => 'categories',
										),
										'condition' => '==',
										'type'      => 'show',
									),
									array(
										'id'        => 'product_to_offer',
										'value'     => array(
											'index' => 'ids',
											'value' => 0,
										),
										'condition' => '>',
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
				),
				'deps'   => array(
					'id'        => 'product_to_offer',
					'value'     => array(
						'index' => 'ids',
						'value' => 0,
					),
					'condition' => '>',
					'type'      => 'show',
				),
			),
			array(
				'id'     => 'product_info',
				'name'   => __( 'Product Info', 'wonder-cart' ),
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
				'deps'   => array(
					'conditions' => array(
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'type',
									'value' => 'products',
								),
								'condition' => '==',
								'type'      => 'show',
							),
							array(
								'id'        => 'add_in_cart',
								'value'     => 'no',
								'condition' => '==',
								'type'      => 'show',
							),
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'ids',
									'value' => 0,
								),
								'condition' => '>',
								'type'      => 'show',
							),
						),
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'type',
									'value' => 'categories',
								),
								'condition' => '==',
								'type'      => 'show',
							),
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'ids',
									'value' => 0,
								),
								'condition' => '>',
								'type'      => 'show',
							),
						),
					),
					'relation'   => 'or',
				),
			),
			array(
				'id'     => 'add_to_cart',
				'name'   => __( 'Add to cart', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => array(
					array(
						'id'       => 'add_to_cart_button_label',
						'name'     => __( 'Button label', 'wonder-cart' ),
						'type'     => 'text',
						'required' => true,
						'default'  => __( 'Yes, I want it!', 'wonder-cart' ),
						'style'    => array(
							'size'      => 'xl',
							'fullWidth' => true,
						),
					),
				),
				'deps'   => array(
					'id'        => 'product_to_offer',
					'value'     => array(
						'index' => 'ids',
						'value' => 0,
					),
					'condition' => '>',
					'type'      => 'show',
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
						'default'             => yith_sales_block_editor_paragraph( __( 'Pick your FREE GIFT!', 'wonder-cart' ) ),
					),
				),
			),
			array(
				'id'     => 'style_toggle',
				'name'   => __( 'Style', 'wonder-cart' ),
				'type'   => 'toggle',
				'opened' => true,
				'fields' => yith_sales_get_popup_options(),
				'deps'   => array(
					'conditions' => array(
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'ids',
									'value' => 1,
								),
								'condition' => '>',
								'type'      => 'show',
							),
						),
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'type',
									'value' => 'products',
								),
								'condition' => '!==',
								'type'      => 'show',
							),
						),
						array(
							array(
								'id'        => 'add_in_cart',
								'value'     => 'no',
								'condition' => '==',
								'type'      => 'show',
							),
						),
					),
					'relation'   => 'or',
				),
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
				'deps'   => array(
					'conditions' => array(
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'ids',
									'value' => 1,
								),
								'condition' => '>',
								'type'      => 'show',
							),
						),
						array(
							array(
								'id'        => 'product_to_offer',
								'value'     => array(
									'index' => 'type',
									'value' => 'products',
								),
								'condition' => '!==',
								'type'      => 'show',
							),
						),
						array(
							array(
								'id'        => 'add_in_cart',
								'value'     => 'no',
								'condition' => '==',
								'type'      => 'show',
							),
						),
					),
					'relation'   => 'or',
				),
			),
		),
	),
);

return apply_filters( 'yith_sales_gift_on_cart_options', $options );
