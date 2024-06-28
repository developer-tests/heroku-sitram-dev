<?php
/**
 * Autoloader class
 *
 * @class   YITH_Sales_Autoloader
 * @package YITH/Sales
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Autoloader' ) ) {
	/**
	 * The autoloader class
	 *
	 * @class      YITH_Sales_Autoloader
	 * @since      1.0.0
	 * @author     YITH
	 * @package
	 */
	class YITH_Sales_Autoloader {

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Get mapped file. Array of class => file to use on autoload.
		 *
		 * @return array
		 * @since  1.0.0
		 */
		protected function get_mapped_files() {
			/**
			 * APPLY_FILTERS: yith_sales_autoload_mapped_files
			 *
			 * The filter allow to add remove the class to autoload.
			 *
			 * @param array $mapped_files The mapped files array.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_sales_autoload_mapped_files', array() );
		}

		/**
		 * Autoload callback
		 *
		 * @param string $class The class to load.
		 *
		 * @since  1.0.0
		 */
		public function autoload( $class ) {

			$class = strtolower( $class );
			$class = str_replace( '_', '-', $class );
			if ( false === strpos( $class, 'yith-sales' ) ) {
				return; // Pass over.
			}

			$base_path = YITH_SALES_DIR . 'includes/';
			// Check first for mapped files.
			$mapped = $this->get_mapped_files();
			if ( isset( $mapped[ $class ] ) ) {
				$file = $base_path . $mapped[ $class ];
			} else {
				if ( false !== strpos( $class, 'trait' ) ) {
					$file = $base_path . 'traits/trait-' . $class . '.php';

				} elseif ( false !== strpos( $class, 'abstract' ) ) {
					$base_path .= 'abstracts/';
					$file       = $base_path . $class . '.php';

				} elseif ( false !== strpos( $class, 'interface' ) ) {
					$base_path .= 'interfaces/';
					$file       = $base_path . 'interface-' . $class . '.php';

				}else if( false !== strpos( $class, 'wc-blocks')){
					$base_path .= 'wc-blocks/src/';
					$file       = $base_path  .'class-'. $class . '.php';

				} else {

					if ( false !== strpos( $class, 'data-store' ) ) {
						$base_path .= 'data-stores/';
					} elseif ( false !== strpos( $class, 'controller' ) ) {

						$base_path .= 'controllers/';
					}

					$file = $base_path . 'class-' . $class . '.php';
				}
			}

			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}
}

new YITH_Sales_Autoloader();
