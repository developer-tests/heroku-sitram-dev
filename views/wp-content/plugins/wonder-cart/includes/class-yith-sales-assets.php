<?php
/**
 * Class to manage the scripts and styles
 *
 * @class   YITH_Sales_Assets
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assets Class
 *
 * @class   YITH_Sales_Assets
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */
class YITH_Sales_Assets {
	/**
	 * Contains an array of script handles registered by YITH Sales.
	 *
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of style handles registered by YITH Sales.
	 *
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by YITH Sales.
	 *
	 * @var array
	 */
	private static $yith_sales_localize_scripts = array();

	/**
	 * YITH_Sales_Assets constructor.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_scripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_frontend_scripts' ), 11 );
		add_action( 'init', array( __CLASS__, 'maybe_register_lapilli_ui_scripts' ) );
		add_filter(
			'should_load_block_editor_scripts_and_styles',
			array(
				__CLASS__,
				'should_load_block_editor_scripts_and_styles',
			)
		);
	}

	/**
	 * Get the dependencies for the admin scripts
	 *
	 * @return array
	 */
	private static function get_react_admin_assets_dependencies() {
		$asset_file = include YITH_SALES_DIR . 'assets/js/build/admin/admin.asset.php';

		return $asset_file;
	}

	/**
	 * Get the dependencies for the frontend scripts
	 *
	 * @return array
	 */
	private static function get_react_frontend_assets_dependencies() {
		$asset_file = include YITH_SALES_DIR . 'assets/js/build/frontend/frontend.asset.php';

		return $asset_file;
	}

	/**
	 * Check if needed register the lapilli-ui library
	 *
	 * @return void
	 */
	public static function maybe_register_lapilli_ui_scripts() {

		if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
			$base_url  = YITH_SALES_ASSETS_URL . '/js/lapilli-ui/';
			$base_path = YITH_SALES_DIR . '/assets/js/lapilli-ui/';
			$packages  = array(
				'lapilli-ui-block-editor' => 'block-editor',
				'lapilli-ui-components'   => 'components',
				'lapilli-ui-styles'       => 'styles',
				'lapilli-ui-date'         => 'date',
			);

			foreach ( $packages as $handle => $package ) {
				$asset_file = $base_path . $package . '/index.asset.php';

				if ( file_exists( $asset_file ) ) {
					$script_asset = include $asset_file;
					$dependencies = $script_asset['dependencies'] ?? array();
					$version      = $script_asset['version'] ?? '1.0.0';

					$script = $base_url . $package . '/index.js';

					if ( 'lapilli-ui-date' === $handle ) {
						$dependencies[] = 'wp-date';
					}

					wp_register_script( $handle, $script, $dependencies, $version, true );
				}
			}

			$locale_options = array(
				'options' => array(
					'weekStartsOn' => (int) get_option( 'start_of_week', 0 ),
				),
			);

			$date_formats = array(
				'year'         => 'Y',
				'month'        => 'F',
				'dayOfMonth'   => 'j',
				'monthShort'   => 'M',
				'weekday'      => 'l',
				'weekdayShort' => 'D',
				'fullDate'     => get_option( 'date_format', __( 'F j, Y' ) ),
				'inputDate'    => 'Y-m-d',
				'monthAndDate' => 'F j',
				'monthAndYear' => 'F Y',
			);

			wp_add_inline_script(
				'lapilli-ui-date',
				'lapilliUI.date.setLocale( ' . wp_json_encode( $locale_options ) . ' );
				lapilliUI.date.setDateFormats( ' . wp_json_encode( $date_formats ) . ' );
				lapilliUI.date.setFormatDate( wp.date.format );'
			);
		}
	}

	/**
	 * Register admin scripts
	 */
	private static function register_admin_scripts() {
		$asset_file                   = self::get_react_admin_assets_dependencies();
		$asset_file['dependencies'][] = 'yith-sales-common';
		$admin_scripts                = array(
			'yith-sales-admin'  => array(
				'src'     => yith_sales_load_js_file( YITH_SALES_ASSETS_URL . '/js/build/admin/admin.js' ),
				'deps'    => $asset_file['dependencies'],
				'version' => $asset_file['version'],
			),
			'yith-sales-common' => array(
				'src'     => yith_sales_load_js_file( YITH_SALES_ASSETS_URL . '/js/build/common/common.js' ),
				'deps'    => false,
				'version' => $asset_file['version'],
			),
		);

		foreach ( $admin_scripts as $handle => $admin_script ) {
			self::register_script( $handle, $admin_script['src'], $admin_script['deps'], $admin_script['version'] );
		}

		wp_tinymce_inline_scripts();
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'yith-sales-admin', 'wonder-cart', YITH_SALES_DIR . 'languages/' );
			wp_set_script_translations( 'yith-sales-common', 'wonder-cart', YITH_SALES_DIR . 'languages/' );
		}
	}

	/**
	 * Register the admin styles
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	private static function register_admin_styles() {
		$admin_styles = array(
			'yith-sales-admin' => array(
				'src'     => YITH_SALES_ASSETS_URL . '/css/admin.css',
				'deps'    => array(),
				'version' => YITH_SALES_VERSION,
				'has_rtl' => false,
			),
		);

		foreach ( $admin_styles as $handle => $admin_style ) {
			self::register_style( $handle, $admin_style['src'], $admin_style['deps'], $admin_style['version'], 'all', $admin_style['has_rtl'] );
		}
	}

	/**
	 * Register the frontend scripts
	 *
	 * @return void
	 */
	private static function register_frontend_scripts() {
		$asset_file                   = self::get_react_frontend_assets_dependencies();
		$asset_file['dependencies'][] = 'yith-sales-common';
		$frontend_scripts             = array(
			'yith-sales-frontend' => array(
				'src'     => yith_sales_load_js_file( YITH_SALES_ASSETS_URL . '/js/build/frontend/frontend.js' ),
				'deps'    => array_merge( $asset_file['dependencies'], array( 'jquery', 'wc-add-to-cart-variation' ) ),
				'version' => $asset_file['version'],
			),
			'yith-sales-common'   => array(
				'src'     => yith_sales_load_js_file( YITH_SALES_ASSETS_URL . '/js/build/common/common.js' ),
				'deps'    => false,
				'version' => $asset_file['version'],
			),
		);

		foreach ( $frontend_scripts as $handle => $frontend_script ) {
			self::register_script( $handle, $frontend_script['src'], $frontend_script['deps'], $frontend_script['version'] );
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'yith-sales-admin', 'wonder-cart', YITH_SALES_DIR . '/languages/' );
			wp_set_script_translations( 'yith-sales-common', 'wonder-cart', YITH_SALES_DIR . '/languages/' );
		}
	}

	/**
	 * Register the frontend styles
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	private static function register_frontend_styles() {
		$frontend_styles = array(
			'yith-sales-frontend' => array(
				'src'     => YITH_SALES_ASSETS_URL . '/css/frontend.css',
				'deps'    => array(),
				'version' => YITH_SALES_VERSION . time(),
				'has_rtl' => false,
			),
		);
		foreach ( $frontend_styles as $handle => $frontend_style ) {
			self::register_style( $handle, $frontend_style['src'], $frontend_style['deps'], $frontend_style['version'], 'all', $frontend_style['has_rtl'] );
		}
	}

	/**
	 * Register a style for use.
	 *
	 * @param   string    $handle   Name of the stylesheet. Should be unique.
	 * @param   string    $path     Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param   string[]  $deps     An array of registered stylesheet handles this stylesheet depends on.
	 * @param   string    $version  String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param   string    $media    The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param   boolean   $has_rtl  If has RTL version to load too.
	 *
	 * @author YITH
	 * @since  1.0.0
	 * @uses   wp_register_style()
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = YITH_SALES_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @param   string    $handle   Name of the stylesheet. Should be unique.
	 * @param   string    $path     Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param   string[]  $deps     An array of registered stylesheet handles this stylesheet depends on.
	 * @param   string    $version  String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param   string    $media    The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param   boolean   $has_rtl  If has RTL version to load too.
	 *
	 * @uses   wp_enqueue_style()
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = YITH_SALES_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles, true ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register a script for use.
	 *
	 * @param   string    $handle     Name of the script. Should be unique.
	 * @param   string    $path       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param   string[]  $deps       An array of registered script handles this script depends on.
	 * @param   string    $version    String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param   boolean   $in_footer  Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 *
	 * @since  1.0.0
	 * @author YITH
	 * @uses   wp_register_script()
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = YITH_SALES_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @param   string    $handle     Name of the script. Should be unique.
	 * @param   string    $path       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param   string[]  $deps       An array of registered script handles this script depends on.
	 * @param   string    $version    String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param   boolean   $in_footer  Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 *
	 * @uses   wp_enqueue_script()
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = YITH_SALES_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}

		wp_enqueue_script( $handle );
	}

	/**
	 * Localize a WC script once.
	 *
	 * @since 2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
	 *
	 * @param   string  $handle  Script handle the data will be attached to.
	 */
	public static function localize_script( $handle ) {

		if ( ! in_array( $handle, self::$yith_sales_localize_scripts, true ) ) {
			$data = self::get_script_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name                                = str_replace( '-', '_', $handle ) . '_params';
			self::$yith_sales_localize_scripts[] = $handle;
			/**
			 * APPLY_FILTERS: yith_sales_$handle_params
			 * The filter allow to add ,remove the data in the script.
			 *
			 * @param   array  $data  The script data.
			 *
			 * @return array
			 */
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 *
	 * @param   string  $handle  Script handle the data will be attached to.
	 *
	 * @return array|bool
	 * @author YITH
	 * @since  1.0.0
	 */
	private static function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'yith-sales-admin':
				$params = array(
					'assetsURL'           => YITH_SALES_ASSETS_URL,
					'logo'                => apply_filters( 'yith_sales_dashboard_logo', YITH_SALES_ASSETS_URL . '/images/bluehost.svg' ),
					'logotype'            => apply_filters( 'yith_sales_dashboard_logo_mobile', YITH_SALES_ASSETS_URL . '/images/bh-logotype.svg' ),
					'panelPage'           => yith_sales_get_panel_page(),
					'adminURL'            => get_admin_url(),
					'currency_symbol'     => get_woocommerce_currency_symbol(),
					'settings'            => wp_json_encode( yith_sales_get_options() ),
					'autosaveInterval'    => apply_filters( 'yith_sales_autosave_interval', 10 ),
					'mainAppId'           => apply_filters( 'yith_sales_main_app_id', '#wppbh-app' ),
					'campaignTypes'       => array_map( 'wp_json_encode', yith_sales()->modules ),
					'groups'              => wp_json_encode( yith_sales()->get_groups() ),
					'dateFormat'          => wc_date_format(),
					'timeFormat'          => wc_time_format(),
					'pluginVersion'       => YITH_SALES_VERSION,
					'wc'                  => self::get_wc_data(),
					'numCampaignsPerPage' => 10,
					'blockEditorSettings' => self::get_block_editor_settings( 'yith/sales/panel' ),
					/*'uiLibraryColors'     => array(
						'button'             => array(
							'borderRadius' => '6px',
							'contained'    => array(
								'background'               => '#196BDE!important',
								'backgroundColor'          => '#196BDE',
								'color'                    => '#fff!important',
								'&:hover'                  => array(
									'background'      => '#0A2B59!important',
									'backgroundColor' => '#0A2B59',
									'color'           => '#fff',
								),
								'&:focus, &:focus-visible' => array(
									'boxShadow' => '0 0 0 1px #fff, 0 0 0 2px #196BDE'
								),
								'&:disabled'               => array(
									'opacity'         => 1,
									'background'      => '#DCE2EA',
									'backgroundColor' => '#DCE2EA',
									'color'           => '#999999'
								),
							),
							'outlined'     => array(
								'background'               => '#fff!important',
								'backgroundColor'          => '#fff',
								'color'                    => '#000!important',
								'border'                   => '1px solid #196BDE',
								'&:hover'                  => array(
									'background'      => '#ccdcf4!important',
									'backgroundColor' => '#ccdcf4',
								),
								'&:focus, &:focus-visible' => array(
									'boxShadow' => '0 0 0 1px #fff, 0 0 0 2px #196BDE'
								),
								'&:disabled'               => array(
									'opacity'         => 1,
									'background'      => '#fff',
									'backgroundColor' => '#fff',
									'border'          => '1px solid #949fb2',
									'color'           => '#999999'
								),
							),
						),
						      'primary'            => '#196bde', //ok
						'primaryHover'       => '#0A2B59!important', //ok
						'primaryHoverBorder' => '#196bde',
						'focusedBorderColor' => 'rgb(25 107 222)',
						    'success'            => '#348528', //ok
						'tabs'               => array(  //ok
							'activeTab' => '#196bde',
							'hoverTab'  => '#cbd5e1'
						)
					),*/
					'uiLibraryColors'     => array()
				);
				break;
			case 'yith-sales-frontend':
				$params = array(
					'assetsURL'        => YITH_SALES_ASSETS_URL,
					'addToCartClasses' => 'single_add_to_cart_button ' . wc_wp_theme_get_element_class_name( 'button' ),
				);
				break;
			case 'yith-sales-common':
				$params = array(
					'assetsURL'            => YITH_SALES_ASSETS_URL,
					'ajaxURL'              => admin_url( 'admin-ajax.php' ),
					'ajaxAction'           => 'yith_sales_action',
					'ajaxNonce'            => wp_create_nonce( 'yith-sales-action' ),
					'imgPlaceholder'       => wc_placeholder_img(),
					'wc'                   => self::get_wc_data(),
					'dateFormat'           => wc_date_format(),
					'timeFormat'           => wc_time_format(),
					'numCampaignsPerPage'  => 10,
					'settings'             => wp_json_encode( yith_sales_get_options() ),
					'addToCartClasses'     => 'single_add_to_cart_button ' . wc_wp_theme_get_element_class_name( 'button' ),
					'productToShowOptions' => yith_sales_get_product_to_show_options(),
					'context'              => is_admin() ? 'edit' : 'view',
				);
				break;
			default:
				$params = false;
				break;
		}

		/**
		 * APPLY_FILTERS: yith_sales_get_scripts_data
		 * This filter allow to add, remove or change the param of a specific script.
		 *
		 * @param   array   $params  The script params.
		 * @param   string  $handle  The script handle.
		 *
		 * @return array
		 */
		return apply_filters( 'yith_sales_get_scripts_data', $params, $handle );
	}

	/**
	 * Get the block editor settings
	 *
	 * @param   string  $context_name  The context name.
	 *
	 * @return array
	 */
	private static function get_block_editor_settings( $context_name ) {
		$block_editor_context = new \WP_Block_Editor_Context( array( 'name' => $context_name ) );

		return get_block_editor_settings(
			array(
				'styles'            => get_block_editor_theme_styles(),
				'allowedBlockTypes' => array(
					'core/paragraph',
					'core/heading',
					'core/list',
					'core/list-item',
					'core/quote',
					'core/image',
				),
			),
			$block_editor_context
		);
	}

	/**
	 * Enqueue the block editor scripts
	 *
	 * @return void
	 */
	private static function enqueue_block_editor() {
		$editor_settings = self::get_block_editor_settings( 'yith/sales/panel' );

		wp_enqueue_style( 'wp-edit-blocks' );
		wp_enqueue_style( 'wp-format-library' );

		wp_enqueue_editor();
		wp_enqueue_media();

		do_action( 'enqueue_block_editor_assets' );

		// Preload server-registered block schemas.
		wp_add_inline_script(
			'wp-blocks',
			'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings() ) . ');'
		);

		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( $editor_settings['blockCategories'] ?? array() ) ),
			'after'
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param   string  $hook  The current admin page.
	 */
	public static function load_admin_scripts( $hook ) {
		self::register_admin_scripts();
		self::register_admin_styles();
		$panel_page = apply_filters( 'yith_sales_main_panel_page', 'bluehost' );
		if ( 'toplevel_page_' . $panel_page === $hook || 'toplevel_page_' . yith_sales_get_panel_page() === $hook ) {

			self::load_wp_script_library();
			wp_enqueue_script( 'yith-sales-admin' );
			$asset_file = self::get_react_admin_assets_dependencies();
			wp_enqueue_global_styles_css_custom_properties();
			// Enqueue CSS dependencies.
			foreach ( $asset_file['dependencies'] as $style ) {
				wp_enqueue_style( $style );
			}
			wp_enqueue_style( 'yith-sales-admin' );

			self::enqueue_block_editor();
		}

		self::localize_printed_scripts();
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {

		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}

	/**
	 * Enqueue frontend scripts
	 */
	public static function load_frontend_scripts() {
		self::register_frontend_scripts();
		self::register_frontend_styles();

		self::localize_printed_scripts();

		self::enqueue_script( 'yith-sales-frontend' );

		self::enqueue_style( 'yith-sales-frontend' );

		wp_add_inline_style( 'yith-sales-frontend', self::get_custom_colors() );

	}

	/**
	 * Should load block editor scripts and styles?
	 *
	 * @param   bool  $should_load  Should load flag.
	 *
	 * @return bool
	 */
	public static function should_load_block_editor_scripts_and_styles( $should_load ) {
		if ( ! $should_load ) {
			$should_load = wp_script_is( 'lapilli-ui-block-editor', 'enqueued' );
		}

		return $should_load;
	}

	/** -------------------------------------------------------
	 * Public Static Getters - to get specific settings
	 */

	/**
	 * Get WC data
	 *
	 * @return array
	 */
	public static function get_wc_data() {
		$currency_code = get_woocommerce_currency();

		$wc_settings = array(
			'currency'                  => array(
				'code'               => $currency_code,
				'precision'          => wc_get_price_decimals(),
				'symbol'             => html_entity_decode( get_woocommerce_currency_symbol( $currency_code ) ),
				'position'           => get_option( 'woocommerce_currency_pos' ),
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'price_format'       => html_entity_decode( get_woocommerce_price_format() ),
			),
			'placeholderImageSrc'       => wc_placeholder_img_src(),
			'discountRoundingMode'      => defined( 'WC_DISCOUNT_ROUNDING_MODE' ) && PHP_ROUND_HALF_UP === WC_DISCOUNT_ROUNDING_MODE ? 'half-up' : 'half-down',
			'states'                    => yith_sales_get_allowed_states(),
			'countries'                 => yith_sales_get_allowed_countries(),
			'cartURL'                   => wc_get_cart_url(),
			'checkoutURL'               => wc_get_checkout_url(),
			'simpleAddToCartSelector'   => 'form.cart > .single_add_to_cart_button',
			'variableAddToCartSelector' => 'form.variations_form .woocommerce-variation-add-to-cart > .single_add_to_cart_button',
		);

		return apply_filters( 'yith_sales_wc_settings', $wc_settings );
	}

	/**
	 * Load scripts
	 */
	public static function load_wp_script_library() {
		wp_enqueue_script( 'wp-api' );
		wp_enqueue_media();

	}

	/**
	 * Return variable color settings.
	 *
	 * @return string.
	 */
	public static function get_custom_colors() {

		$colors = array(
			'saving-color'             => yith_sales_get_default_setting( '#D10000', 'yith_sales_price_colors', 'saving_color' ),
			'sale-color'               => yith_sales_get_default_setting( '#000000', 'yith_sales_price_colors', 'sale_color' ),
			'regular-color'            => yith_sales_get_default_setting( '#949494', 'yith_sales_price_colors', 'regular_color' ),
			'free-color'               => yith_sales_get_default_setting( '#B6A000', 'yith_sales_price_colors', 'free_color' ),
			'modal-border-radius'      => yith_sales_get_default_setting( '15', 'yith_sales_modal_border', 'border_radius' ) . 'px',
			'modal-overlay-bg-color'   => yith_sales_get_default_setting( '#000', 'yith_sales_modal_colors', 'overlay_color' ),
			'modal-bg-color'           => yith_sales_get_default_setting( '#ffffff', 'yith_sales_modal_colors', 'background_color' ),
			'badge-bg-color'           => yith_sales_get_default_setting( '#ffffff', 'yith_sales_badge_colors', 'background_color' ),
			'badge-color'              => yith_sales_get_default_setting( '#ffffff', 'yith_sales_badge_colors', 'text_color' ),
			'form--border-radius'      => yith_sales_get_default_setting( '5', 'yith_sales_form_border', 'border_radius' ) . 'px',
			'form--bg-color'           => yith_sales_get_default_setting( '#ffffff', 'yith_sales_form_colors', 'background_color' ),
			'form--border-color'       => yith_sales_get_default_setting( '#E0E0E0', 'yith_sales_form_colors', 'border_color' ),
			'form--color'              => yith_sales_get_default_setting( '#000', 'yith_sales_form_colors', 'text_color' ),
			'campaign-label--color'    => yith_sales_get_default_setting( '#fff', 'yith_sales_campaign_label_colors', 'text_color' ),
			'campaign-label--bg-color' => yith_sales_get_default_setting( '#000', 'yith_sales_campaign_label_colors', 'background_color' ),
		);

		$css = ':root {';
		foreach ( $colors as $key => $color ) {
			$css .= sprintf( '--yith-sales-%s: %s;', $key, $color );
		}
		$css .= '}';

		return apply_filters( 'yith_sales_custom_colors', $css );
	}
}
