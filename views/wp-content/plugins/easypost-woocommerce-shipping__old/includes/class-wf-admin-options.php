<?php
if ( ! class_exists( 'WF_Admin_Options' ) ) {
	class WF_Admin_Options {
		public function __construct() {
			$this->init();
		}

		public function init() {
			// add a custome field in product page
			add_action( 'woocommerce_product_options_shipping', array( $this, 'wf_add_hs_code_fields' ) );
			// Saving the values
			add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_hs_code_fields' ) );
			// add customs declared value for variable product.
			add_action( 'woocommerce_save_product_variation', array( $this, 'wf_easypost_save_product_variations_customs_fields_value' ), 10, 2 );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'wf_easypost_product_variation_customs_fields' ), 10, 3 );
		}

		public function wf_add_hs_code_fields() {
			$label_settings  = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
			$check_signature = isset( $label_settings['signature_option'] ) ? $label_settings['signature_option'] : 'no';
			echo '<div>';
			echo '<p class="form-field _easyPost" >';
			echo '<label for ="easyPost"><b>EasyPost Settings</b></label>';
			echo '</p>';
			echo '</div>';
			// Print a custom text field
			woocommerce_wp_text_input(
				array(
					'id'          => '_wf_hs_code',
					'label'       => ' HS Tariff Number',
					'description' => esc_attr( 'HS is a standardized system of names and numbers to classify products.' ),
					'desc_tip'    => 'true',
					'placeholder' => 'Harmonized System',
				)
			);
			if ( 'no' === $check_signature ) {
				woocommerce_wp_select(
					array(
						'id'          => '_wf_easypost_signature',
						'label'       => esc_attr( 'Delivery Signature', 'wf-easypost' ),
						'options'     => array(
							0 => esc_attr( 'None', 'wf-easypost' ),
							1 => esc_attr( 'No Signature', 'wf-easypost' ),
							2 => esc_attr( 'Adult Signature', 'wf-easypost' ),
						),
						'description' => esc_attr( 'If you want to request a signature, you can choose "ADULT SIGNATURE". You may also request "NO SIGNATURE" to leave the package at the door.', 'wf-easypost' ),
						'desc_tip'    => 'true',
					)
				);
			}
			// Customs declared value
			woocommerce_wp_text_input(
				array(
					'id'          => '_wf_easypost_custom_declared_value',
					'label'       => esc_attr( 'Customs Declared Value', 'wf-easypost' ),
					'description' => esc_attr( 'Customs declared value for international shipments', 'wf-easypost' ),
					'desc_tip'    => 'true',
					'placeholder' => esc_attr( 'Customs declared value', 'wf-easypost' ),
				)
			);
			if ( ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {
				// lead time value
				woocommerce_wp_text_input(
					array(
						'id'          => '_wf_easypost_custom_lead_time_value',
						'label'       => esc_attr( 'Lead Time', 'wf-easypost' ),
						'description' => esc_attr( 'Configure the lead time for your shipment. The estimated delivery date will be displayed based on this.', 'wf-easypost' ),
						'type'        => 'number',
						'desc_tip'    => 'true',
						'placeholder' => esc_attr( 'Lead time value', 'wf-easypost' ),
					)
				);
			}

			// Dry Ice Product
			woocommerce_wp_checkbox(
				array(
					'id'          => '_wf_dry_ice_code',
					'label'       => 'Require Dry Ice',
					'description' => esc_attr( 'Enable the checkbox if your product requires Dry Ice for shipping.', 'wf-easypost' ),
					'type'        => 'checkbox',
					'default'     => 'no',
					'desc_tip'    => 'true',
				)
			);
		}

		public function wf_save_hs_code_fields( $post_id ) {
			if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
				return false;
			}
			if ( isset( $_POST['_wf_hs_code'] ) ) {
				update_post_meta( $post_id, '_wf_hs_code', esc_attr( sanitize_text_field( $_POST['_wf_hs_code'] ) ) );
			}
			if ( isset( $_POST['_wf_easypost_signature'] ) ) {
				update_post_meta( $post_id, '_wf_easypost_signature', esc_attr( sanitize_text_field( $_POST['_wf_easypost_signature'] ) ) );
			}
			if ( isset( $_POST['_wf_easypost_custom_declared_value'] ) ) {

				update_post_meta( $post_id, '_wf_easypost_custom_declared_value', esc_attr( sanitize_text_field( $_POST['_wf_easypost_custom_declared_value'] ) ) );
			}
			if ( isset( $_POST['_wf_easypost_custom_lead_time_value'] ) && ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {

				update_post_meta( $post_id, '_wf_easypost_custom_lead_time_value', esc_attr( sanitize_text_field( $_POST['_wf_easypost_custom_lead_time_value'] ) ) );
			}
			$dry_ice_checkbox = isset( $_POST['_wf_dry_ice_code'] ) ? 'yes' : 'no';
			update_post_meta( $post_id, '_wf_dry_ice_code', $dry_ice_checkbox );
		}
		 /* Saving custom fields values for product variations*/
		public function wf_easypost_save_product_variations_customs_fields_value( $variation_id, $loop ) {
			if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
				return false;
			}
			$custom_field_value = isset( $_POST[ '_wf_easypost_product_variations_custom_declared_value_' . $loop ] ) ? sanitize_text_field( $_POST[ '_wf_easypost_product_variations_custom_declared_value_' . $loop ] ) : '';
			// Custom Declared value for product variations
			$check = get_post_meta( $variation->ID, '_wf_easypost_custom_declared_value', true );
			if ( $custom_field_value ) {
				update_post_meta( $variation_id, '_wf_easypost_custom_declared_value', esc_attr( $custom_field_value ) );
			} elseif ( ! $check ) {
				update_post_meta( $variation_id, '_wf_easypost_custom_declared_value', '' );
			}
		}

		/* Provide custom fields for product variations */
		public function wf_easypost_product_variation_customs_fields( $loop, $variation_data, $variation ) {
			// Custom declared value
			woocommerce_wp_text_input(
				array(
					'id'            => '_wf_easypost_product_variations_custom_declared_value_' . $loop,
					'label'         => esc_attr( 'Customs Declared Value (EasyPost)', 'wf-easypost' ),
					'description'   => esc_attr( 'Customs declared value for international shipments', 'wf-easypost' ),
					'desc_tip'      => 'true',
					'placeholder'   => esc_attr( 'Customs declared value', 'wf-easypost' ),
					'value'         => get_post_meta( $variation->ID, '_wf_easypost_custom_declared_value', true ),
					'wrapper_class' => 'form-row form-row-full',
				)
			);
		}

	}
	new WF_Admin_Options();
}
