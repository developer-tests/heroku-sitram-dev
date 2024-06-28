<?php
/**
 * Class that installs the plugin requirements
 *
 * @package YITH\Sales
 * @author  YITH
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class that init the db
 */
class YITH_Sales_Install {
	/**
	 * The function that init the configuration
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public static function init() {
		self::create_media();
		self::enable_wc_coupons();
	}

	/**
	 * Create the default media
	 *
	 * @return void
	 */
	protected static function create_media() {

		self::create_default_media( 'yith_sales_default_gift_icon', 'gift-icon.png' );
		self::create_default_media( 'yith_sales_default_gift_bg_image', 'gift-bg-image.png' );
		self::create_default_media( 'yith_sales_default_free_shipping_icon', 'free-shipping-icon.png' );
	}

	/**
	 * Create the default media by option name
	 *
	 * @param string $option_name   The option name.
	 * @param string $file_to_store The file name to save in media library.
	 *
	 * @return void
	 */
	protected static function create_default_media( $option_name, $file_to_store ) {

		$option = get_option( $option_name, array() );

		if ( ! empty( $option['id'] ) ) {
			if ( ! is_numeric( $option['id'] ) ) {
				return;
			} elseif ( wp_attachment_is_image( $option['id'] ) ) {
				return;
			}
		}

		$upload_dir = wp_upload_dir();
		$source     = YITH_SALES_DIR . '/assets/images/' . $file_to_store;
		$filename   = $upload_dir['basedir'] . '/' . $file_to_store;

		if ( ! file_exists( $filename ) ) {
			copy( $source, $filename ); // @codingStandardsIgnoreLine.
		}

		if ( ! file_exists( $filename ) ) {
			update_option( $option_name, array() );

			return;
		}
		$filetype   = wp_check_filetype( basename( $filename ) );
		$attachment = array(
			'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $filename );
		if ( is_wp_error( $attach_id ) ) {
			update_option( $option_name, array() );

			return;
		}

		update_option(
			$option_name,
			array(
				'id'       => $attach_id,
				'type'     => $filetype['type'],
				'fileName' => $attachment['post_title'],
				'url'      => wp_get_attachment_url( $attach_id ),
			),
		);

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Enable the WC Coupons system
	 *
	 * @return void
	 */
	protected static function enable_wc_coupons() {
		$coupons_enabled = get_option( 'woocommerce_enable_coupons' );
		if ( 'yes' !== $coupons_enabled ) {
			update_option( 'woocommerce_enable_coupons', 'yes' );
		}
	}
}
