<?php
/**
 * Draft order trait.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Booking\Traits
 */

/**
 * Draft order trait.
 *
 * @internal
 * @since 5.8.1
 */
trait YITH_WCBK_Draft_Order_Trait {
	/**
	 * Gets draft order data from the customer session.
	 *
	 * @return int
	 */
	protected function get_draft_order_id() {
		if ( ! wc()->session ) {
			wc()->initialize_session();
		}

		return absint( wc()->session->get( 'store_api_draft_order', 0 ) );
	}

	/**
	 * Uses the draft order ID to return an order object, if valid.
	 *
	 * @return WC_Order|null;
	 */
	protected function get_draft_order() {
		$draft_order_id = $this->get_draft_order_id();
		$draft_order    = $draft_order_id ? wc_get_order( $draft_order_id ) : false;

		return $this->is_valid_draft_order( $draft_order ) ? $draft_order : null;
	}

	/**
	 * Whether the passed argument is a draft order or an order that is
	 * pending/failed and the cart hasn't changed.
	 *
	 * @param WC_Order $order Order object to check.
	 *
	 * @return boolean Whether the order is valid as a draft order.
	 */
	protected function is_valid_draft_order( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		// Draft orders are okay.
		if ( $order->has_status( 'checkout-draft' ) ) {
			return true;
		}

		// Pending and failed orders can be retried if the cart hasn't changed.
		if ( $order->needs_payment() && $order->has_cart_hash( wc()->cart->get_cart_hash() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets draft order data from the customer session.
	 *
	 * @return int
	 */
	protected function get_resuming_order_id() {
		if ( ! wc()->session ) {
			wc()->initialize_session();
		}

		return absint( wc()->session->get( 'order_awaiting_payment', 0 ) );
	}

	/**
	 * Uses the draft order ID to return an order object, if valid.
	 *
	 * @return WC_Order|null;
	 */
	protected function get_resuming_order() {
		$draft_order_id = $this->get_resuming_order_id();
		$draft_order    = $draft_order_id ? wc_get_order( $draft_order_id ) : false;

		return $this->is_valid_resuming_order( $draft_order ) ? $draft_order : null;
	}

	/**
	 * Whether the passed argument is a draft order or an order that is
	 * pending/failed and the cart hasn't changed.
	 *
	 * @param WC_Order $order Order object to check.
	 *
	 * @return boolean Whether the order is valid as a draft order.
	 */
	protected function is_valid_resuming_order( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		// Draft orders are okay.
		if ( $order->has_status( array( 'pending', 'failed' ) ) ) {
			return true;
		}

		// Pending and failed orders can be retried if the cart hasn't changed.
		if ( $order->needs_payment() && $order->has_cart_hash( wc()->cart->get_cart_hash() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a draft order or a resuming order.
	 *
	 * @return WC_Order|null;
	 */
	protected function get_draft_or_resuming_order() {
		$draft_order = $this->get_draft_order();

		return ! ! $draft_order ? $draft_order : $this->get_resuming_order();
	}
}
