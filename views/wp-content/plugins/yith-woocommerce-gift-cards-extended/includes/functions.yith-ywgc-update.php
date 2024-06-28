<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Update functions
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Enable the option to show the update modal instead of the "welcome" one.
 */
function yith_ywgc_update_show_update_modal_3() {
	update_option( 'yith-ywgc-welcome-modal', 'update' );
}
