<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 *  YITH Sales Free Shipping Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Campaigns
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Sales_Free_Shipping_Campaign' ) ) {

	/**
	 * The Free Shipping Campaign
	 */
	class YITH_Sales_Free_Shipping_Campaign extends Abstract_YITH_Sales_Promotion_Campaign {

		/**
		 * The campaign type
		 *
		 * @var array
		 */
		protected $type = 'free-shipping';

		/**
		 * Stores price rule data.
		 *
		 * @var array the common data
		 */
		protected $extra_data = array(
			'free_shipping_rules'     => array(),
			'show_amount_left'        => 'no',
			'title'                   => '',
			'notice_background_color' => '',
		);


		/**
		 * Initialize free shipping campaign.
		 *
		 * @param YITH_Sales_Free_Shipping_Campaign|int $campaign Campaign instance or ID.
		 * @throws Exception The exception.
		 */
		public function __construct( $campaign = 0 ) {
			parent::__construct( $campaign );
			$this->read();
		}

		/**
		 * Get the free shipping rules
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 */
		public function get_free_shipping_rules( $context = 'view' ) {
			return $this->get_prop( 'free_shipping_rules', $context );
		}

		/**
		 * Get if show amount left
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_show_amount_left( $context = 'view' ) {
			return $this->get_prop( 'show_amount_left', $context );
		}

		/**
		 * Get the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_title( $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Get the background color of the notice
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 */
		public function get_notice_background_color( $context = 'view' ) {
			return $this->get_prop( 'notice_background_color', $context );
		}

		/**
		 * Set the free shipping rules
		 *
		 * @param array $free_shipping_rules Free shipping rules.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_free_shipping_rules( $free_shipping_rules ) {
			$this->set_prop( 'free_shipping_rules', $free_shipping_rules );
		}

		/**
		 * Set if show amount left
		 *
		 * @param string $show_amount_left Yes or no.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_show_amount_left( $show_amount_left ) {
			$this->set_prop( 'show_amount_left', $show_amount_left );
		}


		/**
		 * Set content of the notice
		 *
		 * @param string $title Content of  notice.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_title( $title ) {
			$this->set_prop( 'title', $title );
		}


		/**
		 * Set the notice background color.
		 *
		 * @param string $notice_background_color Notice background color.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function set_notice_background_color( $notice_background_color ) {
			$this->set_prop( 'notice_background_color', $notice_background_color );
		}


		/**
		 * Check if the campaign is valid
		 *
		 * @param array $cart_details Contain the information needed to check the rule.
		 *
		 * @return bool
		 */
		public function is_valid( $cart_details ) {
			$rules = $this->get_free_shipping_rules();
			if ( 'always' === $rules['type'] ) {
				return true;
			}
			$handle_rule = $rules['handle'];

			$cart_conditions = $rules['rules'];
			$is_valid        = false;
			foreach ( $cart_conditions as $cart_condition ) {
				$cart_condition_type = $cart_condition['type'];
				switch ( $cart_condition_type ) {
					case 'country_is':
					case 'country_not':
						$current_condition_valid = $this->is_valid_country( $cart_condition['value'], $cart_details['country'], 'country_is' === $cart_condition_type );
						break;
					case 'state_is':
					case 'state_not':
						$current_condition_valid = $this->is_valid_state( $cart_condition['value'], $cart_details['state'], 'state_is' === $cart_condition_type );
						break;
					default:
						$current_condition_valid = $this->is_valid_cart_total( $cart_condition['value'], $cart_details['cart_total'], $cart_condition['condition'] );
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
		 * Check if the condition for the country is valid
		 *
		 * @param array  $countries_to_check The countries to check.
		 * @param string $country            The customer country.
		 * @param bool   $inclusive          The condition check.
		 *
		 * @return bool
		 */
		public function is_valid_country( $countries_to_check, $country, $inclusive = true ) {
			$is_valid = in_array( $country, $countries_to_check, true );

			return $inclusive ? $is_valid : ! $is_valid;
		}

		/**
		 * Check if the condition for the state is valid
		 *
		 * @param array  $states_to_check The states to check.
		 * @param string $state           The customer state.
		 * @param bool   $inclusive       The condition check.
		 *
		 * @return bool
		 */
		public function is_valid_state( $states_to_check, $state, $inclusive = true ) {
			$is_valid = in_array( $state, $states_to_check, true );

			return $inclusive ? $is_valid : ! $is_valid;
		}

		/**
		 * Check if is possible show the banner in the cart page
		 *
		 * @param float $cart_total The cart total.
		 *
		 * @return bool|string
		 */
		public function get_formatted_banner( $cart_total ) {

			if ( 'yes' === $this->get_show_amount_left() ) {
				$banner_text = $this->get_title();
				$rules       = $this->get_free_shipping_rules();

				if ( 'always' === $rules['type'] || false === strpos( $banner_text, '%value%' ) ) {
					return $banner_text;
				} else {
					$total_condition = $this->get_first_cart_total_condition( $rules['rules'] );
					if ( $total_condition ) {
						$remain = $total_condition['value'] - $cart_total;
						if ( $remain > 0 ) {
							return str_replace( '%value%', wc_price( $remain ), $banner_text );
						}
					}
				}
			}

			return false;
		}

		/**
		 * Get if set the first condition on the cart rules
		 *
		 * @param array $cart_conditions The cart conditions.
		 *
		 * @return mixed
		 */
		protected function get_first_cart_total_condition( $cart_conditions ) {
			$total_condition = false;
			foreach ( $cart_conditions as $cart_condition ) {

				if ( 'cart_total' === $cart_condition['type'] && 'at_least' === $cart_condition['condition'] ) {
					$total_condition = $cart_condition;
					break;
				}
			}

			return $total_condition;
		}

	}
}
