<?php
/**
 * Rest API
 *
 * @class   YITH_Sales
 * @package YITH/Sales/RestAPI
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Class
 */
class YITH_REST_Campaigns_Controller extends WP_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'yith_campaign';

	/**
	 * Whether the controller supports batching.
	 *
	 * @since 5.9.0
	 * @var array
	 */
	protected $allow_batch = array( 'v1' => true );

	/**
	 * Initialize product actions.
	 */
	public function __construct() {
		parent::__construct( $this->post_type );
	}

	/**
	 * Registers the routes for posts.
	 *
	 * @since 4.7.0
	 *
	 * @see   register_rest_route()
	 */
	public function register_routes() {
		parent::register_routes();
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/clone/(?P<id>[\d]+)',
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for clone the post.' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'clone_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
				),

				'allow_batch' => $this->allow_batch,
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/statistics/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_statistics' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/check-cart-discount/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'check_cart_discount' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				)
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/get-coupon-label/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_coupon_label' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				)
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/register-event/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'register_event' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				)
			)
		);
	}


	/**
	 * Retrieves a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 4.7.0
	 */
	public function get_items( $request ) {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		// Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error(
				'rest_no_search_term_defined',
				__( 'You need to define a search term to order by relevance.' ),
				array( 'status' => 400 )
			);
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error(
				'rest_orderby_include_missing_include',
				__( 'You need to define an include parameter to order by include.' ),
				array( 'status' => 400 )
			);
		}
		/**
		 * Get only published campaigns
		 */
		$request['status'] = 'publish';
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'author'         => 'author__in',
			'author_exclude' => 'author__not_in',
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'menu_order'     => 'menu_order',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'search'         => 's',
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Check for & assign any parameters which require special handling or setting.
		$args['date_query'] = array();

		if ( isset( $registered['before'], $request['before'] ) ) {
			$args['date_query'][] = array(
				'before' => $request['before'],
				'column' => 'post_date',
			);
		}

		if ( isset( $registered['modified_before'], $request['modified_before'] ) ) {
			$args['date_query'][] = array(
				'before' => $request['modified_before'],
				'column' => 'post_modified',
			);
		}

		if ( isset( $registered['after'], $request['after'] ) ) {
			$args['date_query'][] = array(
				'after'  => $request['after'],
				'column' => 'post_date',
			);
		}

		if ( isset( $registered['modified_after'], $request['modified_after'] ) ) {
			$args['date_query'][] = array(
				'after'  => $request['modified_after'],
				'column' => 'post_modified',
			);
		}

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		$args['meta_query'] = array( 'relation' => 'AND' );
		if ( isset( $registered['type'], $request['type'] ) && array( 'all' ) !== $request['type'] ) {
			$args['meta_query'][] = array(
				'key'     => 'type',
				'value'   => $request['type'],
				'compare' => 'IN',
			);
		}

		if ( isset( $registered['campaign_status'], $request['campaign_status'] ) && $request['campaign_status'] ) {
			switch ( $request['campaign_status'] ) {
				case 'inactive':
					$args['meta_query'] [] = array(
						array(
							'key'     => 'campaign_status',
							'value'   => $request['campaign_status'],
							'compare' => '=',
						),
					);
					break;
				case 'scheduled':
					$args['meta_query'] [] = array(
						'relation' => 'AND',
						array(
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								'key'     => 'schedule_date_from',
								'value'   => time(),
								'compare' => '>',
							),
						),
					);
					break;
				case 'ended':
					$args['meta_query'] [] = array(
						'relation' => 'AND',
						array(
							'relation' => 'AND',
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								array(
									'key'     => 'schedule_date_to',
									'compare' => '<',
									'value'   => time(),
								),
								array(
									'key'     => 'schedule_date_to',
									'value'   => '',
									'compare' => '!=',
								),
							),

						),
					);
					break;
				case 'active':
					$args['meta_query'] [] = array(
						'relation' => 'OR',
						array(
							'relation' => 'AND',
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								'key'     => 'schedule_date_from',
								'value'   => time(),
								'compare' => '<=',
							),
							array(
								'relation' => 'OR',
								array(
									'key'     => 'schedule_date_to',
									'compare' => '>',
									'value'   => time(),
								),
								array(
									'key'     => 'schedule_date_to',
									'value'   => '',
									'compare' => '=',
								),
							),
						),
					);
					break;
			}
		}

		// Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error(
				'rest_no_search_term_defined',
				__( 'You need to define a search term to order by relevance.' ),
				array( 'status' => 400 )
			);
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error(
				'rest_orderby_include_missing_include',
				__( 'You need to define an include parameter to order by include.' ),
				array( 'status' => 400 )
			);
		}
		/**
		 * Get only published campaigns
		 */
		$request['status'] = 'publish';
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'author'         => 'author__in',
			'author_exclude' => 'author__not_in',
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'menu_order'     => 'menu_order',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'search'         => 's',
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Check for & assign any parameters which require special handling or setting.
		$args['date_query'] = array();

		if ( isset( $registered['before'], $request['before'] ) ) {
			$args['date_query'][] = array(
				'before' => $request['before'],
				'column' => 'post_date',
			);
		}

		if ( isset( $registered['modified_before'], $request['modified_before'] ) ) {
			$args['date_query'][] = array(
				'before' => $request['modified_before'],
				'column' => 'post_modified',
			);
		}

		if ( isset( $registered['after'], $request['after'] ) ) {
			$args['date_query'][] = array(
				'after'  => $request['after'],
				'column' => 'post_date',
			);
		}

		if ( isset( $registered['modified_after'], $request['modified_after'] ) ) {
			$args['date_query'][] = array(
				'after'  => $request['modified_after'],
				'column' => 'post_modified',
			);
		}

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		$args['meta_query'] = array( 'relation' => 'AND' );
		if ( isset( $registered['type'], $request['type'] ) && array( 'all' ) !== $request['type'] ) {
			$args['meta_query'][] = array(
				'key'     => 'type',
				'value'   => $request['type'],
				'compare' => 'IN',
			);
		}
		if ( isset( $registered['campaign_status'], $request['campaign_status'] ) && $request['campaign_status'] ) {
			switch ( $request['campaign_status'] ) {
				case 'inactive':
					$args['meta_query'] [] = array(
						array(
							'key'     => 'campaign_status',
							'value'   => $request['campaign_status'],
							'compare' => '=',
						),
					);
					break;
				case 'scheduled':
					$args['meta_query'] [] = array(
						'relation' => 'AND',
						array(
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								'key'     => 'schedule_date_from',
								'value'   => time(),
								'compare' => '>',
							),
						),
					);
					break;
				case 'ended':
					$args['meta_query'] [] = array(
						'relation' => 'AND',
						array(
							'relation' => 'AND',
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								array(
									'key'     => 'schedule_date_to',
									'compare' => '<',
									'value'   => time(),
								),
								array(
									'key'     => 'schedule_date_to',
									'value'   => '',
									'compare' => '!=',
								),
							),

						),
					);
					break;
				case 'active':
					$args['meta_query'] [] = array(
						'relation' => 'OR',
						array(
							'relation' => 'AND',
							array(
								'key'     => 'campaign_status',
								'value'   => 'active',
								'compare' => '=',
							),
							array(
								'key'     => 'schedule_date_from',
								'value'   => time(),
								'compare' => '<=',
							),
							array(
								'relation' => 'OR',
								array(
									'key'     => 'schedule_date_to',
									'compare' => '>',
									'value'   => time(),
								),
								array(
									'key'     => 'schedule_date_to',
									'value'   => '',
									'compare' => '=',
								),
							),
						),
					);
					break;
			}
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		/**
		 * Filters WP_Query arguments when querying posts via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_post_query`
		 *  - `rest_page_query`
		 *  - `rest_attachment_query`
		 *
		 * Enables adding extra arguments or setting defaults for a post collection request.
		 *
		 * @param array $args Array of arguments for WP_Query.
		 * @param WP_REST_Request $request The REST API request.
		 *
		 * @link  https://developer.wordpress.org/reference/classes/wp_query/
		 *
		 * @since 4.7.0
		 * @since 5.7.0 Moved after the `tax_query` query arg is generated.
		 */
		$args       = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', array( $this, 'check_password_required' ), 10, 2 );
		}

		$posts = array();

		update_post_author_caches( $query_result );
		update_post_parent_caches( $query_result );

		foreach ( $query_result as $post ) {
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}

			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		// Reset filter.
		if ( 'edit' === $request['context'] ) {
			remove_filter( 'post_password_required', array( $this, 'check_password_required' ) );
		}

		$page        = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 && $page > 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.' ),
				array( 'status' => 400 )
			);
		}

		$response = rest_ensure_response( $posts );

		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$collection_url = rest_url( rest_get_route_for_post_type_items( $this->post_type ) );
		$base           = add_query_arg( urlencode_deep( $request_params ), $collection_url );

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

		// phpcs:enable
		return $response;

	}

	/**
	 * Return the campaign's statistics
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @throws Exception The error.
	 */
	public function get_statistics( $request ) {

		$args = array(
			'post_status' => 'publish',
			'post_type'   => $this->post_type,
			'fields'      => 'ids',
		);

		$query_args   = $this->prepare_items_query( $args, $request );
		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );
		$counter      = $this->init_counter();
		update_post_author_caches( $query_result );
		update_post_parent_caches( $query_result );
		foreach ( $query_result as $post_id ) {
			$campaign        = yith_sales_get_campaign( $post_id );
			$campaign_type   = $campaign->get_type();
			$campaign_status = $campaign->get_campaign_status();

			$counter[ $campaign_type ]['total'] += 1;
			if ( 'inactive' === $campaign_status ) {
				$counter[ $campaign_type ]['inactive'] += 1;
			} else {

				$scheduled_from      = $campaign->get_schedule_date_from();
				$scheduled_to        = $campaign->get_schedule_date_to();
				$scheduled_from_date = false;
				$scheduled_to_date   = false;
				if ( ! empty( $scheduled_from ) ) {
					$scheduled_from      = gmdate( 'Y-m-d H:i', $scheduled_from );
					$scheduled_from_date = new DateTime( $scheduled_from );
				}

				if ( ! empty( $scheduled_to ) ) {
					$scheduled_to      = gmdate( 'Y-m-d H:i', $scheduled_to );
					$scheduled_to_date = new DateTime( $scheduled_to );
				}
				$now = new DateTime( gmdate( 'Y-m-d H:i' ) );

				if ( $scheduled_from_date && $scheduled_from_date > $now ) {
					$counter[ $campaign_type ]['scheduled'] += 1;
				} elseif ( $scheduled_to_date && $scheduled_to_date < $now ) {
					$counter[ $campaign_type ]['ended'] += 1;
				} else {
					$counter[ $campaign_type ]['active'] += 1;
				}
			}
		}

		$response = rest_ensure_response( $counter );
		$response->header( 'X-WP-Total', count( $counter ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;

	}

	/**
	 * Init the statistics counter
	 *
	 * @return array
	 */
	public function init_counter() {
		$campaign_type = yith_sales()->get_order_of_campaigns();
		$counter       = array();
		foreach ( $campaign_type as $type ) {
			$counter[ $type ] = array(
				'active'    => 0,
				'inactive'  => 0,
				'scheduled' => 0,
				'ended'     => 0,
				'total'     => 0,
			);
		}

		return $counter;
	}

	/**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @param array $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
	 * @param WP_REST_Request $request Optional. Full details about the request.
	 *
	 * @return array Items query arguments.
	 * @since 4.7.0
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = array();

		foreach ( $prepared_args as $key => $value ) {
			/**
			 * Filters the query_vars used in get_items() for the constructed query.
			 *
			 * The dynamic portion of the hook name, `$key`, refers to the query_var key.
			 *
			 * @param string $value The query_var value.
			 *
			 * @since 4.7.0
			 */
			$query_args[ $key ] = apply_filters( "rest_query_var-{$key}", $value ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		$query_args['ignore_sticky_posts'] = true;

		// Map to proper WP_Query orderby param.
		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'            => 'ID',
				'include'       => 'post__in',
				'slug'          => 'post_name',
				'include_slugs' => 'post_name__in',
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;
	}


	/**
	 * Prepares a single post output for response.
	 *
	 * @param WP_Post $item Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response object.
	 * @since 5.9.0 Renamed `$post` to `$item` to match parent class for PHP 8 named parameter support.
	 *
	 * @since 4.7.0
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Restores the more descriptive, specific name for use within this method.
		$post            = $item;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		setup_postdata( $post );

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = $post->ID;
		}
		$type = get_post_meta( $post->ID, 'type', 1 );
		if ( rest_is_field_included( 'type', $fields ) ) {
			$data['type'] = $type;
		}

		if ( rest_is_field_included( 'name', $fields ) ) {
			$meta         = json_decode( get_post_meta( $post->ID, $type, true ), ARRAY_A );
			$data['name'] = $post->post_title;
		}

		if ( rest_is_field_included( 'date', $fields ) ) {
			$data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );
		}

		if ( rest_is_field_included( 'date_gmt', $fields ) ) {
			/*
			 * For drafts, `post_date_gmt` may not be set, indicating that the date
			 * of the draft should be updated each time it is saved (see #38883).
			 * In this case, shim the value based on the `post_date` field
			 * with the site's timezone offset applied.
			 */
			if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
				$post_date_gmt = get_gmt_from_date( $post->post_date );
			} else {
				$post_date_gmt = $post->post_date_gmt;
			}
			$data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
		}

		if ( rest_is_field_included( 'modified', $fields ) ) {
			$data['modified'] = $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified );
		}

		if ( rest_is_field_included( 'modified_gmt', $fields ) ) {
			/*
			 * For drafts, `post_modified_gmt` may not be set (see `post_date_gmt` comments
			 * above). In this case, shim the value based on the `post_modified` field
			 * with the site's timezone offset applied.
			 */
			if ( '0000-00-00 00:00:00' === $post->post_modified_gmt ) {
				$post_modified_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
			} else {
				$post_modified_gmt = $post->post_modified_gmt;
			}
			$data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );
		}

		if ( rest_is_field_included( 'status', $fields ) ) {
			$data['status'] = $post->post_status;
		}

		if ( rest_is_field_included( 'campaign_status', $fields ) ) {
			$data['campaign_status'] = get_post_meta( $post->ID, 'campaign_status', 1 );
		}

		if ( rest_is_field_included( 'priority', $fields ) ) {
			$priority         = get_post_meta( $post->ID, 'priority', 1 );
			$data['priority'] = empty( $priority ) ? 1 : $priority;
		}

		if ( rest_is_field_included( 'schedule_date_from', $fields ) ) {
			$data['schedule_date_from'] = (int) get_post_meta( $post->ID, 'schedule_date_from', 1 );
		}

		if ( rest_is_field_included( 'schedule_date_to', $fields ) ) {
			$data['schedule_date_to'] = (int) get_post_meta( $post->ID, 'schedule_date_to', 1 );
		}

		if ( rest_is_field_included( 'author', $fields ) ) {
			$data['author'] = (int) $post->post_author;
		}

		if ( rest_is_field_included( 'menu_order', $fields ) ) {
			$data['menu_order'] = (int) $post->menu_order;
		}

		if ( rest_is_field_included( 'meta', $fields ) ) {
			$data['meta'] = $this->meta->get_value( $post->ID, $request );

		}

		$post_type_obj = get_post_type_object( $post->post_type );
		if ( is_post_type_viewable( $post_type_obj ) && $post_type_obj->public ) {
			$permalink_template_requested = rest_is_field_included( 'permalink_template', $fields );
			$generated_slug_requested     = rest_is_field_included( 'generated_slug', $fields );

			if ( $permalink_template_requested || $generated_slug_requested ) {
				if ( ! function_exists( 'get_sample_permalink' ) ) {
					require_once ABSPATH . 'wp-admin/includes/post.php';
				}

				$sample_permalink = get_sample_permalink( $post->ID, $post->post_title, '' );

				if ( $permalink_template_requested ) {
					$data['permalink_template'] = $sample_permalink[0];
				}

				if ( $generated_slug_requested ) {
					$data['generated_slug'] = $sample_permalink[1];
				}
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );

		if ( 'view' === $context ) {
			$data = $this->render_wp_blocks( $data );
		}
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the post data for a REST API response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_prepare_post`
		 *  - `rest_prepare_page`
		 *  - `rest_prepare_attachment`
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post $post Post object.
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 4.7.0
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
	}

	/**
	 * Retrieves the post's schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 * @since 4.7.0
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			// Base properties for every Post.
			'properties' => array(
				'name'               => array(
					'description' => __( 'Name of campaign.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'date'               => array(
					'description' => __( "The date the post was published, in the site's timezone." ),
					'type'        => array( 'string', 'null' ),
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'date_gmt'           => array(
					'description' => __( 'The date the post was published, as GMT.' ),
					'type'        => array( 'string', 'null' ),
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'id'                 => array(
					'description' => __( 'Unique identifier for the post.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'modified'           => array(
					'description' => __( "The date the post was last modified, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt'       => array(
					'description' => __( 'The date the post was last modified, as GMT.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'             => array(
					'description' => __( 'A named status for the post.' ),
					'type'        => 'string',
					'enum'        => array_keys( get_post_stati( array( 'internal' => false ) ) ),
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'validate_callback' => array( $this, 'check_status' ),
					),
				),
				'type'               => array(
					'description' => __( 'Type of campaign.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'campaign_status'    => array(
					'description' => __( 'Status of campaign.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'schedule_date_from' => array(
					'description' => __( 'Schedule date from of campaign.' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'schedule_date_to'   => array(
					'description' => __( 'Schedule date to of campaign.' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'priority'           => array(
					'description' => __( 'Priority of campaign.' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		$post_type_obj = get_post_type_object( $this->post_type );

		$post_type_attributes = array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'page-attributes',
			'post-formats',
			'custom-fields',
		);

		foreach ( $post_type_attributes as $attribute ) {
			if ( ! post_type_supports( $this->post_type, $attribute ) ) {
				continue;
			}

			switch ( $attribute ) {

				case 'title':
					$schema['properties']['title'] = array(
						'description' => __( 'The title for the post.', 'wonder-cart' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'arg_options' => array(
							'sanitize_callback' => null,
							// Note: sanitization implemented in self::prepare_item_for_database().
							'validate_callback' => null,
							// Note: validation implemented in self::prepare_item_for_database().
						),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Title for the post, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML title for the post, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					);
					break;

				case 'author':
					$schema['properties']['author'] = array(
						'description' => __( 'The ID for the author of the post.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
					);
					break;

				case 'custom-fields':
					$schema['properties']['meta'] = $this->meta->get_field_schema();
					break;

			}
		}

		// Take a snapshot of which fields are in the schema pre-filtering.
		$schema_fields = array_keys( $schema['properties'] );

		/**
		 * Filters the post's schema.
		 *
		 * The dynamic portion of the filter, `$this->post_type`, refers to the
		 * post type slug for the controller.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_post_item_schema`
		 *  - `rest_page_item_schema`
		 *  - `rest_attachment_item_schema`
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 5.4.0
		 */
		$schema = apply_filters( "rest_{$this->post_type}_item_schema", $schema );

		// Emit a _doing_it_wrong warning if user tries to add new properties using this filter.
		$new_fields = array_diff( array_keys( $schema['properties'] ), $schema_fields );
		if ( count( $new_fields ) > 0 ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			_doing_it_wrong(
				__METHOD__,
				sprintf(
				/* translators: %s: register_rest_field */
					__( 'Please use %s to add new schema properties.' ),
					'register_rest_field'
				),
				'5.4.0'
			);
			// phpcs:enable
		}

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Creates a single campaign.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.0.0
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error(
				'rest_post_exists',
				__( 'Cannot create existing post.' ),
				array( 'status' => 400 )
			);
		}

		$prepared_post = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_post ) ) {
			return $prepared_post;
		}

		$prepared_post->post_type = $this->post_type;

		if ( ! empty( $prepared_post->post_name ) &&
		     ! empty( $prepared_post->post_status ) &&
		     in_array( $prepared_post->post_status, array( 'draft', 'pending' ), true )
		) {
			/*
			 * `wp_unique_post_slug()` returns the same
			 * slug for 'draft' or 'pending' posts.
			 *
			 * To ensure that a unique slug is generated,
			 * pass the post data with the 'publish' status.
			 */
			$prepared_post->post_name = wp_unique_post_slug(
				$prepared_post->post_name,
				$prepared_post->id,
				'publish',
				$prepared_post->post_type,
				$prepared_post->post_parent
			);
		}

		$post_id = wp_insert_post( wp_slash( (array) $prepared_post ), true, false );

		if ( is_wp_error( $post_id ) ) {

			if ( 'db_insert_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}

			return $post_id;
		}

		$post = get_post( $post_id );

		/**
		 * Fires after a single post is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_insert_post`
		 *  - `rest_insert_page`
		 *  - `rest_insert_attachment`
		 *
		 * @param WP_Post $post Inserted or updated post object.
		 * @param WP_REST_Request $request Request object.
		 * @param bool $creating True when creating a post, false when updating.
		 *
		 * @since 4.7.0
		 */
		do_action( "rest_insert_{$this->post_type}", $post, $request, true );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['campaign_status'] ) ) {
			update_post_meta( $post_id, 'campaign_status', ! empty( $request['campaign_status'] ) ? $request['campaign_status'] : 'inactive' );
		}

		if ( ! empty( $schema['properties']['type'] ) ) {
			if ( ! empty( $request['type'] ) ) {
				update_post_meta( $post_id, 'type', $request['type'] );
			}
		}

		if ( ! empty( $schema['properties']['schedule_date_from'] ) ) {
			if ( isset( $request['schedule_date_from'] ) ) {
				update_post_meta( $post_id, 'schedule_date_from', $request['schedule_date_from'] );
			}
		}

		if ( ! empty( $schema['properties']['schedule_date_to'] ) ) {
			if ( isset( $request['schedule_date_to'] ) ) {
				update_post_meta( $post_id, 'schedule_date_to', 0 === $request['schedule_date_to'] ? '' : $request['schedule_date_to'] );
			}
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $post_id );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$post          = get_post( $post_id );
		$fields_update = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		/**
		 * Fires after a single post is completely created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_after_insert_post`
		 *  - `rest_after_insert_page`
		 *  - `rest_after_insert_attachment`
		 *
		 * @param WP_Post $post Inserted or updated post object.
		 * @param WP_REST_Request $request Request object.
		 * @param bool $creating True when creating a post, false when updating.
		 *
		 * @since 5.0.0
		 */
		do_action( "rest_after_insert_{$this->post_type}", $post, $request, true );

		wp_after_insert_post( $post, false, null );

		$response = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( rest_get_route_for_post( $post ) ) );

		return $response;
	}

	/**
	 * Updates a single post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.0.0
	 */
	public function update_item( $request ) {
		$valid_check = $this->get_post( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}

		$post_before = get_post( $request['id'] );
		$post        = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! empty( $post->post_status ) ) {
			$post_status = $post->post_status;
		} else {
			$post_status = $post_before->post_status;
		}

		/*
		 * `wp_unique_post_slug()` returns the same
		 * slug for 'draft' or 'pending' posts.
		 *
		 * To ensure that a unique slug is generated,
		 * pass the post data with the 'publish' status.
		 */
		if ( ! empty( $post->post_name ) && in_array( $post_status, array( 'draft', 'pending' ), true ) ) {
			$post_parent     = ! empty( $post->post_parent ) ? $post->post_parent : 0;
			$post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, 'publish', $post->post_type, $post_parent );
		}

		// Convert the post object to an array, otherwise wp_update_post() will expect non-escaped input.
		$post_id = wp_update_post( wp_slash( (array) $post ), true, false );

		if ( is_wp_error( $post_id ) ) {
			if ( 'db_update_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}

			return $post_id;
		}

		$post = get_post( $post_id );

		/** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
		do_action( "rest_insert_{$this->post_type}", $post, $request, false );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['campaign_status'] ) ) {
			update_post_meta( $post_id, 'campaign_status', ! empty( $request['campaign_status'] ) ? $request['campaign_status'] : 'inactive' );
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			update_post_meta( $post_id, 'name', ! empty( $request['name'] ) ? $request['name'] : '' );
		}

		if ( ! empty( $schema['properties']['schedule_date_from'] ) ) {
			update_post_meta( $post_id, 'schedule_date_from', empty( $request['schedule_date_from'] ) ? '' : $request['schedule_date_from'] );
		}

		if ( ! empty( $schema['properties']['schedule_date_to'] ) ) {
			update_post_meta( $post_id, 'schedule_date_to', empty( $request['schedule_date_to'] ) ? '' : $request['schedule_date_to'] );
		}

		$properties_to_update = array(
			'type',
			'priority',
		);

		foreach ( $properties_to_update as $property_to_update ) {
			if ( ! empty( $schema['properties'][ $property_to_update ] ) ) {
				if ( ! empty( $request[ $property_to_update ] ) ) {
					update_post_meta( $post_id, $property_to_update, $request[ $property_to_update ] );
				}
			}
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $post->ID );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$post          = get_post( $post_id );
		$fields_update = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		/** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
		do_action( "rest_after_insert_{$this->post_type}", $post, $request, false );

		wp_after_insert_post( $post, true, $post_before );

		$response = $this->prepare_item_for_response( $post, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Clone a single post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.0.0
	 */
	public function clone_item( $request ) {
		$valid_check = $this->get_post( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}
		$post          = get_post( $request['id'] );
		$campaign_name = $post->post_title;
		$new_title     = $campaign_name . esc_html_x( ' - Copy', 'Name of duplicated post type', 'wonder-cart' );

		$new_post = array(
			'post_status' => 'publish',
			'post_type'   => $this->post_type,
			'post_title'  => $new_title,
		);

		$new_post_id = wp_insert_post( $new_post );
		$new_post    = get_post( $new_post_id );

		$metas = get_post_meta( $request['id'] );

		if ( ! empty( $metas ) ) {
			foreach ( $metas as $meta_key => $meta_value ) {
				if ( 'name' === $meta_key || '_edit_lock' === $meta_key || '_edit_last' === $meta_key ) {
					continue;
				}
				$value = maybe_unserialize( $meta_value[0] );

				if ( is_string( $value ) && strpos( $value, '"campaign_name":"' . $campaign_name . '"' ) !== false ) {
					$value = str_replace( '"campaign_name":"' . $campaign_name . '"', '"campaign_name":"' . $new_title . '"', $value );
				}

				update_post_meta( $new_post_id, $meta_key, $value );
			}
		}

		update_post_meta( $new_post_id, 'name', $new_title );

		$request->set_param( 'context', 'edit' );

		wp_after_insert_post( $new_post, false, null );

		$response = $this->prepare_item_for_response( $new_post, $request );

		return rest_ensure_response( $response );

	}


	/**
	 * Prepares a single post for create or update.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return stdClass|WP_Error Post object or WP_Error.
	 * @since 1.0.0
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_post  = new stdClass();
		$current_status = '';

		// Post ID.
		if ( isset( $request['id'] ) ) {
			$existing_post = $this->get_post( $request['id'] );
			if ( is_wp_error( $existing_post ) ) {
				return $existing_post;
			}

			$prepared_post->ID = $existing_post->ID;
			$current_status    = $existing_post->post_status;
		}

		$schema = $this->get_item_schema();

		// Post title.
		if ( ! empty( $schema['properties']['title'] ) && isset( $request['name'] ) ) {
			if ( is_string( $request['name'] ) ) {
				$prepared_post->post_title = $request['name'];
			} elseif ( ! empty( $request['name']['raw'] ) ) {
				$prepared_post->post_title = $request['name']['raw'];
			}
		}

		// Post type.
		if ( empty( $request['id'] ) ) {
			// Creating new post, use default type for the controller.
			$prepared_post->post_type = $this->post_type;
		} else {
			// Updating a post, use previous type.
			$prepared_post->post_type = get_post_type( $request['id'] );
		}

		$post_type = get_post_type_object( $prepared_post->post_type );

		// Post status.
		if (
			! empty( $schema['properties']['status'] ) &&
			isset( $request['status'] ) &&
			( ! $current_status || $current_status !== $request['status'] )
		) {
			$status = $this->handle_status_param( $request['status'], $post_type );

			if ( is_wp_error( $status ) ) {
				return $status;
			}

			$prepared_post->post_status = $status;
		}

		// Post date.
		if ( ! empty( $schema['properties']['date'] ) && ! empty( $request['date'] ) ) {
			$current_date = isset( $prepared_post->ID ) ? get_post( $prepared_post->ID )->post_date : false;
			$date_data    = rest_get_date_with_gmt( $request['date'] );

			if ( ! empty( $date_data ) && $current_date !== $date_data[0] ) {
				list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
				$prepared_post->edit_date = true;
			}
		} elseif ( ! empty( $schema['properties']['date_gmt'] ) && ! empty( $request['date_gmt'] ) ) {
			$current_date = isset( $prepared_post->ID ) ? get_post( $prepared_post->ID )->post_date_gmt : false;
			$date_data    = rest_get_date_with_gmt( $request['date_gmt'], true );

			if ( ! empty( $date_data ) && $current_date !== $date_data[1] ) {
				list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
				$prepared_post->edit_date = true;
			}
		}

		// Sending a null date or date_gmt value resets date and date_gmt to their
		// default values (`0000-00-00 00:00:00`).
		if (
			( ! empty( $schema['properties']['date_gmt'] ) && $request->has_param( 'date_gmt' ) && null === $request['date_gmt'] ) ||
			( ! empty( $schema['properties']['date'] ) && $request->has_param( 'date' ) && null === $request['date'] )
		) {
			$prepared_post->post_date_gmt = null;
			$prepared_post->post_date     = null;
		}

		// Author.
		if ( ! empty( $schema['properties']['author'] ) && ! empty( $request['author'] ) ) {
			$post_author = (int) $request['author'];

			if ( get_current_user_id() !== $post_author ) {
				$user_obj = get_userdata( $post_author );

				if ( ! $user_obj ) {
					return new WP_Error(
						'rest_invalid_author',
						__( 'Invalid author ID.' ),
						array( 'status' => 400 )
					);
				}
			}

			$prepared_post->post_author = $post_author;
		}

		// Menu order.
		if ( ! empty( $schema['properties']['menu_order'] ) && isset( $request['menu_order'] ) ) {
			$prepared_post->menu_order = (int) $request['menu_order'];
		}

		/**
		 * Filters a post before it is inserted via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `rest_pre_insert_post`
		 *  - `rest_pre_insert_page`
		 *  - `rest_pre_insert_attachment`
		 *
		 * @param stdClass $prepared_post An object representing a single post prepared
		 *                                           for inserting or updating the database.
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 4.7.0
		 */
		return apply_filters( "rest_pre_insert_{$this->post_type}", $prepared_post, $request );

	}

	/**
	 * Render the blocks
	 *
	 * @param array $data The campaign data.
	 *
	 * @return array
	 */
	public function render_wp_blocks( $data ) {

		$campaign_meta = json_decode( $data['meta'][ $data['type'] ], ARRAY_A );

		$campaign_title                       = $campaign_meta['title'];
		$text_popup                           = $campaign_meta['text_to_show_popup'] ?? '';
		$text_banner                          = $campaign_meta['text_to_show_banner'] ?? '';
		$campaign_title                       = do_blocks( $campaign_title );
		$text_popup                           = do_blocks( $text_popup );
		$text_banner                          = do_blocks( $text_banner );
		$campaign_meta['title']               = $campaign_title;
		$campaign_meta['text_to_show_popup']  = $text_popup;
		$campaign_meta['text_to_show_banner'] = $text_banner;
		$data['meta'][ $data['type'] ]        = wp_json_encode( $campaign_meta );

		return $data;
	}

	/**
	 * Return if exists, the valid cart campaign, to add the coupon via rest-api
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.4.0
	 */
	public function check_cart_discount( $request ) {
		if( !isset( $request['cart_subtotal'])){
			return rest_ensure_response( new WP_Error( 'rest_setting_value_invalid', __( 'Cart subtotal args is missed', 'wonder-cart' ), array( 'status' => 400 ) ) );
		}
		$controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Cart_Discount_Controller' );
		$result     = array( 'campaign' => false );

		if ( $controller instanceof YITH_Sales_Cart_Discount_Controller ) {
			$campaign = $controller->get_valid_campaign($request['cart_subtotal'] );
			if ( $campaign instanceof YITH_Sales_Cart_Discount_Campaign ) {
				$result['campaign'] = $campaign->get_id();
			}
		}

		return rest_ensure_response( $result );
	}

	public function get_coupon_label( $request ){
		if( !isset( $request['cart_subtotal'])){
			return rest_ensure_response( new WP_Error( 'rest_setting_value_invalid', __( 'Cart subtotal args is missed', 'wonder-cart' ), array( 'status' => 400 ) ) );
		}
		$controller = YITH_Sales_Controller::get_instance()->get_controller( 'YITH_Sales_Cart_Discount_Controller' );
		$result     = array( 'coupon_label' => '' );
		if ( $controller instanceof YITH_Sales_Cart_Discount_Controller ) {
			$campaign = $controller->get_valid_campaign($request['cart_subtotal']);
			if ( $campaign instanceof YITH_Sales_Cart_Discount_Campaign ) {
				$result['coupon_label'] = $campaign->get_title();
			}
		}
		return rest_ensure_response( $result );
	}

	/**
	 * Register the events from campain panel.
	 *
	 * It has been created to analyze shop manager actions.
	 *
	 * @param array $request Request
	 *
	 * @since 1.7.0
	 * @return void|WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function register_event( $request ) {
		if( !isset( $request['eventName'])){
			return rest_ensure_response( new WP_Error( 'rest_setting_value_invalid', 'The event is not present into the request ', array( 'status' => 400 ) ) );
		}

		$event = $request['eventName'];
		$args = $request['args'] ?? array() ;

		/**
		 * Trigger events from campaign editor panel
		 *
		 * The dynamic portion of the hook name, `$event`, refers to the event type slug.
		 *
		 * Possible hook names include:
		 *
		 *  - `yith_sales_edit_campaign_event_modal_opened`
		 *  - `yith_sales_edit_campaign_event_campaign_selected`
		 *  - `yith_sales_edit_campaign_event_campaign_abandoned`
		 *
		 * @param array $args A list of details that were involved on the event.
		 * @param string $event The name of the event.
		 *
		 * @since 1.7.0
		 */
		do_action('yith_sales_edit_campaign_event_'.$event, $args, $event);

		/**
		 * Trigger events from campaign editor panel
		 *
		 * @param   string  $event  The name of the event ( 'modal_opened' | 'campaign_selected' | 'campaign_abandoned' )
		 * @param   array   $args   A list of details that were involved on the event.
		 *
		 * @since 1.7.0
		 */
		do_action('yith_sales_edit_campaign_event', $event, $args );


		return rest_ensure_response( array( 'response' => 'Event has been registered') );
	}
}
