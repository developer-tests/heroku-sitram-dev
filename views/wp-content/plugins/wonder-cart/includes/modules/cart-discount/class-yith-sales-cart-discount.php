<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Cart Discount Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Cart_Discount_Campaign' ) ) {
	/**
	 * The Cart Discount Campaign
	 */
	class YITH_Sales_Cart_Discount_Campaign extends Abstract_YITH_Sales_Campaign {


		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'cart-discount';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'trigger_product'        => array(),
			'cart_rules'             => array(),
			'discount_to_apply'      => array(),
			'title'                  => '',
			'label_background_color' => '',
			'label_text_color'       => '',
		);


		/**
		 * Initialize cart discount campaign.
		 *
		 * @param YITH_Sales_Cart_Discount_Campaign|int $campaign Campaign instance or ID.
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}


		/**
		 * Get the products where apply offer
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_trigger_product( $context = 'view' ) {
			return $this->get_prop( 'trigger_product', $context );
		}

		/**
		 * Get the cart rules
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function get_cart_rules( $context = 'view' ) {
			return $this->get_prop( 'cart_rules', $context );
		}

		/**
		 * Get the discount to apply
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_discount_to_apply( $context = 'view' ) {
			return $this->get_prop( 'discount_to_apply', $context );
		}

		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get the label background color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_label_background_color( $context = 'view' ) {
			return $this->get_prop( 'label_background_color', $context );
		}


		/**
		 * Get the label text color
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_label_text_color( $context = 'view' ) {
			return $this->get_prop( 'label_text_color', $context );
		}


		/**
		 * Get the trigger products
		 *
		 * @param array $trigger_product Array type, ids.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_trigger_product( $trigger_product ) {
			$this->set_prop( 'trigger_product', $trigger_product );
		}


		/**
		 * Get the cart rules
		 *
		 * @param array $cart_rules Cart rules.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_cart_rules( $cart_rules ) {
			$this->set_prop( 'cart_rules', $cart_rules );
		}

		/**
		 * Set the discount to apply
		 *
		 * @param array $discount_to_apply Discount to apply.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_discount_to_apply( $discount_to_apply ) {
			$this->set_prop( 'discount_to_apply', $discount_to_apply );
		}

		/**
		 * Set title
		 *
		 * @param string $title Set the title.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_title( $title ) {
			$this->set_prop( 'title', $title );
		}

		/**
		 * Set the background color of the label.
		 *
		 * @param string $label_background_color Set the label background color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_label_background_color( $label_background_color ) {
			$this->set_prop( 'label_background_color', $label_background_color );
		}


		/**
		 * Set the text color of the label.
		 *
		 * @param string $label_text_color Set the label text color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_label_text_color( $label_text_color ) {
			$this->set_prop( 'label_text_color', $label_text_color );
		}

		/**
		 * Check if the campaign is valid
		 *
		 * @param array $extra_args The args to check.
		 *
		 * @return bool
		 */
		public function is_valid( $extra_args ) {

			$is_valid = false;
			$rules    = $this->get_cart_rules();

			if ( 'always' === $rules['type'] ) {
				return true;
			}
			$handle_rule     = $rules['handle'];
			$cart_conditions = $rules['rules'];

			foreach ( $cart_conditions as $cart_condition ) {
				$cart_condition_type = $cart_condition['type'];
				switch ( $cart_condition_type ) {
					case 'num_order':
						$current_condition_valid = $this->is_valid_number_of_order( $cart_condition['value'], $extra_args['num_of_orders'], $cart_condition['condition'] );
						break;
					case 'past_expense':
						$current_condition_valid = $this->is_valid_total_amount_spent( $cart_condition['value'], $extra_args['past_expense'], $cart_condition['condition'] );
						break;
					default:
						$current_condition_valid = $this->is_valid_cart_total( $cart_condition['value'], $extra_args['cart_total'], $cart_condition['condition'] );
						break;
				}
				if ( ! $current_condition_valid && 'all' === $handle_rule ) {
					$is_valid = false;
					break;
				} else {
					$is_valid = $is_valid || $current_condition_valid;
				}
			}

			return $is_valid;
		}

		/**
		 * Check if the condition on cart total is valid
		 *
		 * @param float  $total_to_check The total to check.
		 * @param float  $cart_total     The cart total.
		 * @param string $condition      The condition.
		 *
		 * @return bool
		 */
		public function is_valid_cart_total( $total_to_check, $cart_total, $condition ) {
			switch ( $condition ) {
				case 'at_least':
					$is_valid = $cart_total >= $total_to_check;
					break;
				case 'at_most':
					$is_valid = $cart_total < $total_to_check;
					break;
				default:
					$is_valid = floatval( $total_to_check ) === floatval( $cart_total );
					break;
			}

			return $is_valid;
		}

		/**
		 * Check if the condition on total amount spent is valid
		 *
		 * @param float  $total_to_check The total to check.
		 * @param float  $amount_total   The total amount spent.
		 * @param string $condition      The condition.
		 *
		 * @return bool
		 */
		public function is_valid_total_amount_spent( $total_to_check, $amount_total, $condition ) {

			if ( $total_to_check < 0 ) {
				return true;
			}

			switch ( $condition ) {
				case 'at_least':
					$is_valid = $amount_total >= $total_to_check;
					break;
				case 'at_most':
					$is_valid = $amount_total < $total_to_check;
					break;
				default:
					$is_valid = floatval( $total_to_check ) === floatval( $amount_total );
					break;
			}

			return $is_valid;
		}

		/**
		 * Check if the condition on number of order is valid
		 *
		 * @param float  $number_of_order_to_check The number of order to check.
		 * @param float  $number_of_order          The number of order.
		 * @param string $condition                The condition.
		 *
		 * @return bool
		 */
		public function is_valid_number_of_order( $number_of_order_to_check, $number_of_order, $condition ) {
			switch ( $condition ) {
				case 'at_least':
					$is_valid = $number_of_order >= $number_of_order_to_check;
					break;
				case 'at_most':
					$is_valid = $number_of_order < $number_of_order_to_check;
					break;
				default:
					$is_valid = intval( $number_of_order_to_check ) === intval( $number_of_order );
					break;
			}

			return $is_valid;
		}

	}
}
