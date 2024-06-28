<?php
/**
 * Main Class
 *
 * @class   YITH_Sales
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Class
 */
class YITH_Sales {

	use YITH_Sales_Trait_Singleton;

	/**
	 * Single instance of the class
	 *
	 * @var YITH_Sales_Admin
	 */
	public $admin;

	/**
	 * Single instance of frontend class
	 *
	 * @var YITH_Sales_Frontend
	 */
	public $frontend;

	/**
	 * Modules installed
	 *
	 * @var array
	 */
	public $modules;

	/**
	 * Constructor
	 */
	protected function __construct() {
		// Plugin framework implementation.
		add_action( 'init', array( $this, 'load_text_domain' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'load' ), 20 );
		add_action( 'rest_api_init', array( $this, 'register_custom_products_endpoint' ) );
		add_action( 'rest_api_init', array( $this, 'register_settings_endpoint' ) );
		// Script Translations.
		add_filter( 'pre_load_script_translations', array( $this, 'script_translations' ), 10, 4 );


	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function load() {

		if ( ! doing_action( 'plugins_loaded' ) ) {
			_doing_it_wrong( __METHOD__, 'This method should be called only once on plugins loaded!', '1.0.0' );

			return;
		}
		$this->get_installed_modules();
		$this->load_modules();

		YITH_Sales_Assets::init();
		YITH_Sales_Ajax::get_instance();
		YITH_Sales_Post_Type::get_instance();
		YITH_Sales_Controller::get_instance();
		YITH_Sales_WC_Blocks::get_instance();
		yith_sales_get_options();
		YITH_Sales_Brands::get_instance();
		if ( $this->is_admin() ) {
			$this->admin = new YITH_Sales_Admin();
		}

	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wonder-cart/wonder-cart-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wonder-cart-LOCALE.mo
	 */
	public function load_text_domain() {
		$locale = determine_locale();

		/**
		 * APPLY_FILTERS: plugin_locale
		 *
		 * Filter the locale.
		 *
		 * @param string $locale      the locale.
		 * @param string $text_domain The text domain.
		 *
		 * @return string
		 */
		$locale = apply_filters( 'plugin_locale', $locale, 'wonder-cart' );

		unload_textdomain( 'wonder-cart' );
		load_textdomain( 'wonder-cart', WP_LANG_DIR . '/wonder-cart/wonder-cart-' . $locale . '.mo' );
		load_plugin_textdomain( 'wonder-cart', false, plugin_basename( YITH_SALES_DIR ) . '/languages' );
	}


	/**
	 * Check if is admin or not and load the correct class
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_admin() {
		$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$check_context = isset( $_REQUEST['context'] ) && 'frontend' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return is_admin() && ! ( $check_ajax && $check_context );
	}

	/**
	 * Return the group of modules
	 *
	 * @return array
	 */
	public function get_groups() {
		$groups = array(
			array(
				'id'   => 'product',
				'name' => __( 'Product page', 'wonder-cart' ),
			),
			array(
				'id'   => 'discount',
				'name' => __( 'Discount & Promotions', 'wonder-cart' ),
			),
			array(
				'id'   => 'cart-checkout',
				'name' => __( 'Cart & Checkout', 'wonder-cart' ),
			),
			array(
				'id'   => 'other',
				'name' => __( 'Other', 'wonder-cart' ),
			),
		);

		return apply_filters( 'yith_sales_groups', $groups );
	}

	/**
	 * Return the list of modules
	 *
	 * @return array
	 */
	public function get_installed_modules() {
		if ( $this->modules ) {
			return $this->modules;
		}

		$modules = array(
			array(
				'id'                    => 'frequently-bought-together',
				'name'                  => __( 'Frequently Bought Together', 'wonder-cart' ),
				'cta_button'            => __( 'Create a FBT campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You haven\'t created any Frequently Bought Together campaigns yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create your first one now!', 'wonder-cart' ),
				'description'           => __( 'Boost your sales by presenting customers with frequently bought together products as suggestions while they browse.', 'wonder-cart' ),
				'group'                 => 'product',
				'controller'            => 'YITH_Sales_Frequently_Bought_Together_Controller',
			),
			array(
				'id'                    => 'upsell',
				'name'                  => __( 'Upsell Modal', 'wonder-cart' ),
				'cta_button'            => __( 'Create an Upsell Modal campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Upsell Modal Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create your first one now!', 'wonder-cart' ),
				'description'           => __( 'Increase your average order value by displaying a pop-up window with relevant upsell options after customers add items to their cart.', 'wonder-cart' ),
				'group'                 => 'product',
				'controller'            => 'YITH_Sales_Upsell_Controller',
			),
			array(
				'id'                    => 'three-for-two',
				'name'                  => __( '3x2', 'wonder-cart' ),
				'cta_button'            => __( 'Create a 3x2 campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any 3x2 Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create your first one now!', 'wonder-cart' ),
				'description'           => __( 'Encourage customers to buy more by offering a promotion where they can purchase three items but pay for only two.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Dynamic_Controller',
			),
			array(
				'id'                    => 'buy-x-get-y',
				'name'                  => __( 'Buy X Get Y', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Buy X Get Y campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Buy X Get Y Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create your first one now!', 'wonder-cart' ),
				'description'           => __( 'Entice customers by offering a free or discounted product when they purchase a specific quantity or type of another.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Buy_X_Get_Y_Controller',
			),
			array(
				'id'                    => 'bogo',
				'name'                  => __( 'BOGO (Buy One, Get One)', 'wonder-cart' ),
				'cta_button'            => __( 'Create a BOGO campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any BOGO Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Attract customers with a compelling deal where they can buy one product and get another product for free.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Dynamic_Controller',
			),
			array(
				'id'                    => 'quantity-discount',
				'name'                  => __( 'Quantity Discount', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Quantity Discount campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Quantity Discount Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Motivate customers to buy in bulk by offering a discount for buying a certain quantity of products.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Dynamic_Controller',
			),
			array(
				'id'                    => 'category-discount',
				'name'                  => __( 'Category Discount', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Category Discount campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Category Discount Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Give your customers a discount on specific categories of products and boost sales for those categories.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Price_Controller',
			),
			array(
				'id'                    => 'shop-discount',
				'name'                  => __( 'Discount on all Products', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Discount on all Products campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Discount on all Products Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Attract and retain customers by offering a storewide discount on all products for a limited time.', 'wonder-cart' ),
				'group'                 => 'discount',
				'controller'            => 'YITH_Sales_Price_Controller',
			),
			array(
				'id'                    => 'cart-discount',
				'name'                  => __( 'Cart Discount', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Cart Discount campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Cart Discount Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Encourage customers to spend more by providing a discount based on their cart\'s total value, amount spent, or number of orders.', 'wonder-cart' ),
				'group'                 => 'cart-checkout',
				'controller'            => 'YITH_Sales_Cart_Discount_Controller',
			),
			array(
				'id'                    => 'last-deal',
				'name'                  => __( 'Last Deal', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Last Deal campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Last Deal Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Create urgency and drive sales with a special time-limited deal for customers on the cart and checkout pages.', 'wonder-cart' ),
				'group'                 => 'cart-checkout',
				'controller'            => 'YITH_Sales_Modal_Controller',
			),
			array(
				'id'                    => 'gift-on-cart',
				'name'                  => __( 'Gift Product in Cart', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Gift Product in Cart campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Gift Product in Cart Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Reward your customers with a free gift in their cart based on their total cart amount or specific products or categories purchased.', 'wonder-cart' ),
				'group'                 => 'cart-checkout',
				'controller'            => 'YITH_Sales_Modal_Controller',
			),
			array(
				'id'                    => 'thank-you-page',
				'name'                  => __( 'Upsell in Thank You Page', 'wonder-cart' ),
				'cta_button'            => __( 'Create an Upsell in Thank You Page campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Thank you Page Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Maximize sales by presenting customers with relevant upsell options on the Thank You page after they have completed their purchase.', 'wonder-cart' ),
				'group'                 => 'other',
				'controller'            => 'YITH_Sales_Thank_You_Page_Controller',
			),
			array(
				'id'                    => 'free-shipping',
				'name'                  => __( 'Free shipping', 'wonder-cart' ),
				'cta_button'            => __( 'Create a Free shipping campaign', 'wonder-cart' ),
				'empty_state_header'    => __( 'You don\'t have any Free Shipping Campaign yet.', 'wonder-cart' ),
				'empty_state_subheader' => __( 'Create now your first one!', 'wonder-cart' ),
				'description'           => __( 'Attract and retain customers by offering free shipping storewide, based on the total cart amount, or customer\'s shipping location.', 'wonder-cart' ),
				'group'                 => 'cart-checkout',
				'controller'            => 'YITH_Sales_Free_Shipping_Controller',
			),
		);

		foreach ( $modules as $key => $module ) {
			$option_file = YITH_SALES_INC . 'modules/' . $module['id'] . '/options.php';

			if ( file_exists( $option_file ) ) {
				$modules[ $key ]['options'] = include $option_file;
			}
		}

		$this->modules = apply_filters( 'yith_sales_installed_modules', $modules );

		return $this->modules;
	}

	/**
	 * Return the list of campaigns ordered by type
	 *
	 * @return array
	 */
	public function get_order_of_campaigns() {
		return array(
			'frequently-bought-together',
			'upsell',
			'last-deal',
			'thank-you-page',
			'quantity-discount',
			'category-discount',
			'shop-discount',
			'bogo',
			'buy-x-get-y',
			'three-for-two',
			'gift-on-cart',
			'cart-discount',
			'free-shipping',
		);
	}

	/**
	 * Load modules
	 *
	 * @return void
	 */
	public function load_modules() {

		foreach ( $this->modules as $key => $module ) {
			$class_file                 = YITH_SALES_INC . 'modules/' . $module['id'] . '/class-yith-sales-' . $module['id'] . '.php';
			$class_action_schedule_file = YITH_SALES_INC . 'modules/' . $module['id'] . '/class-yith-sales-' . $module['id'] . '-action-schedule.php';
			$class_counter              = YITH_SALES_INC . 'modules/' . $module['id'] . '/class-yith-sales-' . $module['id'] . '-counter.php';
			if ( file_exists( $class_file ) ) {
				require_once $class_file;
			}

			if ( file_exists( $class_action_schedule_file ) ) {
				require_once $class_action_schedule_file;
			}

			if ( file_exists( $class_counter ) ) {
				require_once $class_counter;
			}
		}
	}

	/**
	 * Register the rest api for the product
	 *
	 * @return void
	 */
	public function register_custom_products_endpoint() {
		require_once YITH_SALES_INC . '/rest-api/endpoints/class-yith-rest-products-controller.php';
		$controller = new YITH_REST_Products_Controller();
		$controller->register_routes();
	}

	/**
	 * Register the rest api for the settings
	 *
	 * @return void
	 */
	public function register_settings_endpoint() {
		require_once YITH_SALES_INC . '/rest-api/endpoints/class-yith-rest-settings-controller.php';
		$controller = new YITH_REST_Settings_Controller();
		$controller->register_routes();
	}

	/**
	 * Create the json translation through the PHP file
	 * so it's possible using normal translations (with PO files) also for JS translations
	 *
	 * @param   string|null  $json_translations  Json translation.
	 * @param   string       $file               File.
	 * @param   string       $handle             Handle.
	 * @param   string       $domain             Domain.
	 *
	 * @return string|null
	 * @since 4.0
	 */
	public function script_translations( $json_translations, $file, $handle, $domain ) {
		$plugin_domain = 'wonder-cart';
		$handles       = array( 'yith-sales-admin', 'yith-sales-common' );

		if ( $plugin_domain === $domain && in_array( $handle, $handles, true ) ) {
			$path = YITH_SALES_DIR . 'languages/' . $domain . '.php';
			if ( file_exists( $path ) ) {
				$translations = include $path;

				$json_translations = wp_json_encode(
					array(
						'domain'      => $handles,
						'locale_data' => array(
							'messages' =>
								array(
									'' => array(
										'domain'       => $handles,
										'lang'         => get_locale(),
										'plural-forms' => 'nplurals=2; plural=(n != 1);',
									),
								)
								+
								$translations,
						),
					)
				);
			}
		}

		return $json_translations;
	}

}
