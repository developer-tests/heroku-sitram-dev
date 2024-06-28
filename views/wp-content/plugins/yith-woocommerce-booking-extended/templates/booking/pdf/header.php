<?php
/**
 * Booking PDF Template - Header
 *
 * @var YITH_WCBK_Booking $booking  The booking.
 * @var bool              $is_admin Is admin flag.
 *
 * @package YITH\Booking\Templates
 */

defined( 'YITH_WCBK' ) || exit;

/**
 * APPLY_FILTERS: yith_wcbk_booking_pdf_logo_url
 *
 * Filters the logo URL in PDF.
 *
 * @param string $logo_url The URL.
 *
 * @return string
 */
$logo         = apply_filters( 'yith_wcbk_booking_pdf_logo_url', '' );
$booking_link = $is_admin ? get_edit_post_link( $booking->get_id() ) : $booking->get_view_booking_url();
?>
<div class="logo">
	<img src="<?php echo esc_url( $logo ); ?>"/>
</div>
<div class="clear"></div>
<div class="booking-title">
	<h2>
		<a href="<?php echo esc_url( $booking_link ); ?>">
			<?php echo esc_html( $booking->get_name() ); ?>
		</a>
	</h2>
</div>
