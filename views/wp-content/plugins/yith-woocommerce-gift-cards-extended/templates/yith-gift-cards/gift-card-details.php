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

global $product;

$is_gift_this_product = ! ( $product instanceof WC_Product_Gift_Card );

?>
<h3 class="ywgc_delivery_info_title"><?php echo get_option( 'ywgc_delivery_info_title', esc_html__( 'Delivery info', 'yith-woocommerce-gift-cards' ) ); ?></h3>

<div class="gift-card-content-editor step-content clearfix">

	<?php if ( 'yes' === get_option( 'ywgc_enable_send_later', 'no' ) ) : ?>
		<div class="ywgc-postdated">
			<?php
			/**
			 * APPLY_FILTER: ywgc_delivery_date_label
			 *
			 * Filter the "Delivery date:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			/**
			 * APPLY_FILTER: ywgc_choose_delivery_date_placeholder
			 *
			 * Filter the "Delivery date" field placeholder in the gift card form in the product page.
			 *
			 * @param string the placeholder
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-delivery-date"><?php echo apply_filters( 'ywgc_delivery_date_label', esc_html__( 'Delivery date: ', 'yith-woocommerce-gift-cards' ) ); ?></label>
			<input type="text" id="ywgc-delivery-date" name="ywgc-delivery-date" placeholder="<?php echo apply_filters( 'ywgc_choose_delivery_date_placeholder', sprintf( esc_html__( 'Now', 'yith-woocommerce-gift-cards' ) ) ); ?>" class="datepicker" >
		</div>
	<?php endif; ?>

	<h5 class="ywgc_recipient_info_title">
		<?php echo get_option( 'ywgc_recipient_info_title', esc_html__( 'RECIPIENT INFO', 'yith-woocommerce-gift-cards' ) ); ?>
	</h5>


	<div class="ywgc-single-recipient">
		<div class="ywgc-recipient-name clearfix">
			<?php
			/**
			 * APPLY_FILTER: ywgc_recipient_name_label
			 *
			 * Filter the recipient "Name:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-recipient-name"><?php echo apply_filters( 'ywgc_recipient_name_label', esc_html__( 'Name: ', 'yith-woocommerce-gift-cards' ) ); ?></label>
			<input type="text" id="ywgc-recipient-name" name="ywgc-recipient-name[]" placeholder="<?php echo esc_html__( "Enter the recipient's name", 'yith-woocommerce-gift-cards' ); ?>" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?> class="yith_wc_gift_card_input_recipient_details">
		</div>

		<div class="ywgc-recipient-email clearfix">
			<?php
			/**
			 * APPLY_FILTER: ywgc_recipient_email_label
			 *
			 * Filter the "Email:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-recipient-email"><?php echo apply_filters( 'ywgc_recipient_email_label', esc_html__( 'Email: ', 'yith-woocommerce-gift-cards' ) ); ?></label>
			<input type="email" id="ywgc-recipient-email" name="ywgc-recipient-email[]" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?>
				   class="ywgc-recipient yith_wc_gift_card_input_recipient_details" placeholder="<?php echo esc_html__( "Enter the recipient's email address", 'yith-woocommerce-gift-cards' ); ?>"/>
		</div>
	</div>

	<?php if ( ! $mandatory_recipient ) : ?>
		<?php
		/**
		 * APPLY_FILTER: ywgc_empty_recipient_note
		 *
		 * Filter the "If empty, will be sent to your email address" text in the gift card form in the product page.
		 *
		 * @param string the text
		 *
		 * @return string
		 */
		?>
		<span class="ywgc-empty-recipient-note"><?php echo apply_filters( 'ywgc_empty_recipient_note', esc_html__( 'If empty, will be sent to your email address', 'yith-woocommerce-gift-cards' ) ); ?></span>
	<?php endif; ?>

	<?php if ( 'yes' == get_option( 'ywgc_ask_sender_name', 'yes' ) ) : ?>

		<h5 class="ywgc-sender-info-title">
			<?php
			echo get_option( 'ywgc_sender_info_title', esc_html__( 'YOUR INFO', 'yith-woocommerce-gift-cards' ) );
			?>
		</h5>

		<div class="ywgc-sender-name clearfix">
			<?php
			/**
			 * APPLY_FILTER: ywgc_sender_name_label
			 *
			 * Filter the sender "Name:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-sender-name"><?php echo apply_filters( 'ywgc_sender_name_label', esc_html__( 'Name: ', 'yith-woocommerce-gift-cards' ) ); ?></label>
			<input type="text" name="ywgc-sender-name" id="ywgc-sender-name" value="<?php echo apply_filters( 'ywgc_sender_name_value', '' ); ?>"
				   placeholder="<?php echo esc_html__( 'Enter your name', 'yith-woocommerce-gift-cards' ); ?>">
		</div>
	<?php endif; ?>
	<div class="ywgc-message clearfix">
		<?php
		/**
		 * APPLY_FILTER: ywgc_edit_message_label
		 *
		 * Filter the "Message:" label in the gift card form in the product page.
		 *
		 * @param string the label text
		 *
		 * @return string
		 */
		?>
		<label for="ywgc-edit-message"><?php echo apply_filters( 'ywgc_edit_message_label', esc_html__( 'Message: ', 'yith-woocommerce-gift-cards' ) ); ?></label>
		<textarea id="ywgc-edit-message" name="ywgc-edit-message" rows="5" placeholder="<?php echo  get_option( 'ywgc_sender_message_placeholder', esc_html__( 'Enter a message for the recipient', 'yith-woocommerce-gift-cards' ) ); ?>" ></textarea>
	</div>


</div>
