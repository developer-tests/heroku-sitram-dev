<?php
/**
 * YITH Sales Functions
 *
 * @package YITH\Sales\Functions
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'yith_sales' ) ) {
	/**
	 * Unique access to instance of YITH_sales class
	 *
	 * @return YITH_Sales|YITH_Sales_Extended
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales() { // phpcs:ignore

		if ( defined( 'YITH_SALES_EXTENDED' ) && file_exists( YITH_SALES_INC . 'class-yith-sales-extended.php' ) ) {
			return YITH_Sales_Extended::get_instance();
		}

		return YITH_Sales::get_instance();
	}
}

if ( ! function_exists( 'yith_sales_get_panel_page' ) ) {
	/**
	 * Return the panel page of plugin
	 *
	 * @return string
	 */
	function yith_sales_get_panel_page() {

		return apply_filters( 'yith_sales_panel_page', 'bluehost#/store/sales_discounts' );
	}
}

if ( ! function_exists( 'yith_sales_get_campaigns' ) ) {
	/**
	 * Return the list of active campaigns
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_campaigns() {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$sales_campaigns = array();
		$args            = array(
			'post_type'      => YITH_Sales_Post_Type::$post_type,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'campaign_status',
					'value'   => 'inactive',
					'compare' => 'NOT LIKE',
				),
				array(
					'key'     => 'schedule_date_from',
					'value'   => time(),
					'compare' => '<=',
				),
			),
			'posts_per_page' => - 1,
			'fields'         => 'ids',
		);
		$campaigns       = get_posts( $args );
		if ( $campaigns ) {
			foreach ( $campaigns as $campaign ) {
				$type       = get_post_meta( $campaign, 'type', 1 );
				$class_name = yith_sales_get_class_campaign( $type );
				if ( class_exists( $class_name ) ) {
					$sales_campaigns[] = new $class_name( $campaign );
				}
			}
		}

		// phpcs:enable
		return $sales_campaigns;
	}
}
if ( ! function_exists( 'yith_sales_get_campaign' ) ) {
	/**
	 * Get a campaign
	 *
	 * @param int $campaign_id The campaign ID.
	 *
	 * @return false|Abstract_YITH_Sales_Campaign
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_campaign( $campaign_id ) {
		$type       = get_post_meta( $campaign_id, 'type', 1 );
		$class_name = yith_sales_get_class_campaign( $type );
		if ( class_exists( $class_name ) ) {
			return new $class_name( $campaign_id );
		}

		return false;
	}
}
if ( ! function_exists( 'yith_sales_grouped_campaigns' ) ) {
	/**
	 * Return campaigns grouped
	 *
	 * @param array $campaigns Campaigns to group.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_grouped_campaigns( $campaigns ) {
		$sales_campaigns = array();
		if ( $campaigns ) {
			foreach ( $campaigns as $campaign ) {
				if ( $campaign->is_active() ) {
					$type                       = $campaign->get_type();
					$sales_campaigns[ $type ][] = $campaign;
				}
			}
		}

		return $sales_campaigns;
	}
}

if ( ! function_exists( 'yith_sales_get_class_campaign' ) ) {
	/**
	 * Return the name of the class for a type campaign
	 *
	 * @param string $type Campaign type.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_class_campaign( $type ) {
		$classname = explode( '-', $type );
		$classname = implode( '_', array_map( 'ucfirst', $classname ) );
		$classname = 'YITH_Sales_' . $classname . '_Campaign';

		return $classname;
	}
}

if ( ! function_exists( 'yith_sales_get_controller_by_type' ) ) {
	/**
	 * Return the name of the class controller by campaign type
	 *
	 * @param string $type Campaign type.
	 *
	 * @return string|bool
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_controller_by_type( $type ) {
		$modules    = yith_sales()->modules;
		$module_key = array_search( $type, array_column( $modules, 'id' ), true );

		return $modules[ $module_key ]['controller'] ?? false;
	}
}

if ( ! function_exists( 'yith_sales_is_product_in_list' ) ) {
	/**
	 * Check if the product is in the list of taxonomy terms
	 *
	 * @param WC_Product $product The product to check.
	 * @param array      $list_ids The list of term ids.
	 * @param bool       $check_variations Extend the check to variations.
	 *
	 * @return bool
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_is_product_in_list( $product, $list_ids, $check_variations = true ) {
		if ( ! $product ) {
			return false;
		}
		$product_id = $product->get_id();
		$is_in_list = in_array( $product_id, $list_ids, true );

		if ( ! $is_in_list && $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
			$is_in_list = in_array( $product_id, $list_ids, true );
		}

		if ( ! $is_in_list && $check_variations ) {
			$ids_to_check = array();
			if ( $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				if ( $parent instanceof WC_Product_Variable ) {
					$ids_to_check = $parent->get_children();
				}
			} elseif ( $product->is_type( 'variable' ) ) {
				$ids_to_check = $product->get_children();
			}

			$is_in_list = count( array_intersect( $list_ids, $ids_to_check ) ) > 0;
		}

		/**
		 * APPLY_FILTERS: yith_sales_is_product_in_list
		 * Check if the product is in the list.
		 *
		 * @param bool       $is_in_list The value to filter.
		 * @param WC_Product $product The product.
		 * @param array      $list_ids The ids list.
		 * @param bool       $check_variations Extend the check to variations.
		 *
		 * @return bool
		 */
		return apply_filters( 'yith_sales_is_product_in_taxonomy_list', $is_in_list, $product, $list_ids, $check_variations );
	}
}

if ( ! function_exists( 'yith_sales_is_product_in_taxonomy_list' ) ) {
	/**
	 * Check if the product is in the list of taxonomy terms
	 *
	 * @param WC_Product $product The product to check.
	 * @param array      $list_ids The list of term ids.
	 * @param string     $taxonomy_to_check The taxonomy name.
	 *
	 * @return bool
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_is_product_in_taxonomy_list( $product, $list_ids, $taxonomy_to_check = 'product_cat' ) {
		if ( ! $product || ! is_array( $list_ids ) ) {
			return false;
		}

		$terms      = yith_sales_get_product_term_ids( $product, $taxonomy_to_check );
		$is_in_list = count( array_intersect( $list_ids, $terms ) ) > 0;

		/**
		 * APPLY_FILTERS: yith_sales_is_product_in_taxonomy_list
		 * Check if the product is in the list.
		 *
		 * @param bool       $is_in_list The value to filter.
		 * @param WC_Product $product The product.
		 * @param array      $list_ids The ids list.
		 * @param string     $taxonomy_to_check The taxonomy: product_cat, product_tag,etc.
		 *
		 * @return bool
		 */
		return apply_filters( 'yith_sales_is_product_in_taxonomy_list', $is_in_list, $product, $list_ids, $taxonomy_to_check );
	}
}

if ( ! function_exists( 'yith_sales_get_product_term_ids' ) ) {
	/**
	 * Return the custom term ids of product
	 *
	 * @param WC_Product $product The product.
	 * @param string     $taxonomy_name the taxonomy.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_product_term_ids( $product, $taxonomy_name = 'product_cat' ) {
		$product_id = 'variation' === $product->get_type() ? $product->get_parent_id() : $product->get_id();

		return wc_get_product_terms( $product_id, $taxonomy_name, array( 'fields' => 'ids' ) );
	}
}

if ( ! function_exists( 'yith_sales_get_trigger_options' ) ) {
	/**
	 * Return the options for the tab trigger inside the campaign editor.
	 *
	 * @param array  $args Array of arguments to override.
	 * @param string $campaign_type Type of campaign.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_trigger_options( $args, $campaign_type ) {
		$default_args = array(
			'title'         => __( 'Step 1 - Trigger', 'wonder-cart' ),
			'campaign_name' => __( 'Enjoy the special 3x2 on all our perfumes!', 'wonder-cart' ),
			'trigger_title' => __( 'Trigger when the user add to cart:', 'wonder-cart' ),
		);

		$args    = wp_parse_args( $args, $default_args );
		$options = array(
			'menuItem' => __( 'Trigger', 'wonder-cart' ),
			'title'    => $args['title'],
			'fields'   => array(
				array(
					'id'          => 'campaign_name',
					'name'        => __( 'Campaign name:', 'wonder-cart' ),
					'description' => __( 'Customers will see this in their cart and during checkout.', 'wonder-cart' ),
					'type'        => 'text',
					'placeholder' => $args['campaign_name'],
					'required'    => true,
					'error'       => __( 'Please enter a name to identify this campaign.', 'wonder-cart' ),
					'default'     => '',
					'style'       => array(
						'size'      => 'xl',
						'fullWidth' => true,
					),
				),
				array(
					'id'               => 'trigger_product',
					'name'             => $args['trigger_title'],
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
		);

		return apply_filters( 'yith_sales_trigger_options', $options, $campaign_type );
	}
}

if ( ! function_exists( 'yith_sales_get_promotion_options' ) ) {
	/**
	 * Return the options for the tab promotion inside the campaign editor.
	 *
	 * @param array  $args Array of arguments to override.
	 * @param string $campaign_type Type of campaign.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function yith_sales_get_promotion_options( $args, $campaign_type ) {
		$default_args = array(
			'title'               => __( 'Step 3 - Promotion', 'wonder-cart' ),
			'text_to_show_popup'  => __( 'Enjoy the special 3x2 on all our perfumes!', 'wonder-cart' ),
			'text_to_show_banner' => __( 'Enjoy the special 3x2 on all our perfumes!', 'wonder-cart' ),
			'cta_button_label'    => __( 'Shop now', 'wonder-cart' ),
		);

		$args = wp_parse_args( $args, $default_args );

		$options = array(
			'menuItem' => __( 'Promotion', 'wonder-cart' ),
			'title'    => $args['title'],
			'fields'   => array(
				array(
					'id'     => 'campaign_promotion_toggle',
					'name'   => __( 'Campaign Promotion', 'wonder-cart' ),
					'type'   => 'toggle',
					'opened' => true,
					'fields' => array(
						array(
							'id'      => 'promote_campaign',
							'name'    => __( 'Promote campaign in your shop', 'wonder-cart' ),
							'type'    => 'onoff',
							'default' => 'yes',
							'style'   => array(
								'size' => 'md',
							),
						),
						array(
							'id'      => 'promotion_style',
							'name'    => __( 'Promotion style:', 'wonder-cart' ),
							'type'    => 'select',
							'options' => array(
								array(
									'value' => 'popup',
									'label' => __( 'Popup Window', 'wonder-cart' ),
								),
								array(
									'value' => 'banner',
									'label' => __( 'Top banner', 'wonder-cart' ),
								),
							),
							'default' => 'popup',
							'style'   => array(
								'size'  => 'xl',
								'width' => '100%',
							),
							'deps'    => array(
								'id'    => 'promote_campaign',
								'value' => 'yes',
								'type'  => 'show',
							),
						),
						array(
							'id'                  => 'text_to_show_popup',
							'name'                => __( 'Text to show on popup:', 'wonder-cart' ),
							'type'                => 'textEditor',
							'backgroundColorMeta' => 'background_promotion_popup_color',
							'default'             => yith_sales_block_editor_paragraph( $args['text_to_show_popup'] ),
							'deps'                => array(
								array(
									'id'    => 'promote_campaign',
									'value' => 'yes',
									'type'  => 'show',
								),
								array(
									'id'    => 'promotion_style',
									'value' => 'popup',
									'type'  => 'show',
								),
							),
						),
						array(
							'id'                  => 'text_to_show_banner',
							'name'                => __( 'Text to show on banner:', 'wonder-cart' ),
							'type'                => 'textEditor',
							'backgroundColorMeta' => 'banner_color',
							'default'             => yith_sales_block_editor_paragraph( $args['text_to_show_banner'] ),
							'deps'                => array(
								array(
									'id'    => 'promote_campaign',
									'value' => 'yes',
									'type'  => 'show',
								),
								array(
									'id'    => 'promotion_style',
									'value' => 'banner',
									'type'  => 'show',
								),
							),
						),
						array(
							'id'      => 'show_cta',
							'name'    => __( 'Show Call to Action button', 'wonder-cart' ),
							'type'    => 'onoff',
							'default' => 'no',
							'style'   => array(
								'size' => 'md',
							),
							'deps'    => array(
								'id'    => 'promote_campaign',
								'value' => 'yes',
								'type'  => 'show',
							),
						),
						array(
							'id'       => 'cta_button_label',
							'name'     => __( 'Button label', 'wonder-cart' ),
							'type'     => 'text',
							'required' => true,
							'default'  => $args['cta_button_label'],
							'style'    => array(
								'size'      => 'xl',
								'fullWidth' => true,
							),
							'deps'     => array(
								array(
									'id'    => 'promote_campaign',
									'value' => 'yes',
									'type'  => 'show',
								),
								array(
									'id'    => 'show_cta',
									'value' => 'yes',
									'type'  => 'show',
								),
							),
						),
						array(
							'id'       => 'cta_button_url',
							'name'     => __( 'Button URL', 'wonder-cart' ),
							'type'     => 'search-link',
							'default'  => '',
							'required' => true,
							'style'    => array(
								'size'      => 'xl',
								'fullWidth' => true,
							),
							'deps'     => array(
								array(
									'id'    => 'promote_campaign',
									'value' => 'yes',
									'type'  => 'show',
								),
								array(
									'id'    => 'show_cta',
									'value' => 'yes',
									'type'  => 'show',
								),
							),
						),
					),
				),
				array(
					'id'     => 'style_toggle',
					'name'   => __( 'Style', 'wonder-cart' ),
					'type'   => 'toggle',
					'opened' => true,
					'fields' => yith_sales_get_popup_options( 'promotion' ),
					'deps'   => array(
						array(
							'id'    => 'promote_campaign',
							'value' => 'yes',
							'type'  => 'show',
						),
						array(
							'id'    => 'promotion_style',
							'value' => 'popup',
							'type'  => 'show',
						),
					),
				),
				array(
					'id'     => 'campaign_banner_toggle',
					'name'   => __( 'Banner colors', 'wonder-cart' ),
					'type'   => 'toggle',
					'opened' => true,
					'fields' => array(
						array(
							'id'      => 'banner_color',
							'name'    => __( 'Background', 'wonder-cart' ),
							'type'    => 'colorpicker',
							'default' => '#E9355C',
						),
						array(
							'id'      => 'upload_bg_image',
							'name'    => __( 'Upload background image', 'wonder-cart' ),
							'type'    => 'onoff',
							'default' => 'no',
							'style'   => array(
								'size' => 'md',
							),
						),
						array(
							'id'      => 'upload_bg_image_field',
							'type'    => 'media',
							'default' => array(
								'id'       => '',
								'type'     => '',
								'url'      => '',
								'fileName' => '',
							),
							'style'   => array(
								'allowedTypes' => array( 'image' ),
								'showTabs'     => 'no',
								'size'         => 'xl',
							),
							'deps'    => array(
								'id'    => 'upload_bg_image',
								'value' => 'yes',
								'type'  => 'show',
							),
						),
					),
					'deps'   => array(
						array(
							'id'    => 'promote_campaign',
							'value' => 'yes',
							'type'  => 'show',
						),
						array(
							'id'    => 'promotion_style',
							'value' => 'banner',
							'type'  => 'show',
						),
					),
				),
				array(
					'id'     => 'campaign_promotion_popup_colors_toggle',
					'name'   => __( 'Popup colors', 'wonder-cart' ),
					'type'   => 'toggle',
					'opened' => true,
					'fields' => array(
						array(
							'id'     => 'promotion_popup_color',
							'name'   => '',
							'type'   => 'inline-fields',
							'fields' => array(
								array(
									'id'      => 'background_promotion_popup_color',
									'name'    => __( 'Background', 'wonder-cart' ),
									'default' => yith_sales_get_default_setting( '#fff', 'yith_sales_modal_colors', 'background_color' ),
									'style'   => array(
										'size' => 'lg',
									),
									'type'    => 'colorpicker',
								),
								array(
									'id'      => 'color_promotion_popup_close_icon',
									'name'    => __( 'Close Icon', 'wonder-cart' ),
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
							'id'      => 'upload_promotion_popup_bg_image',
							'name'    => __( 'Upload background image', 'wonder-cart' ),
							'type'    => 'onoff',
							'default' => 'no',
							'style'   => array(
								'size' => 'md',
							),
						),
						array(
							'id'      => 'upload_bg_popup_image_field',
							'type'    => 'media',
							'default' => array(
								'id'       => '',
								'type'     => '',
								'url'      => '',
								'fileName' => '',
							),
							'style'   => array(
								'allowedTypes' => array( 'image' ),
								'showTabs'     => 'no',
								'size'         => 'xl',
							),
							'deps'    => array(
								'id'    => 'upload_promotion_popup_bg_image',
								'value' => 'yes',
								'type'  => 'show',
							),
						),
					),
					'deps'   => array(
						array(
							'id'    => 'promote_campaign',
							'value' => 'yes',
							'type'  => 'show',
						),
						array(
							'id'    => 'promotion_style',
							'value' => 'popup',
							'type'  => 'show',
						),
					),
				),
				array(
					'id'     => 'campaign_promotion_buttons_colors_toggle',
					'name'   => __( 'Button colors', 'wonder-cart' ),
					'type'   => 'toggle',
					'opened' => true,
					'fields' => array(
						array(
							'id'     => 'promotion_button_color',
							'name'   => '',
							'type'   => 'inline-fields',
							'fields' => array(
								array(
									'id'      => 'background_promotion_button_color',
									'name'    => __( 'Background', 'wonder-cart' ),
									'default' => '#1B1A1A',
									'style'   => array(
										'size' => 'lg',
									),
									'type'    => 'colorpicker',
								),
								array(
									'id'      => 'color_promotion_button_text',
									'name'    => __( 'Text', 'wonder-cart' ),
									'default' => '#fff',
									'style'   => array(
										'size' => 'lg',
									),
									'type'    => 'colorpicker',
									'align'   => 'right',
								),
							),
						),
						array(
							'id'     => 'promotion_button_hover_color',
							'name'   => '',
							'type'   => 'inline-fields',
							'fields' => array(
								array(
									'id'      => 'background_promotion_button_hover_color',
									'name'    => __( 'Background Hover', 'wonder-cart' ),
									'default' => '#F8F8F8',
									'style'   => array(
										'size' => 'lg',
									),
									'type'    => 'colorpicker',
								),
								array(
									'id'      => 'color_promotion_button_hover_text',
									'name'    => __( 'Text Hover', 'wonder-cart' ),
									'default' => '#000',
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
						array(
							'id'    => 'promote_campaign',
							'value' => 'yes',
							'type'  => 'show',
						),
					),
				),
			),
		);

		return apply_filters( 'yith_sales_promotion_options', $options, $campaign_type );
	}
}

if ( ! function_exists( 'yith_sales_get_popup_options' ) ) {

	/**
	 * Get the popup options
	 *
	 * @param string $option_type The option type.
	 *
	 * @return array[]
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_popup_options( $option_type = 'popup' ) {

		$modal_border_radius = yith_sales_get_default_setting( '15', 'yith_sales_modal_border', 'border_radius' );
		$popup_options       = array(
			array(
				'id'      => "show_{$option_type}_full_size",
				'name'    => __( 'Show full size', 'wonder-cart' ),
				'type'    => 'onoff',
				'default' => 'no',
				'style'   => array(
					'size' => 'md',
				),
			),
			array(
				'id'        => "{$option_type}_border_radius_item",
				'name'      => __( 'Border radius (px)', 'wonder-cart' ),
				'type'      => 'dimensions',
				'default'   => array(
					'dimension' => array(
						'top'    => $modal_border_radius,
						'right'  => $modal_border_radius,
						'bottom' => $modal_border_radius,
						'left'   => $modal_border_radius,
					),
					'linked'    => 'yes',
				),
				'style'     => array(
					'size'   => 'xl',
					'isMini' => true,
				),
				'showLabel' => 'no',
				'min'       => 0,
				'deps'      => array(
					'id'    => "show_{$option_type}_full_size",
					'value' => 'no',
					'type'  => 'show',
				),
			),
			array(
				'id'      => "show_{$option_type}_position",
				'name'    => __( 'Popup position', 'wonder-cart' ),
				'type'    => 'select',
				'default' => 'center',
				'options' => array(
					array(
						'value' => 'center',
						'label' => __( 'Center', 'wonder-cart' ),
					),
					array(
						'value' => 'top_left',
						'label' => __( 'Top Left', 'wonder-cart' ),
					),
					array(
						'value' => 'top_right',
						'label' => __( 'Top Right', 'wonder-cart' ),
					),
					array(
						'value' => 'bottom_left',
						'label' => __( 'Bottom Left', 'wonder-cart' ),
					),
					array(
						'value' => 'bottom_right',
						'label' => __( 'Bottom Right', 'wonder-cart' ),
					),

				),
				'style'   => array(
					'size'  => 'xl',
					'width' => '100%',
				),
				'deps'    => array(
					'id'    => "show_{$option_type}_full_size",
					'value' => 'no',
					'type'  => 'show',
				),
			),
			array(
				'id'      => "{$option_type}_animation",
				'name'    => __( 'Popup animation', 'wonder-cart' ),
				'type'    => 'select',
				'default' => 'fade',
				'options' => array(
					array(
						'value' => 'fade',
						'label' => __( 'Fade', 'wonder-cart' ),
					),
					array(
						'value' => 'zoom',
						'label' => __( 'Zoom', 'wonder-cart' ),
					),
				),
				'style'   => array(
					'size'  => 'xl',
					'width' => '100%',
				),
			),
		);

		return $popup_options;
	}
}

if ( ! function_exists( 'yith_sales_get_product_to_show_options' ) ) {

	/**
	 * Get the list of product to show
	 *
	 * @return array[]
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_product_to_show_options() {
		$product_to_show_options = array(
			array(
				'value'          => 'products',
				'label'          => __( 'Specific products', 'wonder-cart' ),
				'noProductLabel' => __( 'No products selected.', 'wonder-cart' ),
			),
			array(
				'value'          => 'best-seller',
				'label'          => __( 'Best seller products', 'wonder-cart' ),
				'noProductLabel' => __( 'No best seller products found.', 'wonder-cart' ),
			),
			array(
				'value'          => 'featured',
				'label'          => __( 'Featured products', 'wonder-cart' ),
				'noProductLabel' => __( 'No featured products found.', 'wonder-cart' ),
			),
			array(
				'value'          => 'cross-sell',
				'label'          => __( 'Cross-sell products', 'wonder-cart' ),
				'noProductLabel' => __( 'No cross-sell products found.', 'wonder-cart' ),
			),
			array(
				'value'          => 'upsell',
				'label'          => __( 'Upsell products', 'wonder-cart' ),
				'noProductLabel' => __( 'No upsell products found.', 'wonder-cart' ),
			),
			array(
				'value'          => 'related',
				'label'          => __( 'Related products', 'wonder-cart' ),
				'noProductLabel' => __( 'No related products found.', 'wonder-cart' ),
			),
		);

		return $product_to_show_options;
	}
}

if ( ! function_exists( 'yith_sales_get_box_info_options' ) ) {
	/**
	 * Return the option for a box info
	 *
	 * @param string $box_type The type.
	 * @param string $dep_id The dep id.
	 *
	 * @return array
	 */
	function yith_sales_get_box_info_option( $box_type = 'featured', $dep_id = 'products_to_show' ) {
		$boxes_info = array(
			'featured'    => array(
				'id'   => 'box_featured',
				'type' => 'boxWithInfo',
				'info' => array(
					'icon' => 'InformationCircleIcon',
					'info' => __(
						'To mark a product as featured, go to Products > All products, find the product you want to set as featured, and select the Star icon.',
						'wonder-cart'
					),
				),
				'deps' => array(
					'id'    => $dep_id,
					'value' => array(
						'index' => 'type',
						'value' => 'featured',
					),
					'type'  => 'show',
				),
			),
			'cross-sell'  => array(
				'id'   => 'box_cross_sell',
				'type' => 'boxWithInfo',
				'info' => array(
					'icon' => 'InformationCircleIcon',
					'info' => __(
						'To link products as cross-sells, go to Products > All products, select a product and add the products you want to link as cross-sells from the "Linked Products" section.',
						'wonder-cart'
					),
				),
				'deps' => array(
					'id'    => $dep_id,
					'value' => array(
						'index' => 'type',
						'value' => 'cross-sell',
					),
					'type'  => 'show',
				),
			),
			'upsell'      => array(
				'id'   => 'box_upsell',
				'type' => 'boxWithInfo',
				'info' => array(
					'icon' => 'InformationCircleIcon',
					'info' => __(
						'To link products as upsell, go to Products > All products, select a product and add the products you want to link as upsell from the "Linked Products" section',
						'wonder-cart'
					),
				),
				'deps' => array(
					'id'    => $dep_id,
					'value' => array(
						'index' => 'type',
						'value' => 'upsell',
					),
					'type'  => 'show',
				),
			),
			'related'     => array(
				'id'   => 'box_related',
				'type' => 'boxWithInfo',
				'info' => array(
					'icon' => 'InformationCircleIcon',
					'info' => __(
						'Display random products on the current product\'s category or tag',
						'wonder-cart'
					),
				),
				'deps' => array(
					'id'    => $dep_id,
					'value' => array(
						'index' => 'type',
						'value' => 'related',
					),
					'type'  => 'show',
				),
			),
			'best-seller' => array(
				'id'   => 'box_best-seller',
				'type' => 'boxWithInfo',
				'info' => array(
					'icon' => 'InformationCircleIcon',
					'info' => __(
						'Your best-selling products will be shown',
						'wonder-cart'
					),
				),
				'deps' => array(
					'id'    => $dep_id,
					'value' => array(
						'index' => 'type',
						'value' => 'best-seller',
					),
					'type'  => 'show',
				),
			),
		);

		return $boxes_info[ $box_type ];
	}
}

if ( ! function_exists( 'yith_sales_get_allowed_countries' ) ) {
	/**
	 * Get all allowed countries
	 *
	 * @return array|int[]|string[]
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_allowed_countries() {

		$allowed_countries   = WC()->countries->get_shipping_countries();
		$shipping_continents = WC()->countries->get_shipping_continents();
		$all_countries       = array();
		foreach ( $shipping_continents as $continent ) {
			$countries = array_intersect( array_keys( $allowed_countries ), $continent['countries'] );
			foreach ( $countries as $country_code ) {
				$all_countries[ $country_code ] = $allowed_countries[ $country_code ];
			}
		}

		return $all_countries;
	}
}

if ( ! function_exists( 'yith_sales_get_allowed_states' ) ) {
	/**
	 * Get all allowed states
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_allowed_states() {
		$allowed_states    = array();
		$allowed_countries = yith_sales_get_allowed_countries();
		foreach ( $allowed_countries as $country_code => $allowed_country ) {
			$states                          = WC()->countries->get_states( $country_code );
			$allowed_states[ $country_code ] = $states;
		}

		return $allowed_states;
	}
}

if ( ! function_exists( 'yith_sales_get_options' ) ) {
	/**
	 * Map the panel options to show in react
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_options() {
		$groups          = array(
			'general-settings',
		);
		$filtered_groups = array();
		foreach ( $groups as $group_id ) {
			$file_name = YITH_SALES_DIR . '/plugin-options/' . $group_id . '-options.php';

			if ( file_exists( $file_name ) ) {
				$options = include $file_name;
				add_filter(
					'yith-sales-for-woocommerce_settings-' . $group_id,
					function ( $args ) use ( $options ) {
						return $options;
					}
				);
				if ( isset( $options['sections'] ) ) {
					foreach ( $options['sections'] as $section_key => $section ) {
						$inner_options = $section['options'];
						foreach ( $inner_options as $key => $value ) {
							$option_type = $value['type'];
							$default     = false;

							if ( 'inline-fields' === $option_type ) {
								$default = array();
								$fields  = $value['fields'];
								foreach ( $fields as $field ) {
									$default[ $field['id'] ] = ! empty( $field['default'] ) ? $field['default'] : '';
								}
							} elseif ( 'multiselect' === $option_type ) {
								$default = ! empty( $value['default'] ) ? $value['default'] : array();
							} else {
								$default = ! empty( $value['default'] ) ? $value['default'] : '';
							}

							$options['sections'][ $section_key ]['options'][ $key ]['value'] = get_option( $value['id'], $default );
						}
					}
				}
				$filtered_groups[ $group_id ] = $options;
			}
		}

		return $filtered_groups;
	}
}

if ( ! function_exists( 'yith_sales_get_pages_options' ) ) {
	/**
	 * Get the pages options to show in a field
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_pages_options() {
		$args    = array(
			'sort_column' => 'menu_order',
			'sort_order'  => 'ASC',
			'post_status' => 'publish,private,draft',
			'value_field' => 'ID',
		);
		$pages   = get_pages( $args );
		$options = array();
		foreach ( $pages as $page ) {
			/* translators: %d: ID of a post. */
			$title     = '' === $page->post_title ? sprintf( __( '#%d (no title)' ), $page->ID ) : $page->post_title;
			$options[] = array(
				'value' => "$page->ID",
				'label' => $title,
			);
		}

		return $options;
	}
}

if ( ! function_exists( 'yith_sales_get_default_setting' ) ) {
	/**
	 * Get default setting
	 *
	 * @param mixed  $default Default value.
	 * @param string $key Key of option.
	 * @param string $subkey Optional sub-key.
	 *
	 * @return bool|mixed
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_default_setting( $default, $key, $subkey = '' ) {
		$option = get_option( $key );

		if ( $option ) {
			if ( ! empty( $subkey ) ) {
				$default = $option[ $subkey ] ?? $default;
			} else {
				$default = $option;
			}
		}

		return $default;
	}
}

if ( ! function_exists( 'yith_sales_calculate_discount' ) ) {
	/**
	 * Calculate the discount to apply
	 *
	 * @param float $price Product price.
	 * @param array $discount Discount.
	 *
	 * @return float
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_calculate_discount( $price, $discount ) {
		$saved_value = 0;

		if ( isset( $discount['discount_value'], $discount['discount_type'] ) && $discount['discount_value'] > 0 ) {
			$saved_value = 'percentage' === $discount['discount_type'] ? ( (float) $discount['discount_value'] * (float) $price ) / 100 : $discount['discount_value'];
		}

		return $saved_value;
	}
}

if ( ! function_exists( 'yith_sales_get_saving' ) ) {
	/**
	 * Get default setting
	 *
	 * @param float $price Product price.
	 * @param float $discounted_price Discounted price.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_saving( $price, $discounted_price ) {
		$discounted = empty( $price ) ? 0 : $discounted_price / $price * 100;

		/* translators: %d is the amount of discount */

		return apply_filters( 'yith_sales_get_saving', sprintf( __( 'Save %d%%', 'wonder-cart' ), (int) $discounted ), $discounted, $price, $discounted_price );
	}
}

if ( ! function_exists( 'yith_sales_get_json' ) ) {

	/**
	 * Get an array and return a json
	 *
	 * @param array $data The data.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_json( $data ) {
		$data_json = wp_json_encode( $data );
		$data_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data_json ) : _wp_specialchars( $data_json, ENT_QUOTES, 'UTF-8', true );

		return $data_attr;
	}
}

if ( ! function_exists( 'yith_sales_get_cart_subtotal' ) ) {
	/**
	 * Get the cart total
	 *
	 * @return float
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_cart_subtotal() {
		$total = WC()->cart->get_displayed_subtotal();
		if ( WC()->cart->display_prices_including_tax() ) {
			$total = $total - WC()->cart->get_discount_tax();
		}

		return round( $total, wc_get_price_decimals(), PHP_ROUND_HALF_UP );
	}
}

if ( ! function_exists( 'yith_sales_get_product_subtotal' ) ) {
	/**
	 * Get the product row subtotal using the price not filtered
	 * Gets the tax etc to avoid rounding issues.
	 * When on the checkout (review order), this will get the subtotal based on the customer's tax rate rather than the base rate.
	 *
	 * @param WC_Product $product Product object.
	 * @param float      $price The price.
	 * @param int        $quantity Quantity being purchased.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_product_subtotal( $product, $price, $quantity ) {

		if ( $product->is_taxable() ) {
			if ( WC()->cart->display_prices_including_tax() ) {
				$row_price        = wc_get_price_including_tax(
					$product,
					array(
						'qty'   => $quantity,
						'price' => $price,
					)
				);
				$product_subtotal = wc_price( $row_price );

				if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$row_price        = wc_get_price_excluding_tax(
					$product,
					array(
						'qty'   => $quantity,
						'price' => $price,
					)
				);
				$product_subtotal = wc_price( $row_price );

				if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		} else {
			$row_price        = (float) $price * (float) $quantity;
			$product_subtotal = wc_price( $row_price );
		}

		return apply_filters( 'woocommerce_cart_product_subtotal', $product_subtotal, $product, $quantity, WC()->cart );
	}
}

if ( ! function_exists( 'yith_sales_get_product_price' ) ) {

	/**
	 * Get the product row price per item starting from the price not filtered
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_get_product_price( $product ) {
		if ( WC()->cart->display_prices_including_tax() ) {
			$product_price = wc_get_price_including_tax( $product, array( 'price' => $product->get_price( 'edit' ) ) );
		} else {
			$product_price = wc_get_price_excluding_tax( $product, array( 'price' => $product->get_price( 'edit' ) ) );
		}

		return apply_filters( 'woocommerce_cart_product_price', wc_price( $product_price ), $product );
	}
}

if ( ! function_exists( 'yith_sales_generate_cart_id' ) ) {
	/**
	 * Generate the unique cart item id, this is useful for Upsell,FBT,Last Deals.
	 *
	 * @param array $products Products data.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_generate_cart_id( $products ) {
		$id_parts = array();
		foreach ( $products as $product ) {
			$id_parts[] = $product['product_id'];
			if ( isset( $product['variation_id'] ) && 0 !== $product['variation_id'] ) {
				$id_parts[] = $product['variation_id'];
			}
			if ( ! empty( $product['variations'] ) ) {
				$variation_key = '';
				foreach ( $product['variations'] as $variation ) {
					$variation_key .= trim( $variation['taxonomy'] ) . trim( $variation['option'] );
				}
				$id_parts[] = $variation_key;
			}
		}

		return md5( implode( '_', $id_parts ) );
	}
}

if ( ! function_exists( 'yith_sales_is_modal_visited' ) ) {
	/**
	 * Check if the modal was already visited
	 *
	 * @param Abstract_YITH_Sales_Campaign $campaign The campaign.
	 * @param string                       $modal_type The modal to check.
	 *
	 * @return bool
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_is_modal_visited( $campaign, $modal_type = 'promotions' ) {
		if ( ! empty( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value = json_decode( wp_unslash( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ), true );

			return ! is_null( $value ) && in_array( $campaign->get_id() . ':' . $campaign->get_data_modified(), $value, true );
		}

		return false;
	}
}

if ( ! function_exists( 'yith_sales_set_modal_as_visited' ) ) {
	/**
	 * Set a modal as visited on cookie
	 *
	 * @param Abstract_YITH_Sales_Campaign $campaign Campaign object to remove.
	 * @param string                       $modal_type The modal type promotions|campaigns.
	 * @param int                          $expire_in_days Expiring time.
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_set_modal_as_visited( $campaign, $modal_type = 'promotions', $expire_in_days = 30 ) {

		$value = array();
		if ( ! empty( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value = json_decode( wp_unslash( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ), true );
		}

		$value[]                                      = $campaign->get_id() . ':' . $campaign->get_data_modified();
		$value                                        = wp_json_encode( stripslashes_deep( $value ) );
		$_COOKIE[ 'yith_sales_modal_' . $modal_type ] = $value;

		$time = time() + $expire_in_days * DAY_IN_SECONDS;
		wc_setcookie( 'yith_sales_modal_' . $modal_type, $value, $time, false );
	}
}

if ( ! function_exists( 'yith_sales_delete_single_campaign_from_modal_cookie' ) ) {
	/**
	 * Remove cookie from cache
	 *
	 * @param Abstract_YITH_Sales_Campaign $campaign Campaign object to remove.
	 * @param string                       $modal_type The modal type promotions|campaigns.
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_delete_single_campaign_from_modal_cookie( $campaign, $modal_type = 'promotions' ) {
		$value = array();
		if ( ! empty( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value = json_decode( wp_unslash( $_COOKIE[ 'yith_sales_modal_' . $modal_type ] ), true );
		}
		$key   = $campaign->get_id() . ':' . $campaign->get_data_modified();
		$index = array_search( $key, $value, true );
		unset( $value[ $index ] );
		$value                                        = wp_json_encode( stripslashes_deep( $value ) );
		$_COOKIE[ 'yith_sales_modal_' . $modal_type ] = $value;
		wc_setcookie( 'yith_sales_modal_' . $modal_type, $value, time() - 3600, false );
	}
}

if ( ! function_exists( 'yith_sales_clear_modal_cookie' ) ) {
	/**
	 * Remove cookie from cache
	 *
	 * @param string $modal_type The modal type promotions|campaigns.
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_clear_modal_cookie( $modal_type = 'promotions' ) {
		$value                                        = wp_json_encode( stripslashes_deep( array() ) );
		$_COOKIE[ 'yith_sales_modal_' . $modal_type ] = $value;
		wc_setcookie( 'yith_sales_modal_' . $modal_type, $value, time() - 3600, false );
	}
}

if ( ! function_exists( 'yith_sales_check_product_on_cart' ) ) {
	/**
	 * Check if a product exists on cart and return the cart item key.
	 *
	 * @param int   $product_id Product id.
	 * @param int   $variation_id Variation id.
	 * @param array $variation Array of variations attributes.
	 *
	 * @return string|bool
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_check_product_on_cart( $product_id, $variation_id, $variation ) {
		$variation_id = empty( $variation_id ) ? 0 : $variation_id;
		if ( ! WC()->cart->is_empty() ) {
			foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
				if ( (int) $cart_item['product_id'] === (int) $product_id && (int) $cart_item['variation_id'] === (int) $variation_id && wp_json_encode( $cart_item['variation'] ) === wp_json_encode( $variation ) ) {
					return $cart_item_key;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'yith_sales_load_js_file' ) ) {
	/**
	 * Load .min.js file if WP_Debug is not defined
	 *
	 * @param string $filename The file name.
	 *
	 * @return string The file path
	 */
	function yith_sales_load_js_file( $filename ) {

		if ( ! ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || isset( $_GET['yith_script_debug'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filename = str_replace( '.js', '.min.js', $filename );
		}

		return $filename;
	}
}

if ( ! function_exists( 'yith_sales_block_editor_paragraph' ) ) {
	/**
	 * Get a block editor paragraph from text.
	 *
	 * @param string $text The text.
	 * @param string $attributes The attributes.
	 *
	 * @return string
	 */
	function yith_sales_block_editor_paragraph( $text, $attributes = '' ) {
		if ( $text ) {
			$text = '<!-- wp:paragraph --><p ' . $attributes . '>' . $text . '</p><!-- /wp:paragraph -->';
		}

		return $text;
	}
}


if ( ! function_exists( 'yith_sales_sort_cart_by_price' ) ) {
	/**
	 * Sort the cart by price asc ( from cheapest to most expensive )
	 *
	 * @param array $cart_item_a The first object to compare.
	 * @param array $cart_item_b The second object to compare.
	 *
	 * @return int
	 * @since  1.0.0
	 */
	function yith_sales_sort_cart_by_price( $cart_item_a, $cart_item_b ) {

		if ( empty( $cart_item_a ) || empty( $cart_item_b ) ) {
			return 0;
		} else {
			$product_a = $cart_item_a['data'] ?? false;
			$product_b = $cart_item_b['data'] ?? false;

			if ( $product_a && $product_b ) {
				return $product_a->get_price( 'edit' ) <=> $product_b->get_price( 'edit' );
			} else {
				return 0;
			}
		}
	}
}

if ( ! function_exists( 'yith_sales_clone_cart' ) ) {
	/**
	 * Clone the actual cart.
	 *
	 * @param WC_Cart|null $cart The cart object.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	function yith_sales_clone_cart( $cart = null ) {
		$cloned_cart = array();

		if ( is_null( $cart ) && ! is_null( WC()->cart ) ) {
			$cart = WC()->cart;
		}
		/**
		 * APPLY_FILTERS: yith_sales_skip_cart_check
		 *
		 * Skip cart check.
		 *
		 * @param bool    $skip True or false.
		 * @param WC_Cart $cart The cart.
		 *
		 * @return bool
		 */
		if ( $cart instanceof WC_Cart && ! $cart->is_empty() && ! apply_filters( 'yith_sales_skip_cart_check', false, $cart ) ) {
			foreach ( $cart->get_cart_contents() as $cart_item_key => $cart_item ) {
				/**
				 * APPLY_FILTERS: yith_sales_add_cart_item_in_clone
				 *
				 * Process cart item check.
				 *
				 * @param bool  $process True or false.
				 * @param array $cart_item The cart item.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_sales_add_cart_item_in_clone', true, $cart_item ) ) {
					$cloned_cart[ $cart_item_key ] = yith_sales_reset_cart_item( $cart_item_key );
				}
			}
		}

		return $cloned_cart;
	}
}

if ( ! function_exists( 'yith_sales_reset_cart_item' ) ) {
	/**
	 * Reset the cart items
	 *
	 * @param string $cart_item_key The item key.
	 *
	 * @return array
	 */
	function yith_sales_reset_cart_item( $cart_item_key ) {
		if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] ) ) {
			$price = WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['price_base'];

			unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );
		} else {
			$cart_product  = WC()->cart->cart_contents[ $cart_item_key ]['data'];
			$regular_price = $cart_product->get_price();
			$price         = wc_prices_include_tax() ? wc_get_price_including_tax( $cart_product, array( 'price' => $regular_price ) ) : wc_get_price_excluding_tax( $cart_product, array( 'price' => $regular_price ) );
		}


		WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $price );

		return WC()->cart->cart_contents[ $cart_item_key ];
	}
}

if ( ! function_exists( 'yith_sales_is_using_block_template_in_single_product' ) ) {

	/**
	 * Check if the theme use blocks
	 */
	function yith_sales_is_using_block_template_in_single_product() {
		static $use_blocks = null;

		if ( is_null( $use_blocks ) ) {
			// The blockified templates for Single Products are available since WooCommerce 7.9.
			$use_blocks = version_compare( WC()->version, '7.9.0', '>=' ) && \Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils::supports_block_templates();

			if ( $use_blocks ) {
				try {
					$container  = \Automattic\WooCommerce\Blocks\Package::container();
					$controller = $container->get( \Automattic\WooCommerce\Blocks\BlockTemplatesController::class );
					$use_blocks = $controller->block_template_is_available( 'single-product' );
					if ( $use_blocks ) {
						$templates = get_block_templates( array( 'slug__in' => array( 'single-product' ) ) );

						$use_blocks = ! ( isset( $templates[0] ) && Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils::template_has_legacy_template_block( $templates[0] ) );
					}
				} catch ( Exception $e ) {
					return false;
				}
			}
		}

		return $use_blocks;
	}
}

if ( ! function_exists( 'yith_sales_is_using_block_template_in_archive_product' ) ) {

	/**
	 * Check if the theme use blocks
	 */
	function yith_sales_is_using_block_template_in_archive_product() {
		static $use_blocks = null;
		if ( is_null( $use_blocks ) ) {
			// The blockified templates for Single Products are available since WooCommerce 7.9.
			$use_blocks = version_compare( WC()->version, '7.9.0', '>=' ) && \Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils::supports_block_templates();

			if ( $use_blocks ) {
				try {
					$container  = \Automattic\WooCommerce\Blocks\Package::container();
					$controller = $container->get( \Automattic\WooCommerce\Blocks\BlockTemplatesController::class );
					$use_blocks = $controller->block_template_is_available( 'archive-product' );
					if ( $use_blocks ) {
						$templates = get_block_templates( array( 'slug__in' => array( 'archive-product' ) ) );

						$use_blocks = ! ( isset( $templates[0] ) && Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils::template_has_legacy_template_block( $templates[0] ) );
					}
				} catch ( Exception $e ) {
					return false;
				}
			}
		}

		return $use_blocks;
	}
}