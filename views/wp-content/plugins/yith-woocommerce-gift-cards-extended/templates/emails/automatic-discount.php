<?php
/**
 * Show a section for the automatic discount link and description
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ywgc-add-cart-discount">
	<div class="ywgc-discount-link-section">
		<a class="ywgc-discount-link" href="<?php echo esc_url( $apply_discount_url ); ?>"><?php echo wp_kses_post( empty( $email_button_label_get_option ) ? __( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ) : $email_button_label_get_option ); ?></a>
	</div>
</div>
