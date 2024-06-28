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

?>
<?php if ( 1 != count( $amounts ) ) : ?>
	<h3 class="ywgc_select_amount_title"><?php echo get_option( 'ywgc_select_amount_title', esc_html__( 'Set an amount', 'yith-woocommerce-gift-cards' ) ); ?></h3>
<?php endif; ?>

<?php if ( $amounts ) : ?>

	<?php
	/**
	 * DO_ACTION: yith_gift_cards_template_before_amounts
	 *
	 * Allow to apply changes before the amount selection on the gift card page.
	 *
	 * @param object $product the gift card product
	 */
	do_action( 'yith_gift_cards_template_before_amounts', $product );
	?>
	<?php foreach ( $amounts as $value => $item ) : ?>
		<button class="ywgc-predefined-amount-button ywgc-amount-buttons" value="<?php echo $item['amount']; ?>"
				data-price="<?php echo $item['price']; ?>"
				data-wc-price="<?php echo strip_tags( wc_price( $item['price'] ) ); ?>">
			<?php echo apply_filters( 'yith_gift_card_select_amount_values', $item['title'], $item ); ?>
		</button>

		<input type="hidden" class="ywgc-predefined-amount-button ywgc-amount-buttons" value="<?php echo apply_filters( 'ywgc_amount_selection_hidden_amount', $item['amount'], $product ); ?>"
			   data-price="<?php echo apply_filters( 'ywgc_amount_selection_hidden_price', $item['price'], $product ); ?>"
			   data-wc-price="<?php echo strip_tags( wc_price( apply_filters( 'ywgc_amount_selection_hidden_price', $item['price'], $product ) ) ); ?>" >



	<?php endforeach; ?>
	<?php
endif;

/**
 * DO_ACTION: yith_gift_card_amount_selection_last_option
 *
 * Trigger the last amount in the select amount section, to allow the inclusion of the custom amount.
 *
 * @param object $product the gift card product
 */
do_action( 'yith_gift_card_amount_selection_last_option', $product );

/**
 * DO_ACTION: yith_gift_cards_template_after_amounts
 *
 * Allow to apply changes after the amount selection on the gift card page.
 *
 * @param object $product the gift card product
 */
do_action( 'yith_gift_cards_template_after_amounts', $product );
