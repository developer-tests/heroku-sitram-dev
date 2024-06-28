<?php
/**
 * Rest API
 *
 * @class   YITH_Sales
 * @package YITH/Sales/RestAPI
 * @since   1.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class manage custom API to get products
 */
class YITH_REST_Products_Controller extends WC_REST_Products_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'yith_sales/v1';


	/**
	 * The construct
	 */
	public function __construct() {

		add_filter( 'woocommerce_rest_product_object_query', array( $this, 'add_support_product_type' ), 10, 2 );

	}

	/**
	 * Get a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$query_args = $this->prepare_objects_query( $request );
		if ( is_wp_error( current( $query_args ) ) ) {
			return current( $query_args );
		}

		$query_results = $this->get_objects( $query_args );
		$objects       = array();
		foreach ( $query_results['objects'] as $object ) {
			if ( $object->is_purchasable() ) {
				$data      = $this->prepare_object_for_response( $object, $request );
				$objects[] = $this->prepare_response_for_collection( $data );
			}
		}

		$page      = (int) $query_args['paged'];
		$max_pages = $query_results['pages'];

		$response = rest_ensure_response( $objects );
		$response->header( 'X-WP-Total', $query_results['total'] );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base          = $this->rest_base;
		$attrib_prefix = '(?P<';
		if ( strpos( $base, $attrib_prefix ) !== false ) {
			$attrib_names = array();
			preg_match( '/\(\?P<[^>]+>.*\)/', $base, $attrib_names, PREG_OFFSET_CAPTURE );
			foreach ( $attrib_names as $attrib_name_match ) {
				$beginning_offset = strlen( $attrib_prefix );
				$attrib_name_end  = strpos( $attrib_name_match[0], '>', $attrib_name_match[1] );
				$attrib_name      = substr( $attrib_name_match[0], $beginning_offset, $attrib_name_end - $beginning_offset );
				if ( isset( $request[ $attrib_name ] ) ) {
					$base = str_replace( "(?P<$attrib_name>[\d]+)", $request[ $attrib_name ], $base );
				}
			}
		}
		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Add support for variable and simple products
	 *
	 * @param array $args The query args.
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array
	 */
	public function add_support_product_type( $args, $request ) {

		$tax_query = array(
			'taxonomy' => 'product_type',
			'field'    => 'slug',
			'terms'    => array( 'simple', 'variable' ),
			'operator' => 'IN',
		);
		if ( isset( $args['tax_query'] ) ) {

			$args['tax_query'][] = $tax_query;
		} else {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['tax_query'] = array(
				$tax_query,
			);
		}

		return $args;
	}

	/**
	 * Registers the routes for posts.
	 *
	 * @since 1.0.0
	 *
	 * @see   register_rest_route()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			),
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get the images for a product or product variation.
	 *
	 * @param WC_Product|WC_Product_Variation $product Product instance.
	 *
	 * @return array
	 */
	protected function get_images( $product ) {
		$images = parent::get_images( $product );

		foreach ( $images as $index => $image ) {
			$thumbnail                     = wp_get_attachment_image_src( $image['id'], 'woocommerce_thumbnail' );
			$images[ $index ]['thumbnail'] = current( $thumbnail );
		}

		return $images;
	}


	/**
	 * Get the Product's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		$schema['variations'] = array(
			'description' => __( 'List of variation IDs, if applicable.', 'woocommerce' ),
			'type'        => 'array',
			'context'     => array( 'view', 'edit' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'id'         => array(
						'description' => __( 'The attribute ID, or 0 if the attribute is not taxonomy based.', 'woocommerce' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'attributes' => array(
						'description' => __( 'List of variation attributes.', 'woocommerce' ),
						'type'        => 'array',
						'context'     => array( 'view', 'edit' ),
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'name'  => array(
									'description' => __( 'The attribute name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'value' => array(
									'description' => __( 'The assigned attribute.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
			),
		);
		$schema['attributes'] = array(
			'description' => __( 'List of attributes assigned to the product/variation that are visible or used for variations.', 'woocommerce' ),
			'type'        => 'array',
			'context'     => array( 'view', 'edit' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'id'             => array(
						'description' => __( 'The attribute ID, or 0 if the attribute is not taxonomy based.', 'woocommerce' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'name'           => array(
						'description' => __( 'The attribute name.', 'woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'taxonomy'       => array(
						'description' => __( 'The attribute taxonomy, or null if the attribute is not taxonomy based.', 'woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'has_variations' => array(
						'description' => __( 'True if this attribute is used by product variations.', 'woocommerce' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'prices'         => array(
						'description' => __( 'The minimum and maximum price for a variable product', 'woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'terms'          => array(
						'description' => __( 'List of assigned attribute terms.', 'woocommerce' ),
						'type'        => 'array',
						'context'     => array( 'view', 'edit' ),
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'id'      => array(
									'description' => __( 'The term ID, or 0 if the attribute is not a global attribute.', 'woocommerce' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'name'    => array(
									'description' => __( 'The term name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'slug'    => array(
									'description' => __( 'The term slug.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'default' => array(
									'description' => __( 'If this is a default attribute', 'woocommerce' ),
									'type'        => 'boolean',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
			),
		);

		return $schema;
	}

	/**
	 * Add new options for 'orderby' to the collection params.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$collection_params = parent::get_collection_params();

		$collection_params['type']['enum'] = array_merge( $collection_params['type']['enum'], array( 'variation' ) );

		return $collection_params;
	}

	/**
	 * Get objects.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {

		// Taxonomy query to filter products by type, category, tag, shipping class, and attribute.
		$args = parent::prepare_objects_query( $request );

		// Filter product type by slug.
		if ( ! empty( $request['type'] ) ) {
			if ( 'variation' === $request['type'] ) {
				$args['post_type'] = 'product_variation';
			}
		}

		if ( ! empty( $request['categories_exclude'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => explode( ',', $request['categories_exclude'] ),
				'operator' => 'NOT IN',
			);
		}

		return $args;
	}

	/**
	 * Get product data.
	 *
	 * @param WC_Product $product Product instance.
	 * @param string $context Request context. Options: 'view' and 'edit'.
	 *
	 * @return array
	 */
	protected function get_product_data( $product, $context = 'view' ) {
		$data                     = parent::get_product_data( $product, $context );
		$data['prices']           = $this->get_prices( $product );
		$data['variations']       = $this->get_variations( $product );
		$data['attributes']       = $this->get_attributes( $product );
		$data['addToCartClasses'] = implode(
			' ',
			array_filter(
				array(
					'button',
					wc_wp_theme_get_element_class_name( 'button' ), // escaped in the template.
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
				)
			)
		);
		$data['addToCartURL']     = $product->add_to_cart_url();
		$data['is_in_stock']      = $product->is_in_stock();
		$data['is_visible']       = $product->is_visible();
		$data['availability']     = $product->get_availability();

		return $data;
	}

	/**
	 * Prepare a single product output for response.
	 *
	 * @param WC_Data $object Object data.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 * @since  3.0.0
	 */
	public function prepare_object_for_response( $object, $request ) {

		$context       = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$this->request = $request;
		$data          = $this->get_product_data( $object, $context, $request );

		// Add grouped products data.
		if ( $object->is_type( 'grouped' ) && $object->has_child() ) {
			$data['grouped_products'] = $object->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to object type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data $object Object data.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( "woocommerce_rest_prepare_{$this->post_type}_object", $response, $object, $request );
	}

	/**
	 * Get variation prices.
	 *
	 * @param \WC_Product $product Product instance.
	 *
	 * @returns array
	 */
	protected function get_prices( \WC_Product $product ) {
		$prices = array();
		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices();
			if ( isset( $prices['regular_price'] ) ) {
				$prices['min'] = reset( $prices['regular_price'] );
				$prices['max'] = end( $prices['regular_price'] );
			}
		}

		return $prices;
	}

	/**
	 * Get variation IDs and attributes from the DB.
	 *
	 * @param \WC_Product $product Product instance.
	 *
	 * @returns array
	 */
	protected function get_variations( \WC_Product $product ) {
		$variation_ids = $product->is_type( 'variable' ) ? $product->get_visible_children() : array();

		if ( ! count( $variation_ids ) ) {
			return array();
		}

		/**
		 * Gets default variation data which applies to all of this products variations.
		 */
		$attributes                  = array_filter(
			$product->get_attributes(),
			array(
				$this,
				'filter_variation_attribute',
			)
		);
		$default_variation_meta_data = array_reduce(
			$attributes,
			function ( $defaults, $attribute ) use ( $product ) {
				$meta_key              = wc_variation_attribute_name( $attribute->get_name() );
				$defaults[ $meta_key ] = array(
					'name'  => wc_attribute_label( $attribute->get_name(), $product ),
					'value' => null,
					'key'   => $meta_key,
				);

				return $defaults;
			},
			array()
		);

		$default_variation_meta_keys = array_keys( $default_variation_meta_data );

		/**
		 * Gets individual variation data from the database, using cache where possible.
		 */
		$cache_group   = 'product_variation_meta_data';
		$cache_value   = wp_cache_get( $product->get_id(), $cache_group );
		$last_modified = get_the_modified_date( 'U', $product->get_id() );

		if ( false === $cache_value || $last_modified !== $cache_value['last_modified'] ) {
			global $wpdb;
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery
			$variation_meta_data = $wpdb->get_results(
				"
				SELECT post_id as variation_id, meta_key as attribute_key, meta_value as attribute_value
				FROM {$wpdb->postmeta}
				WHERE post_id IN (" . implode( ',', array_map( 'esc_sql', $variation_ids ) ) . ")
				AND meta_key IN ('" . implode( "','", array_map( 'esc_sql', $default_variation_meta_keys ) ) . "')
			"
			);
			// phpcs:enable

			wp_cache_set(
				$product->get_id(),
				array(
					'last_modified' => $last_modified,
					'data'          => $variation_meta_data,
				),
				$cache_group
			);
		} else {
			$variation_meta_data = $cache_value['data'];
		}

		/**
		 * Merges and formats default variation data with individual variation data.
		 */
		$attributes_by_variation = array_reduce(
			$variation_meta_data,
			function ( $values, $data ) use ( $default_variation_meta_keys ) {
				// The query above only includes the keys of $default_variation_meta_data so we know all of the attributes
				// being processed here apply to this product. However, we need an additional check here because the
				// cache may have been primed elsewhere and include keys from other products.
				// @see AbstractProductGrid::prime_product_variations.
				if ( in_array( $data->attribute_key, $default_variation_meta_keys, true ) ) {
					$values[ $data->variation_id ][ $data->attribute_key ] = $data->attribute_value;
				}

				return $values;
			},
			array_fill_keys( $variation_ids, array() )
		);

		$variations = array();

		foreach ( $variation_ids as $variation_id ) {
			$variation      = wc_get_product( $variation_id );
			$attribute_data = $default_variation_meta_data;

			foreach ( $attributes_by_variation[ $variation_id ] as $meta_key => $meta_value ) {
				if ( '' !== $meta_value ) {
					$attribute_data[ $meta_key ]['value'] = $meta_value;
					$attribute_data[ $meta_key ]['key']   = $meta_key;
				}
			}

			$variations[] = (object) array(
				'id'         => $variation_id,
				'attributes' => array_values( $attribute_data ),
				'data'       => array(
					'price'                => $variation->get_price( 'edit' ),
					'regular_price'        => (float) $variation->get_regular_price( 'edit' ),
					'is_purchasable'       => $variation->is_purchasable(),
					'is_in_stock'          => $variation->is_in_stock(),
					'variation_is_visible' => $variation->is_visible(),
					'availability'         => $variation->get_availability(),
					'stock_quantity'       => $variation->get_stock_quantity(),
				),
			);
		}

		return $variations;
	}

	/**
	 * Returns true if the given attribute is valid.
	 *
	 * @param mixed $attribute Object or variable to check.
	 *
	 * @return boolean
	 */
	protected function filter_valid_attribute( $attribute ) {
		return is_a( $attribute, '\WC_Product_Attribute' );
	}

	/**
	 * Returns true if the given attribute is valid and used for variations.
	 *
	 * @param mixed $attribute Object or variable to check.
	 *
	 * @return boolean
	 */
	protected function filter_variation_attribute( $attribute ) {
		return $this->filter_valid_attribute( $attribute ) && $attribute->get_variation();
	}

	/**
	 * Get list of product attributes and attribute terms.
	 *
	 * @param \WC_Product $product Product instance.
	 *
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes         = array_filter( $product->get_attributes(), array( $this, 'filter_valid_attribute' ) );
		$default_attributes = $product->get_default_attributes();
		$return             = array();

		foreach ( $attributes as $attribute_slug => $attribute ) {
			// Only visible or variation attributes will be exposed by this API.
			if ( ! $attribute->get_visible() && ! $attribute->get_variation() ) {
				continue;
			}

			$terms = $attribute->is_taxonomy() ? array_map(
				array(
					$this,
					'prepare_product_attribute_taxonomy_value',
				),
				$attribute->get_terms()
			) : array_map(
				array(
					$this,
					'prepare_product_attribute_value',
				),
				$attribute->get_options()
			);
			// Custom attribute names are sanitized to be the array keys.
			// So when we do the array_key_exists check below we also need to sanitize the attribute names.

			$sanitized_attribute_name = sanitize_key( $attribute->get_name() );

			if ( array_key_exists( $sanitized_attribute_name, $default_attributes ) ) {
				foreach ( $terms as $term ) {
					$term->default = $term->slug === $default_attributes[ $sanitized_attribute_name ];
				}
			}

			$return[] = (object) array(
				'id'             => $attribute->get_id(),
				'key'            => 'attribute_' . $attribute_slug,
				'name'           => wc_attribute_label( $attribute->get_name(), $product ),
				'taxonomy'       => $attribute->is_taxonomy() ? sanitize_title( $attribute->get_name() ) : null,
				'has_variations' => true === $attribute->get_variation(),
				'terms'          => $terms,
			);
		}

		return $return;
	}

	/**
	 * Prepare an attribute term for the response.
	 *
	 * @param \WP_Term $term Term object.
	 *
	 * @return object
	 */
	protected function prepare_product_attribute_taxonomy_value( \WP_Term $term ) {
		return $this->prepare_product_attribute_value( $term->name, $term->term_id, $term->slug );
	}

	/**
	 * Prepare an attribute term for the response.
	 *
	 * @param string $name Attribute term name.
	 * @param int $id Attribute term ID.
	 * @param string $slug Attribute term slug.
	 *
	 * @return object
	 */
	protected function prepare_product_attribute_value( $name, $id = 0, $slug = '' ) {
		return (object) array(
			'id'   => (int) $id,
			'name' => $name,
			'slug' => $slug ? $slug : $name,
		);
	}
}
