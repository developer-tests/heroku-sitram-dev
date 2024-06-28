<?php
/**
 * Gift Card product add to cart
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Context -> email or pdf
 */

?>
<table cellspacing="0" class="ywgc-table-template">
	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_before_logo
	 *
	 * Allow actions before displaying the logo in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_before_logo', $object, $context );
	?>

	<?php if ( get_option( 'ywgc_shop_logo_on_gift_card_before', 'no' ) === 'yes' ) : ?>
		<tr>
			<td class="ywgc-logo-shop" colspan="2" align="<?php echo esc_attr( get_option( 'ywgc_shop_logo_before_alignment', 'left' ) ); ?>">
				<?php
				if ( isset( $company_logo_url ) && $company_logo_url ) {
					/**
					 * APPLY_FILTERS: ywgc_custom_company_logo_url
					 *
					 * Filter the company logo URL in the gift card template.
					 *
					 * @param string $company_logo_url the company logo URL
					 * @param string $context the context. Allowed values: "email" or "pdf"
					 *
					 * @return string
					 */
					?>
					<img src="<?php echo esc_url( apply_filters( 'ywgc_custom_company_logo_url', $company_logo_url, $context ) ); ?>" class="ywgc-logo-shop-image" alt="<?php esc_attr_e( 'The shop logo for the gift card', 'yith-woocommerce-gift-cards' ); ?>" title="<?php esc_attr_e( 'The shop logo for the gift card', 'yith-woocommerce-gift-cards' ); ?>">
				<?php } ?>
			</td>
		</tr>
	<?php endif; ?>

	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_before_main_image
	 *
	 * Allow actions before displaying the main image in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_before_main_image', $object, $context );

	// Override the gift card image with the one uploaded by the admin.
	$override_default_image = get_post_meta( $object->ID, '_ywgc_set_default_image', true );
	$image_set_by_admin     = get_post_meta( $object->ID, '_ywgc_default_image', true );

	if ( $override_default_image && $image_set_by_admin ) {
		$header_image_url = $image_set_by_admin;
	}

	/**
	 * APPLY_FILTERS: ywgc_custom_header_image_url
	 *
	 * Filter the custom gift card image URL in the gift card template.
	 *
	 * @param string $company_logo_url the custom gift card imag URL
	 * @param string $context the context. Allowed values: "email" or "pdf"
	 *
	 * @return string
	 */
	$header_image_url = apply_filters( 'ywgc_custom_header_image_url', preg_replace( '/^https(?=:\/\/)/i', 'http', $header_image_url ), $context );

	if ( $header_image_url ) :
		// This add the default gift card image when the image is lost.
		if ( substr( $header_image_url, -strlen( '/' ) ) === '/' ) {
			$header_image_url = $default_header_image_url;
		}

		?>
		<tr>
			<td class="ywgc-main-image-td" colspan="2">
				<?php
				if ( $object->design_type == 'custom-modal' && $context == 'email' ) {
					$header_image_64 = $object->design;
					?>
					<img src="<?php echo $header_image_64; ?>"
						 class="ywgc-main-image"
						 alt="<?php _e( 'Gift card image', 'yith-woocommerce-gift-cards' ); ?>"
						 title="<?php _e( 'Gift card image', 'yith-woocommerce-gift-cards' ); ?>">
					<?php
				} else {
					?>
					<img src="<?php echo $header_image_url; ?>"
						class="ywgc-main-image"
						alt="<?php _e( 'Gift card image', 'yith-woocommerce-gift-cards' ); ?>"
						title="<?php _e( 'Gift card image', 'yith-woocommerce-gift-cards' ); ?>">
				<?php } ?>

			</td>

		</tr>
	<?php endif; ?>

	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_after_main_image
	 *
	 * Allow actions after displaying the main image in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_after_main_image', $object, $context );
	?>

	<tr>

		<td class="ywgc-logo-shop" colspan="2" align="<?php echo get_option( 'ywgc_shop_logo_after_alignment' ); ?>">
			<?php if ( isset( $company_logo_url ) && $company_logo_url && get_option( 'ywgc_shop_logo_on_gift_card_after', 'no' ) == 'yes' ) { ?>
				<img src="<?php echo apply_filters( 'ywgc_custom_company_logo_url', $company_logo_url, $context ); ?>"
					 class="ywgc-logo-shop-image"
					 alt="<?php _e( 'The shop logo for the gift card', 'yith-woocommerce-gift-cards' ); ?>"
					 title="<?php _e( 'The shop logo for the gift card', 'yith-woocommerce-gift-cards' ); ?>">

			<?php } ?>
		</td>

		<?php
		/**
		 * DO_ACTION: yith_wcgc_template_after_logo
		 *
		 * Allow actions after displaying the logo in the gift card template.
		 *
		 * @param object $object the gift card object
		 * @param string $context the template context, email or pdf
		 */
		do_action( 'yith_wcgc_template_after_logo', $object, $context );
		?>

	</tr>

	<tr>
		<td class="ywgc-card-product-name yith-ywgc-rtl-align">
			<?php

			$product = wc_get_product( $product_id );

			$product_name_text = is_object( $product ) && $product instanceof WC_Product_Gift_Card ? $product->get_name() : esc_html__( 'Gift card', 'yith-woocommerce-gift-cards' );

			/**
			 * APPLY_FILTER: yith_wcgc_template_product_name_text
			 *
			 * Filter the gift card product name in the gift card template.
			 *
			 * @param string $product_name_text the product name
			 * @param object $object the gift card object
			 * @param string $context the context. Allowed values: "email" or "pdf"
			 * @param int $product_id the product ID
			 *
			 * @return string
			 */
			echo apply_filters( 'yith_wcgc_template_product_name_text', $product_name_text, $object, $context, $product_id );
			?>
		</td>

		<?php
		/**
		 * APPLY_FILTER: ywgc_display_price_template
		 *
		 * Filter the condition to display the gift card price in the gift card template.
		 *
		 * @param bool true to display it, false to not. Default: true
		 * @param string $formatted_price the formatted gift card price
		 * @param object $object the gift card object
		 * @param string $context the context. Allowed values: "email" or "pdf"
		 *
		 * @return bool
		 */
		if ( apply_filters( 'ywgc_display_price_template', true, $formatted_price, $object, $context ) ) :
			?>

			<td class="ywgc-card-amount yith-ywgc-rtl-align">
				<?php
				/**
				 * APPLY_FILTER: yith_wcgc_template_formatted_price
				 *
				 * Filter the formatted gift card price in the gift card template.
				 *
				 * @param string $formatted_price the formatted gift card price
				 * @param object $object the gift card object
				 * @param string $context the context. Allowed values: "email" or "pdf"
				 *
				 * @return string
				 */
				echo apply_filters( 'yith_wcgc_template_formatted_price', $formatted_price, $object, $context );
				?>

			</td>

		<?php endif; ?>

		<?php
		/**
		 * DO_ACTION: yith_wcgc_template_after_price
		 *
		 * Allow actions after displaying the price in the gift card template.
		 *
		 * @param object $object the gift card object
		 * @param string $context the template context, email or pdf
		 */
		do_action( 'yith_wcgc_template_after_price', $object, $context );
		?>

	</tr>

	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_after_logo_price
	 *
	 * Allow actions after displaying the price and logo in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_after_logo_price', $object, $context );
	?>

	<tr>
		<td colspan="2"> <hr style="color: lightgrey"> </td>
	</tr>

	<?php if ( $message ) : ?>
		<tr>
			<td class="ywgc-message-text" colspan="2"> <?php echo nl2br( str_replace( '\\', '', $message ) ); ?> </td>
		</tr>
		<tr>
			<td><br></td>
		</tr>
	<?php endif; ?>

	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_after_message
	 *
	 * Allow actions after displaying the message in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_after_message', $object, $context );
	?>

		<tr>
			<td colspan="2">
				<?php
				/**
				 * APPLY_FILTER: ywgc_preview_code_title
				 *
				 * Filter the "Gift card code: " text in the gift card template.
				 *
				 * @param string the text
				 *
				 * @return string
				 */
				?>
				<span class="ywgc-card-code-title"><?php echo apply_filters( 'ywgc_preview_code_title', esc_html__( 'Gift card code:', 'yith-woocommerce-gift-cards' ) ); ?></span>
				<br>
				<br>
				<?php
				/**
				 * APPLY_FILTER: ywgc_gift_card_code
				 *
				 * Filter the gift card code displayed in the gift card template.
				 *
				 * @param string $gift_card_code the gift card code
				 * @param object $object the gift card object
				 * @param string $context the context. Allowed values: "email" or "pdf"
				 *
				 * @return string
				 */
				?>
				<span class="ywgc-card-code">  <?php echo apply_filters( 'ywgc_gift_card_code', $gift_card_code, $object, $context ); ?></span>
			</td>
		</tr>


	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_after_code
	 *
	 * Allow actions after displaying the code in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_after_code', $object, $context );
	?>

	<?php if ( get_option( 'ywgc_display_description_template', 'no' ) === 'yes' ) : ?>

		<tr>
			<td colspan="2"> <hr style="color: lightgrey"> </td>
		</tr>

		<?php if ( $context === 'email' ) : ?>
		<tr>
			<td colspan="2" class="ywgc-description-template-email-message" style="text-align: center"><?php echo get_option( 'ywgc_description_template_email_text', esc_html__( 'To use this gift card, you can either enter the code in the gift card field on the cart page or click on the following link to automatically get the discount.', 'yith-woocommerce-gift-cards' ) ); ?></td>
		</tr>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	if ( $object->expiration ) :
		/**
		 * APPLY_FILTER: yith_ywgc_gift_card_email_expiration_message
		 *
		 * Filter the expiration message in the gift card template.
		 *
		 * @param string the expiration message
		 * @param object $object the gift card object
		 * @param string $context the context. Allowed values: "email" or "pdf"
		 *
		 * @return string
		 */
		$expiration_message = apply_filters(
			'yith_ywgc_gift_card_email_expiration_message',
			sprintf( _x( 'This gift card code will be valid until %s', 'gift card expiration date', 'yith-woocommerce-gift-cards' ), date_i18n( $date_format, $expiration_date ) ),
			$object,
			$context
		);
		?>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: center;" class="ywgc-expiration-message"><?php echo $expiration_message; ?></td>
		</tr>
	<?php endif; ?>

	<?php
	/**
	 * DO_ACTION: yith_wcgc_template_after_expiration_date
	 *
	 * Allow actions after displaying the expiration date message in the gift card template.
	 *
	 * @param object $object the gift card object
	 * @param string $context the template context, email or pdf
	 */
	do_action( 'yith_wcgc_template_after_expiration_date', $object, $context );
	?>

</table>
