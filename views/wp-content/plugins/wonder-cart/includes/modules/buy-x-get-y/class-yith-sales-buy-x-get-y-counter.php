<?php
/**
 * This class is a counter for the Buy X Get Y Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class store how many products are in the cart.
 */
class YITH_Sales_Buy_X_Get_Y_Counter {
	use YITH_Sales_Trait_Singleton;

	/**
	 * Store an array of counter
	 *
	 * @var array
	 */
	protected $counter = array();

	/**
	 * Update counter
	 *
	 * @param string $key    the array key.
	 * @param int    $amount the value.
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public static function update_counter( $key, $amount ) {
		if ( isset( self::$instance->counter[ $key ] ) ) {
			$old_amount                      = self::$instance->counter[ $key ];
			self::$instance->counter[ $key ] = $old_amount - $amount;
		}
	}

	/**
	 * Reset the counter
	 *
	 * @param string $key    the array key.
	 * @param bool   $remove Check if remove or not the entry.
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public static function reset_counter( $key, $remove = true ) {
		if ( isset( self::$instance->counter[ $key ] ) ) {
			if ( $remove ) {
				unset( self::$instance->counter[ $key ] );
			} else {
				self::$instance->counter[ $key ] = 0;
			}
		}
	}

	/**
	 * Get the counter
	 *
	 * @param string $key     the array key.
	 * @param int    $default the default value.
	 *
	 * @return int
	 * @since  1.0.0
	 * @author YITH
	 */
	public static function get_counter( $key, $default = 0 ) {
		if ( isset( self::$instance->counter[ $key ] ) ) {
			return self::$instance->counter[ $key ];
		} else {
			self::$instance->counter[ $key ] = $default;

			return $default;
		}
	}

	/**
	 * Reset the counter
	 *
	 * @return void
	 */
	public static function reset() {
		self::$instance->counter = array();
	}
}

YITH_Sales_Buy_X_Get_Y_Counter::get_instance();
