<?php
/**
 * Admin Class
 *
 * @class   YITH_Sales_Admin
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class
 */
class YITH_Sales_Admin {
	/**
	 * Construct
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function __construct() {

		// Plugin row action and licence.
		add_filter(
			'plugin_action_links_' . plugin_basename( YITH_SALES_DIR . '/' . basename( YITH_SALES_FILE ) ),
			array(
				$this,
				'action_links',
			)
		);
	}


	/**
	 * Action Links
	 *
	 * Add the action links to plugin admin page
	 *
	 * @param array $links | links plugin array.
	 *
	 * @return   array
	 * @since    1.0
	 * @author   YITH
	 */
	public function action_links( $links ) {
		$links[] = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=' . yith_sales_get_panel_page() ), _x( 'Settings', 'Action links', 'wonder-cart' ) );

		return $links;
	}

}
