<?php
/**
 * Cron class.
 * handle Cron processes.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Booking\Modules\Premium
 */

defined( 'YITH_WCBK' ) || exit;

if ( ! class_exists( 'YITH_WCBK_Cron' ) ) {
	/**
	 * Class YITH_WCBK_Cron
	 *
	 * @since  2.0.0
	 * @since  5.8.1 Split in base and premium class, to handle reserved bookings and resources in the base one.
	 */
	class YITH_WCBK_Cron {
		use YITH_WCBK_Extensible_Singleton_Trait;

		/**
		 * The constructor.
		 */
		protected function __construct() {
			add_action( 'wp_loaded', array( $this, 'schedule_actions' ), 30 );
		}

		/**
		 * Schedule actions through the WooCommerce Action Scheduler.
		 */
		public function schedule_actions() {
			if ( ! WC()->queue()->get_next( 'yith_wcbk_reserved_bookings_clean_expired' ) ) {
				WC()->queue()->schedule_single( strtotime( 'tomorrow midnight' ), 'yith_wcbk_reserved_bookings_clean_expired', array(), 'yith-booking' );
			}

			if ( ! WC()->queue()->get_next( 'yith_wcbk_reserved_resources_clean_expired' ) && yith_wcbk_is_resources_module_active() ) {
				WC()->queue()->schedule_single( strtotime( 'tomorrow midnight' ), 'yith_wcbk_reserved_resources_clean_expired', array(), 'yith-booking' );
			}
		}
	}
}
