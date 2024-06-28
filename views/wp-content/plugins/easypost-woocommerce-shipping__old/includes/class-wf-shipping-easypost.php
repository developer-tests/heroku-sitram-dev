<?php

/**
 * WF_USPS_Easypost class.
 *
 * @extends WC_Shipping_Method
 */
class WF_Easypost extends WC_Shipping_Method {
	private $found_rates;
	private $carrier_list;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = WF_EASYPOST_ID;
		$this->method_title       = esc_attr( 'EasyPost', 'wf-easypost' );
		$this->method_description = wp_kses_post( 'The <strong>EasyPost.com USPS</strong> plugin obtains rates dynamically from the EasyPost.com API during cart/checkout.', 'wf-easypost' );
		$this->services           = include 'data-wf-services.php';
		$this->flat_rate_boxes    = include 'data-wf-flat-rate-boxes.php';
		$this->set_carrier_list();
		$this->init();
		/**
		 * Check whether multi vendor is active or not.
		 * 
		 * @since 1.1.0
		 */
		$this->vendor_check = in_array( 'multi-vendor-add-on-for-thirdparty-shipping/multi-vendor-add-on-for-thirdparty-shipping.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? true : false;
	}

	/**
	 * Init function.
	 *
	 * @return void
	 */
	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		// Define user set variables
		$this->enable_standard_services = true;
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );

			add_action( 'woocommerce_checkout_fields', array( $this, 'wf_easypost_custom_override_checkout_fields' ) );
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'elex_add_easypost_insurance' ), 10 );
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'handling_fees' ), 10, 1 );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'elex_inavalidating_woocmmerce_key' ), 99 );

	}
	public function elex_inavalidating_woocmmerce_key() {
		   global $woocommerce;
		if ( $woocommerce->session->get( 'easypost_has_latest_rates' ) ) {
			return;
		}
			$packages = $woocommerce->cart->get_shipping_packages();
		foreach ( $packages as $package_key => $value ) {
				$shipping_session = 'shipping_for_package_' . $package_key;
				unset( $woocommerce->session->$shipping_session );

		}
			$woocommerce->cart->calculate_totals();
	}
	public function handling_fees( $cart ) {
		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$handling_fee   = isset( $rates_settings['handling_fee'] ) ? $rates_settings['handling_fee'] : '';
		if ( $handling_fee ) {
			$cart->add_fee( esc_attr( 'Handling Fee', 'woocommerce' ), $rates_settings['handling_fee'], false );
		}
		return $cart;
	}
	// function to show insurance in the checkout page.
	public function elex_add_easypost_insurance() {
		global $woocommerce;
		$label_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$packing_settings = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		if ( isset( $label_settings['insurance'] ) && ! empty( $label_settings['insurance'] ) && ( 'optional' === $label_settings['insurance'] || 'yes' === $label_settings['insurance'] ) ) {

			if ( 'weight_based_packing' === $packing_settings['packing_method'] && 'pack_simple' === $packing_settings['weight_packing_process'] ) {
				$items        = $woocommerce->cart->get_cart();
				$total_weight = 0;
				$total_price  = 0;
				$amount       = 0;
				foreach ( $items as $key ) {
					$total_weight += $key['data']->get_weight() * $key['quantity'];
					$total_price  += $key['line_subtotal'];
				}
				$max_weight     = $packing_settings['box_max_weight'];
				$per_item_price = $total_price / $total_weight;
				do {
					$pack_weight  = ( $total_weight / $max_weight ) > 1 ? $max_weight : $total_weight;
					$boxes[]      = array(
						'weight' => $pack_weight,
					);
					$total_weight = $total_weight - $pack_weight;
				} while ( $total_weight );
				foreach ( $boxes as $key => $value ) {
					$boxes[ $key ]['price'] = $value['weight'] * $per_item_price;
					if ( $boxes[ $key ]['price'] < 100 ) {
						$amount++;
					} else {
						$amount += ( $boxes[ $key ]['price'] ) / 100;
					}
				}
			} else {
				$package = $this->elex_easypost_get_package();
				foreach ( $package as $key => $val ) {
					$package_requests = $this->get_package_requests( $package[ $key ] );
					$amount           = $this->elex_easypost_add_insurance_amount( $package_requests );
				}
			}
			$chosen_methods = $woocommerce->session->get( 'chosen_shipping_methods' );
			foreach ( $chosen_methods as $key => $val ) {
				$selected_shipping_service = explode( ':', $chosen_methods[ $key ] );
				if ( 'wf_easypost_id' === $selected_shipping_service[0] ) {
					$woocommerce->cart->add_fee( esc_attr( 'Insurance', 'woocommerce' ), $amount, false );
				}
			}
		}
	}

	// function to calculate shipping insurance.
	public function elex_easypost_add_insurance_amount( $package_requests ) {
		$packing_settings = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$insurance_amount = 0;
		foreach ( $package_requests as $key => $value ) {
			$package_total = $value['request']['Rate']['Amount'];
			if ( $package_total <= 100 ) {
				
				$insurance_amount = $insurance_amount + 0.50;
				
			} else {
				
				$insurance_amount = $insurance_amount + ( $package_total * 0.005 );
				
			}
		}
		return $insurance_amount;
	}

	/**
	 * Function to get shipping package.
	 */
	public function elex_easypost_get_package() {
		global $woocommerce;
		/**
		 * Take car shipping packages.
		 * 
		 * @since 1.0.0
		 */
		return apply_filters(
			'woocommerce_cart_shipping_packages',
			array(
				array(
					'contents'        => $woocommerce->cart->get_cart(),
					'contents_cost'   => array_sum( wp_list_pluck( $woocommerce->cart->get_cart(), 'line_total' ) ),
					'applied_coupons' => $woocommerce->cart->applied_coupons,
					'user'            => array(
						'ID' => get_current_user_id(),
					),
					'destination'     => array(
						'country'   => $woocommerce->customer->get_shipping_country(),
						'state'     => $woocommerce->customer->get_shipping_state(),
						'postcode'  => $woocommerce->customer->get_shipping_postcode(),
						'city'      => $woocommerce->customer->get_shipping_city(),
						'address'   => $woocommerce->customer->get_shipping_address(),
						'address_1' => $woocommerce->customer->get_shipping_address(),
						'address_2' => $woocommerce->customer->get_shipping_address_2(),
					),
					'cart_subtotal'   => $woocommerce->cart->subtotal,
				),
			)
		);
	}
	/**
	 * Function to add the custom field.
	 * EasyPost Insurance Field.
	 */
	public function wf_easypost_custom_override_checkout_fields( $fields ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		if ( 'optional' === $label_settings['insurance'] ) {
			$fields['billing']['easypost_insurance'] = array(
				'label'    => 'Enable EasyPost Shipping Insurance',
				'type'     => 'checkbox',
				'required' => 0,
				'default'  => false,
				'class'    => array( 'update_totals_on_change' ),
			);
		}

		return $fields;
	}
	/**
	 * To get Insurance From Checkout Page.
	 */
	public function wf_easypost_insurance_field( $order_id ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$order          = new WC_Order( $order_id );
		if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce-process-checkout-nonce'] ), 'woocommerce_save_data' ) ) { // Input var okay.
			if ( isset( $_POST['easypost_insurance'] ) ) {
				$easy_post_insurance = sanitize_text_field( $_POST['easypost_insurance'] );
				if ( ! empty( $easy_post_insurance ) ) {
					add_post_meta( $order_id, 'wf_easypost_insurance', '1' );
				}
			} else {
				if ( 'yes' === $label_settings['insurance'] ) {
					add_post_meta( $order_id, 'wf_easypost_insurance', '1' );
				} else {
					add_post_meta( $order_id, 'wf_easypost_insurance', '0' );
				}
			}
		}
	}
	/**
	 * Function to add the Est delivery time.
	 */
	public function wf_easypost_add_delivery_time( $label, $method ) {
		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		if ( 'yes' === $rates_settings['est_delivery'] ) {
			if ( ! is_object( $method ) ) {
				return $label;
			}
			$est_delivery = $method->get_meta_data();
			if ( isset( $rates_settings['cut_off_time'] ) && ! empty( $rates_settings['cut_off_time'] ) ) {
				$cut_off_time = explode( ':', $rates_settings['cut_off_time'], 2 );
			}
			$h_m_d                 = gmdate( 'H i D A', current_time( 'timestamp' ) );
			$h_m_d                 = explode( ' ', $h_m_d );
			$hour                  = $h_m_d[0];
			$min                   = $h_m_d[1];
			$day                   = $h_m_d[2];
			$date_added            = 0;
			$est_delivery_modified = true;
			if ( ! empty( $rates_settings['working_days'] ) && ! in_array( $day, $rates_settings['working_days'] ) ) {
				$working_days = $rates_settings['working_days'];
				while ( ! in_array( $day, $working_days ) ) {
					$date_added            = ++$date_added;
					$add                   = current_time( 'timestamp' ) + ( 86400 * $date_added );
					$day                   = gmdate( 'D', $add );
					$est_delivery_modified = false;
				}
			}

			if ( $est_delivery_modified && isset( $rates_settings['cut_off_time'] ) && ! empty( $rates_settings['cut_off_time'] ) ) {
				if ( $hour > $cut_off_time[0] ) {
					 $date_added = ++$date_added;
					 $add        = current_time( 'timestamp' ) + ( 86400 * $date_added );
					 $day        = gmdate( 'D', $add );
					if ( ! empty( $rates_settings['working_days'] ) ) {

						while ( ! in_array( $day, $rates_settings['working_days'] ) ) {
							$date_added = ++$date_added;
							$add        = current_time( 'timestamp' ) + ( 86400 * $date_added );
							$day        = gmdate( 'D', $add );
						}
					}
				} elseif ( $hour === $cut_off_time[0] ) {
					if ( $min > $cut_off_time[1] ) {
						$date_added = ++$date_added;
						$add        = current_time( 'timestamp' ) + ( 86400 * $date_added );
						$day        = gmdate( 'D', $add );
						if ( ! empty( $rates_settings['working_days'] ) ) {
							while ( ! in_array( $day, $rates_settings['working_days'] ) ) {
								$date_added = ++$date_added;
								$add        = current_time( 'timestamp' ) + ( 86400 * $date_added );
								$day        = gmdate( 'D', $add );
							}
						}
					}
				}
			}
			if ( isset( $est_delivery['easypost_delivery_date'] ) && ! empty( $est_delivery['easypost_delivery_date'] ) ) {
				$date              = gmdate( 'F j, Y ', current_time( 'timestamp' ) + ( 86400 * ( $est_delivery['easypost_delivery_date'] + $date_added ) ) );
				$est_delivery_html = '<br /><small>' . __( ' Est delivery: ', 'wf-easypost' ) . $date . '.</small>';
				/**
				 * To get the est. delivery dates.
				 * 
				 * @since 1.0.0
				 */
				$est_delivery_html = apply_filters( 'wf_easypost_estimated_delivery', $est_delivery_html, $est_delivery );
				$label            .= $est_delivery_html;
			} elseif ( isset( $est_delivery['easypost_delivery_time'] ) && ! empty( $est_delivery['easypost_delivery_time'] ) ) {
				$date              = gmdate( 'F j, Y ', current_time( 'timestamp' ) + ( 86400 * ( $est_delivery['easypost_delivery_time'] + $date_added ) ) );
				$est_delivery_html = '<br /><small>' . __( ' Est delivery: ', 'wf-easypost' ) . $date . '.</small>';
				/**
				 * To get the est. delivery dates.
				 * 
				 * @since 1.0.0
				 */
				$est_delivery_html = apply_filters( 'wf_easypost_estimated_delivery', $est_delivery_html, $est_delivery );
				$label            .= $est_delivery_html;
			}
		}
		return $label;
	}

	private function set_carrier_list() {
		global $woocommerce;
		foreach ( array_keys( $this->services ) as $key => $carrier ) {
			$carrier_list[ $carrier ] = $carrier;
		}
		$this->carrier_list = $carrier_list;
	}


	/**
	 * Environment_check function.
	 *
	 * @return void
	 */
	private function environment_check() {
		global $woocommerce;
		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$enabled          = isset( $general_settings['enabled'] ) ? $general_settings['enabled'] : 'no';
		$api_key          = isset( $general_settings['api_key'] ) ? $general_settings['api_key'] : WF_USPS_EASYPOST_ACCESS_KEY;
		$api_test_key     = isset( $general_settings['api_test_key'] ) ? $general_settings['api_test_key'] : WF_USPS_EASYPOST_ACCESS_KEY;
		$zip              = isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '';
		$admin_page       = version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ? 'wc-settings' : 'woocommerce_settings';

		if ( ! $zip && 'yes' === $enabled ) {
			echo '<div class="error">
                <p>' . esc_attr( 'EasyPost.com is enabled, but the zip code has not been set.', 'wf-easypost' ) . '</p>
            </div>';
		}

		$error_message = '';

		// Check for Easypost.com APIKEY
		if ( ! $api_key && ! $api_test_key && 'yes' === $enabled ) {
			$error_message .= '<p>' . esc_attr( 'EasyPost.com is enabled, but the EasyPost.com API KEY has not been set.', 'wf-easypost' ) . '</p>';
		}

		if ( ! '' === $error_message ) {
			echo '<div class="error">';
			echo wp_kses_post( $error_message );
			echo '</div>';
		}
	}

	/**
	 * Admin_options function.
	 *
	 * @return void
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	/**
	 * Generate_services_html function.
	 */
	public function generate_services_html() {
		ob_start();
		include 'html-wf-services.php';
		return ob_get_clean();
	}

	/**
	 * Generate_box_packing_html function.
	 */
	public function generate_box_packing_html() {
		ob_start();
		$this->init();
		include 'html-wf-box-packing.php';
		return ob_get_clean();
	}
	/**
	 * Genarate_weight_based_packing_html function.
	 */

	/**
	 * Validate_box_packing_field function.
	 *
	 * @param mixed $key
	 * @return void
	 */

	public function generate_multipleaddresses_html() {
		ob_start();
		include ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK_PATH . 'includes/data-addon-settings.php';
		return ob_get_clean();

	}
	public function validate_multipleaddresses_field( $key ) {
		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
		$total_count             = isset( $_POST['elex_multiple_warehouse_custom_address_title'] ) ? count( $_POST['elex_multiple_warehouse_custom_address_title'] ) : 0;
		$multiwarehouse_settings = get_option( 'woocommerce_WF_EASYPOST_ID_multiple_warehouse_settings', null );
		$warehouse_address       = array();
		$i                       = 0;

		while ( $total_count > count( $warehouse_address ) ) {
			if ( isset( $_POST['elex_multiple_warehouse_custom_address_title'][ $i ] ) ) {
				$warehouse_address [ $i ]['address_title']        = isset( $_POST['elex_multiple_warehouse_custom_address_title'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_title'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin_name']          = isset( $_POST['elex_multiple_warehouse_custom_address_origin_name'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin_name'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin_city']          = isset( $_POST['elex_multiple_warehouse_custom_address_origin_suburb'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin_suburb'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin_state']         = isset( $_POST['elex_multiple_warehouse_custom_address_origin_state'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin_state'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin_line_1']        = isset( $_POST['elex_multiple_warehouse_custom_address_origin_line_1'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin_line_1'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin_line_2']        = isset( $_POST['elex_multiple_warehouse_custom_address_origin_line_2'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin_line_2'][ $i ] ) : '';
				$warehouse_address [ $i ]['country']              = isset( $_POST['elex_ep_country_field'][ $i ] ) ? sanitize_text_field( $_POST['elex_ep_country_field'][ $i ] ) : '';
				$warehouse_address [ $i ]['shipper_phone_number'] = isset( $_POST['elex_multiple_warehouse_custom_address_shipper_phone_number'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_shipper_phone_number'][ $i ] ) : '';
				$warehouse_address [ $i ]['shipper_email']        = isset( $_POST['elex_multiple_warehouse_custom_address_shipper_email'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_shipper_email'][ $i ] ) : '';
				$warehouse_address [ $i ]['origin']               = isset( $_POST['elex_multiple_warehouse_custom_address_origin'][ $i ] ) ? sanitize_text_field( $_POST['elex_multiple_warehouse_custom_address_origin'][ $i ] ) : '';
			}

			$i++;
		}

		$multiwarehouse_settings['custom_shipper_address'] = array_values( $warehouse_address );
		update_option( 'woocommerce_wf_multi_warehouse_settings', $multiwarehouse_settings['custom_shipper_address'] );
	}

	public function validate_box_packing_field( $key ) {
		$boxes = array();
		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
	
		if ( isset( $_POST['boxes_outer_length'] ) ) {
			$boxes_name         = isset( $_POST['boxes_name'] ) ? map_deep( wp_unslash( $_POST['boxes_name'] ), 'sanitize_text_field' ) : array();
			$boxes_outer_length = isset( $_POST['boxes_outer_length'] ) ? map_deep( wp_unslash( $_POST['boxes_outer_length'] ), 'sanitize_text_field' ) : array();
			$boxes_outer_width  = isset( $_POST['boxes_outer_width'] ) ? map_deep( wp_unslash( $_POST['boxes_outer_width'] ), 'sanitize_text_field' ) : array();
			$boxes_outer_height = isset( $_POST['boxes_outer_height'] ) ? map_deep( wp_unslash( $_POST['boxes_outer_height'] ), 'sanitize_text_field' ) : array();
			$boxes_inner_length = isset( $_POST['boxes_inner_length'] ) ? map_deep( wp_unslash( $_POST['boxes_inner_length'] ), 'sanitize_text_field' ) : array();
			$boxes_inner_width  = isset( $_POST['boxes_inner_width'] ) ? map_deep( wp_unslash( $_POST['boxes_inner_width'] ), 'sanitize_text_field' ) : array();
			$boxes_inner_height = isset( $_POST['boxes_inner_height'] ) ? map_deep( wp_unslash( $_POST['boxes_inner_height'] ), 'sanitize_text_field' ) : array();
			$boxes_box_weight   = isset( $_POST['boxes_box_weight'] ) ? map_deep( wp_unslash( $_POST['boxes_box_weight'] ), 'sanitize_text_field' ) : array();
			$boxes_max_weight   = isset( $_POST['boxes_max_weight'] ) ? map_deep( wp_unslash( $_POST['boxes_max_weight'] ), 'sanitize_text_field' ) : array();
			$boxes_is_letter    = isset( $_POST['boxes_is_letter'] ) ? map_deep( wp_unslash( $_POST['boxes_is_letter'] ), 'sanitize_text_field' ) : array();
			
			for ( $i = 0; $i < count( $boxes_outer_length ); $i ++ ) {

				if ( $boxes_outer_length[ $i ] && $boxes_outer_width[ $i ] && $boxes_outer_height[ $i ] && $boxes_inner_length[ $i ] && $boxes_inner_width[ $i ] && $boxes_inner_height[ $i ] ) {
					$boxes[] = array(
						'name'         => wc_clean( $boxes_name[ $i ] ),
						'outer_length' => floatval( $boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $boxes_max_weight[ $i ] ),
						'is_letter'    => isset( $boxes_is_letter[ $i ] ) ? true : false,
					);
				}
			}
		}
		return $boxes;
	}

	/**
	 * Validate_services_field function.
	 *
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field( $key ) {
		$services = array();
		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
		$posted_services = isset( $_POST['easypost_service'] ) ? map_deep( wp_unslash( $_POST['easypost_service'] ), 'sanitize_text_field' ) : array();
		foreach ( $posted_services as $code => $settings ) {

			foreach ( $this->services[ $code ]['services'] as $key => $name ) {

				$services[ $code ][ $key ]['enabled']            = isset( $settings[ $key ]['enabled'] ) ? true : false;
				$services[ $code ][ $key ]['adjustment']         = wc_clean( $settings[ $key ]['adjustment'] );
				$services[ $code ][ $key ]['adjustment_percent'] = wc_clean( $settings[ $key ]['adjustment_percent'] );
				$services[ $code ][ $key ]['name']               = wc_clean( $settings[ $key ]['name'] );
			}
		}

		return $services;
	}

	/**
	 * Clear_transients function.
	 *
	 * @return void
	 */
	public function clear_transients() {
		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_easypost_quote_%') OR `option_name` LIKE ('_transient_timeout_easypost_quote_%')" );
	}

	public function generate_activate_box_html() {
		ob_start();
		$plugin_name = 'easypost';
		include 'wf_api_manager/html/html-wf-activation-window.php';
		return ob_get_clean();
	}


	public function generate_easypost_tabs_html() {
			$current_tab = ( ! empty( $_GET['subtab'] ) ) ? esc_attr( sanitize_text_field( $_GET['subtab'] ) ) : 'general';
		if ( 'licence' === $current_tab ) {
			wc_enqueue_js(
				"(function($){
					$('.woocommerce-save-button').hide();
					})(jQuery);"
			);
		}

				echo '
                <div class="wrap">
				
                    <style>
                    .wrap {
                                min-height: 800px;
                            }
                    a.nav-tab{
                                cursor: default;
                    }
                    </style>
                    <hr class="wp-header-end">';
					$tabs = array(
						'general' => esc_attr( 'General', 'wf-easypost' ),
						'rates'   => esc_attr( 'Rates & Services', 'wf-easypost' ),
						'labels'  => esc_attr( 'Label Generation', 'wf-easypost' ),
						'packing' => esc_attr( 'Packaging', 'wf-easypost' ),
						'licence' => esc_attr( 'Licence', 'wf-easypost' ),
					);
					/**
					 * To get the current tab of settings.
					 * 
					 * @since 2.1.0
					 */
					$tabs = apply_filters( 'elex_shipping_easypost_settings_tabs', $tabs );
					$html = '<h2 class="nav-tab-wrapper">';

					foreach ( $tabs as $stab => $name ) {
						$class = ( $stab === $current_tab ) ? 'nav-tab-active' : '';
						$style = ( $stab === $current_tab ) ? 'border-bottom: 1px solid transparent !important;' : '';
						$html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class . '" href="?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_easypost_id&subtab=' . $stab . '">' . $name . '</a>';
					}
					$html .= '</h2>';
					echo wp_kses_post( $html );
					if ( 'return' === $current_tab ) {
						$return = '<div style ="padding-top: 10px;"><ul class="subsubsub" ><li><a href="#" class="elex_easypost_return_label_general_section">General</a>  </li><li><a href="#" class="elex_easypost_return_label_license_section">| Licence </a></li></ul></div> </br>';
						echo wp_kses_post( $return );
						$plugin_name = 'elex-easypost-return-addon';
						include_once EASYPOST_RETURN_LABEL_ADDON_MAIN_PATH . 'includes/wf_api_manager/html/html-wf-activation-window.php';
					}
					if ( 'auto_generate' === $current_tab ) {
						$auto_label = '<div style ="padding-top: 10px;"><ul class="subsubsub" ><li><a href="#" class="elex_easypost_auto_label_general_section">General</a>  </li><li><a href="#" class="elex_easypost_auto_label_license_section">| Licence </a></li></ul></div> </br>';
						echo wp_kses_post( $auto_label );
						$plugin_name = 'elex-easypost-auto-generate-email-addon';
						include_once EASYPOST_AUTO_LABEL_GENERATE_EMAIL_ADDON_MAIN_PATH . 'includes/wf_api_manager/html/html-wf-activation-window.php';
					}
					if ( 'multiple_warehouse' === $current_tab ) {
						$plugin_name = 'elex-multiple-warehouse-addon-for-easypost';
					}

	}


	public function get_option_key() {
		return 'woocommerce_' . $this->id . '_' . $this->get_sub_section() . '_settings';
	}

	public function get_sub_section() {

		$sub_section = ! empty( $_GET['subtab'] ) ? sanitize_text_field( $_GET['subtab'] ) : 'general';

		return $sub_section;
	}

	public function init_general_form_fields() {
		global $woocommerce;
		if ( WF_EASYPOST_ADV_DEBUG_MODE === 'on' ) { // Test mode is only for development purpose.
			$api_mode_options = array(
				'Live' => esc_attr( 'Live', 'wf-easypost' ),
				'Test' => esc_attr( 'Test', 'wf-easypost' ),
			);
		} else {
			$api_mode_options = array(
				'Live' => esc_attr( 'Live', 'wf-easypost' ),
			);
		}
		$general_fields = array(

			'enabled'           => array(
				'title'       => esc_attr( 'Realtime Rates', 'wf-easypost' ),
				'type'        => 'checkbox',
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'description' => esc_attr( 'Enable realtime rates on Cart/Checkout page.', 'wf-easypost' ),
				'default'     => 'no',
				'desc_tip'    => true,
				'class'       => 'general_tab_field',
			),
			'debug_mode'        => array(
				'title'       => esc_attr( 'Debug Mode', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable debug mode to display debugging information on your cart/checkout. Not recommended to enable this on a live site with traffic.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'general_tab_field',
			),
			'status_log'        => array(
				'title'       => esc_attr( 'Status Log', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable status log to display information relevant to developers for troubleshooting. Not recommended to enable this on a live site with traffic.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'general_tab_field',
			),
			'disable_cart_rate' => array(
				'title'       => esc_attr( 'Disable rates on cart', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable checkbox to disable rates on cart.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'general_tab_field',
			),
			'api'               => array(
				'title'       => esc_attr( 'Generic API Settings', 'wf-easypost' ),
				'type'        => 'title',
				'description' => esc_attr( 'To obtain a the EasyPost.com API Key, Signup & Login to the ', 'wf-easypost' ) . '<a href="http://www.easypost.com?utm_source=elextensions" target="_blank">' . esc_attr( 'EasyPost.com', 'wf-easypost' ) . '</a>' . esc_attr( ' and then go to the ', 'wf-easypost' ) . '<a href="https://www.easypost.com/account/api-keys?utm_source=elextensions" target="_blank">' . esc_attr( 'API Keys section.', 'wf-easypost' ) . '</a></br>' . esc_attr( 'You will find different API Keys for Live and Test mode.', 'wf-easypost' ),
				'class'       => 'general_tab_field',
			),
			'api_mode'          => array(
				'title'       => esc_attr( 'Select The API Mode', 'wf-easypost' ),
				'type'        => 'select',
				'css'         => 'padding: 0px;',
				'default'     => 'Live',
				'options'     => $api_mode_options,
				'description' => esc_attr( 'We recommend using the Test mode when you are setting up the plugin. Once everything is set up and the website is ready to go live to accept customer orders, move to the Live mode. You are provided with separate API keys for testing and for running live transactions. ', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'general_tab_field',
			),
			'api_key'           => array(
				'title'             => esc_attr( ' API Live Key', 'wf-easypost' ),
				'type'              => 'password',
				'description'       => esc_attr( 'Enter the API key. Please note API keys are different for Live and Test modes. Make sure to enter the right key based on the API Mode you have selected.', 'wf-easypost' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
				'class'             => 'general_tab_field',
			),
			'api_test_key'      => array(
				'title'             => esc_attr( 'API Test Key', 'wf-easypost' ),
				'type'              => 'password',
				'description'       => esc_attr( 'Enter the API key. Please note API keys are different for Live and Test modes. Make sure to enter the right key based on the API Mode you have selected.', 'wf-easypost' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
				'class'             => 'general_tab_field',
			),
		);
		if ( in_array( 'elex-easypost-for-woocommerce-bulk-printing-labels-addon/elex-easypost-for-woocommerce-bulk-printing-labels-addon.php', get_option( 'active_plugins' ) ) ) {
			$bulk_printing_add_on_fields = array(
				'addon_bulk_printing_title'       => array(
					'title'       => esc_attr( 'ELEX EasyPost Bulk Label Printing Add-On Settings', 'wf-easypost' ),
					'type'        => 'title',
					'class'       => 'general_tab_field',
					'description' => wp_kses_post( 'Get your credentials by logging in to <a href="https://developer.ilovepdf.com/login" target="_blank">iLovePDF site</a>. Free subscription allows 250 files to be downloaded per month. For higher plans checkout their <a href="https://developer.ilovepdf.com/pricing" target="_blank">pricing page</a>.', 'wf-easypost' ),
				),
				'addon_bulk_printing_project_key' => array(
					'title'       => esc_attr( 'Project Key', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'general_tab_field',
					'description' => esc_attr( 'Enter the Project Key you received from iLovePDF\'s project page.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'addon_bulk_printing_secret_key'  => array(
					'title'       => esc_attr( 'Secret Key', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'general_tab_field',
					'description' => esc_attr( 'Enter the Secret Key you received from iLovePDF\'s project page.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
			);
			$general_fields              = array_merge( $general_fields, $bulk_printing_add_on_fields );
		}
		return $general_fields;
	}
	public function init_rates_form_fields() {
			global $woocommerce;
			$carrier_boxes  = include 'data-wf-flat-rate-boxes.php';
			$rate_box_names = array();
		foreach ( $carrier_boxes as $carrier => $rate_boxes ) {
			foreach ( $rate_boxes as $rate_box_code => $rate_box ) {
				$rate_box_names[ $carrier . ':' . $rate_box_code ] = $carrier . ' : ' . $rate_box['name'];
			}
		}
			$rates_field = array(
				'title'                                   => array(
					'title'       => esc_attr( 'Method Title', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'This controls the title for fall-back rate which the user sees during Cart/Checkout.', 'wf-easypost' ),
					'default'     => esc_attr( $this->method_title, 'wf-easypost' ),
					'placeholder' => esc_attr( $this->method_title, 'wf-easypost' ),
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'availability'                            => array(
					'title'       => esc_attr( 'Method Available to', 'wf-easypost' ),
					'type'        => 'select',
					'css'         => 'padding: 0px;',
					'default'     => 'all',
					'description' => esc_attr( 'Select where to apply this shipping method.', 'wf-easypost' ),
					'desc_tip'    => true,
					'class'       => 'country_availability rates_tab_field',
					'options'     => array(
						'all'      => esc_attr( 'All Countries', 'wf-easypost' ),
						'specific' => esc_attr( 'Specific Countries', 'wf-easypost' ),
					),
				),
				'zip'                                     => array(
					'title'       => esc_attr( 'Zip Code', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'Enter the postcode for the sender', 'wf-easypost' ),
					'default'     => '',
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'state'                                   => array(
					'title'       => esc_attr( 'Sender State Code', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'Enter 2 letter shortcode of the State.', 'wf-easypost' ),
					'default'     => '',
					'desc_tip'    => true,
					'placeholder' => esc_attr( 'XX', 'wf-easypost' ),
					'class'       => 'rates_tab_field',
				),
				// adding country
				'country'                                 => array(
					'title'       => esc_attr( 'Sender Country', 'wf-easypost' ),
					'type'        => 'select',
					'class'       => 'chosen_select',
					'css'         => 'width: 450px;',
					'description' => esc_attr( 'Select the sender country', 'wf-easypost' ),
					'desc_tip'    => true,
					'default'     => 'US',
					'options'     => $woocommerce->countries->get_allowed_countries(),
					'class'       => 'rates_tab_field',
				),
				'countries'                               => array(
					'title'       => esc_attr( 'Choose Specific Countries', 'wf-easypost' ),
					'type'        => 'multiselect',
					'class'       => 'chosen_select rates_tab_field',
					'description' => esc_attr( 'Select the destination countries for which you want to show the shipping method.', 'wf-easypost' ),
					'desc_tip'    => true,
					'css'         => 'width: 450px;',
					'default'     => '',
					'options'     => $woocommerce->countries->get_allowed_countries(),
				),
				'estimated_title'                         => array(
					'title' => esc_attr( 'Estimated Delivery Date Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'est_delivery'                            => array(
					'title'       => esc_attr( 'Estimated Delivery Date', 'wf-easypost' ),
					'label'       => esc_attr( 'Enable', 'wf-easypost' ),
					'type'        => 'checkbox',
					'default'     => 'no',
					'description' => esc_attr( 'Enable this option to show estimated delivery date for each EasyPost service.', 'wf-easypost' ),
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'working_days'                            => array(
					'title'       => esc_attr( 'Working Days', 'wf-easypost' ),
					'type'        => 'multiselect',
					'class'       => 'chosen_select  rates_tab_field',
					'description' => esc_attr( 'Configure the regular working days. The estimated delivery date will be calculated based on this. The shipment is supposed to happen only on working days.', 'wf-easypost' ),
					'desc_tip'    => true,
					'options'     => array(
						'Sun' => 'Sunday',
						'Mon' => 'Monday',
						'Tue' => 'Tuesday',
						'Wed' => 'Wednesday',
						'Thu' => 'Thursday',
						'Fri' => 'Friday',
						'Sat' => 'Saturday',
					),
				),
				'cut_off_time'                            => array(
					'title'       => esc_attr( 'Cut-off time', 'wf-easypost' ),
					'type'        => 'time',
					'class'       => 'rates_tab_field',
					'description' => esc_attr( 'Configure the cut-off time for your shipment. The orders placed after the cut-off time will be shipped on the next working day. The estimated delivery date will be displayed based on this.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'lead_time'                               => array(
					'title'       => esc_attr( 'Lead time', 'wf-easypost' ),
					'type'        => 'number',
					'class'       => 'rates_tab_field',
					'css'         => 'width:10%;',
					'description' => esc_attr( 'Configure the lead time for your shipment. The estimated delivery date will be displayed based on this.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_title'                   => array(
					'title' => esc_attr( 'Flat Rate Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'flat_rate_boxes_domestic'                => array(
					'title' => esc_attr( 'Domestic Flat Rate Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'flat_rate_boxes_domestic_carrier'        => array(
					'title'             => esc_attr( 'EasyPost Flat Rate Service(s)', 'wf-easypost' ),
					'type'              => 'multiselect',
					'description'       => esc_attr( 'Select your EasyPost Flat Rate Services.', 'wf-easypost' ),
					'default'           => array( 'priority_mail' ),
					'css'               => 'width: 450px;',
					'class'             => 'chosen_select rates_tab_field',
					'options'           => array(
						'priority_mail'         => 'Priority Mail',
						'priority_mail_express' => 'Priority Mail Express',
						'first_class_mail'      => 'First-Class Mail',
						'fedex_onerate'         => 'FedEx One Rate',
					),
					'desc_tip'          => true,
					'custom_attributes' => array(
						'autocomplete' => 'off',
					),
				),
				'selected_flat_rate_boxes'                => array(
					'title'       => esc_attr( 'Flat Rate Boxes', 'wf-easypost' ),
					'type'        => 'multiselect',
					'class'       => 'multiselect chosen_select selected_flat_rate_boxes rates_tab_field',
					'default'     => '',
					'options'     => $rate_box_names,
					'description' => esc_attr( 'Select USPS flat rate boxes to be made available.', 'wf-easypost' ),
					'desc_tip'    => false,
				),
				'flat_rate_boxes_text'                    => array(
					'title'       => esc_attr( 'Priority Mail Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate:Priority Mail',
					'placeholder' => esc_attr( 'USPS Flat Rate:Priority Mail', 'wf-easypost' ),
					'description' => esc_attr( 'Enter the text for the Flat Rate label to be shown on the cart and checkout pages for domestic shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_express_text'            => array(
					'title'       => esc_attr( 'Priority Mail Express Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate:Priority Mail Express',
					'placeholder' => esc_attr( 'USPS Flat Rate:Priority Mail Express', 'wf-easypost' ),
					'description' => esc_attr( 'Enter the text for the Flat Rate label to be shown on the cart and checkout pages for domestic shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_first_class_text'        => array(
					'title'       => esc_attr( 'First-Class Mail Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate:First-Class Mail',
					'placeholder' => esc_attr( 'USPS Flat Rate:First-Class Mail', 'wf-easypost' ),
					'description' => esc_attr( 'Enter the text for the Flat Rate label to be shown on the cart and checkout pages for domestic shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_fedex_one_rate_text'     => array(
					'title'       => esc_attr( 'FedEx One Rate Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'FedEx Flat Rate:FedEx One Rate',
					'placeholder' => esc_attr( 'FedEx Flat Rate:FedEx One Rate', 'wf-easypost' ),
					'description' => esc_attr( 'Enter the text for the Flat Rate label to be shown on the cart and checkout pages for domestic shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_fee'                           => array(
					'title'       => esc_attr( 'Flat Rate Fee', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'Fee per-box excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'wf-easypost' ),
					'default'     => '',
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'flat_rate_boxes_international'           => array(
					'title' => esc_attr( 'USPS International Flat Rate Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'flat_rate_boxes_international_carrier'   => array(
					'title'             => esc_attr( 'EasyPost Flat Rate Service(s)', 'wf-easypost' ),
					'type'              => 'multiselect',
					'description'       => esc_attr( 'Select your EasyPost Flat Rate Services.', 'wf-easypost' ),
					'default'           => array( 'priority_mail_international' ),
					'css'               => 'width: 450px;',
					'class'             => 'chosen_select rates_tab_field',
					'options'           => array(
						'priority_mail_international'    => 'Priority Mail International',
						'priority_mail_express_international' => 'Express Mail International',
						'first_class_mail_international' => 'First-Class Mail International',
					),
					'desc_tip'          => true,
					'custom_attributes' => array(
						'autocomplete' => 'off',
					),
				),

				'predefined_package_service_usps'         => array(
					'title'       => esc_attr( 'USPS Flat Rate Boxes', 'wf-easypost' ),
					'type'        => 'multiselect',
					'css'         => 'padding: 0px;',
					'class'       => 'multiselect chosen_select selected_flat_rate_boxes rates_tab_field',
					'options'     => array(
						'Card'                      => esc_attr( 'Card', 'wf-easypost' ),
						'Letter'                    => esc_attr( 'Letter', 'wf-easypost' ),
						'Flat'                      => esc_attr( 'Flat', 'wf-easypost' ),
						'Parcel'                    => esc_attr( 'Parcel', 'wf-easypost' ),
						'FlatRateEnvelope'          => esc_attr( 'FlatRateEnvelope', 'wf-easypost' ),
						'FlatRateLegalEnvelope'     => esc_attr( 'FlatRateLegalEnvelope', 'wf-easypost' ),
						'FlatRatePaddedEnvelope'    => esc_attr( 'FlatRatePaddedEnvelope', 'wf-easypost' ),
						'FlatRateGiftCardEnvelope'  => esc_attr( 'FlatRateGiftCardEnvelope', 'wf-easypost' ),
						'FlatRateWindowEnvelope'    => esc_attr( 'FlatRateWindowEnvelope', 'wf-easypost' ),
						'SmallFlatRateEnvelope'     => esc_attr( 'SmallFlatRateEnvelope', 'wf-easypost' ),
						'SmallFlatRateBox'          => esc_attr( 'SmallFlatRateBox', 'wf-easypost' ),
						'MediumFlatRateBox'         => esc_attr( 'MediumFlatRateBox', 'wf-easypost' ),
						'LargeFlatRateBox'          => esc_attr( 'LargeFlatRateBox', 'wf-easypost' ),
						'LargeFlatRateBoardGameBox' => esc_attr( 'LargeFlatRateBoardGameBox', 'wf-easypost' ),
					),
					'description' => esc_attr( 'Select USPS flat rate boxes to be made available.', 'wf-easypost' ),
				),
				'flat_rate_boxes_text_international_mail' => array(
					'title'       => esc_attr( 'Priority Mail International Flat Rate Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate Priority Mail International',
					'placeholder' => 'USPS Flat Rate Priority Mail International',
					'description' => esc_attr( 'Enter the text for the Flat Rate label[Priority Mail International] to be shown on the cart and checkout pages for international shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_text_international_express' => array(
					'title'       => esc_attr( 'Express Mail International Flat Rate Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate International Express',
					'placeholder' => 'USPS Flat Rate International Express',
					'description' => esc_attr( 'Enter the text for the Flat Rate label[Express Mail International] to be shown on the cart and checkout pages for international shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),
				'flat_rate_boxes_text_first_class_mail_international' => array(
					'title'       => esc_attr( 'First-Class Mail International Flat Rate Label', 'wf-easypost' ),
					'type'        => 'text',
					'class'       => 'rates_tab_field',
					'default'     => 'USPS Flat Rate First-Class Mail International ',
					'placeholder' => 'USPS Flat Rate First-Class Mail International ',
					'description' => esc_attr( 'Enter the text for the Flat Rate label[First-Class Mail International] to be shown on the cart and checkout pages for international shipments.', 'wf-easypost' ),
					'desc_tip'    => true,
				),

				'duty_settings'                           => array(
					'title' => esc_attr( 'Duty Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'ex_easypost_duty'                        => array(
					'title'       => esc_attr( 'Duty Payer', 'wf-easypost' ),
					'type'        => 'select',
					'css'         => 'padding: 0px;',
					'default'     => '',
					'description' => esc_attr( 'Select the payer to pay the duty while international shipments', 'wf-easypost' ),
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
					'options'     => array(
						'none' => esc_attr( 'None', 'wf-easypost' ),
						'DDU'  => esc_attr( 'Pay by the recipient', 'wf-easypost' ),
						'DDP'  => esc_attr( 'Paid by the sender/seller', 'wf-easypost' ),
					),
				),

				'carrier_rates_title'                     => array(
					'title' => esc_attr( 'Carrier & Rate Settings', 'wf-easypost' ),
					'type'  => 'title',
					'class' => 'rates_tab_field',
				),
				'fallback'                                => array(
					'title'       => esc_attr( 'Fallback', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'If EasyPost.com returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'wf-easypost' ),
					'default'     => '',
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'show_rates'                              => array(
					'title'       => esc_attr( 'Rates Type', 'wf-easypost' ),
					'type'        => 'select',
					'css'         => 'padding: 0px;',
					'default'     => 'commercial',
					'options'     => array(
						'residential' => esc_attr( 'Residential', 'wf-easypost' ),
						'commercial'  => esc_attr( 'Commercial', 'wf-easypost' ),
						'retail'      => esc_attr( 'Retail', 'wf_easypost' ),
					),
					'description' => esc_attr( 'Rates will be fetched based on the address type that you choose here. Please note this functionality will be available only for supported carriers.', 'wf-easypost' ),
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'handling_fee'                            => array(
					'title'       => esc_attr( 'Handling Fee', 'wf-easypost' ),
					'type'        => 'text',
					'description' => esc_attr( 'Enter the amount to be added to the order total as handling fee. Leave the field empty to not add.', 'wf-easypost' ),
					'default'     => '',
					'desc_tip'    => true,
					'class'       => 'rates_tab_field',
				),
				'easypost_carrier'                        => array(
					'title'             => esc_attr( 'EasyPost Carrier(s)', 'wf-easypost' ),
					'type'              => 'multiselect',
					'description'       => esc_attr( 'Select your EasyPost Carriers. Please add UPS DAP only if you have integrated the UPS Digital Access Program (DAP) in your EasyPost account. If both UPS DAP and UPS are chosen, priority goes to UPS DAP services.', 'wf-easypost' ),
					'default'           => array( 'USPS' ),
					'css'               => 'width: 450px;',
					'class'             => 'ups_packaging chosen_select rates_tab_field',
					'options'           => $this->carrier_list,
					'desc_tip'          => true,
					'custom_attributes' => array(
						'autocomplete' => 'off',
					),
				),
				'services'                                => array(
					'type'  => 'services',
					'class' => 'rates_tab_field',
				),

			);
			return $rates_field;
	}
	public function init_labels_form_fields() {
		global $woocommerce;
		$labels_field = array(
			'label-settings'                             => array(
				'title' => esc_attr( 'Label Printing API Settings', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),
			'printLabelType'                             => array(
				'title'       => esc_attr( 'Print Label Type', 'wf-easypost' ),
				'type'        => 'select',
				'css'         => 'padding: 0px;',
				'default'     => 'yes',
				'options'     => array(
					'PNG'  => esc_attr( 'PNG', 'wf-easypost' ),
					'PDF'  => esc_attr( 'PDF', 'wf-easypost' ),
					'ZPL'  => esc_attr( 'ZPL', 'wf-easypost' ),
					'EPL2' => esc_attr( 'EPL2', 'wf-easypost' ),
				),
				'description' => esc_attr( 'Choose the file format which the label is printed.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'insurance'                                  => array(
				'title'       => esc_attr( 'Insurance', 'wf-easypost' ),
				'type'        => 'select',
				'css'         => 'padding: 0px;',
				'default'     => 'no',
				'options'     => array(
					'optional' => esc_attr( 'Customer Choice', 'wf-easypost' ),
					'yes'      => esc_attr( 'Mandatory', 'wf-easypost' ),
					'no'       => esc_attr( 'No Insurance', 'wf-easypost' ),
				),
				'description' => esc_attr( 'Enable this option to insure the parcel. EasyPost charge 1% of the value, with a $1 minimum, and handle all the claims', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'elex_shipping_label_size'                   => array(
				'title'       => esc_attr( 'Label Size', 'wf-easypost' ),
				'type'        => 'select',
				'default'     => 'label_type',
				'options'     => array(
					'label_type'       => esc_attr( 'Default', 'wf-easypost' ),
					'shipping_service' => esc_attr( 'Custom Size', 'wf-easypost' ),
				),
				'description' => esc_attr( 'Select an option to specify the label size. You can choose custom or default option.The default label size is predefined (PDF: 8.5x11,EPL: 4x5,ZPL: 4x5,PNG: 4x6). If you choose custom, you will have to select label size for each carrier.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'elex_shipping_label_size_usps'              => array(
				'title'       => wp_kses_post( '<i><font size = "2">USPS</font></i>', 'wf-easypost' ),
				'type'        => 'select',
				'default'     => 'label_type',
				'options'     => array(
					'4x6' => esc_attr( '4x6' ),
				),
				'description' => esc_attr( 'Select the label size for usps.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'elex_shipping_label_size_ups'               => array(
				'title'       => wp_kses_post( '<i><font size = "2">UPS</font></i>', 'wf-easypost' ),
				'type'        => 'select',
				'default'     => 'label_type',
				'options'     => array(
					'4x8' => esc_attr( '4x8' ),
					'4x6' => esc_attr( '4x6' ),
				),
				'description' => esc_attr( 'Select the label size for ups.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'elex_shipping_label_size_fedex'             => array(
				'title'       => wp_kses_post( '<i><font size = "2">FedEx</font></i>', 'wf-easypost' ),
				'type'        => 'select',
				'default'     => 'label_type',
				'options'     => array(
					'4x8' => esc_attr( '4x8' ),
					'4x7' => esc_attr( '4x7' ),
					'4x6' => esc_attr( '4x6' ),
				),
				'description' => esc_attr( 'Select the label size for FedEx.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'elex_shipping_label_size_canadapost'        => array(
				'title'       => wp_kses_post( '<i><font size = "2">CanadaPost</font></i>', 'wf-easypost' ),
				'type'        => 'select',
				'default'     => 'label_type',
				'options'     => array(
					'4x6' => esc_attr( '4x6' ),
				),
				'description' => esc_attr( 'Select the label size for CanadaPost.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'signature_option'                           => array(
				'title'       => esc_attr( 'Signature Option', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable to set a default signature option to all your products. Please note, it will hide the EasyPost Delivery Signature option from your individual product settings.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'branding_url'                               => array(
				'title'       => esc_attr( 'Branding link', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable to set a default label generation link as branding link.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'ioss_number'                                => array(
				'title'       => esc_attr( 'IOSS Number', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter IOSS for Europian countries.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'default_shipment'                           => array(
				'title' => esc_attr( 'Default Shipment Services', 'wf-usps-easypost-woocommerce' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),
			'easypost_default_domestic_shipment_service' => array(
				'title'       => esc_attr( 'Default Domestic Service', 'wf-easypost' ),
				'type'        => 'select',
				'description' => esc_attr( 'Select a default shipment service for domestic shipment.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
				'options'     => array(
					'None'                => 'None',
					'First'               => 'First-Class (USPS)',
					'Priority'            => 'Priority Mail&#0174; (USPS)',
					'Express'             => 'Priority Mail Express&#8482; (USPS)',
					'ParcelSelect'        => 'USPS Parcel Select (USPS)',
					'LibraryMail'         => 'Library Mail Parcel (USPS)',
					'MediaMail'           => 'Media Mail Parcel (USPS)',
					'CriticalMail'        => 'USPS Critical Mail (USPS)',
					'FEDEX_2_DAY_AM'      => 'FedEx 2 Day AM (FedEx)',
					'FEDEX_2_DAY'         => 'FedEx 2 Day (FedEx)',
					'FEDEX_EXPRESS_SAVER' => 'FedEx Express Saver (FedEx)',
					'FEDEX_GROUND'        => 'FedEx Ground (FedEx)',
					'FIRST_OVERNIGHT'     => 'First Overnight (FedEx)',
					'PRIORITY_OVERNIGHT'  => 'Priority Overnight (FedEx)',
					'STANDARD_OVERNIGHT'  => 'Standard Overnight (FedEx)',
					'Ground'              => 'Ground (UPS)',
					'3DaySelect'          => '3 Day Select (UPS)',
					'2ndDayAirAM'         => '2nd Day Air AM (UPS)',
					'2ndDayAir'           => '2nd Day Air (UPS)',
					'NextDayAirSaver'     => 'Next Day Air Saver (UPS)',
					'NextDayAirEarlyAM'   => 'Next Day Air Early AM (UPS)',
					'NextDayAir'          => 'Next Day Air (UPS)',
					'ExpeditedParcel'     => 'Expedited Parcel (CanadaPost)',
					'RegularParcel'       => 'Regular Parcel (CanadaPost)',
					'Xpresspost'          => 'Xpresspost (CanadaPost)',
					'SurePostOver1Lb'     => 'SurePost Over1Lb (UPSSurePost)',
					'SurePostUnder1Lb'    => 'SurePost Under1Lb (UPSSurePost)',
				),
			),

			'easypost_default_international_shipment_service' => array(
				'title'       => esc_attr( 'Default International Service', 'wf-easypost' ),
				'type'        => 'select',
				'description' => esc_attr( 'Select a default shipment service for international shipment.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
				'options'     => array(
					'None'                            => 'None',
					'FirstClassMailInternational'     => 'First Class Mail International (USPS)',
					'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International (USPS)',
					'PriorityMailInternational'       => 'Priority Mail International&#0174; (USPS)',
					'ExpressMailInternational'        => 'Express Mail International (USPS)',
					'INTERNATIONAL_PRIORITY'          => 'FedEx International Priority (FedEx)',
					'INTERNATIONAL_ECONOMY'           => 'FedEx International Economy (FedEx)',
					'Express'                         => 'Express (UPS)',
					'Expedited'                       => 'Expedited (UPS)',
					'ExpressPlus'                     => 'Express Plus (UPS)',
					'UPSStandard'                     => 'UPS Standard (UPS)',
					'INTERNATIONAL_FIRST'             => 'FedEx International First (FedEx)',
					'ExpeditedParcelUSA'              => 'Expedited Parcel USA (CanadaPost)',
					'PriorityWorldwideParcelUSA'      => 'Priority Worldwide Parcel USA (CanadaPost)',
					'SmallPacketUSAAir'               => 'Small Packet USA Air (CanadaPost)',
					'TrackedPacketUSA'                => 'Tracked Packet USA (CanadaPost)',
					'XpresspostUSA'                   => 'Xpresspost USA (CanadaPost)',
					'PriorityWorldwidePakIntl'        => 'Priority Worldwide Pak Intl (CanadaPost)',
					'InternationalParcelSurface'      => 'International Parcel Surface (CanadaPost)',
					'PriorityWorldwideParcelIntl'     => 'Priority Worldwide Parcel Intl (CanadaPost)',
					'SmallPacketInternationalSurface' => 'Small Packet International Surface (CanadaPost)',
					'SmallPacketInternationalAir'     => 'Small Packet International Air (CanadaPost)',
					'TrackedPacketInternational'      => 'Tracked Packet International (CanadaPost)',
					'XpresspostInternational'         => 'Xpresspost International (CanadaPost)',
				),
			),
			'origin_address'                             => array(
				'title' => esc_attr( 'Origin Address', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),

			'name'                                       => array(
				'title'       => esc_attr( 'Sender Name', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter your name.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',

			),
			'company'                                    => array(
				'title'       => esc_attr( 'Sender Company Name', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter your company name.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'street1'                                    => array(
				'title'       => esc_attr( 'Sender Address Line1', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter your address line 1.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'street2'                                    => array(
				'title'       => esc_attr( 'Sender Address Line2', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter your address line 2.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'city'                                       => array(
				'title'       => esc_attr( 'Sender City', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter your city.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),

			'email'                                      => array(
				'title'       => esc_attr( 'Sender Email', 'wf-easypost' ),
				'type'        => 'email',
				'description' => esc_attr( 'Enter sender email', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'phone'                                      => array(
				'title'       => esc_attr( 'Sender Phone', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter sender phone', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),

			'customs_description'                        => array(
				'title'       => esc_attr( 'Customs Description', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter product description for International shipping.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'Custom_signer_title'                        => array(
				'title' => esc_attr( 'USPS Customs Signer', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),
			'customs_signer'                             => array(
				'title'       => esc_attr( 'USPS Customs Signature', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter the USPS customs signer for international shipping.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'return_header'                              => array(
				'title' => esc_attr( 'Return Address', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),
			'return_address'                             => array(
				'title'       => esc_attr( 'Return Address', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'description' => esc_attr( 'Enable this option if you want to input another address, which will be picked up as the return address while generating return labels. If not enabled, returns will be sent to the origin address mentioned above.', 'wf-easypost' ),
				'desc_tip'    => true,
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'label_tab_field',
			),
			'return_name'                                => array(
				'title'       => esc_attr( 'Name', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter name.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_company'                             => array(
				'title'       => esc_attr( 'Company Name', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter Company name.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',

			),
			'return_street1'                             => array(
				'title'       => esc_attr( 'Address Line1', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter Address Line1.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_street2'                             => array(
				'title'       => esc_attr( 'Address Line2', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter Address Line2.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_city'                                => array(
				'title'       => esc_attr( 'City', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter City.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_state'                               => array(
				'title'       => esc_attr( 'State Code', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter state short code (Eg: CA)', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_zip'                                 => array(
				'title'       => esc_attr( 'Zip Code', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter Postcode.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_country'                             => array(
				'title'       => esc_attr( 'Country', 'wf-easypost' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'css'         => 'width: 450px;',
				'description' => esc_attr( 'Select the country.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => 'US',
				'options'     => $woocommerce->countries->get_allowed_countries(),
				'class'       => 'label_tab_field',
			),
			'return_phone'                               => array(

				'title'       => esc_attr( 'Phone', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter phone number.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'label_tab_field',
			),
			'return_email'                               => array(
				'title'       => esc_attr( 'Email', 'wf-easypost' ),
				'type'        => 'email',
				'description' => esc_attr( 'Enter email', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'third_party__header'                        => array(
				'title' => esc_attr( 'Third Party Billing (UPS)', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),

			'third_party_billing'                        => array(
				'title'       => esc_attr( 'Third Party Billing', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'description' => esc_attr( 'Enable this field to enter UPS third party account details. This account will be charged whenever a shipment label is printed.', 'wf-easypost' ),
				'desc_tip'    => true,
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'label_tab_field',
			),
			'third_party_apikey'                         => array(
				'title'       => esc_attr( 'Third Party Account No', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter the UPS third party account details.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'third_party_country'                        => array(
				'title'       => esc_attr( 'Third Party Country', 'wf-easypost' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'css'         => 'width: 450px;',
				'description' => esc_attr( 'Select the UPS third party country.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => 'US',
				'options'     => $woocommerce->countries->get_allowed_countries(),
				'class'       => 'label_tab_field',
			),
			'third_party_zip'                            => array(
				'title'       => esc_attr( 'Third Party Zip Code', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter the postcode/zipcode for the UPS third party.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'third_party_checkout'                       => array(
				'title'       => esc_attr( 'Third Party Billing On Checkout', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable this option to enter UPS third party details on the checkout page.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'third_party_fedex_header'                        => array(
				'title' => esc_attr( 'Third Party Billing (FedEx)', 'wf-easypost' ),
				'type'  => 'title',
				'class' => 'label_tab_field',
			),

			'fedex_third_party_billing'                        => array(
				'title'       => esc_attr( 'Third Party Billing', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'description' => esc_attr( 'Enable this field to enter FedEx third party account details. This account will be charged whenever a shipment label is printed.', 'wf-easypost' ),
				'desc_tip'    => true,
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'label_tab_field',
			),
			'fedex_third_party_apikey'                         => array(
				'title'       => esc_attr( 'Third Party Account No', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter the FedEx third party account details.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'fedex_third_party_country'                        => array(
				'title'       => esc_attr( 'Third Party Country', 'wf-easypost' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'css'         => 'width: 450px;',
				'description' => esc_attr( 'Select the FedEx third party country.', 'wf-easypost' ),
				'desc_tip'    => true,
				'default'     => 'US',
				'options'     => $woocommerce->countries->get_allowed_countries(),
				'class'       => 'label_tab_field',
			),
			'fedex_third_party_zip'                            => array(
				'title'       => esc_attr( 'Third Party Zip Code', 'wf-easypost' ),
				'type'        => 'text',
				'description' => esc_attr( 'Enter the postcode/zipcode for the FedEx third party.', 'wf-easypost' ),
				'default'     => '',
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
			'fedex_third_party_checkout'                       => array(
				'title'       => esc_attr( 'Third Party Billing On Checkout', 'wf-easypost' ),
				'label'       => esc_attr( 'Enable', 'wf-easypost' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => esc_attr( 'Enable this option to enter FedEx third party details on the checkout page.', 'wf-easypost' ),
				'desc_tip'    => true,
				'class'       => 'label_tab_field',
			),
		);
		return $labels_field;
	}
	public function init_packing_form_fields() {
		$woocommerce_weight = get_option( 'woocommerce_weight_unit' );
		$packing_field      = array(
			'packing_method'         => array(
				'title'       => esc_attr( 'Parcel Packing', 'wf-easypost' ),
				'type'        => 'select',
				'css'         => 'padding: 0px;',
				'default'     => '',
				'class'       => 'packing_method package_tab_field',
				'options'     => array(
					'per_item'             => esc_attr( 'Default: Pack items individually', 'wf-easypost' ),
					'box_packing'          => esc_attr( 'Recommended: Pack into boxes with weights and dimensions', 'wf-easypost' ),
					'weight_based_packing' => esc_attr( 'Pack items based on weight', 'wf-easypost' ),
				),
				'desc_tip'    => true,
				'description' => esc_attr( 'Select an option to determine how items are packed before being sent to EasyPost.', 'wf-shipping-fedex' ),
			),
			'packing_algorithm'      => array(
				'title'   => esc_attr( 'Packing Algorithm', 'wf-easypost' ),
				'type'    => 'select',
				'css'     => 'padding: 0px;',
				'default' => '',
				'class'   => 'package_tab_field',
				'options' => array(
					'volume_based' => esc_attr( 'Default: Volume Based Packing', 'wf-easypost' ),
					'stack_first'  => esc_attr( 'Stack First Packing', 'wf-easypost' ),
				),
			),
			'box_max_weight'         => array(
				'title'       => esc_attr( 'Box Maximum Weight (' . $woocommerce_weight . ')', 'wf-easypost' ),
				'type'        => 'text',
				'default'     => '10',
				'class'       => 'weight_based_option package_tab_field',
				'id'          => 'woocommerce_wf_easy_post_box_max_weight',
				'desc_tip'    => true,
				'description' => esc_attr( "Enter maximum weight allowed for a single box. You have to configure the product weight on the individual product's admin page", 'wf-easypost' ),
			),
			'weight_packing_process' => array(
				'title'       => esc_attr( 'Packing Process', 'wf-easypost' ),
				'type'        => 'select',
				'css'         => 'padding: 0px;',
				'default'     => 'pack_descending',
				'class'       => 'weight_based_option package_tab_field',
				'options'     => array(
					'pack_descending' => esc_attr( 'Pack heavier items first', 'wf-easypost' ),
					'pack_ascending'  => esc_attr( 'Pack lighter items first.', 'wf-easypost' ),
					'pack_simple'     => esc_attr( 'Pack purely divided by weight.', 'wf-easypost' ),
				),
				'desc_tip'    => true,
				'description' => esc_attr( 'Select your packing order.', 'wf-easypost' ),
			),

			'boxes'   => array(
				'type'  => 'box_packing',
				'class' => 'package_tab_field',
			),

		);
		return $packing_field;
	}
	public function init_licence_form_fields() {
		$licence_field = array(
			'licence' => array(
				'type'  => 'activate_box',
				'class' => 'licence_tab_field',
			),
		);
		return $licence_field;
	}
	public function init_auto_generate_form_fields() {
		 // Auto Label Generate Add-on.
		 $auto_fields = array();
		if ( ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK ) {
			$auto_add_on_fields = include ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK_PATH . 'includes/data-wf-settings.php';
			if ( is_array( $auto_add_on_fields ) ) {
				$auto_fields = array_merge( $auto_fields, $auto_add_on_fields );
			}
		}
		return $auto_fields;
	}
	public function init_return_form_fields() {
		// Return Label Add-on.
		$return_field = array();
		if ( ELEX_EASYPOST_RETURN_ADDON_STATUS && ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH ) {
			$add_on_fields = include ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH . 'includes/data-wf-settings.php';
			if ( is_array( $add_on_fields ) ) {
				$return_field = array_merge( $return_field, $add_on_fields );
			}
		}
		return $return_field;
	}
	public function init_multiple_warehouse_form_fields() {
		// Multiple warehouse addon
		$warehouse_fields = array();
		if ( ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {
			$warehouse_add_on_fields = include ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK_PATH . 'includes/elex-html-multiple-address-settings.php';
			if ( is_array( $warehouse_add_on_fields ) ) {
				$warehouse_fields = array_merge( $warehouse_fields, $warehouse_add_on_fields );
			}
		}
		return $warehouse_fields;
	}
	/**
	 * Init_form_fields function.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		global $woocommerce;

		$shipping_classes = array();
		$shipping_class   = get_terms( 'product_shipping_class', array( 'hide_empty' => '0' ) );
		$classes          = ! empty( $shipping_class ) ? $shipping_class : array();
		foreach ( $classes as $class ) {
			$shipping_classes[ $class->term_id ] = $class->name;
		}

		$tabs = array(
			'easypost_wrapper' => array(
				'type' => 'easypost_tabs',
			),
		);
		$method            = 'init_' . $this->get_sub_section() . '_form_fields';
		$this->form_fields = array_merge( $tabs, $this->$method() );

	}

	/**
	 * Calculate_shipping function.
	 *
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {

		global $woocommerce;
		$checkout_rates = array();
		$session_data   = array();

		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$domestic_country = isset( $rates_settings['country'] ) ? array( $rates_settings['country'] ) : array( 'US' );
		$custom_services  = isset( $rates_settings['services'] ) ? $rates_settings['services'] : array();
		$disable_cart     = isset( $general_settings['disable_cart_rate'] ) ? $general_settings['disable_cart_rate'] : 'no';
		$enabled          = isset( $general_settings['enabled'] ) ? $general_settings['enabled'] : 'no';
		$country_availability = isset( $rates_settings['availability'] ) ? $rates_settings['availability'] : 'all';
		$specific_countries = isset( $rates_settings['countries'] ) ? $rates_settings['countries'] : array();

		if ( 'no' === $enabled || ( 'specific' === $country_availability && ! in_array( $package['destination']['country'], $specific_countries ) ) ) {
			return;
		}
		if ( 'yes' === $disable_cart && ! is_checkout() && 'yes' === $enabled ) {
			$latest_rate_session_key = 'easypost_has_latest_rates';
			$rate_data = 'checkout_page_data';
			unset( $woocommerce->session->$rate_data );
			unset( $woocommerce->session->$latest_rate_session_key );
			return;
		}

		$woocommerce->session->set( 'easypost_has_latest_rates', true );

		if ( is_checkout() && 'yes' === $enabled ) {
			 // check if cached data is exist
			if ( $woocommerce->session && $woocommerce->session->get( 'checkout_page_data' ) ) {
				$session_checkout_data = $woocommerce->session->get( 'checkout_page_data' );
				
				if ( ! $this->vendor_check && $session_checkout_data['expiration_time'] > time() && $package['destination']['country'] === $session_checkout_data['package_country'] && $package['destination']['postcode'] === $session_checkout_data['package_zip'] ) {
					foreach ( $session_checkout_data['package_rates'] as $rate_key => $rate_val ) {
						$this->add_rate( $rate_val );
					}
					return;
				}
			}
		}
		$this->rates               = array();
		$lead_time                 = 0;
		$this->carrier             = ( isset( $rates_settings['easypost_carrier'] ) && ! empty( $rates_settings['easypost_carrier'] ) ) ? $rates_settings['easypost_carrier'] : array();

		if ( isset( $package['contents'] ) ) {
			if ( ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {
				foreach ( $package['contents'] as $key => $val ) {
					$lead_time_prdct = get_post_meta( $val['product_id'], '_wf_easypost_custom_lead_time_value', true );
					if ( $lead_time < $lead_time_prdct ) {
						$lead_time = $lead_time_prdct;
					}
				}
			}
			if ( 'yes' === $rates_settings['est_delivery'] && 0 === $lead_time && isset( $rates_settings['lead_time'] ) && ! empty( $rates_settings['lead_time'] ) ) {
				$lead_time = $rates_settings['lead_time'];
			}
		}
		if ( $this->vendor_check ) {
			$domestic_country = isset( $package['origin']['country'] ) ? array( $package['origin']['country'] ) : array( 'US' );
		}
		$domestic = in_array( $package['destination']['country'], $domestic_country ) ? true : false;
		if ( 'AE' === $package['destination']['country'] ) {
			$package['destination']['postcode'] = '00000';
		}
		if ( 'NG' === $package['destination']['country'] ) {
			$package['destination']['postcode'] = '100001';
		}
		if ( in_array( 'US', $domestic_country ) && 'PR' === $package['destination']['country'] || 'GU' === $package['destination']['country'] || 'AS' === $package['destination']['country'] || 'VI' === $package['destination']['country'] || 'MP' === $package['destination']['country'] ) {
			$domestic = true;
		}

		$this->debug( esc_attr( 'EasyPost.com debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-easypost' ) );
		if ( ! $domestic && $rates_settings['predefined_package_service_usps'] ) {
	
			if ( $this->enable_standard_services ) {
				// Get cart package details and proceed with GetRates.

				$package_requests = array();
				if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' ) {
					/**
					 * Get packages from multi vendor
					 * 
					 * @since 2.1.0
					 */
					$package               = apply_filters( 'wf_filter_package_address', array( $package ), '', false );
					$package_requests_temp = array();
					$package_requests      = array();
					foreach ( $package as $key => $val ) {
						$package_requests_temp = $this->calculate_flat_rate_box_rate( $val, '', 'international' );
						$package_requests      = array_merge( $package_requests, $package_requests_temp );
					}
				} else {
							$package_requests = $this->calculate_flat_rate_box_rate( $package, '', 'international' );
					
				}
				if ( isset( $data ) ) {
					foreach ( $package_requests as $key => $package_request ) {
						$package_requests[] = $package_request;
					}
				}
				libxml_use_internal_errors( true );
				
				if ( $package_requests ) {
					if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
						require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
					}
					$responses                       = array();
					$count                           = 0;
					$flat_rate_service_international = '';
					foreach ( $package_requests as $key => $package_request ) {

						$flat_rate = $this->box_name;
						$flat_rate = str_replace( 'USPS:', '', $flat_rate[ $count ] );
						if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_allow_vedor_api_key' ) === 'yes' ) {
							$easypost_api_key = get_user_meta( $package_request['vendor_id'], 'vendor_easypost_api_key', true );
						} elseif ( 'Live' === $general_settings['api_mode'] ) {
							$easypost_api_key = $general_settings['api_key'];
						} else {
							 $easypost_api_key = $general_settings['api_test_key'];
						}
						\EasyPost\EasyPost::setApiKey( $easypost_api_key );
						if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' ) {
							$responses[] = $this->get_result( $package_request, $flat_rate, $package_request['origin'] );
						} elseif ( $this->vendor_check ) {
							
							$responses[] = $this->get_result( $package_request, $flat_rate, $package['origin'] );
						} else {
							$responses[] = $this->get_result( $package_request, $flat_rate );
						}                   
					}

					if ( ! $responses ) {
						return false;
					}
					$found_rates   = array();
					$labels_stored = array();
					foreach ( $responses as $response_ele ) {
						$response_obj = isset( $response_ele['response'] ) ? $response_ele['response'] : '';
						if ( isset( $response_obj->rates ) && ! empty( $response_obj->rates ) ) {
							$service_name_label = array();
							foreach ( $this->carrier as $carrier_name ) {
								$flag_currency_convertion = false;
								foreach ( $response_obj->rates as $easypost_rate ) {
									if ( $carrier_name === $easypost_rate->carrier ) {
										/**
										 * Code snippet to use two different carrier accounts for domestic and international.
										 * 
										 * @since 1.1.0
										 */
										$manage_accounts = apply_filters( 'elex_easypost_two_different_carrier_account', false );
										if ( $manage_accounts ) {
											if ( isset( $manage_accounts[ $carrier_name ] ) ) {
												if ( $domestic && isset( $manage_accounts[ $carrier_name ]['domestic'] ) ) {
													if ( $easypost_rate->carrier_account_id !== $manage_accounts[ $carrier_name ]['domestic'] ) {
														continue;
													}
												}
												if ( ! $domestic && isset( $manage_accounts[ $carrier_name ]['international'] ) ) {
													if ( $easypost_rate->carrier_account_id !== $manage_accounts[ $carrier_name ]['international'] ) {
														continue;
													}
												}
											}
										}
										if ( false === $flag_currency_convertion ) {
											$from_currency            = $easypost_rate->currency;
											$to_currency              = get_woocommerce_currency();
											$converted_currency       = $this->xa_currency_converter( $from_currency, $to_currency, $easypost_rate->carrier );
											$flag_currency_convertion = true;
										}

										if ( ! $converted_currency ) {
											break;
										}

										$service_type = (string) $easypost_rate->service;
										$service_name = (string) ( isset( $custom_services[ $carrier_name ][ $service_type ]['name'] ) && ! empty( $custom_services[ $carrier_name ][ $service_type ]['name'] ) ) ? $custom_services[ $carrier_name ][ $service_type ]['name'] : $this->services[ $carrier_name ]['services'][ $service_type ];
										if ( 'retail' === $rates_settings['show_rates'] ) {
											$total_amount = $response_ele['quantity'] * $easypost_rate->retail_rate;
										} else {
											$total_amount = $response_ele['quantity'] * $easypost_rate->rate;
										}
										$total_amount = $total_amount * $converted_currency;
										// Sort
										if ( isset( $custom_services[ $carrier_name ][ $service_type ]['order'] ) ) {
											$sort = $custom_services[ $carrier_name ][ $service_type ]['order'];
										} else {
											$sort = 999;
										}

										if ( isset( $found_rates[ $service_type ] ) ) {
											$found_rates[ $service_type ]['cost'] = $found_rates[ $service_type ]['cost'] + $total_amount;
										} elseif ( empty( $labels_stored ) || in_array( $service_name, $labels_stored ) ) {
											$found_rates[ $service_type ]['label']         = $service_name;
											$found_rates[ $service_type ]['cost']          = $total_amount;
											$found_rates[ $service_type ]['carrier']       = $easypost_rate->carrier;
											$found_rates[ $service_type ]['sort']          = $sort;
											$found_rates[ $service_type ]['delivery_days'] = $easypost_rate->delivery_days;
										}

										if ( isset( $found_rates[ $service_type ]['label'] ) ) {
											$service_name_label [] = (string) $easypost_rate->service;
										}
									}
								}
							}
						} else {
							$this->debug( esc_attr( 'EasyPost.com - No rated returned from API.', 'wf-easypost' ) );
							// return;
						}
						if ( empty( $labels_stored ) ) {
							foreach ( $found_rates as $labels => $value ) {
								$labels_stored[] = $labels;
							}
						}

						if ( $domestic ) {
								$international = array(
									'FirstClassMailInternational' => 'First Class Mail International (USPS)',
									'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International (USPS)',
									'PriorityMailInternational' => 'Priority Mail International&#0174; (USPS)',
									'ExpressMailInternational' => 'Express Mail International (USPS)',
									'INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',
									'INTERNATIONAL_ECONOMY' => 'FedEx International Economy (FedEx)',
									'INTERNATIONAL_FIRST' => 'INTERNATIONAL_FIRST',
									'Express'             => 'Express (UPS)',
									'Expedited'           => 'Expedited (UPS)',
									'ExpressPlus'         => 'Express Plus (UPS)',
									'INTERNATIONAL_FIRST' => 'FedEx International First (FedEx)',
									'PriorityWorldwidePakIntl' => 'Priority Worldwide Pak Intl (CanadaPost)',
									'InternationalParcelSurface' => 'International Parcel Surface (CanadaPost)',
									'PriorityWorldwideParcelIntl' => 'Priority Worldwide Parcel Intl (CanadaPost)',
									'SmallPacketInternationalSurface' => 'Small Packet International Surface (CanadaPost)',
									'SmallPacketInternationalAir' => 'Small Packet International Air (CanadaPost)',
									'TrackedPacketInternational' => 'Tracked Packet International (CanadaPost)',
									'XpresspostInternational' => 'Xpresspost International (CanadaPost)',
								);
								foreach ( $found_rates as $key => $value ) {
									foreach ( $international as $rates => $values ) {
										if ( $key === $rates ) {
											unset( $found_rates[ $rates ] );
										}
									}
								}
						}

						$check = false;
						foreach ( $labels_stored as $labels => $value ) {
							foreach ( $service_name_label as $service => $value_service ) {
								if ( $value === $value_service ) {
									$check = true;
								}
							}
							if ( true !== $check ) {
								unset( $found_rates[ $value ] );
							}
							$check = false;
						}
					}
					$rate_added = 0;
					if ( $found_rates ) {
						uasort( $found_rates, array( $this, 'sort_rates' ) );
						foreach ( $this->carrier as $carrier_name ) {
							foreach ( $found_rates as $service_type => $found_rate ) {
								// Enabled check

								$insurance_amount = 0;
								if ( in_array( 'UPS', $this->carrier ) && in_array( 'UPSDAP', $this->carrier ) ) {
									if ( 'UPS' === $found_rate['carrier'] ) {
										continue;
									}
								}
								if ( $carrier_name === $found_rate['carrier'] ) {
									if ( isset( $custom_services[ $carrier_name ][ $service_type ] ) && empty( $custom_services[ $carrier_name ][ $service_type ]['enabled'] ) ) {
										continue;
									}
									$total_amount  = $found_rate['cost'];
									$delivery      = $found_rate['delivery_days'];
									$delivery_days = 0;
									if ( ! empty( $found_rate['delivery_date'] ) ) {
										$delivery_date = explode( 'T', $found_rate['delivery_date'] );
										$from          = date_create( gmdate( 'Y-m-d' ) );
										$to            = date_create( $delivery_date[0] );
										$diff          = date_diff( $to, $from );
										$delivery_days = $diff->days;
									}
									/*
									// Cost adjustment %
									if (!empty($custom_services[$carrier_name][$service_type]['adjustment_percent'])) {
										$total_amount = $total_amount + ( $total_amount * ( floatval($custom_services[$carrier_name][$service_type]['adjustment_percent']) / 100 ) );
									}
									// Cost adjustment
									if (!empty($custom_services[$carrier_name][$service_type]['adjustment'])) {
										$total_amount = $total_amount + floatval($custom_services[$carrier_name][$service_type]['adjustment']);
									}*/
									$labelName = ! empty( $rates_settings['services'][ $carrier_name ][ $service_type ]['name'] ) ? $rates_settings['services'][ $carrier_name ][ $service_type ]['name'] : $this->services[ $carrier_name ]['services'][ $service_type ];

									if ( ! $domestic ) {

										if ( is_array( $rates_settings['flat_rate_boxes_international_carrier'] ) && ! empty( $rates_settings['flat_rate_boxes_international_carrier'] ) ) {
											$label = '';

											foreach ( $this->services as $key => $code ) {

												foreach ( $code['services'] as $key => $value ) {
													if ( 'Express Mail International (USPS)' === $this->services[ $carrier_name ]['services'][ $service_type ] && in_array( 'priority_mail_express_international', $rates_settings['flat_rate_boxes_international_carrier'] ) ) {
														$label = ! empty( $rates_settings['flat_rate_boxes_text_international_express'] ) ? $rates_settings['flat_rate_boxes_text_international_express'] : 'USPS Flat Rate International Express';
													} elseif ( 'Priority Mail International&#0174; (USPS)' === $this->services[ $carrier_name ]['services'][ $service_type ] && in_array( 'priority_mail_international', $rates_settings['flat_rate_boxes_international_carrier'] ) ) {
														$label = ! empty( $rates_settings['flat_rate_boxes_text_international_mail'] ) ? $rates_settings['flat_rate_boxes_text_international_mail'] : 'USPS Flat Rate Priority Mail International';
													} elseif ( 'First Class Mail International (USPS)' === $this->services[ $carrier_name ]['services'][ $service_type ] && in_array( 'first_class_mail_international', $rates_settings['flat_rate_boxes_international_carrier'] ) ) {
														$label = ! empty( $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) ? $rates_settings['flat_rate_boxes_text_first_class_mail_international'] : 'USPS Flat Rate First-Class Mail International';
													}
												}
												break;
											}
										}
										if ( ! empty( $label ) ) {
											$rate = array(
												'id'       => (string) $this->id . ':' . $service_type . $rate_added,
												'label'    => (string) $label,
												'cost'     => (string) $total_amount,
												'meta_data' => array(
													'easypost_delivery_time' => $delivery + $lead_time,
													'easypost_delivery_date' => ! empty( $delivery_days ) ? $delivery_days + $lead_time : 0,
												),
												'calc_tax' => 'per_order',
											);
										}
									} else {
										$rate = array(
											'id'        => (string) $this->id . ':' . $service_type . $rate_added,
											'label'     => (string) $labelName,
											'cost'      => (string) $total_amount,
											'meta_data' => array(
												'easypost_delivery_time' => $delivery + $lead_time,
												'easypost_delivery_date' => ! empty( $delivery_days ) ? $delivery_days + $lead_time : 0,
											),
											'calc_tax'  => 'per_order',
										);
									}
									if ( isset( $rate ) && is_array( $rate ) && ! empty( $rate['cost'] ) ) {
										// Register the rate
										$checkout_rates[] = $rate;
										$this->add_rate( $rate );
										$rate_added++;
									}
								}
							}
						}
					}
				}
			}
		}
		if ( $this->enable_standard_services ) {
			// Get cart package details and proceed with GetRates.
			if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' ) {
				/**
				 * To get packages from multivendor
				 * 
				 * @since 2.1.0
				 */
				$package               = apply_filters( 'wf_filter_package_address', array( $package ), '', false );
				$package_requests_temp = array();
				$package_requests      = array();
				foreach ( $package as $key => $val ) {
					$package_requests_temp = $this->get_package_requests( $val );
					$package_requests[]    = $package_requests_temp[0];
				}
			} else {
				$package_requests = $this->get_package_requests( $package );
			}

			if ( isset( $data ) ) {
				foreach ( $package_requests as $key => $package_request ) {
					$package_requests[] = $package_request;
				}
			}
			libxml_use_internal_errors( true );
			if ( $package_requests ) {

				if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
					require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
				}
			
				$responses = array();
				foreach ( $package_requests as $key => $package_request ) {
					if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_allow_vedor_api_key' ) === 'yes' ) {
						$easypost_api_key = get_user_meta( $package_request['user']['ID'], 'vendor_easypost_api_key', true );
					} elseif ( 'Live' === $general_settings['api_mode'] ) {
						$easypost_api_key = $general_settings['api_key'];
						
					} else {
						$easypost_api_key = $general_settings['api_test_key'];
						
					}
				
					\EasyPost\EasyPost::setApiKey( $easypost_api_key );
					$responses[] = $this->get_result( $package_request );
				}
				if ( ! $responses ) {
					return false;
				}

				$found_rates   = array();
				$labels_stored = array();
		
				if ( is_array( $responses ) && ! empty( $responses ) ) {
					foreach ( $responses as $response_ele ) {
						$response_obj = isset( $response_ele['response'] ) ? $response_ele['response'] : '';
						if ( isset( $response_obj->rates ) && ! empty( $response_obj->rates ) ) {
							$service_name_label = array();
							foreach ( $this->carrier as $carrier_name ) {
								$flag_currency_convertion = false;
								foreach ( $response_obj->rates as $easypost_rate ) {
									if ( $carrier_name === $easypost_rate->carrier ) {
					
										/**
										 * Code snippet to use two different carrier accounts for domestic and international.
										 * 
										 * @since 1.1.0
										 */
										$manage_accounts = apply_filters( 'elex_easypost_two_different_carrier_account', false );
										if ( $manage_accounts ) {
											if ( isset( $manage_accounts[ $carrier_name ] ) ) {
												if ( $domestic && isset( $manage_accounts[ $carrier_name ]['domestic'] ) ) {
													if ( $easypost_rate->carrier_account_id !== $manage_accounts[ $carrier_name ]['domestic'] ) {
														continue;
													}
												}
												if ( ! $domestic && isset( $manage_accounts[ $carrier_name ]['international'] ) ) {
													if ( $easypost_rate->carrier_account_id !== $manage_accounts[ $carrier_name ]['international'] ) {
														continue;
													}
												}
											}
										}
										if ( false === $flag_currency_convertion ) {
											$from_currency            = $easypost_rate->currency;
											$to_currency              = get_woocommerce_currency();
											$converted_currency       = $this->xa_currency_converter( $from_currency, $to_currency, $easypost_rate->carrier );
											$flag_currency_convertion = true;
										}

										if ( ! $converted_currency ) {
											break;
										}

										$service_type = (string) $easypost_rate->service;
										$service_name = (string) ( isset( $custom_services[ $carrier_name ][ $service_type ]['name'] ) && ! empty( $custom_services[ $carrier_name ][ $service_type ]['name'] ) ) ? $custom_services[ $carrier_name ][ $service_type ]['name'] : $this->services[ $carrier_name ]['services'][ $service_type ];
									
										
										if ( 'retail' === $rates_settings['show_rates'] ) {
											$total_amount = $response_ele['quantity'] * $easypost_rate->retail_rate;
										} else {
											$total_amount = $response_ele['quantity'] * $easypost_rate->rate;
										}

										$total_amount = $total_amount * $converted_currency;
										// Sort
										if ( isset( $custom_services[ $carrier_name ][ $service_type ]['order'] ) ) {
											$sort = $custom_services[ $carrier_name ][ $service_type ]['order'];
										} else {
											$sort = 999;
										}

										if ( isset( $found_rates[ $service_type ] ) && $found_rates[ $service_type ]['carrier'] === $easypost_rate->carrier ) {
											$found_rates[ $service_type ]['cost'] = $found_rates[ $service_type ]['cost'] + $total_amount;
										} elseif ( empty( $labels_stored ) || in_array( $service_name, $labels_stored ) ) {
											$found_rates[ $service_type ]['label']         = $service_name;
											$found_rates[ $service_type ]['cost']          = $total_amount;
											$found_rates[ $service_type ]['carrier']       = $easypost_rate->carrier;
											$found_rates[ $service_type ]['sort']          = $sort;
											$found_rates[ $service_type ]['delivery_days'] = $easypost_rate->delivery_days;
										}
										if ( isset( $found_rates[ $service_type ]['label'] ) && $found_rates[ $service_type ]['carrier'] === $easypost_rate->carrier ) {
											$service_name_label [] = (string) $easypost_rate->service;
										}
									}
								}
							}
						} else {
							$this->debug( esc_attr( 'EasyPost.com - No rated returned from API.', 'wf-easypost' ) );
							// return;
						}
						if ( empty( $labels_stored ) ) {
							foreach ( $found_rates as $labels => $value ) {
								$labels_stored[] = $labels;
							}
						}

						if ( $domestic ) {
								$international = array(
									'FirstClassMailInternational' => 'First Class Mail International (USPS)',
									'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International (USPS)',
									'PriorityMailInternational' => 'Priority Mail International&#0174; (USPS)',
									'ExpressMailInternational' => 'Express Mail International (USPS)',
									'INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',
									'INTERNATIONAL_ECONOMY' => 'FedEx International Economy (FedEx)',
									'INTERNATIONAL_FIRST' => 'INTERNATIONAL_FIRST',
									'ExpressPlus'         => 'Express Plus (UPS)',
									'INTERNATIONAL_FIRST' => 'FedEx International First (FedEx)',
									'PriorityWorldwidePakIntl' => 'Priority Worldwide Pak Intl (CanadaPost)',
									'InternationalParcelSurface' => 'International Parcel Surface (CanadaPost)',
									'PriorityWorldwideParcelIntl' => 'Priority Worldwide Parcel Intl (CanadaPost)',
									'SmallPacketInternationalSurface' => 'Small Packet International Surface (CanadaPost)',
									'SmallPacketInternationalAir' => 'Small Packet International Air (CanadaPost)',
									'TrackedPacketInternational' => 'Tracked Packet International (CanadaPost)',
									'XpresspostInternational' => 'Xpresspost International (CanadaPost)',
								);
								foreach ( $found_rates as $key => $value ) {
									foreach ( $international as $ke => $val ) {
										if ( $key === $ke ) {
											unset( $found_rates[ $ke ] );
										}
									}
								}
						}

						$check = false;
						foreach ( $labels_stored as $labels => $value ) {
							foreach ( $service_name_label as $service => $value_service ) {
								if ( $value === $value_service ) {
									$check = true;
								}
							}
							if ( true !== $check ) {
								unset( $found_rates[ $value ] );
							}
							$check = false;
						}
					}
				}
				$rate_added = 0;
			
				if ( $found_rates ) {
					uasort( $found_rates, array( $this, 'sort_rates' ) );
					foreach ( $this->carrier as $carrier_name ) {
						foreach ( $found_rates as $service_type => $found_rate ) {
							// Enabled check
							$insurance_amount = 0;
							if ( in_array( 'UPS', $this->carrier ) && in_array( 'UPSDAP', $this->carrier ) ) {
								if ( 'UPS' === $found_rate['carrier'] ) {
									continue;
								}
							}

							if ( $carrier_name === $found_rate['carrier'] ) {
								if ( isset( $custom_services[ $carrier_name ][ $service_type ] ) && empty( $custom_services[ $carrier_name ][ $service_type ]['enabled'] ) ) {
									continue;
								}
								$total_amount  = $found_rate['cost'];
								$delivery      = $found_rate['delivery_days'];
								$delivery_days = 0;
								if ( ! empty( $found_rate['delivery_date'] ) ) {
									$delivery_date = explode( 'T', $found_rate['delivery_date'] );
									$from          = date_create( gmdate( 'Y-m-d' ) );
									$to            = date_create( $delivery_date[0] );
									$diff          = date_diff( $to, $from );
									$delivery_days = $diff->days;
								}
								// Cost adjustment %
								if ( ! empty( $custom_services[ $carrier_name ][ $service_type ]['adjustment_percent'] ) ) {
									$total_amount = $total_amount + ( $total_amount * ( floatval( $custom_services[ $carrier_name ][ $service_type ]['adjustment_percent'] ) / 100 ) );
								}
								// Cost adjustment
								if ( ! empty( $custom_services[ $carrier_name ][ $service_type ]['adjustment'] ) ) {
									$total_amount = $total_amount + floatval( $custom_services[ $carrier_name ][ $service_type ]['adjustment'] );
								}
								$labelName = ! empty( $rates_settings['services'][ $carrier_name ][ $service_type ]['name'] ) ? $rates_settings['services'][ $carrier_name ][ $service_type ]['name'] : $this->services[ $carrier_name ]['services'][ $service_type ];
									$rate  = array(
										'id'        => (string) $this->id . ':' . $service_type,
										'label'     => (string) $labelName,
										'cost'      => (string) $total_amount,
										'meta_data' => array(
											'easypost_delivery_time' => $delivery + $lead_time,
											'easypost_delivery_date' => ! empty( $delivery_days ) ? $delivery_days + $lead_time : 0,
										),
										'calc_tax'  => 'per_order',
									);
									// Register the rate
									$checkout_rates[] = $rate;
									if ( ! empty( $rate['cost'] ) ) {
										$this->add_rate( $rate );
									}
									$rate_added++;
							}
						}
					}
				}
			}
		}
		if ( $domestic ) {
			$flat_rate = array();
			$flat_rate_international = array();
			$international_flat = false;
			$domestic_flat = false;
			if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' ) {

				foreach ( $package as $key => $val ) {
				$domestic_country_vendor = isset( $val['origin']['country'] ) ? array( $val['origin']['country'] ) : array( 'US' );
					if ( ! in_array( $val['destination']['country'], $domestic_country_vendor ) ) {
						$flat_rate_international = array_merge( $flat_rate_international, $this->calculate_flat_rate_box_rate( $val, '', 'international', true ) );
						$international_flat = true;
					} else {
						$flat_rate = array_merge( $flat_rate, $this->calculate_flat_rate_box_rate( $val ) );
						$domestic_flat = true;
					}               
				}
			} else {
				$flat_rate = $this->calculate_flat_rate_box_rate( $package );
			}
			$cost_mail          = 0; // flat rate priority mail
			$cost_express       = 0;// flat rate priortiy express
			$cost_first_class   = 0; // flat rate first class mail
			$cost_fedex_onerate = 0; // flat rate first class mail
			if ( ! empty( $flat_rate_international ) ) {
				foreach ( $flat_rate_international as $key => $value ) {
					if ( 'mail' === $value['service'] ) {
						$cost_mail                                    = $cost_mail + $value['cost'];
						$value['cost']                                = $cost_mail;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:mail_international'] = $value;
					}
					if ( 'express' === $value['service'] ) {
						$cost_express                                 = $cost_express + $value['cost'];
						$value['cost']                                = $cost_express;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:express_international'] = $value;
					}
					if ( 'first' === $value['service'] ) {
						$cost_first_class                             = $cost_first_class + $value['cost'];
						$value['cost']                                = $cost_first_class;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:first_class_mail_international'] = $value;
					}
				}
			} elseif ( $flat_rate ) {
				foreach ( $flat_rate as $key => $value ) {
					if ( 'mail' === $value['service'] ) {
						$cost_mail                                    = $cost_mail + $value['cost'];
						$value['cost']                                = $cost_mail;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:mail'] = $value;
					}
					if ( 'express' === $value['service'] ) {
						$cost_express                                 = $cost_express + $value['cost'];
						$value['cost']                                = $cost_express;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:express'] = $value;
					}
					if ( 'first' === $value['service'] ) {
						$cost_first_class                             = $cost_first_class + $value['cost'];
						$value['cost']                                = $cost_first_class;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates['wf_easypost_id:easy_post_flat_rate:first'] = $value;
					}
					if ( ! empty( $value['carrier_name'] ) && 'FedEx' === $value['carrier_name'] ) {
						$cost_fedex_onerate                           = $cost_fedex_onerate + $value['cost'];
						$value['cost']                                = $cost_fedex_onerate;
						$value['meta_data']['easypost_delivery_time'] = $value['meta_data']['easypost_delivery_time'] + $lead_time;
						$found_flat_rates[]                           = $value;
					}
					$cost_fedex_onerate = 0;

				}
			}
			if ( ! $international_flat && ! $domestic_flat ) {
				if ( isset( $found_flat_rates ) && is_array( $found_flat_rates ) ) {
					foreach ( $found_flat_rates as $found_flat_rate => $rate ) {
						$checkout_rates[] = $rate;
						$this->add_rate( $rate );
						$rate_added++;
					}
				}
			} elseif ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' && ! ( $international_flat && $domestic_flat ) ) {
				if ( isset( $found_flat_rates ) && is_array( $found_flat_rates ) ) {
					foreach ( $found_flat_rates as $found_flat_rate => $rate ) {
						$checkout_rates[] = $rate;
						$this->add_rate( $rate );
						$rate_added++;
					}
				}
			}       
		}
		if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) === 'sum_cart' ) {
			foreach ( $package as $key => $val ) {
				$session_data = array(
					'package_rates'   => $checkout_rates,
					'package_country' => $val['destination']['country'],
					'package_zip'     => $val['destination']['postcode'],
					'expiration_time' => time() + ( 1 * 1 * 10 * 60 ),
				);
				$woocommerce->session->set( 'checkout_page_data', $session_data );
				break;
			}
		} else {
			$session_data = array(
				'package_rates'   => $checkout_rates,
				'package_country' => $package['destination']['country'],
				'package_zip'     => $package['destination']['postcode'],
				'expiration_time' => time() + ( 1 * 1 * 10 * 60 ),
			);
			$woocommerce->session->set( 'checkout_page_data', $session_data );
		}
		
	

	}
	/**
	 * Function to check the address fields.
	 */
	public function elex_check_address_fields( $package ) {

		// check
		if ( ! isset( $package['origin'] ) && ! isset( $package['request'] ) ) {
			return false;
		}
		// package check with multi vendor package
		if ( isset( $package['origin'] ) ) {
			if ( '' !== $package['origin']['country'] && '' !== $package['origin']['postcode'] ) {
				return true;
			} else {
				$this->debug( esc_attr( 'To process the shipping rate destination country and postcode are mandatory', 'wf-easypost' ) );
				return false;
			}
		}
		// address check
		if ( isset( $package['request'] ) ) {
			if ( '' !== $package['request']['Rate']['ToZIPCode'] && '' !== $package['request']['Rate']['ToCountry'] ) {
				return true;
			} else {
				$this->debug( esc_attr( 'To process the shipping rate destination country and postcode are mandatory', 'wf-easypost' ) );
				return false;
			}
		}

	}
	public function xa_currency_converter( $from_currency, $to_currency, $carrier ) {
		if ( $from_currency === $to_currency ) {
			 return 1;
		} else {
			$from_currency = urlencode( $from_currency );
			$to_currency   = urlencode( $to_currency );
			try {
				$result = @file_get_contents( "https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=$from_currency&to_currency=$to_currency&apikey=G1QF4V7WM07HNOB2" );
				if ( false === $result ) {
					throw new Exception( "Unable to receive currency conversion response from Alpha Vantage API call ( https://www.alphavantage.co ). Skipping the shipping rates for the carrier $carrier as shop currency and the currency returned by Rates API differs." );
				}
			} catch ( Exception $e ) {
				 $this->debug( esc_attr( $e->getMessage(), 'wf-easypost' ) );
				 return 0;
			}
			$result = json_decode( $result, true );
			if ( is_array( $result ) && ! empty( $result['Realtime Currency Exchange Rate']['5. Exchange Rate'] ) && isset( $result['Realtime Currency Exchange Rate']['5. Exchange Rate'] ) ) {
				$converted_currency = $result['Realtime Currency Exchange Rate']['5. Exchange Rate'];
			} else {
				$converted_currency = 1;
			}
			return $converted_currency;
		}
	}


	private function get_result( $package_request, $flat_rate_service_international = '', $vendor_origin_address = '' ) {
		// Get rates.
		 $rates_settings     = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		 $label_settings     = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		 $title              = ! empty( $rates_settings['title'] ) ? $rates_settings['title'] : $this->method_title;
		 $zip                = isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '';
		 $resid              = isset( $rates_settings['show_rates'] ) && 'residential' === $rates_settings['show_rates'] ? true : '';
		 $senderName         = isset( $label_settings['name'] ) ? $label_settings['name'] : '';
		 $senderCompanyName  = isset( $label_settings['company'] ) ? $label_settings['company'] : '';
		 $senderAddressLine1 = isset( $label_settings['street1'] ) ? $label_settings['street1'] : '';
		 $senderAddressLine2 = isset( $label_settings['street2'] ) ? $label_settings['street2'] : '';
		 $senderCity         = isset( $label_settings['city'] ) ? $label_settings['city'] : '';
		 $senderState        = isset( $rates_settings['state'] ) ? $rates_settings['state'] : '';
		 // adding country
		 $senderCountry                 = isset( $rates_settings['country'] ) ? $rates_settings['country'] : '';
		 $ioss_number                   = isset( $label_settings['ioss_number'] ) ? $label_settings['ioss_number'] : '';
		 $senderEmail                   = isset( $label_settings['email'] ) ? $label_settings['email'] : '';
		 $senderPhone                   = isset( $label_settings['phone'] ) ? $label_settings['phone'] : '';
		
		$customs_signer                 = isset( $label_settings['customs_signer'] ) ? $label_settings['customs_signer'] : '';
		$fallback                       = ! empty( $rates_settings['fallback'] ) ? $rates_settings['fallback'] : '';
		$duty_payer                     = isset( $rates_settings['ex_easypost_duty'] ) ? $rates_settings['ex_easypost_duty'] : '';
		$signature_check                = isset( $label_settings['signature_option'] ) ? $label_settings['signature_option'] : 'no';
		$address_check                  = $this->elex_check_address_fields( $package_request );

		if ( $address_check ) {
			try {
				$payload = array();
				// Multi-vendor Support
				if ( isset( $package_request['origin'] ) && '' !== $package_request['origin']['postcode'] ) {
					$payload['from_address'] = array(
						'name'    => $package_request['origin']['first_name'],
						'company' => $package_request['origin']['company'],
						'street1' => $package_request['origin']['address_1'],
						'street2' => $package_request['origin']['address_2'],
						'city'    => $package_request['origin']['city'],
						'state'   => $package_request['origin']['state'],
						'zip'     => $package_request['origin']['postcode'],
						// adding country
						'country' => $package_request['origin']['country'],
						'phone'   => $package_request['origin']['phone'],
					);
				} elseif ( ! empty( $vendor_origin_address ) ) {
					$payload['from_address'] = array(
						'name'    => $vendor_origin_address['first_name'],
						'company' => $vendor_origin_address['company'],
						'street1' => $vendor_origin_address['address_1'],
						'street2' => $vendor_origin_address['address_2'],
						'city'    => $vendor_origin_address['city'],
						'state'   => $vendor_origin_address['state'],
						'zip'     => $vendor_origin_address['postcode'],
						// adding country
						'country' => $vendor_origin_address['country'],
						'phone'   => $vendor_origin_address['phone'],
					);
				} else {
					$payload['from_address'] = array(
						'name'    => $senderName,
						'company' => $senderCompanyName,
						'street1' => $senderAddressLine1,
						'street2' => $senderAddressLine2,
						'city'    => $senderCity,
						'state'   => $senderState,
						'zip'     => $zip,
						// adding country
						'country' => $senderCountry,
						'phone'   => $senderPhone,
					);
				}
				$payload['to_address'] = array(
					// Name and Street1 are required fields for getting rates.
					// But, at this point, these details are not available.
					'name'        => '-',
					'street1'     => '-',
					'residential' => $resid,
					'zip'         => $package_request['request']['Rate']['ToZIPCode'],
					'country'     => $package_request['request']['Rate']['ToCountry'],
				);

				if ( ! empty( $package_request['request']['Rate']['WeightLb'] ) && 0.00 === $package_request['request']['Rate']['WeightOz'] ) {
					$package_request['request']['Rate']['WeightOz'] = number_format( $package_request['request']['Rate']['WeightLb'] * 16, 0, '.', '' );
				}
				if ( $flat_rate_service_international ) {
					$flat_rate_service_international = rtrim( $flat_rate_service_international, '-2' );
					$payload['parcel']               = array(
						'length'             => $package_request['request']['Rate']['Length'],
						'width'              => $package_request['request']['Rate']['Width'],
						'height'             => $package_request['request']['Rate']['Height'],
						'weight'             => $package_request['request']['Rate']['WeightOz'],
						'predefined_package' => $flat_rate_service_international,
					);
				} else {
					$payload['parcel'] = array(
						'length' => $package_request['request']['Rate']['Length'],
						'width'  => $package_request['request']['Rate']['Width'],
						'height' => $package_request['request']['Rate']['Height'],
						'weight' => $package_request['request']['Rate']['WeightOz'],
					);
				}
				if ( isset( $package_request['request']['Rate']['dry_ice'] ) && 'yes' === $package_request['request']['Rate']['dry_ice'] ) {
					$payload['options'] = array(
						'special_rates_eligibility' => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL',
						'dry_ice'                   => 'true',
						'dry_ice_weight'            => $package_request['request']['Rate']['WeightOz'],

					);
				} else {
						 $payload['options'] = array(
							 'special_rates_eligibility' => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL',
						 );
				}

				if ( 'yes' === $signature_check || isset( $package_request['request']['Rate']['signature'] ) && 2 === $package_request['request']['Rate']['signature'] ) {
					 $payload['options']['delivery_confirmation'] = 'ADULT_SIGNATURE';
				}

				if ( ( $package_request['request']['Rate']['ToCountry'] !== $senderCountry ) && ( 'none' !== $duty_payer ) ) {
					$payload['options']['incoterm'] = $duty_payer;
				}
			
				// Only Canada Post International Shipping

				if ( 'CA' === $payload['from_address']['country'] && $payload['from_address']['country'] !== $payload['to_address']['country'] ) {

					$payload['customs_info']['customs_certify']      = true;
					$payload['customs_info']['customs_signer']       = $customs_signer;
					$payload['customs_info']['contents_type']        = 'merchandise';
					$payload['customs_info']['contents_explanation'] = '';
					$payload['customs_info']['restriction_type']     = 'none';
					$payload['customs_info']['eel_pfc']              = 'NOEEI 30.37(a)';
					$payload['customs_info']['customs_items']        = array();
					if ( isset( $package_request ['request']['products_info'] ) && ! empty( $package_request ['request']['products_info'] ) ) {
						$payload['customs_info']['customs_items'] = $package_request ['request']['products_info'];
					}
				}

				$this->debug( 'EASYPOST REQUEST: <pre>' . print_r( $payload, true ) . '</pre>' );
				$shipment = \EasyPost\Shipment::create( $payload );
				$response = json_decode( $shipment );
				$this->debug( 'EASYPOST RESPONSE: <pre>' . print_r( $response, true ) . '</pre>' );
				$response_ele             = array();
				$response_ele['response'] = $response;
				$response_ele['quantity'] = $package_request['quantity'];
			} catch ( Exception $e ) {

				if ( ! empty( $e->getMessage() ) ) {
					if ( $fallback ) {
						$this->debug( esc_attr( 'EasyPost.com - Calculating fall back rates', 'wf-easypost' ) );
						$rate = array(
							'id'       => (string) $this->id . ':_fallback',
							'label'    => (string) $title,
							'cost'     => $fallback,
							'calc_tax' => 'per_item',
						);
						// Register the rate
						$this->add_rate( $rate );
					}
				}

				$this->debug( esc_attr( 'EasyPost.com - Unable to Get Rates: ', 'wf-easypost' ) . $e->getMessage() );
				if ( WF_EASYPOST_ADV_DEBUG_MODE === 'on' ) {
					$this->debug( print_r( $e, true ) );
				}
				return false;
			}

			return $response_ele;
		} else {
			return false;
		}

	}



	/**
	 * Sort_rates function.
	 *
	 * @param mixed $sort_rate
	 * @param mixed $sort_rater
	 * @return void
	 */
	public function sort_rates( $sort_rate, $sort_rater ) {
		if ( $sort_rate['sort'] === $sort_rater['sort'] ) {
			return 0;
		}
		return ( $sort_rate['sort'] < $sort_rater['sort'] ) ? -1 : 1;
	}

	/**
	 * Get_request function.
	 *
	 * @return void
	 */
	// WF - Changing function to public.
	public function get_package_requests( $package ) {
		$packing_settings = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$packing_method   = isset( $packing_settings['packing_method'] ) ? $packing_settings['packing_method'] : 'per_item';
		// Choose selected packing
		switch ( $packing_method ) {
			case 'box_packing':
				$requests = $this->box_shipping( $package );
				break;
			case 'weight_based_packing':
				$requests = $this->weight_based_shipping( $package );
				break;
			case 'per_item':
			default:
				$requests = $this->per_item_shipping( $package );
				break;
		}

		return $requests;
	}
	/**
	 * Weight_based_shipping function.
	 *
	 * @param mixed $package
	 * @return void
	 */
	private function weight_based_shipping( $package ) {
		if ( ! class_exists( 'WeightPack' ) ) {
			include_once 'weight_pack/class-wf-weight-packing.php';
		}
		$packing_settings       = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$rates_settings         = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$domestic_country       = isset( $rates_settings['country'] ) ? array( $rates_settings['country'] ) : array( 'US' );
		$weight_packing_process = isset( $packing_settings['weight_packing_process'] ) ? $packing_settings['weight_packing_process'] : 'pack_descending';
		$box_max_weight         = isset( $packing_settings['box_max_weight'] ) ? $packing_settings['box_max_weight'] : 10;
		$domestic               = in_array( $package['destination']['country'], $domestic_country ) ? true : false;

		$weight_pack = new WeightPack( $weight_packing_process );

		if ( WC()->version < '3.0' ) {
			$weight_pack->set_max_weight( woocommerce_get_weight( $box_max_weight, 'lbs' ) );
		} else {
			$weight_pack->set_max_weight( wc_get_weight( $box_max_weight, 'lbs' ) );
		}
		if ( ! is_cart() && ! is_checkout() ) {
			$box_package = $this->elex_calculate_flat_rate_box_wc_order_page( $package );
		}
		
		$count = 0;

		foreach ( $package['contents'] as $item_id => $values ) {
			++$count;

			$values['data'] = $this->wf_load_product( $values['data'] );
			if ( empty( $values['data'] ) || ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( esc_attr( 'Product # is virtual. Skipping.', 'wf-easypost' ), $item_id ), 'error' );
				continue;
			}

				$package_weight = $values['data']->get_weight();

			if ( ! $package_weight ) {
				$this->debug( sprintf( esc_attr( 'Product # is missing weight. Aborting.', 'wf-easypost' ), $item_id ), 'error' );
				return;
			}

			if ( empty( $package_weight ) ) {
				$package_weight = 0;
			}

			if ( WC()->version < '3.0' ) {
				$weight_pack->add_item( woocommerce_get_weight( $package_weight, 'lbs' ), $values['data'], $values['quantity'] );
			} else {
				$weight_pack->add_item( wc_get_weight( $package_weight, 'lbs' ), $values['data'], $values['quantity'] );
			}
		}

		$pack   = $weight_pack->pack_items();
		$errors = $pack->get_errors();
		if ( ! empty( $errors ) ) {
			// do nothing
			return;
		} else {
			$boxes                       = $pack->get_packed_boxes();
			$unpacked_items              = $pack->get_unpacked_items();
			$request                     = array();
			$insured_value               = 0;
			$insured_value_international = 0;
			$packages                    = array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
			foreach ( $packages as $new_package ) {
				$insured_value = 0;
				if ( isset( $new_package['items'] ) && is_array( $new_package['items'] ) ) {
					$product_info  = array();
					$check_no_item = true;
					foreach ( $new_package['items'] as $item ) {
						$insured_value += $item->get_price();
						if ( ! $domestic ) {
							$product_custom_declared_value = get_post_meta( $item->id, '_wf_easypost_custom_declared_value', true );

							if ( $product_custom_declared_value && $check_no_item ) {
								$insured_value_international = $product_custom_declared_value;
								$check_no_item               = false;
							} elseif ( $product_custom_declared_value && ! $check_no_item ) {
								$insured_value_international += $product_custom_declared_value;
							} elseif ( $check_no_item ) {
								$insured_value_international = $item->get_price();
								$check_no_item               = false;
							} elseif ( ! $check_no_item ) {
								$insured_value_international += $item->get_price();
							}
						}
						if ( isset( $product_info [ $item->id ] ) ) {
							$product_info [ $item->id ]['quantity'] ++;
						} else {
							$product_info [ $item->id ] = array(

								'description' => $item->obj->get_title(),
								'value'       => $item->obj->get_price(),
								'quantity'    => 1,
								'weight'      => wc_get_weight( $item->obj->get_weight(), 'oz' ),
							);
							$hs_tariff_number           = get_post_meta( $values['product_id'], '_wf_hs_code', 1 );
							if ( $hs_tariff_number ) {
								$product_info [ $item->id ]['hs_tariff_number'] = $hs_tariff_number;
							}
						}
					}
				}
				if ( 'pack_simple' === $packing_settings['weight_packing_process'] ) {
					$insured_value = $new_package['price'];
				}
				$weight           = $new_package['weight'];
				$dest_postal_code = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
				if ( $domestic ) {
					$insured_value_amount = $insured_value;
				} else {
					$insured_value_amount = $insured_value_international;
				}
				   $request['Rate'] = array(
					   'FromZIPCode'  => str_replace( ' ', '', strtoupper( $rates_settings['zip'] ) ),
					   'ToZIPCode'    => $dest_postal_code,
					   'ToCountry'    => $package['destination']['country'],
					   'Amount'       => $insured_value_amount,
					   'WeightLb'     => floor( $weight ),
					   'WeightOz'     => round( $weight * 16, 2 ),
					   'Length'       => '',
					   'Width'        => '',
					   'Height'       => '',
					   'PackageType'  => 'package',
					   'ShipDate'     => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
					   'InsuredValue' => ( isset( $insured_value_amount ) && $insured_value_amount > 1 ) ? $insured_value_amount : '',

				   );

				   $request['packed']       = $pack->get_packed_boxes();
				   $request_ele             = array();
				   $request_ele['request']  = $request;
				   $request_ele['quantity'] = 1;
				   // Multi-vendor Support
				   if ( isset( $package['origin'] ) ) {
					   $request_ele['origin'] = $package['origin'];
				   }
				   if ( isset( $package['user'] ) ) {
					   $request_ele['user'] = $package['user'];
				   }

				   if ( isset( $new_package['items'] ) && is_array( $new_package['items'] ) ) {
						true;
				   } else { // Packages purely devided by weight will not have packed items info
					   $order_items = $package['contents'];

					   $total_weight   = 0;
					   $total_quantity = 0;
					   $total_value    = 0;
					   foreach ( $order_items as $order_item ) {
						   $product_data = $order_item['data'];

						   $title  = $product_data->get_title();
						   $weight = wc_get_weight( $product_data->get_weight(), 'lbs' );

						   $shipment_description = $title;
						   $shipment_description = ( strlen( $shipment_description ) >= 50 ) ? substr( $shipment_description, 0, 45 ) . '...' : $shipment_description;
						   $quantity             = $order_item['quantity'];

						   $total_quantity = $total_quantity + $quantity;
						   $total_weight   = $total_weight + $quantity * $weight;
						   $total_value    = $total_value + $quantity * $product_data->get_price();
					   }

					   $no_of_packages = count( $packages );

					   $line_weight    = $total_weight / $no_of_packages;
					   $line_weight_lb = floor( $line_weight );
					   $line_weight_oz = ( $line_weight - floor( $line_weight ) ) * 16;

					   $line_price = $total_value / $no_of_packages;

					   $country_of_origin = WC()->countries->get_base_country();

					   $custom_line                = array();
					   $custom_line['Description'] = $shipment_description;
					   $custom_line['Quantity']    = 1;
					   $custom_line['Value']       = $line_price;

					   $custom_line['WeightLb']        = (string) $line_weight_lb;
					   $custom_line['WeightOz']        = (string) $line_weight_oz;
					   $custom_line['CountryOfOrigin'] = $country_of_origin;

					   $request_ele['line_items'] = array( $custom_line );
				   }
				   if ( isset( $product_info ) && ! empty( $product_info ) ) {
					   $request_ele['request']['products_info'] = array_values( $product_info );
				   }

				   $requests[] = $request_ele;
			}
		}

		return $requests;
	}


	// Function to get all the possible flat rate boxes.
	public function elex_calculate_flat_rate_box_wc_order_page( $package ) {

		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		if ( ! empty( $package['title'] ) ) {
			if ( $package['title'] === $rates_settings['flat_rate_boxes_text'] || $package['title'] === $rates_settings['flat_rate_boxes_express_text'] || $package['title'] === $rates_settings['flat_rate_boxes_first_class_text'] || strpos( $package['title'], $rates_settings['flat_rate_boxes_fedex_one_rate_text'] ) !== false ) {
				$box          = $this->calculate_flat_rate_box_rate( $package, $package['orderId'] );
				$box_packages = get_option( 'easypost_flat_rate_box', true );
			} elseif ( $package['title'] === $rates_settings['flat_rate_boxes_text_international_mail'] || $package['title'] === $rates_settings['flat_rate_boxes_text_international_express'] || $package['title'] === $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) {
				$box          = $this->calculate_flat_rate_box_rate( $package, $package['orderId'], 'international' );
				$box          = $this->box_name;
				$box_packages = get_option( 'easypost_flat_rate_box', true );

			} else {
				return array();
			}
			return $box_packages;
		}
	}

	/**
	 * Per_item_shipping function.
	 *
	 * @param mixed $package
	 * @return void
	 */
	private function per_item_shipping( $package ) {    
		global $woocommerce;
		$requests         = array();
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$domestic_country = isset( $rates_settings['country'] ) ? array( $rates_settings['country'] ) : array( 'US' );
		$domestic         = in_array( $package['destination']['country'], $domestic_country ) ? true : false;
		// Get weight of order
		if ( ! is_cart() && ! is_checkout() ) {
				$box_package = $this->elex_calculate_flat_rate_box_wc_order_page( $package );
		}

		$count = 0;
		if ( is_array( $package ) && ! empty( $package ) ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				// dry ice
				$dry_ice   = get_post_meta( $values['data']->get_ID(), '_wf_dry_ice_code', true );
				$signature = get_post_meta( $values['data']->get_ID(), '_wf_easypost_signature', true );

				$values['data'] = $this->wf_load_product( $values['data'] );

				if ( empty( $values['data'] ) || ! $values['data']->needs_shipping() ) {
					$this->debug( sprintf( esc_attr( 'Product # is virtual. Skipping.', 'wf-easypost' ), $item_id ) );
					continue;
				}

				if ( ! $values['data']->get_weight() ) {
					$this->debug( sprintf( esc_attr( 'Product # is missing weight. Using 1lb.', 'wf-easypost' ), $item_id ) );

					$weight   = 1;
					$weightoz = 1; // added for default
				} else {
					$weight   = wc_get_weight( $values['data']->get_weight(), 'lbs' );
					$weightoz = wc_get_weight( $values['data']->get_weight(), 'oz' );
				}

				$size = 'REGULAR';

				if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

					$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

					sort( $dimensions );

					if ( max( $dimensions ) > 12 ) {
						$size = 'LARGE';
					}

					$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
				} else {
					$dimensions = array( '', '', '' );
					$girth      = 0;
				}

				$quantity = $values['quantity'];

				if ( 'LARGE' === $size ) {
					$rectangular_shaped = 'true';
				} else {
					$rectangular_shaped = 'false';
				}
				$dest_postal_code = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
				if ( ! $domestic ) {
					$product_custom_declared_value = get_post_meta( $item_id, '_wf_easypost_custom_declared_value', true );
					if ( $product_custom_declared_value ) {
						$insured_value = $product_custom_declared_value;
						$amount        = $product_custom_declared_value;
					} else {
						$insured_value = $values['data']->get_price();
						$amount        = $values['data']->get_price();
					}
				}
				if ( isset( $flat_rate_box_name ) ) {
					$selected_flat_rate_boxes = include 'data-wf-flat-rate-boxes.php';
					foreach ( $selected_flat_rate_boxes as $carrierr => $boxes ) {
						foreach ( $boxes as $box_no => $box ) {
							if ( 'USPS:' . $box_no === $flat_rate_box_name[ $count ] ) {
								$dimensions[0] = $selected_flat_rate_boxes['USPS'][ $box_no ]['height'];
								$dimensions[1] = $selected_flat_rate_boxes['USPS'][ $box_no ]['width'];
								$dimensions[2] = $selected_flat_rate_boxes['USPS'][ $box_no ]['length'];
							}
						}
					}
				}

				$count++;
				if ( $domestic ) {
					$insured_value_amount = $values['data']->get_price();
					$amount_val           = $values['data']->get_price();
				} else {
					$insured_value_amount = $insured_value;
					$amount_val           = $amount;
				}
					$request['Rate'] = array(
						'FromZIPCode'       => str_replace( ' ', '', strtoupper( $rates_settings['zip'] ) ),
						'ToZIPCode'         => $dest_postal_code,
						'Amount'            => $amount_val,
						'ToCountry'         => $package['destination']['country'],
						'WeightLb'          => floor( $weight ),
						'WeightOz'          => round( $weightoz, 2 ),
						'PackageType'       => 'Package',
						'Length'            => $dimensions[2],
						'Width'             => $dimensions[1],
						'Height'            => $dimensions[0],
						'dry_ice'           => $dry_ice,
						'signature'         => $signature,
						'ShipDate'          => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
						'InsuredValue'      => $insured_value_amount,
						'RectangularShaped' => $rectangular_shaped,
					);

					$request['unpacked'] = array();
					$request['packed']   = array( $values['data'] );

					$request_ele             = array();
					$request_ele['request']  = $request;
					$request_ele['quantity'] = $quantity;

					// Multi-vendor Support
					if ( isset( $package['origin'] ) ) {
						$request_ele['origin'] = $package['origin'];
					}
					if ( isset( $package['user'] ) ) {
						$request_ele['user'] = $package['user'];
					}
					$customs_items                              = array();
					$customs_items[ $values['data']->get_ID() ] = array(
						'description' => $values['data']->get_title(),
						'value'       => $values['data']->get_price(),
						'quantity'    => 1,
						'weight'      => wc_get_weight( $values['data']->get_weight(), 'oz' ),
					);
					$hs_tariff_number                           = get_post_meta( $values['data']->get_ID(), '_wf_hs_code', 1 );
					if ( $hs_tariff_number ) {
						$customs_items[ $values['data']->get_ID() ] ['hs_tariff_number'] = $hs_tariff_number;
					}
					$request_ele['request']['products_info'] = array_values( $customs_items );
					$requests[]                              = $request_ele;
			}
		}

		return $requests;
	}

	/**
	 * Generate a package ID for the request
	 *
	 * Contains qty and dimension info so we can look at it again later when it comes back from USPS if needed
	 *
	 * @return string
	 */
	public function generate_package_id( $id, $qty, $length, $width, $height, $weight ) {
		return implode( ':', array( $id, $qty, $length, $width, $height, $weight ) );
	}

	public function elex_ep_volume_based_packing( $package, $mode, $boxes ) {
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$domestic_country = isset( $rates_settings['country'] ) ? array( $rates_settings['country'] ) : array( 'US' );
		$domestic         = in_array( $package['destination']['country'], $domestic_country ) ? true : false;
		if ( 'stack_first' === isset( $mode ) && $mode ) {
			$boxpack = new WF_Boxpack_Stack();
			$this->debug( esc_attr( 'Packed based on Stack First', 'wf-easypost' ) );
		} else {
			$boxpack = new WF_Boxpack();
			$this->debug( esc_attr( 'Packed based on Volume Based', 'wf-easypost' ) );
		}

		// Define boxes
		foreach ( $boxes as $key => $box ) {

			$newbox = $boxpack->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );

			$newbox->set_id( isset( $box['name'] ) ? $box['name'] : $key );
			$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );

			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
		}

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {
			$values['data'] = $this->wf_load_product( $values['data'] );

			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}

			if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {

				$dimensions = array( $values['data']->length, $values['data']->height, $values['data']->width );
			} else {
				$this->debug( sprintf( esc_html( 'Product #%d is missing dimensions. Using 1x1x1.', 'wf-easypost' ), $item_id ), 'error' );

				$dimensions = array( 1, 1, 1 );
			}
			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$dry_ice   = get_post_meta( $item_id, '_wf_dry_ice_code', true );
				$signature = get_post_meta( $values['data']->get_ID(), '_wf_easypost_signature', true );
				if ( ! $domestic ) {
					$product_custom_declared_value = get_post_meta( $item_id, '_wf_easypost_custom_declared_value', true );
					if ( $product_custom_declared_value ) {
						$product_price = $product_custom_declared_value;
					} else {
						$product_price = $values['data']->get_price();
					}
				} else {
					$product_price = $values['data']->get_price();
				}
				$wtLB = wc_get_weight( $values['data']->get_weight(), 'lbs' );
				$boxpack->add_item(
					wc_get_dimension( $dimensions[2], 'in' ),
					wc_get_dimension( $dimensions[1], 'in' ),
					wc_get_dimension( $dimensions[0], 'in' ),
					$wtLB,
					$product_price,
					$values['data']->get_id() // WF: Adding Item Id and Quantity as meta.
				);
			}
		}
		$box_package_flat = $this->elex_calculate_flat_rate_box_wc_order_page( $package );

		// Pack it
		$boxpack->pack();
		// Get packages
		$box_packages = $boxpack->get_packages();
		// Check remaining space in box by using stack first
		if ( 'stack_first' === isset( $mode ) && $mode ) {
			foreach ( $box_packages as $key => $box_package ) {
				$box_volume                 = $box_package->length * $box_package->width * $box_package->height;
				$box_used_volume            = ! empty( $box_package->volume ) ? $box_package->volume : 0;
				$box_used_volume_percentage = ( $box_used_volume * 100 ) / $box_volume;
				if ( $box_used_volume_percentage < 44 && 0 === $key ) {
					$mode = 'volume_based';
					$this->debug( esc_attr( '(FALLBACK) : Stack First Option changed to Volume Based' ) );

				}
			}
		}

		return $box_packages;
	}
	/**
	 * Box_shipping function.
	 *
	 * @param mixed $package
	 * @return void
	 */
	private function box_shipping( $package ) {
		global $woocommerce;
		$packing_settings = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$domestic_country = isset( $rates_settings['country'] ) ? array( $rates_settings['country'] ) : array( 'US' );
		$fallback         = ! empty( $rates_settings['fallback'] ) ? $rates_settings['fallback'] : '';
		$mode             = isset( $packing_settings['packing_algorithm'] ) ? $packing_settings['packing_algorithm'] : 'volume_based';
		$boxes            = isset( $packing_settings['boxes'] ) ? $packing_settings['boxes'] : array();
		$requests         = array();
		$domestic         = in_array( $package['destination']['country'], $domestic_country ) ? true : false;

		if ( ! class_exists( 'WF_Boxpack' ) ) {
			include_once 'class-wf-packing.php';
		}
		if ( ! class_exists( 'WF_Boxpack_Stack' ) ) {
			include_once 'class-wf-packing-stack.php';
		}

		// algorithm check
		$box_packages = $this->elex_ep_volume_based_packing( $package, $mode, $boxes );
		$dry_ice_option = '';
		$signature_option = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$values['data'] = $this->wf_load_product( $values['data'] );
			$dry_ice   = get_post_meta( $values['data']->get_ID(), '_wf_dry_ice_code', true );
			$signature = get_post_meta( $values['data']->get_ID(), '_wf_easypost_signature', true );
			if ( 'yes' === $dry_ice ) {
				$dry_ice_option = 'yes';
			} if ( '2' === $signature ) {
				$signature_option = 2;
			}
		}
	
		foreach ( $box_packages as $key => $box_package ) {
		
			if ( ! empty( $box_package->unpacked ) ) {
				$this->debug( 'Unpacked Item' );

				
			} if ( ! empty( $box_package->packed ) ) {
				$this->debug( 'Packed ' . $box_package->id );
			}

			$weight     = number_format( $box_package->weight * 16, 2, '.', '' );
			$size       = 'REGULAR';
			$dimensions = array( $box_package->length, $box_package->width, $box_package->height );

			sort( $dimensions );

			if ( max( $dimensions ) > 12 ) {
				$size = 'LARGE';
			}

			$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];

			if ( 'LARGE' === $size ) {
				$rectangular_shaped = 'true';
			} else {
				$rectangular_shaped = 'false';
			}
			$request          = array();
			$dest_postal_code = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
			
				$request['Rate'] = array(
					'FromZIPCode'       => str_replace( ' ', '', strtoupper( $rates_settings['zip'] ) ),
					'ToZIPCode'         => $dest_postal_code,
					'ToCountry'         => $package['destination']['country'],
					'Amount'            => $box_package->value,
					'WeightOz'          => round( $weight, 2 ),
					'PackageType'       => 'Package',
					'Length'            => $dimensions[2],
					'Width'             => $dimensions[1],
					'Height'            => $dimensions[0],
					'Value'             => $box_package->value,
					'dry_ice'           => $dry_ice_option,
					'signature'         => $signature_option,
					'ShipDate'          => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
					'InsuredValue'      => $box_package->value,
					'RectangularShaped' => $rectangular_shaped,
				);

				$request['unpacked']     = ( isset( $box_package->unpacked ) && ! empty( $box_package->unpacked ) ) ? $box_package->unpacked : array();
				$request['packed']       = ( isset( $box_package->packed ) && ! empty( $box_package->packed ) ) ? $box_package->packed : array();
				$request_ele             = array();
				$request_ele['request']  = $request;
				$request_ele['quantity'] = 1;

				if ( isset( $request_ele['request']['packed'] ) && ! empty( $request_ele['request']['packed'] ) ) {
					$customs_items = array();
					foreach ( $request_ele['request']['packed'] as $packed_product ) {
						$packed_item = wc_get_product( $packed_product->meta );
						if ( $packed_item ) {
							if ( isset( $customs_items[ $packed_item->get_id() ] ) ) {
								$customs_items[ $packed_item->get_id() ]['quantity']++;
							} else {
								$customs_items[ $packed_item->get_id() ] = array(
									'description' => $packed_item->get_title(),
									'value'       => $packed_item->get_price(),
									'quantity'    => 1,
									'weight'      => wc_get_weight( $packed_item->get_weight(), 'oz' ),
								);
								$hs_tariff_number                        = get_post_meta( $packed_item->get_id(), '_wf_hs_code', 1 );
								if ( $hs_tariff_number ) {
									$customs_items[ $packed_item->get_id() ]['hs_tariff_number'] = $hs_tariff_number;
								}
							}
						}
					}
					$request_ele['request']['products_info'] = array_values( $customs_items );
				}

				// Multi-vendor Support
				if ( isset( $package['origin'] ) ) {
					$request_ele['origin'] = $package['origin'];
				}
				if ( isset( $package['user'] ) ) {
					$request_ele['user'] = $package['user'];
				}

				$requests[] = $request_ele;
		}

		return $requests;
	}

	public function debug( $message, $type = 'notice' ) {
		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$this->debug      = isset( $general_settings['debug_mode'] ) && 'yes' === $general_settings['debug_mode'] ? true : false;
		if ( $this->debug && ! is_admin() ) { // WF: is_admin check added.
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
				wc_add_notice( $message, $type );
			} else {
				global $woocommerce;
				$woocommerce->add_message( $message );
			}
		}
	}

	/**
	 * Wf_get_api_rate_box_data function.
	 */
	public function wf_get_api_rate_box_data( $package, $packing_method ) {
		$packing_method     = $packing_method;
		$increment          = 0;
		$requests           = $this->get_package_requests( $package );
		$package_data_array = array();
		if ( $requests ) {
			foreach ( $requests as $key => $request ) {
				$package_data = array();
				$request_data = $request['request']['Rate'];
				if ( 'weight_based_packing' === $packing_method ) {
					$package_data['PackedItem'] = ! empty( $request['request']['packed'][ $increment ]['items'] ) ? $request['request']['packed'][ $increment ]['items'] : '';
					$increment++;
				} else {
					$package_data['PackedItem'] = ! empty( $request['request']['packed'] ) ? $request['request']['packed'] : '';
				}
				// PS: Some of PHP versions doesn't allow to combining below two line of code as one.
				// id_array must have value at this point. Force setting it to 1 if it is not.
				$package_data['BoxCount']          = isset( $request['quantity'] ) ? $request['quantity'] : 1;
				$package_data['WeightOz']          = isset( $request_data['WeightOz'] ) ? $request_data['WeightOz'] : '';
				$package_data['FromZIPCode']       = isset( $request_data['FromZIPCode'] ) ? $request_data['FromZIPCode'] : '';
				$package_data['ToZIPCode']         = isset( $request_data['ToZIPCode'] ) ? $request_data['ToZIPCode'] : '';
				$package_data['ToCountry']         = isset( $request_data['ToCountry'] ) ? $request_data['ToCountry'] : '';
				$package_data['RectangularShaped'] = isset( $request_data['RectangularShaped'] ) ? $request_data['RectangularShaped'] : '';
				$package_data['InsuredValue']      = isset( $request_data['InsuredValue'] ) ? $request_data['InsuredValue'] : '';
				$package_data['ShipDate']          = isset( $request_data['ShipDate'] ) ? $request_data['ShipDate'] : '';
				$package_data['Width']             = isset( $request_data['Width'] ) ? $request_data['Width'] : '';
				$package_data['Length']            = isset( $request_data['Length'] ) ? $request_data['Length'] : '';
				$package_data['Height']            = isset( $request_data['Height'] ) ? $request_data['Height'] : '';
				$package_data['Value']             = isset( $request_data['Value'] ) ? $request_data['Value'] : '';
				$package_data['Girth']             = isset( $request_data['Girth'] ) ? $request_data['Girth'] : '';

				$package_data_array[] = $package_data;
			}
		}
		return $package_data_array;
	}

	public function elex_ep_flat_rate_volume_based_packing( $package, $mode, $international ) {
		$return_package = array();
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$added          = array();
		if ( $international ) {
			$selected_flat_rate_boxes = array( 'USPS' => $rates_settings['predefined_package_service_usps'] ); // International
		} else {
			$selected_flat_rate_boxes = $this->selected_flat_rate_box_format_data( $this->selected_flat_rate_boxes ); // Domestic
		}
		$flat_rate_boxes = $this->flat_rate_boxes;
		// Add Boxes

		$flat_package_carrier = array();

		foreach ( $selected_flat_rate_boxes as $carrier => $boxes ) {
			if ( isset( $mode ) && 'stack_first' === $mode ) {
				$boxpack = new WF_Boxpack_Stack();
				$this->debug( esc_attr( 'Packed based on Stack First', 'wf-easypost' ) );
			} else {
				$boxpack = new WF_Boxpack();
				$this->debug( esc_attr( 'Packed based on Volume Based', 'wf-easypost' ) );
			}
			if ( ! empty( $boxes ) ) {
				foreach ( $boxes as $box_code ) {
					if ( ! isset( $flat_rate_boxes[ $carrier ][ $box_code ] ) ) { // Can't load the box
						continue;
					}

					$box = $flat_rate_boxes[ $carrier ][ $box_code ];

					$service_code = $carrier . ':' . $box_code;
					$newbox       = $boxpack->add_box( $box['length'], $box['width'], $box['height'] );
					$newbox->set_max_weight( $box['weight'] );
					$newbox->set_id( $service_code );

					if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
						$newbox->set_volume( $box['volume'] );
					}

					if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
						$newbox->set_type( $box['type'] );
					}

					$added[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
				}
			}

			// if multi-vendor

			// Add items

			$pack_contents = isset( $package['contents'] ) ? $package['contents'] : '';
			// Add items
			foreach ( $pack_contents as $item_id => $values ) {
				$values['data'] = $this->wf_load_product( $values['data'] );
				if ( ! $values['data']->needs_shipping() ) {
					continue;
				}
				/**
				 * To skip shipping product.
				 * 
				 * @since 1.1.0
				 */
				$skip_product = apply_filters( 'wf_shipping_skip_product', false, $values, $pack_contents );
				if ( $skip_product ) {
					continue;
				}

				if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {

					$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );
				} else {
					$this->debug( sprintf( esc_html( 'Product #%d is missing dimensions! Using 1x1x1.', 'wf-easypost' ), wp_kses_post( $item_id ) ), 'error' );

					$dimensions = array( 1, 1, 1 );
				}

				for ( $i = 0; $i < $values['quantity']; $i ++ ) {
					$boxpack->add_item(
						$dimensions[2],
						$dimensions[1],
						$dimensions[0],
						wc_get_weight( $values['data']->get_weight(), 'lbs' ),
						$values['data']->get_price(),
						$item_id // WF: Adding Item Id and Quantity as meta.
					);
				}
			}

			// Pack it
			$boxpack->pack();
			// Get packages
			$flat_packages_temp = $boxpack->get_packages();
			if ( is_array( $flat_packages_temp ) ) {
				$flat_package_carrier[ $carrier ] = $flat_packages_temp;
			}
		}
		if ( isset( $mode ) && 'stack_first' === $mode ) {
			foreach ( $flat_package_carrier as $flat_packages ) {

				foreach ( $flat_packages as $key => $box_package ) {
					$box_volume                 = $box_package->length * $box_package->width * $box_package->height;
					$box_used_volume            = ! empty( $box_package->volume ) ? $box_package->volume : 0;
					$box_used_volume_percentage = ( $box_used_volume * 100 ) / $box_volume;
					if ( $box_used_volume_percentage < 44 && 0 === $key ) {
						$mode = 'volume_based';
						$this->debug( esc_attr( '(FALLBACK) : Stack First Option changed to Volume Based', 'wf-easypost' ) );
					}
				}
			}
		}

		$return_package = array_merge( array( 'flat_package_carrier' => $flat_package_carrier ), array( 'added' => $added ) );
		return $return_package;
	}
	/*
	  Calculate flat rate box function
	 */

	private function calculate_flat_rate_box_rate( $package, $id = '', $international = '', $vendor_sum = false ) {
		global $woocommerce;
		$packing_settings               = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$rates_settings                 = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$mode                           = isset( $packing_settings['packing_algorithm'] ) ? $packing_settings['packing_algorithm'] : 'volume_based';
		$this->selected_flat_rate_boxes = isset( $rates_settings['selected_flat_rate_boxes'] ) ? $rates_settings['selected_flat_rate_boxes'] : array();
		$this->flat_rate_fee            = ! empty( $rates_settings['flat_rate_fee'] ) ? $rates_settings['flat_rate_fee'] : '';
		$cost                           = 0;
		if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
		}
		if ( ! class_exists( 'WF_Boxpack' ) ) {
			include_once 'class-wf-packing.php';
		}
		if ( ! class_exists( 'WF_Boxpack_Stack' ) ) {
			include_once 'class-wf-packing-stack.php';
		}

		$return_package       = $this->elex_ep_flat_rate_volume_based_packing( $package, $mode, $international );
		$flat_package_carrier = $return_package['flat_package_carrier'];
		$added                = $return_package['added'];
		if ( empty( $flat_package_carrier->unpacked ) ) {
			if ( $id ) {
				foreach ( $flat_package_carrier as $flat_packages ) {
					foreach ( $flat_packages as $key => $value ) {
						$selected_box[] = $flat_packages[ $key ]->id;
					}
				}
				update_post_meta( $id, 'easypost_flat_rate_box_name', $selected_box );
				update_option( 'easypost_flat_rate_box', $flat_packages );
			}
			if ( $flat_package_carrier ) {
				$usps_flag  = false;
				$fedex_flag = false;
				// if products will not get fitted in any flat boxes it will not send request
				foreach ( $flat_package_carrier as $carrier => $flat_packages ) {
					foreach ( $flat_packages as $key => $flat_package ) {
						if ( 'USPS' === $carrier ) {
							if ( ! isset( $flat_package->packed ) ) {
								$usps_flag = true;
							}
						}
						if ( 'FedEx' === $carrier ) {
							if ( ! isset( $flat_package->packed ) ) {
								$fedex_flag = true;
							}
						}
					}
				}
	 
				if ( $vendor_sum && $international ) {
					$flat_rate_to_send = array();
					$flat_carrier_name[] = '';
					if ( $usps_flag ) {
						$this->debug( 'Product will not get fit into USPS box' );
					}

					foreach ( $flat_package_carrier as $carrier => $flat_packages ) {
						$count          = 0;
						$this->box_name = array();
						foreach ( $flat_packages as $key => $flat_package ) {
							$flat_flag = false;
							if ( empty( $flat_package->unpacked ) ) {
								$flat_flag = true;
							}
							$dimensions       = array( $flat_package->length, $flat_package->width, $flat_package->height );
							$this->box_name[] = $flat_packages[ $count ]->id;
							if ( '' !== $this->box_name[ $count ] && $flat_flag && ! $usps_flag ) {
								$weight = number_format( $flat_package->weight * 16, 2, '.', '' );

								$dest_postal_code = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
								$from_zip_code = ! empty( $package['origin']['postcode'] ) ? $package['origin']['postcode'] : ( isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '' );
								$request                 = array();
								$request['Rate']         = array(
									'FromZIPCode'  => str_replace( ' ', '', strtoupper( $from_zip_code ) ),
									'ToZIPCode'    => strtoupper( $dest_postal_code ),
									'ToCountry'    => $package['destination']['country'],
									'Amount'       => $flat_package->value,
									'WeightOz'     => $weight,
									'PackageType'  => 'Package',
									'Length'       => $dimensions[0],
									'Width'        => $dimensions[1],
									'Height'       => $dimensions[2],
									'Value'        => $flat_package->value,
									'ShipDate'     => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
									'InsuredValue' => $flat_package->value,
								);
								$request_ele             = array();
								$request_ele['request']  = $request;
								$request_ele['quantity'] = 1;
								$flat_box               = $flat_packages[ $count ]->id;
								$flat_carrier_name      = explode( ':', $flat_box );
								if ( 'USPS' === $flat_carrier_name[0] ) {
									$flat_rate = str_replace( 'USPS:', '', $flat_box );
								}                           
							} else {
								$request_ele = '';
							
							}
							if ( ! empty( $request_ele ) ) {
								$this->debug( 'Calculating EasyPost Flat Rate with boxes: ' . implode( ', ', $added ) );
							}
							$response = $this->get_result( $request_ele, $flat_rate, $package['origin'] );
							$count++;
							$flat_package_id = explode( ':', $flat_package->id );
							$carrier         = isset( $flat_package_id[0] ) ? $flat_package_id[0] : '';
							$box_code        = isset( $flat_package_id[1] ) ? $flat_package_id[1] : '';
							if ( is_array( $response ) && ! empty( $response ) ) {
								foreach ( $response as $key => $res_val ) {
									$res_vals = $res_val;
									if ( isset( $res_vals->rates ) ) {

										foreach ( $res_vals->rates as $key => $rates ) {
											$delivery_days = 0;
											if ( ! empty( $rates->delivery_date ) ) {
												$delivery_date = explode( 'T', $rates->delivery_date );
												$from          = date_create( gmdate( 'Y-m-d' ) );
												$to            = date_create( $delivery_date[0] );
												$diff          = date_diff( $to, $from );
												$delivery_days = $diff->days;
											}
									
											if ( ! empty( $rates_settings['flat_rate_boxes_international_carrier'] ) ) {
												if ( in_array( 'priority_mail_express_international', $rates_settings['flat_rate_boxes_international_carrier'] ) && 'USPS' === $rates->carrier && 'Express' === $rates->service ) {
													$carrier = $rates->carrier;
													$rate    = $rates->rate;
										
										
													$service = ! empty( $rates_setting['flat_rate_boxes_text_international_express'] ) ? $rates_setting['flat_rate_boxes_text_international_express'] : 'USPS Flat Rate:Express Mail International';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':Express',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'express',
													);

												} elseif ( in_array( 'priority_mail_international', $rates_settings['flat_rate_boxes_international_carrier'] ) && 'USPS' === $rates->carrier && 'Priority' === $rates->service ) {
													$carrier = $rates->carrier;
										
													$rate    = $rates->rate;
											
													$service              = ! empty( $rates_settings['flat_rate_boxes_text_international_mail'] ) ? $rates_settings['flat_rate_boxes_text_international_mail'] : 'USPS Flat Rate:Priority Mail International';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':Priority',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'mail',
													);
												} elseif ( in_array( 'first_class_mail_international', $rates_settings['flat_rate_boxes_international_carrier'] ) && 'USPS' === $rates->carrier && 'First' === $rates->service ) {
													$carrier = $rates->carrier;
											
													$rate    = $rates->rate;
										
													$service = ! empty( $rates_setting['flat_rate_boxes_text_first_class_mail_international'] ) ? $rates_setting['flat_rate_boxes_text_first_class_mail_international'] : 'USPS Flat Rate:First-Class Mail International';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':First',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'first',
													);

												} 
											}
										}
									}
								}
							}
						}
					}
			
					return $flat_rate_to_send;
				} elseif ( $international ) {
					if ( $usps_flag ) {
						$this->debug( 'Product will not get fit into USPS box' );
					}

					foreach ( $flat_package_carrier as $carrier => $flat_packages ) {
						$count          = 0;
						$this->box_name = array();
						foreach ( $flat_packages as $key => $flat_package ) {
							$flat_flag = false;
							if ( empty( $flat_package->unpacked ) ) {
								$flat_flag = true;
							}
							$dimensions       = array( $flat_package->length, $flat_package->width, $flat_package->height );
							$this->box_name[] = $flat_packages[ $count ]->id;
							if ( '' !== $this->box_name[ $count ] && $flat_flag && ! $usps_flag ) {
								$weight = number_format( $flat_package->weight * 16, 2, '.', '' );

								$dest_postal_code = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
								$from_zip_code = ! empty( $package['origin']['postcode'] ) ? $package['origin']['postcode'] : ( isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '' );
								$request                 = array();
								$request['Rate']         = array(
									'FromZIPCode'  => str_replace( ' ', '', strtoupper( $from_zip_code ) ),
									'ToZIPCode'    => strtoupper( $dest_postal_code ),
									'ToCountry'    => $package['destination']['country'],
									'Amount'       => $flat_package->value,
									'WeightOz'     => $weight,
									'PackageType'  => 'Package',
									'Length'       => $dimensions[0],
									'Width'        => $dimensions[1],
									'Height'       => $dimensions[2],
									'Value'        => $flat_package->value,
									'ShipDate'     => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
									'InsuredValue' => $flat_package->value,
								);
								$request_ele             = array();
								$request_ele['request']  = $request;
								$request_ele['quantity'] = 1;
								if ( $this->vendor_check ) {
									$request_ele['origin']   = $package['origin'];
									$request_ele['vendor_id']   = isset( $package['user']['ID'] ) ? $package['user']['ID'] : '';
								}
								$requests[]              = $request_ele;
								$count++;
							} else {
									$request_ele = '';
									$requests[]  = $request_ele;
							}
						}
					}
		
					return $requests;
				}
				$flat_rate_to_send = array();

				$flat_carrier_name[] = '';
				
				if ( $usps_flag && $fedex_flag ) {
					$this->debug( ' Product will not get fit into USPS and FedEx box' );
				}
				foreach ( $flat_package_carrier as $carrier => $flat_packages ) {
					$count = 0;
					foreach ( $flat_packages as $key => $flat_package ) {
						if ( 'USPS' === $carrier ) {
							$flat_flag = $usps_flag;
						}
						if ( 'FedEx' === $carrier ) {
							$flat_flag = $fedex_flag;
						}
						if ( empty( $id ) ) {

							$this->box_name           = array();
							$flat_rate                = '';
							$dest_postal_code         = ! empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : ( isset( $package['destination']['zip'] ) ? $package['destination']['zip'] : '' );
							$from_zip_code = ! empty( $package['origin']['postcode'] ) ? $package['origin']['postcode'] : ( isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '' );
							$weight                   = number_format( $flat_package->weight * 16, 2, '.', '' );
							$dimensions               = array( $flat_package->length, $flat_package->width, $flat_package->height );
							$this->box_name[ $count ] = $flat_packages[ $count ]->id;
							if ( '' !== $this->box_name[ $count ] && ! $flat_flag ) {
								$request                = array();
								$request['Rate']        = array(
									'FromZIPCode'  => str_replace( ' ', '', strtoupper( $from_zip_code ) ),
									'ToZIPCode'    => strtoupper( $dest_postal_code ),
									'ToCountry'    => $package['destination']['country'],
									'Amount'       => $flat_package->value,
									'WeightOz'     => $weight,
									'PackageType'  => 'Package',
									'Length'       => $dimensions[0],
									'Width'        => $dimensions[1],
									'Height'       => $dimensions[2],
									'Value'        => $flat_package->value,
									'ShipDate'     => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ),
									'InsuredValue' => $flat_package->value,
								);
								$request_ele            = array();
								$request_ele['request'] = $request;
								$flat_box               = $flat_packages[ $count ]->id;
								$flat_carrier_name      = explode( ':', $flat_box );
								if ( 'USPS' === $flat_carrier_name[0] ) {
									$flat_rate = str_replace( 'USPS:', '', $flat_box );
								} else {
									$flat_rate = str_replace( 'FedEx:', '', $flat_box );
								}
								$request_ele['quantity'] = 1;
							} else {
								$request_ele = '';
							}
							$flat_box          = $flat_packages[ $count ]->id;
							$flat_carrier_name = explode( ':', $flat_box );

							if ( ! empty( $request_ele ) ) {
								$this->debug( 'Calculating EasyPost Flat Rate with boxes: ' . implode( ', ', $added ) );
							} elseif ( '' !== $this->box_name[ $count ] && 'USPS' === $flat_carrier_name[0] && $flat_flag ) {
								$this->debug( ' Product will not get fit into USPS box' );
							} elseif ( '' !== $this->box_name[ $count ] && 'FedEx' === $flat_carrier_name[0] && $flat_flag ) {
								$this->debug( ' Product will not get fit into FedEx box' );
							}
							if ( $this->vendor_check ) {
								$response = $this->get_result( $request_ele, $flat_rate, $package['origin'] );
							} else {
								$response = $this->get_result( $request_ele, $flat_rate );
							}
							
							$count++;
							$cost_express    = 0;
							$cost_mail       = 0;
							$flat_package_id = explode( ':', $flat_package->id );
							$carrier         = isset( $flat_package_id[0] ) ? $flat_package_id[0] : '';
							$box_code        = isset( $flat_package_id[1] ) ? $flat_package_id[1] : '';
							$service         = 'USPS Flat Rate:';
							if ( is_array( $response ) && ! empty( $response ) ) {
								foreach ( $response as $key => $res_val ) {

									$res_vals = $res_val;
									if ( isset( $res_vals->rates ) ) {

										foreach ( $res_vals->rates as $key => $rates ) {
											$delivery_days = 0;
											if ( ! empty( $rates->delivery_date ) ) {
												$delivery_date = explode( 'T', $rates->delivery_date );
												$from          = date_create( gmdate( 'Y-m-d' ) );
												$to            = date_create( $delivery_date[0] );
												$diff          = date_diff( $to, $from );
												$delivery_days = $diff->days;
											}
											
											if ( ! empty( $rates_settings['flat_rate_boxes_domestic_carrier'] ) ) {
												if ( in_array( 'priority_mail_express', $rates_settings['flat_rate_boxes_domestic_carrier'] ) && 'USPS' === $rates->carrier && 'Express' === $rates->service ) {
													$carrier = $rates->carrier;
													$service = $service . $rates->service;
													$rate    = $rates->rate;
													// Fees
													if ( ! empty( $this->flat_rate_fee ) ) {
														$sym = substr( $this->flat_rate_fee, 0, 1 );
														$fee = '-' === $sym ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;
														if ( strstr( $fee, '%' ) ) {
															$fee = str_replace( '%', '', $fee );

															if ( '-' === $sym ) {
																$rate = $rate - ( $rate * ( floatval( $fee ) / 100 ) );
															} else {
																$rate = $rate + ( $rate * ( floatval( $fee ) / 100 ) );
															}
														} else {
															if ( '-' === $sym ) {
																$rate = $rate - $fee;
															} else {
																$rate += $fee;
															}
														}
													}
													$service = ! empty( $rates_setting['flat_rate_boxes_express_text'] ) ? $rates_setting['flat_rate_boxes_express_text'] : 'USPS Flat Rate:Priority Mail Express';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':Express',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'express',
													);

												} elseif ( in_array( 'priority_mail', $rates_settings['flat_rate_boxes_domestic_carrier'] ) && 'USPS' === $rates->carrier && 'Priority' === $rates->service ) {
													$carrier = $rates->carrier;
													$service = $service . $rates->service;
													$rate    = $rates->rate;
													// Fees
													if ( ! empty( $this->flat_rate_fee ) ) {
														$sym = substr( $this->flat_rate_fee, 0, 1 );
														$fee = '-' === $sym ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;
														if ( strstr( $fee, '%' ) ) {
															$fee = str_replace( '%', '', $fee );

															if ( '-' === $sym ) {
																$rate = $rate - ( $rate * ( floatval( $fee ) / 100 ) );
															} else {
																$rate = $rate + ( $rate * ( floatval( $fee ) / 100 ) );
															}
														} else {
															if ( '-' === $sym ) {
																$rate = $rate - $fee;
															} else {
																$rate += $fee;
															}
														}
													}
													$service              = ! empty( $rates_settings['flat_rate_boxes_text'] ) ? $rates_settings['flat_rate_boxes_text'] : 'USPS Flat Rate:Priority Mail';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':Priority',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'mail',
													);
												} elseif ( in_array( 'first_class_mail', $rates_settings['flat_rate_boxes_domestic_carrier'] ) && 'USPS' === $rates->carrier && 'First' === $rates->service ) {
													$carrier = $rates->carrier;
													$service = $service . $rates->service;
													$rate    = $rates->rate;
													// Fees
													if ( ! empty( $this->flat_rate_fee ) ) {
														$sym = substr( $this->flat_rate_fee, 0, 1 );
														$fee = '-' === $sym ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;
														if ( strstr( $fee, '%' ) ) {
															$fee = str_replace( '%', '', $fee );

															if ( '-' === $sym ) {
																$rate = $rate - ( $rate * ( floatval( $fee ) / 100 ) );
															} else {
																$rate = $rate + ( $rate * ( floatval( $fee ) / 100 ) );
															}
														} else {
															if ( '-' === $sym ) {
																$rate = $rate - $fee;
															} else {
																$rate += $fee;
															}
														}
													} 
													$service = ! empty( $rates_setting['flat_rate_boxes_first_class_text'] ) ? $rates_setting['flat_rate_boxes_express_text'] : 'USPS Flat Rate:First-Class Mail';
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':First',
														'label' => $service,
														'cost' => $rate,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => 'first',
													);

												} elseif ( in_array( 'fedex_onerate', $rates_settings['flat_rate_boxes_domestic_carrier'] ) && 'FedEx' === $rates->carrier ) {

													$carrier = $rates->carrier;
													$service = $service . $rates->service;
													$rate    = $rates->rate;
													// Fees
													if ( ! empty( $this->flat_rate_fee ) ) {
																$sym = substr( $this->flat_rate_fee, 0, 1 );
																$fee = '-' === $sym ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;
														if ( strstr( $fee, '%' ) ) {
															$fee = str_replace( '%', '', $fee );

															if ( '-' === $sym ) {
																$rate = $rate - ( $rate * ( floatval( $fee ) / 100 ) );
															} else {
																$rate = $rate + ( $rate * ( floatval( $fee ) / 100 ) );
															}
														} else {
															if ( '-' === $sym ) {
																				$rate = $rate - $fee;
															} else {
																$rate += $fee;
															}
														}
													} 
													$service            = ! empty( $rates_settings['flat_rate_boxes_fedex_one_rate_text'] ) ? $rates_settings['flat_rate_boxes_fedex_one_rate_text'] : 'FedEx Flat Rate:FedEx One Rate'; 
													$flat_rate_to_send [] = array(
														'id'   => $this->id . ':' . $carrier . ':' . $box_code . ':' . $rates->service,
														'label' => $rates->service . '( ' . $service . ' ) ',
														'cost' => $rate,
														'carrier_name' => $carrier,
														'meta_data' => array( 'easypost_delivery_time' => ! empty( $delivery_days ) ? $delivery_days : $rates->est_delivery_days ),
														'service' => $rates->service,
													);

												}
											}
										}
									}
								}
							}
						}
					}
				}
				return $flat_rate_to_send;
			}
		}

	}

	public function selected_flat_rate_box_format_data( $selected_flat_rate_boxes ) {
		$boxes = array();
		if ( is_array( $selected_flat_rate_boxes ) ) {
			foreach ( $selected_flat_rate_boxes as $selected_flat_rate_box ) {

				list($carrier, $box_code) = explode( ':', $selected_flat_rate_box );
				$boxes[ $carrier ][]      = $box_code;
			}
		}
		return $boxes;
	}

	private function wf_load_product( $product ) {
		if ( ! $product ) {
			return false;
		}
		if ( ! class_exists( 'Wf_Product' ) ) {
			include_once 'class-wf-legacy.php';
		}
		if ( $product instanceof Wf_Product ) {
			return $product;
		}
		return ( WC()->version < '2.7.0' ) ? $product : new Wf_Product( $product );
	}

}
