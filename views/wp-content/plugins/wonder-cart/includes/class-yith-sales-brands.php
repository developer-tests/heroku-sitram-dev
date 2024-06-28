<?php
/**
 * The class manage the compatibility with HostGator brand
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class manage the NF Brands
 */
class YITH_Sales_Brands {
	use YITH_Sales_Trait_Singleton;

	/**
	 * Store all nf id
	 *
	 * @var string
	 */
	protected $nf_id;

	/**
	 * The construct
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'init_nf_integration' ) );
	}

	/**
	 * Init the integration
	 *
	 * @return void
	 */
	public function init_nf_integration() {

		if ( function_exists( 'yith_nfbm_get_container_plugin_attribute' ) ) {

			$this->nf_id = yith_nfbm_get_container_plugin_attribute( 'id' );
			$this->nf_integration();
		}
	}

	/**
	 * Add the custom style and change the panel pages
	 *
	 * @return void
	 */
	public function nf_integration() {
		add_filter( 'yith_sales_get_scripts_data', array( $this, 'add_nf_style' ), 10, 2 );
		add_filter( 'yith_sales_panel_page', array( $this, 'get_nf_panel_page' ) );
		add_filter( 'yith_sales_main_panel_page', array( $this, 'get_main_nf_panel_page' ) );
		add_filter( 'yith_sales_main_app_id', array( $this, 'get_main_app_id_nf' ) );
	}

	/**
	 * Add the custom style
	 *
	 * @param array  $params The style args.
	 * @param string $handle The script handler.
	 *
	 * @return array
	 */
	public function add_nf_style( $params, $handle ) {
		if ( 'yith-sales-admin' === $handle ) {
			switch ( $this->nf_id ) {
				case 'hostgator':
					$params['uiLibraryColors'] = $this->get_hostgator_style();
					break;
				case 'crazy-domains':
					$params['uiLibraryColors'] = $this->get_crazy_domains_style();
					break;
			}
		}

		return $params;
	}

	/**
	 * Get the style for hostgator
	 *
	 * @return array
	 */
	public function get_hostgator_style() {
		return array(
			'fields'   => array(
				'focusedBorderColor' => '#191936'
			),
			'palette'  => array(
				'primary'   => array(
					'light'         => '#1F2044',
					'main'        => '#191936',
					'contrastText' => '#FFF'
				),
				'secondary' => array(
					'main'         => '#FFCF00',
					'light'        => '#ECA93E',
					'contrastText' => '#000'
				),
				'tabs'      => array(
					'active' => '#1f2044',
					'hover'  => '#cdd8df'
				),
				'text'      => array(
					'primary'   => '#1F2044',
					'secondary' => '#363636'
				),
				'success'   => array(
					'main'  => '#388E3C',
					'light' => '#DFF2E0'
				)
			),
			'outlined' => '#FFCF00'
		);
	}

	/**
	 * Get the style for crazy domains
	 *
	 * @return array
	 */
	public function get_crazy_domains_style() {
		return array(
			'fields'   => array(
				'focusedBorderColor' => '#44691d'
			),
			'palette'  => array(
				'primary'   => array(
					'light'        => '#548224',
					'main'         => '#44691d',
					'contrastText' => '#FFF'
				),
				'secondary' => array(
					'main'         => '#576164',
					'light'        => '#4F595C',
					'contrastText' => '#FFF'
				),
				'tabs'      => array(
					'active' => '#558224',
					'hover'  => '#cdd8df'
				),
				'text'      => array(
					'primary'   => '#484848',
					'secondary' => '#4F595C'
				),
				'success'   => array(
					'main'  => '#388E3C',
					'light' => '#DFF2E0'
				)
			),
			'outlined' => '#576164'
		);
	}

	/**
	 * Get the right  panel page
	 *
	 * @param string $panel_page The panel page.
	 *
	 * @return string
	 */
	public function get_nf_panel_page( $panel_page ) {
		switch ( $this->nf_id ) {
			case 'hostgator':
				$panel_page = $this->nf_id . '#/store/sales_discounts';
				break;
			case 'crazy-domains':
				$panel_page = $this->nf_id . '#/store/sales_discounts';
				break;
		}

		return $panel_page;
	}

	/**
	 * Get the right  panel page
	 *
	 * @return string
	 */
	public function get_main_nf_panel_page() {
		return $this->nf_id;
	}

	/**
	 * Get the right  panel page id
	 *
	 * @return string
	 */
	public function get_main_app_id_nf( $main_page ) {
		switch ( $this->nf_id ) {
			case 'hostgator':
				$main_page = '#hwa-app';
				break;
			case 'crazy-domains':
				$main_page = '#wppcd-app';
				break;
		}

		return $main_page;
	}

}