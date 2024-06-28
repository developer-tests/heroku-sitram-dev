<?php
/**
 * Plugin Name: WonderCart
 * Description: Boost your revenue by launching and scheduling promotional campaigns like Buy One, Get One Free, Frequently Bought Together, Free Shipping, Bulk Discounts, and more.
 * Version: 1.8.0
 * Text Domain: wonder-cart
 * Domain Path: /languages/
 * WC requires at least: 8.6
 * WC tested up to: 8.8
 *
 * Init file
 *
 * @author YITH
 * @package YITH/Sales
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

! defined( 'YITH_SALES_DIR' ) && define( 'YITH_SALES_DIR', plugin_dir_path( __FILE__ ) );


// Define constants ________________________________________

$wp_upload_dir = wp_upload_dir();

! defined( 'YITH_SALES_VERSION' ) && define( 'YITH_SALES_VERSION', '1.8.0' );
! defined( 'YITH_SALES_DIR' ) && define( 'YITH_SALES_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_SALES_INIT' ) && define( 'YITH_SALES_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_SALES_FILE' ) && define( 'YITH_SALES_FILE', __FILE__ );
! defined( 'YITH_SALES_URL' ) && define( 'YITH_SALES_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_SALES_ASSETS_URL' ) && define( 'YITH_SALES_ASSETS_URL', YITH_SALES_URL . 'assets' );
! defined( 'YITH_SALES_TEMPLATE_PATH' ) && define( 'YITH_SALES_TEMPLATE_PATH', YITH_SALES_DIR . 'templates' );
! defined( 'YITH_SALES_INC' ) && define( 'YITH_SALES_INC', YITH_SALES_DIR . '/includes/' );
! defined( 'YITH_SALES_VIEWS_PATH' ) && define( 'YITH_SALES_VIEWS_PATH', YITH_SALES_INC . 'admin/views' );
! defined( 'YITH_SALES_SLUG' ) && define( 'YITH_SALES_SLUG', 'wonder-cart' );
! defined( 'YITH_SALES_EXTENDED' ) && define( 'YITH_SALES_EXTENDED', '1' );
! defined( 'YITH_SALES_SECRET_KEY' ) && define( 'YITH_SALES_SECRET_KEY', 'L4dwPflABFsm4nu6rouL' );


/* Newfold plugin module */
if ( ! function_exists( 'yith_nfbm_init' ) && file_exists( YITH_SALES_DIR . 'yith-nf-brands-module/init.php' ) ) {
	require_once YITH_SALES_DIR . 'yith-nf-brands-module/init.php';
}

if ( ! function_exists( 'yith_sales_install_woocommerce_admin_notice_premium' ) ) {
	/**
	 * Print a notice if WooCommerce is not installed.
	 */
	function yith_sales_install_woocommerce_admin_notice_premium() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'WonderCart is enabled but not effective. It requires WooCommerce in order to work.', 'wonder-cart' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_sales_install' ) ) {
	/**
	 * Check WC installation.
	 */
	function yith_sales_install() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_sales_install_woocommerce_admin_notice_premium' );
		} else {
			add_action( 'before_woocommerce_init', 'yith_sales_add_support_hpos_system' );
			do_action( 'yith_sales_init' );
			require_once 'includes/class-yith-sales-install.php';
			YITH_Sales_Install::init();
		}
	}
}
add_action( 'plugins_loaded', 'yith_sales_install', 11 );

if ( ! function_exists( 'yith_sales_add_support_hpos_system' ) ) {
	/**
	 * Add support with HPOS system for WooCommerce 8
	 *
	 * @return void
	 */
	function yith_sales_add_support_hpos_system() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_SALES_INIT );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', YITH_SALES_INIT );
		}
	}
}

// Require plugin autoload.
if ( ! class_exists( 'YITH_Sales_Autoloader' ) ) {
	require_once YITH_SALES_INC . 'class-yith-sales-autoloader.php';
}

if ( ! function_exists( 'yith_sales' ) ) {
	/**
	 * Unique access to instance of YITH_Sales class
	 *
	 * @return YITH_Sales
	 * @since 1.0.0
	 */
	function yith_sales() { // phpcs:ignore
		return YITH_Sales::get_instance();
	}
}

if ( ! function_exists( 'yith_sales_constructor' ) ) {
	/**
	 * Start the game.
	 */
	function yith_sales_constructor() {
		load_plugin_textdomain( 'wonder-cart', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		include_once YITH_SALES_INC . 'rest-api/endpoints/class-yith-rest-campaigns-controller.php';
		include_once YITH_SALES_INC . 'yith-sales-functions.php';
		yith_sales();
	}
}
add_action( 'yith_sales_init', 'yith_sales_constructor' );

require_once YITH_SALES_INC.'wc-blocks/src/class-yith-sales-wc-blocks-register.php';

// Auto update via Hiive CDN
require_once YITH_SALES_DIR . '/hiive-autoupdate.php';