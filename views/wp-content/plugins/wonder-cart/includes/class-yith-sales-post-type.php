<?php
/**
 * The class register the post type
 *
 * @class   YITH_Sales_Post_Type
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post type class
 */
class YITH_Sales_Post_Type {

	use YITH_Sales_Trait_Singleton;

	/**
	 * Post type name
	 *
	 * @var string
	 */
	public static $post_type = 'yith_campaign';

	/**
	 * The construct
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_campaign_post_type' ) );
		add_action( 'rest_api_init', array( $this, 'register_meta_data' ) );
		add_action( 'rest_' . self::$post_type . '_query', array( $this, 'extend_rest_query_to_draft' ), 20, 2 );
		add_filter( 'rest_' . self::$post_type . '_collection_params', array( $this, 'add_collection_params' ), 20, 2 );
	}


	/**
	 * Register the post type
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	public function register_campaign_post_type() {
		$labels = array(
			'name'               => _x( 'Campaigns', 'campaign list', 'wonder-cart' ),
			'singular_name'      => __( 'Campaign', 'yith - complete - upsell - cross - sell - solution' ),
			'menu_name'          => __( 'Campaign', 'wonder-cart' ),
			'parent_item_colon'  => '',
			'all_items'          => '',
			'view_item'          => '',
			'add_new_item'       => __( '+ Add campaign', 'wonder-cart' ),
			'add_new'            => __( '+ Add campaign', 'wonder-cart' ),
			'edit_item'          => __( 'Campaign', 'wonder-cart' ),
			'update_item'        => __( 'Update campaign', 'wonder-cart' ),
			'search_items'       => _x( 'Search', 'search campaign button', 'wonder-cart' ),
			'not_found'          => __( 'Not found', 'wonder-cart' ),
			'not_found_in_trash' => '',
		);
		$args   = array(
			'label'                 => __( 'Campaign', 'wonder-cart' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'author', 'custom-fields' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => false,
			'show_in_rest'          => true,
			'exclude_from_search'   => true,
			'capability_type'       => 'post',
			'rest_namespace'        => 'yith_sales/v1',
			'rest_base'             => 'campaigns',
			'rest_controller_class' => 'YITH_REST_Campaigns_Controller',
		);

		register_post_type( self::$post_type, $args );
	}


	/**
	 * Find all the keys in a nested array.
	 *
	 * @param string $key      Key to search.
	 * @param array  $fields   Array.
	 * @param array  $key_list Recursive parameter.
	 *
	 * @return array
	 */
	public function find_key( $key, $fields, $key_list = array() ) {
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( is_array( $field ) && isset( $field[ $key ] ) ) {
					if ( isset( $field['default'] ) ) {
						$key_list[ $field[ $key ] ] = $field['default'];
					}
					if ( isset( $field['fields'] ) && ! empty( $field['fields'] ) ) {
						$key_list = array_merge( $key_list, $this->find_key( $key, $field['fields'], $key_list ) );
					}
				}
			}
		}

		return $key_list;
	}

	/**
	 * Find all the keys in a nested array.
	 *
	 * @param array $module List of options.
	 *
	 * @return array
	 */
	public function get_default_options( $module ) {
		$key_list = array();
		$options  = $module['options'] ?? false;
		if ( $options ) {
			foreach ( $options as $option ) {
				if ( is_array( $option ) && isset( $option['fields'] ) && $option['fields'] ) {
					$fields   = $option['fields'];
					$key_list = array_merge( $key_list, $this->find_key( 'id', $fields, $key_list ) );
				}
			}
		}

		return $key_list;
	}

	/**
	 * Register the meta inside Rest Api.
	 *
	 * Get the list from the option registered for a specific module.
	 *
	 * @return void
	 */
	public function register_meta_data() {
		foreach ( yith_sales()->modules as $module ) {
			register_post_meta(
				self::$post_type,
				$module['id'],
				array(
					'type'         => 'string',
					'description'  => $module['id'] . ' settings',
					'default'      => wp_json_encode( $this->get_default_options( $module ) ),
					'single'       => true,
					'show_in_rest' => true,
				)
			);
		}
	}

	/**
	 * Add params to the query string
	 *
	 * @param array        $query_params The query params.
	 * @param WP_Post_Type $post_type    The post type name.
	 */
	public function add_collection_params( $query_params, $post_type ) {
		if ( self::$post_type === $post_type->name ) {
			$query_params['type']            = array(
				'description' => __( 'Limit result set to type of campaign.' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
				),
			);
			$query_params['campaign_status'] = array(
				'description' => __( 'Limit result set to the status of campaign.' ),
				'type'        => 'string',
				'items'       => array(
					'type' => 'string',
				),
			);
		}

		return $query_params;
	}

	/**
	 * Return also the campaign in draft
	 *
	 * @param array           $args    Array of arguments for WP_Query.
	 * @param WP_REST_Request $request The REST API request.
	 *
	 * @return array
	 */
	public function extend_rest_query_to_draft( $args, $request ) {
		$args['post_status'] = array( 'publish', 'draft', 'auto-draft' );

		return $args;
	}
}
