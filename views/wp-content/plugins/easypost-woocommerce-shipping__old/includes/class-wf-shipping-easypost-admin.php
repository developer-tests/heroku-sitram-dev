<?php

class WF_Shipping_Easypost_Admin {

	private $easypost_services;
	private $signature_options = array(
		0 => '',
		1 => 'NO_SIGNATURE',
		2 => 'ADULT_SIGNATURE',
	);

	public function __construct() {

		$this->wf_init();
		add_action( 'wp_ajax_elex_easypost_update_shipping_services', array( $this, 'elex_easypost_update_shipping_services' ) );

		if ( ! class_exists( 'WF_Easypost' ) ) {
			include_once 'class-wf-shipping-easypost.php';
		}
		if ( 1 === session_status() ) {
			session_start();
		}
	
		/**
		 * Check whether multivendor is active or not.
		 * 
		 * @since 1.0.0
		 */
		$this->vendor_check      = in_array( 'multi-vendor-add-on-for-thirdparty-shipping/multi-vendor-add-on-for-thirdparty-shipping.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? true : false;
		$this->easypost_services = include 'data-wf-services.php';
		// Third Party Billing Checkout Page Actions.

		add_action( 'woocommerce_after_order_notes', array( $this, 'elex_easypost_third_party_billing_checkout_content' ), 15 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'elex_easypost_save_third_party_billing_checkout_details' ), 15 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'elex_easypost_third_party_checkout_field_display_admin_order_meta' ), 15 );

		// Print Shipping Label.
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'wf_add_easypost_metabox' ), 15 );
			add_action( 'admin_notices', array( $this, 'wf_admin_notice' ), 15 );
			add_action( 'load-edit.php', array( $this, 'wf_easypost_orders_bulk_action' ) ); // to handle post id for bulk actions
			add_action( 'admin_notices', array( $this, 'bulk_easypost_label_admin_notice' ) ); // to add admin notices to update bulk actions status
		}

		if ( isset( $_GET['wf_easypost_shipment_confirm'] ) ) {
			add_action( 'init', array( $this, 'wf_easypost_shipment_confirm' ), 15 );
		} elseif ( isset( $_GET['wf_easypost_void_shipment'] ) ) {
			add_action( 'init', array( $this, 'wf_easypost_void_shipment' ), 15 );
		} elseif ( isset( $_GET['wf_easypost_generate_packages'] ) ) {
			add_action( 'init', array( $this, 'wf_easypost_generate_packages' ), 15 );
		}
		add_filter( 'wp_mail_content_type', array( $this, 'elex_multi_vendor_email_hook' ) );
		add_filter( 'elex_easypost_order_package', array( $this, 'elex_easypost_order_package' ), 10, 2 );
		
	}

	public function elex_multi_vendor_email_hook() {
		return 'text/html';
	}

	public function wf_admin_notice() {
		global $pagenow;
		global $post;

		if ( ! isset( $_SESSION['wfeasypostmsg'] ) && empty( $_SESSION['wfeasypostmsg'] ) ) {
			return;
		}

		$wfeasypostmsg = sanitize_text_field( $_SESSION['wfeasypostmsg'] );
		unset( $_SESSION['wfeasypostmsg'] );

		switch ( $wfeasypostmsg ) {
			case '0':
				echo '<div class="error"><p>' . esc_attr( 'EasyPost.com: Sorry, An unexpected error occurred.', 'wf-easypost' ) . '</p></div>';
				break;
			case '1':
				echo '<div class="updated"><p>' . wp_kses_post( 'EasyPost.com: Create Shipment is complete. You can proceed with print label.' ) . '</p></div>';    
				break;
			case '2':
				$wfeasypostmsg = get_post_meta( $post->ID, 'wfeasypostmsg', true );
				echo '<div class="error"><p>EasyPost.com: ' . wp_kses_post( $wfeasypostmsg ) . '</p></div>';
				break;
			case '3':
			case '4':
				echo '<div class="updated"><p>' . wp_kses_post( 'EasyPost.com: Shipment cancelled successfully. Strongly recommend to double check it by login in to your EasyPost.com account.', 'wf-easypost' ) . '</p></div>';
				break;
			case '5':
				echo '<div class="updated"><p>' . wp_kses_post( 'EasyPost.com: Client side reset of labels and shipment completed. You can re-initiate shipment now.', 'wf-easypost' ) . '</p></div>';
				break;
			case '6':
				$wfeasypostmsg = get_post_meta( $post->ID, 'wfeasypostmsg', true );
				echo '<div class="updated"><p>' . wp_kses_post( $wfeasypostmsg ) . '</p></div>';
				break;
			default:
				break;
		}
	}

	private function wf_init() {
		global $post;

		$shipmentconfirm_requests = array();
		// Load EasyPost.com Settings.

		$this->bulk_label = false; // to determine whether the current action is a bulk action or not
		// Units
		$this->weight_unit       = 'LBS';
		$this->weight_ounce_unit = 'OZ';
		$this->dim_unit          = 'IN';
	}

	public function wf_add_easypost_metabox() {
		global $post;

		if ( ! $post ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'shop_order' ) ) ) {
			return;
		}

		$order    = $this->wf_load_order( $post->ID );
		$order_id = $order->get_id();
		update_option( 'current_order_id_easypost_elex', $order_id );
		if ( ! $order ) {
			return;
		}

		$shipping_service_data = $this->wf_get_shipping_service_data( $order );

		if ( $shipping_service_data ) {
			/**
			 * To add metabox content.
			 * 
			 * @since 1.0.0
			 */
			$callback = apply_filters( 'wf_easypost_metabox_content', array( $this, 'wf_easypost_metabox_content' ) );
			add_meta_box( 'WF_Easypost_metabox', esc_attr( 'Generate Shipping Label By Using EasyPost API', 'wf-easypost' ), $callback, 'shop_order', 'advanced', 'default' );
			/*
			if ('yes' === $this->easypost_admin_enabled) {
				//add_meta_box( 'WF_Easypost_Account_metabox', esc_attr( 'Easypost.com Account', 'wf-easypost' ), array( $this, 'wf_easypost_account_metabox_content' ), 'shop_order', 'side', 'default' );
			}*/
		}
	}



	// Perferred service selection in order page for flat rate services
	public function wf_label_generation_flat_service( $service_name ) {
		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		if ( isset( $rates_settings['flat_rate_boxes_text'] ) && '' === $rates_settings['flat_rate_boxes_text'] ) {
			$rates_settings['flat_rate_boxes_text'] = 'USPS Flat Rate:Priority Mail';
		}
		if ( isset( $rates_settings['flat_rate_boxes_express_text'] ) && '' === $rates_settings['flat_rate_boxes_express_text'] ) {
			$rates_settings['flat_rate_boxes_express_text'] = 'USPS Flat Rate:Priority Mail Express';
		}
		if ( isset( $rates_settings['flat_rate_boxes_first_class_text'] ) && '' === $rates_settings['flat_rate_boxes_first_class_text'] ) {
			$rates_settings['flat_rate_boxes_first_class_text'] = 'USPS Flat Rate:First-Class Mail';
		}
		if ( isset( $rates_settings['flat_rate_boxes_fedex_one_rate_text'] ) && '' === $rates_settings['flat_rate_boxes_fedex_one_rate_text'] ) {
			$rates_settings['flat_rate_boxes_fedex_one_rate_text'] = 'FedEx Flat Rate:FedEx One Rate';
		}
		if ( isset( $rates_settings['flat_rate_boxes_text_international_mail'] ) && '' === $rates_settings['flat_rate_boxes_text_international_mail'] ) {
			$rates_settings['flat_rate_boxes_text_international_mail'] = 'USPS Flat Rate:Priority Mail International';
		}
		if ( isset( $rates_settings['flat_rate_boxes_text_international_express'] ) && '' === $rates_settings['flat_rate_boxes_text_international_express'] ) {
			$rates_settings['flat_rate_boxes_text_international_express'] = 'USPS Flat Rate:Express Mail International';
		}
		if ( isset( $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) && '' === $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) {
			$rates_settings['flat_rate_boxes_text_first_class_mail_international'] = 'USPS Flat Rate:First-Class Mail International';
		}
		if ( '' !== $service_name ) {
			$mail_international         = '';
			$express_international      = '';
			$priority_mail_flat         = '';
			$priority_mail_flat         = $rates_settings['flat_rate_boxes_text'];
			$priority_mail_express_flat = $rates_settings['flat_rate_boxes_express_text'];
			$first_class_mail_flat      = $rates_settings['flat_rate_boxes_first_class_text'];
			$fedex_onerate_flat         = $rates_settings['flat_rate_boxes_fedex_one_rate_text'];
			$service_from_customer      = explode( ':', $service_name );
			if ( ! empty( $rates_settings['flat_rate_boxes_text_international_mail'] ) ) {
				$mail_international = $rates_settings['flat_rate_boxes_text_international_mail'];
			}
			if ( ! empty( $rates_settings['flat_rate_boxes_text_international_express'] ) ) {
				$express_international = $rates_settings['flat_rate_boxes_text_international_express'];
			}
			if ( ! empty( $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) ) {
				$first_class_international = $rates_settings['flat_rate_boxes_text_first_class_mail_international'];
			}
			foreach ( $this->easypost_services as $key => $code ) {
				foreach ( $code['services'] as $key => $code_name ) {
					if ( $service_name === $priority_mail_flat ) {
						$service_name = $code['services']['Priority'];
						break;
					}
					if ( $service_name === $priority_mail_express_flat ) {
						$service_name = $code['services']['Express'];
						break;
					}
					if ( $service_name === $first_class_mail_flat ) {
						$service_name = $code['services']['First'];
						break;
					}

					if ( strpos( $service_name, $fedex_onerate_flat ) !== 0 ) {
						$fedex_flat_service = explode( '(', $service_name );
						if ( $fedex_flat_service[0] === $key ) {
							$service_name = $code['services'][ $key ];
							break;
						}
					}
					if ( ( $mail_international === $service_name ) || ( isset( $service_from_customer[1] ) && $service_from_customer[1] === $code_name ) ) {
						$service_name = $code['services']['PriorityMailInternational'];
						break;
					}
					if ( ( $express_international === $service_name ) || ( isset( $service_from_customer[1] ) && $service_from_customer[1] === $code_name ) ) {

						$service_name = $code['services']['ExpressMailInternational'];
						break;
					}
					if ( ( $first_class_international === $service_name ) || ( isset( $service_from_customer[1] ) && $service_from_customer[1] === $code_name ) ) {
						$service_name = $code['services']['FirstClassMailInternational'];
						break;
					}
				}
			}
		}
		return $service_name;
	}
	public function elex_ep_update_service() {
		?>
		<script type="text/javascript">
		function updateServicesFunction(elementId){
			if (jQuery("input[id='easypost_signature_option']").is(":checked") === true) { 
				jQuery("input[id='easypost_signature_option']").val('yes');
			} else { 
				jQuery("input[id='easypost_signature_option']").val('no');
			 }
		jQuery('#'+elementId).next().show();
		var packageOrder              = parseInt(elementId.match(/\d+/));
		var manual_package_nonce         =   jQuery("input[id='manual_package_nonce']").map(function(){return jQuery(this).val();}).get();
		var manual_weight_arr         =   jQuery("input[id='easypost_manual_weight']").map(function(){return jQuery(this).val();}).get();
		var manual_height_arr         =   jQuery("input[id='easypost_manual_height']").map(function(){return jQuery(this).val();}).get();
		var manual_width_arr          =   jQuery("input[id='easypost_manual_width']").map(function(){return jQuery(this).val();}).get();
		var manual_length_arr         =   jQuery("input[id='easypost_manual_length']").map(function(){return jQuery(this).val();}).get();
		var shipping_service_easypost =   jQuery(".easypost_manual_service").map(function(){return jQuery(this).attr("value");}).get();
		var easypost_package_price    =   jQuery("input[id='easypost_manual_insurance']").map(function(){return jQuery(this).val();}).get();
		var easypost_custom_desc      =   jQuery("input[id='easypost_manual_custom_desc']").map(function(){return jQuery(this).val();}).get();
		var manual_signature          =   jQuery("input[id='easypost_signature_option']").map(function(){return jQuery(this).val();}).get();
		var signature                 =   manual_signature[packageOrder];
		var packageWeight             =   manual_weight_arr[packageOrder];
		var packageLength             =   manual_length_arr[packageOrder];
		var packageWidth              =   manual_width_arr[packageOrder];
		var packageHeight             =   manual_height_arr[packageOrder];
		var packageService            =   shipping_service_easypost[packageOrder];
		var flatrate_box              =   jQuery(".easypost_flatrate_box").map(function(){
			if(jQuery(this).length)
				return jQuery(this).val();}).get();
		var warehouse_values           =  jQuery(".easypost_multiwarehouse_box").map(function(){
			return jQuery(this).val();}).get();	
		var warehouse_option           =   warehouse_values[packageOrder];

		jQuery('.elex_easypost_available_services').hide();
		jQuery('#rates_loader_img'+packageOrder).show();
		if(warehouse_option === ''){
			alert('select warehouse for rates');
		}
		
			var updateServiceAction = jQuery.ajax({
			type: 'post',
			url: ajaxurl,
			data: { action:'elex_easypost_update_shipping_services',
			order_id :jQuery("input#easypost_manual_order_id").val(),
			 weight: packageWeight, length: packageLength, width: packageWidth, height: packageHeight, serviceSelected: packageService, packageOrder: packageOrder,flateRate:flatrate_box, warehouse: warehouse_option, custom_desc : easypost_custom_desc, signature: signature, nonce:manual_package_nonce},
			dataType: 'json',
			});
	   
		updateServiceAction.done(function(response){
			
			jQuery('#rates_loader_img'+packageOrder).hide();
			jQuery(elementId).hide();
			jQuery('#'+elementId).next().hide();
			jQuery('.cost').show();
			if(response.status === 200){
				jQuery('.wf_easypost_shipping_package_types').css('width', '100%');
				var updatedServices = response.rates_response;
				if(updatedServices.length === 0){
					jQuery('#elex_easypost_available_services_table_title').text("<?php esc_attr( 'No Services/Rates Available for Preferred Service', 'wf-easypost-woocommerce' ); ?>");
					jQuery('#wf_easypost_service_select_'+packageOrder+' tr').remove();
				}else{
					  jQuery('#wf_easypost_service_select_'+packageOrder+' tr').remove();
					  jQuery('#elex_easypost_available_services'+packageOrder).slideDown("slow");
					
					  jQuery('#'+elementId).closest('div').find('div').slideDown("slow");
					  jQuery('#'+elementId).closest('div').find('div').find('.arrow-up-easypost-elex').show();
					var tr;
					for (var i = 0; i < updatedServices.length; i++) {
						tr = jQuery('<tr/>');
							let val = updatedServices[i];
							tr.append('<tr style="padding:10px;"><td></td>');
							tr.append('<td><input type="radio" data-packageNumber="'+packageOrder+'" name="service_radio_button_easypost_elex_'+packageOrder+'" class="service_radio_button_easypost_elex_'+packageOrder+'" id="'+val.key+'" style = align:right  value="'+val.key+';'+val.cost+' "></td>');
							tr.append("<td><small>" + val.label + "</small></td>");
							tr.append('<td style=text-align:center><small> ' + val.cost+ ' USD' + '</small></td>');
						   
						
						jQuery('#wf_easypost_service_select_'+packageOrder).append(tr);
						var $radios = jQuery('input:radio[name=service_radio_button_easypost_elex_'+packageOrder+']');
								if($radios.is(':checked') === false && packageService ) {
									$radios.filter('[id='+packageService+']').attr('checked', true);
								}
							
					}

				}
			}
		});

		updateServiceAction.fail(function(jqXHR, textStatus){
			jQuery('#'+elementId).next().hide();
			jQuery('#elex_usps_easypost_update_services'+packageOrder).show();
			alert('Update Service Failed ', textStatus);
		});
		jQuery(document).on('click', '.service_radio_button_easypost_elex_'+packageOrder+'', function(){
					var service = jQuery(this).val().split(';');
					var serviceChosen = service[0];
					var serviceCost   = service[1];
					jQuery(this).attr('checked', true);
					var id_stored_packages = jQuery(this).attr('data-packageNumber') ;
					
					enabled_services = <?php echo json_encode( get_option( 'easypost_enabled_services', true ) ); ?>;
					if(typeof(jQuery(this).closest('div').parent('div').parent('div').find('.easypost_manual_service').val() )== 'undefined'){
						jQuery('#'+id_stored_packages+'elex_easypost_update_services').html(enabled_services[serviceChosen]);
						jQuery('#'+id_stored_packages+'elex_easypost_update_services').attr('value',serviceChosen);
						jQuery('#'+id_stored_packages+'easypost_services_rates').html(serviceCost);
					   
					}else{
						jQuery(this).closest('div').parent('div').parent('div').find('.easypost_manual_service').html(enabled_services[serviceChosen]);
						jQuery(this).closest('div').parent('div').parent('div').find('.easypost_manual_service').attr('value',serviceChosen);
						jQuery(this).closest('div').parent('div').parent('div').find('.easypost_services_rates').html(serviceCost);
					}
					
		 });
	}
	</script>
		<?php
	}
	public function elex_ep_create_shipment( $post ) {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
			 jQuery("a.elex_easypost_create_shipment").one("click", function() {
				if (jQuery("input[id='easypost_signature_option']").is(":checked") === true) { 
						jQuery("input[id='easypost_signature_option']").val('yes'); 
					} else { 
						jQuery("input[id='easypost_signature_option']").val('no'); 
					} 
					jQuery(this).click(function () { return false; });
					var elex_easypost_third_party_billing_api           =       jQuery('#elex_easypost_third_party_billing_api').val();
					var elex_easypost_third_party_billing_api_str       =       JSON.stringify(elex_easypost_third_party_billing_api);

					var elex_easypost_third_party_billing_country       =       jQuery('#elex_easypost_third_party_billing_country').val();
					var elex_easypost_third_party_billing_country_str   =       JSON.stringify(elex_easypost_third_party_billing_country);

					var elex_easypost_third_party_billing_zipcode       =       jQuery('#elex_easypost_third_party_billing_zipcode').val();
					var elex_easypost_third_party_billing_zipcode_str   =       JSON.stringify(elex_easypost_third_party_billing_zipcode);
					
					var elex_easypost_fedex_third_party_billing_api           =       jQuery('#elex_easypost_fedex_third_party_billing_api').val();
					var elex_easypost_fedex_third_party_billing_api_str       =       JSON.stringify(elex_easypost_fedex_third_party_billing_api);

					var elex_easypost_fedex_third_party_billing_country       =       jQuery('#elex_easypost_fedex_third_party_billing_country').val();
					var elex_easypost_fedex_third_party_billing_country_str   =       JSON.stringify(elex_easypost_fedex_third_party_billing_country);

					var elex_easypost_fedex_third_party_billing_zipcode       =       jQuery('#elex_easypost_fedex_third_party_billing_zipcode').val();
					var elex_easypost_fedex_third_party_billing_zipcode_str   =       JSON.stringify(elex_easypost_fedex_third_party_billing_zipcode);

					var post_id = "<?php echo esc_attr( $post->ID ); ?>";
					var manual_package_nonce   =   jQuery("input[id='manual_package_nonce']").map(function(){return jQuery(this).val();}).get();
					var manual_nonce       =   JSON.stringify(manual_package_nonce);

					var manual_weight_arr   =   jQuery("input[id='easypost_manual_weight']").map(function(){return jQuery(this).val();}).get();
					var manual_weight       =   JSON.stringify(manual_weight_arr);
					
					var manual_height_arr   =   jQuery("input[id='easypost_manual_height']").map(function(){return jQuery(this).val();}).get();
					var manual_height       =   JSON.stringify(manual_height_arr);
					
					var manual_width_arr    =   jQuery("input[id='easypost_manual_width']").map(function(){return jQuery(this).val();}).get();
					var manual_width        =   JSON.stringify(manual_width_arr);
					
					var manual_length_arr   =   jQuery("input[id='easypost_manual_length']").map(function(){return jQuery(this).val();}).get();
					var manual_length       =   JSON.stringify(manual_length_arr);

					var manual_custom_description    =   jQuery("input[id='easypost_manual_custom_desc']").map(function(){return jQuery(this).val();}).get();
					var manual_custom_desc        =   JSON.stringify(manual_custom_description);
					
					var manual_signature    =   jQuery("input[id='easypost_signature_option']").map(function(){return jQuery(this).val();}).get();
					var signature           =   JSON.stringify(manual_signature);

					var manual_insurance_arr    =   jQuery("input[id='easypost_manual_insurance']").map(function(){return jQuery(this).val();}).get();
					var manual_insurance        =   JSON.stringify(manual_insurance_arr);
					var easypost_manual_service = [];
						jQuery('.easypost_manual_service').each(function(e){
							easypost_manual_service.push(jQuery(this).attr('value'));
						});
					var manual_service       =   JSON.stringify(easypost_manual_service);
					var flatrate_box    =   jQuery(".easypost_flatrate_box").map(function(){
						if(jQuery(this).length)
							return jQuery(this).val();}).get();
					var flatrate_box        =   JSON.stringify(flatrate_box);
					var warehouse_addr   =   jQuery(".easypost_multiwarehouse_box").map(function(){
						if(jQuery(this).length)
							return jQuery(this).children("option:selected").val();}).get();
					var warehouse_addr        =   JSON.stringify(warehouse_addr);
				   location.href = this.href + '&weight=' + manual_weight +
					'&length=' + manual_length
					+ '&width=' + manual_width
					+ '&height=' + manual_height
					+ '&description=' + manual_custom_desc
					+ '&signature=' + signature
					+ '&insurance=' + manual_insurance
					+ '&wf_easypost_flatrate_box=' + flatrate_box
					+ '&wf_easypost_warehouse_box=' + warehouse_addr
					+ '&wf_easypost_service=' + manual_service
					+'&wf_elex_easypost_third_party_billing_api_str='+elex_easypost_third_party_billing_api_str
					+'&wf_elex_easypost_third_party_billing_country_str='+elex_easypost_third_party_billing_country_str
					+'&wf_elex_easypost_third_party_billing_zipcode_str='+elex_easypost_third_party_billing_zipcode_str
					+'&wf_elex_easypost_fedex_third_party_billing_api_str='+elex_easypost_fedex_third_party_billing_api_str
					+'&wf_elex_easypost_fedex_third_party_billing_country_str='+elex_easypost_fedex_third_party_billing_country_str
					+'&wf_elex_easypost_fedex_third_party_billing_zipcode_str='+elex_easypost_fedex_third_party_billing_zipcode_str
					+'&nonce='+manual_nonce;
				   return false;
				});
			});
		</script>
		<?php
	}
	public function elex_ep_add_new_package( $total_packages, $shipping_price, $all_usps_flat_rate_boxes, $service_customer_selected_default, $order_id, $custom_description ) {

		?>
	<script type="text/javascript">
		
	jQuery(document).ready(function(){	
		<?php
		 $columns               = array();
		 $options               = array();
		 /**
		 * Get coloumns from multiwarehouse addons.
		 * 
		 * @since 2.1.0
		 */
		 $columns               = apply_filters( 'easypost_metabox_columns', $columns );
		/**
		 * Get coloumns from multiwarehouse addons.
		 * 
		 * @since 2.1.0
		 */
		 $options               = apply_filters( 'easypost_metabox_column_data_warehouse', $options );
		 $service_selected_name = '';
		 $empty_flat_rate       = 'None';
		 $count                 = 0;
		?>
		
		coloumns = <?php echo json_encode( $columns ); ?>;
		var packageTotal  = <?php echo esc_attr( $total_packages ); ?>;
		var packageTotals  = 0;
		var package_orderid =<?php echo esc_attr( $order_id ); ?>;
		var packagePrice = <?php echo esc_attr( $shipping_price ); ?>;  
		custom_description = <?php echo json_encode( $custom_description ); ?>;                              
		service_name_selected = <?php echo json_encode( $service_selected_name ); ?>;
		service_selected = <?php echo json_encode( $service_customer_selected_default ); ?>;   
		var url = <?php echo json_encode( untrailingslashit( plugins_url() ) . '/easypost-woocommerce-shipping/resources/images/menu-513.png' ); ?>;
		var rate_loader = <?php echo json_encode( untrailingslashit( plugins_url() ) . '/easypost-woocommerce-shipping/resources/images/load.gif' ); ?>;
		enabled_services = <?php echo json_encode( get_option( 'easypost_enabled_services', true ) ); ?>;
		empty_flat_rate = <?php echo json_encode( $empty_flat_rate ); ?>;
		selected_flat_rate_boxes  = <?php echo json_encode( $all_usps_flat_rate_boxes ); ?>;
		warehouse_box = <?php echo json_encode( $options ); ?>;
		
		 jQuery('#wf_easypost_add_package').on("click", function(){
			 var packageNumber         = jQuery('.elex_package_count').length;
			 var new_row = '';
			 new_row += '<h4 class ="elex_package_count"> Package #'+(packageNumber+1)+'</h4>';
			 new_row += '<div style="overflow-x: scroll;" id="'+packageNumber+'wf_easypost_package_list">';
			 new_row += '<table id="'+packageNumber+'wf_easypost_package_list" class="wf-shipment-package-table"  style="border:1px solid #ddd; width:100%; height: auto; font-size:medium !important; margin-bottom:5px;border-radius: 8px">';
			 new_row += '<tbody>';
			 new_row += '<tr>';
			 new_row += '<th><?php echo esc_attr( 'Wt.(OZ)', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'L', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'W', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'H', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'PREFERRED SERVICE', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'RATE', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th></th>';
			 new_row += '<th><?php echo esc_attr( ' FLAT RATE BOX', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 
			 if( coloumns){
				 for(coloumn in coloumns){
					 new_row += '<th>'+coloumns[coloumn]['title'] +'</th>';
				 }
			 }
			 new_row += '<th><?php echo esc_attr( 'CUSTOM DESCRIPTION', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'SIGNATURE', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th><?php echo esc_attr( 'INSURANCE', 'wf-usps-easypost-woocommerce' ); ?></th>';
			 new_row += '<th></th>';
			 new_row += '</tr>';
			 new_row += '<tr>';
			
			 for(var services in enabled_services){
				for(var service_value in enabled_services[services]){
					if(service_selected ===  enabled_services[services]){
						service_name_selected = services;
					}
				}
			 }
			 new_row     += '<td style="text-align: center;"><input type="text" id="easypost_manual_weight" name="easypost_manual_weight[]" size="2" value="0"></td>';
			 new_row     += '<td style="text-align: center;"><input type="text" id="easypost_manual_length" name="easypost_manual_length[]" size="2" value="0"></td>';                               
			 new_row     += '<td style="text-align: center;"><input type="text" id="easypost_manual_width" name="easypost_manual_width[]" size="2" value="0"></td>';
			 new_row     += '<td style="text-align: center;"><input type="text" id="easypost_manual_height" name="easypost_manual_height[]" size="2" value="0"></td>';
			 new_row     += '<td style="text-align: center;"><input type="hidden" id="easypost_manual_order_id" name="easypost_manual_order_id[]" size="3" value='+package_orderid+'>';
			 new_row     += '<li class="wide"><label class="select easypost_manual_service" id="'+packageNumber+'elex_easypost_update_services" value='+service_name_selected+'>'+service_selected+'</label>';
			 new_row 	 += '</td>';
			 new_row     += '<td style= "padding-bottom:8px"><label class= easypost_services_rates value= '+packagePrice+' name= easypost_services_rates[] id = '+packageNumber+'easypost_services_rates >'+packagePrice+'</label></td>'
			 new_row     +=  '<td align ="center"> <img src="'+url+'" style="height:20px" class="cost" id="'+packageNumber+'elex_easypost_update_services" name="easypost_manual_service[]" value="'+service_name_selected+'"  onclick="updateServicesFunction(this.id)"></td>';                       
			 new_row     += '<td> <li class="wide"><select class="select easypost_flatrate_box" id="easypost_flatrate_box">';
			 new_row     += '<option value="">'+empty_flat_rate+'</option>';
			 for(var keyy in selected_flat_rate_boxes){
				 for(var service_name in selected_flat_rate_boxes[keyy]){
					 var service_name_fr = selected_flat_rate_boxes[keyy][service_name]['name']+':: ';
					 var service_name_length = selected_flat_rate_boxes[keyy][service_name]['length']+'L -';
					 var service_name_width = selected_flat_rate_boxes[keyy][service_name]['width']+'W -';;
					 var service_name_height = selected_flat_rate_boxes[keyy][service_name]['height']+'H -';;
					 new_row += '<option value="'+service_name+'"  >'+service_name_fr+service_name_length+service_name_width+service_name_height+'</option>';
				 }
			 
			 }
	 
			 new_row  +='</td>';
			 if(coloumns){
				 for(coloumn in coloumns){
					 new_row     += '<td><li class="wide"><select class="select easypost_multiwarehouse_box" id="easypost_multiwarehouse_box">';
					 new_row     += '<option value="">'+empty_flat_rate+'</option>';
					 if(warehouse_box){
						 for(var keyy in warehouse_box){
						 new_row += '<option value="'+ warehouse_box[keyy]+'"  >'+warehouse_box[keyy]+'</option>';
						 }
					 }
					 new_row  +='</td>';
				 }
			 }
			 new_row     += '<td style="text-align: center;" class="sd"><input type="text" id="easypost_manual_custom_desc" name="easypost_manual_custom_desc[]" size="10" value="'+custom_description+'" /></td>';
			 new_row     += '<td style="text-align: center;" class="sd"><input type="checkbox" id="easypost_signature_option" name="easypost_signature_option" size="5" value="no" /></td>';
			new_row     += '<td class="sd" style="text-align: center;"><input type="text" id="easypost_manual_insurance" name="easypost_manual_insurance[]" size="2" value="0"></td>';
			 new_row     += '<td style="text-align: center;"><a class="wf_easypost_package_line_remove">&#x26D4;</a></td>';
			 new_row     +='<td style="text-align: center;"><input type="hidden" name="manual_package_nonce" id="manual_package_nonce" value="<?php echo esc_attr( wp_create_nonce( '_wpnonce' ) ); ?>" /></td>'
			 new_row     += '</tr>';
			 new_row +='</tbody>';
			 new_row +='</table>';
		  
			 new_row += '<img src="'+rate_loader+'" align="center" style="padding-left:390px;height:150px" id="rates_loader_img'+(packageNumber)+'" class="rates_loader_img">';
			 new_row +='</div>';
			 availabaleServiceHtmlNewPackage = '<div class="elex_easypost_available_services" id = elex_easypost_available_services'+packageNumber+'>';
			 availabaleServiceHtmlNewPackage += '<span id="elex_easypost_available_services_table_title" style="font-weight:bold;"><?php esc_attr( 'Available Service/Rates', 'wf-easypost-woocommerce' ); ?>:</span>';
			 availabaleServiceHtmlNewPackage += '<span class="arrow-down-easypost-elex dashicons dashicons-arrow-down" style="align: left;" ></span>';
			 availabaleServiceHtmlNewPackage += '<span class="arrow-up-easypost-elex dashicons dashicons-arrow-up" style="align: left;" ></span>';
			 availabaleServiceHtmlNewPackage += '<div class="elex-easypost-shipment-package-div">';
			 availabaleServiceHtmlNewPackage += '<table id="wf_easypost_service_select_'+packageNumber+'" class="wf-shipment-package-table" style="margin-bottom: 5px;margin-top: 5px;box-shadow:.5px .5px 5px lightgrey;">';                  
			 availabaleServiceHtmlNewPackage += '<tr>';
			 availabaleServiceHtmlNewPackage += '<th></th>';
			 availabaleServiceHtmlNewPackage += '<th></th>';
			 availabaleServiceHtmlNewPackage += '<th style="text-align:center;padding:5px;"><?php esc_attr( 'Service Name', 'wf-easypost-woocommerce' ); ?></th>';
			 availabaleServiceHtmlNewPackage += '</tr>';         
			 availabaleServiceHtmlNewPackage += '</table>';
			 availabaleServiceHtmlNewPackage += '</div>';
			 availabaleServiceHtmlNewPackage += '</div>';
			 var shippingBlock ='<li>'+ new_row + availabaleServiceHtmlNewPackage  +'</li>';
		  

			 //jQuery('#'+packagenewbox+'wf_easypost_package_list').after(shippingBlock);
			 jQuery('.elex_ep_package_data_list').append(shippingBlock);
			 jQuery('.rates_loader_img').hide();
			 jQuery('.elex_easypost_available_services').hide();

		 });
		 
		 jQuery(document).on('click', '.wf_easypost_package_line_remove', function(){
			 jQuery(this).closest('table').remove();
			 jQuery('.elex_easypost_available_services').hide();
			 
		 });
		 jQuery(".easypost_manual_service").on("click", function() {
			 var usps_service = ['First','Priority','Express','LibraryMail','ParcelSelect','FirstClassMailInternational','FirstClassPackageInternationalService','PriorityMailInternational','ExpressMailInternational'];
			 if(usps_service.indexOf(jQuery(this).val()) === -1)
			 {  
				var va = jQuery(this.id);
				jQuery('#'+va['selector'][0]+'easypost_flatrate_box').find(":selected").val('');
				jQuery('#'+va['selector'][0]+'easypost_flatrate_box').find(":selected").text('None');   
			 }
		 });
	
	 });
	

</script>
		<?php
	}

	public function wf_easypost_metabox_content() {
		global $post;
		global $woocommerce;
		$shipmentId                        = '';
		$order                             = $this->wf_load_order( $post->ID );
		$order_id                          = $order->get_id();
		$shipping_price                    = $order->get_shipping_total();
		$shipping_service_data             = $this->wf_get_shipping_service_data( $order );
		$rates_settings                    = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$general_settings                  = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$return_settings                   = get_option( 'woocommerce_WF_EASYPOST_ID_return_settings', null );
		$label_settings                    = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$custom_description                = ! empty( $label_settings['customs_description'] ) ? $label_settings['customs_description'] : '';
		$elex_ep_status_log                = isset( $general_settings ['status_log'] ) && 'yes' === $general_settings ['status_log'] ? true : false;
		$service_customer_selected_default = $shipping_service_data['shipping_service_name'];
		$download_url                      = admin_url( '/?wf_easypost_shipment_confirm=' . base64_encode( $shipmentId . '|' . $post->ID ) . '&button_name=' );
		$easypost_labels                   = get_post_meta( $post->ID, 'wf_easypost_labels', true );
		$easypost_return_labels            = get_post_meta( $post->ID, 'wf_easypost_return_labels', true );
		$easypost_customer                 = get_post_meta( $post->ID, 'wf_easypost_insurance', true );
		$stored_packages                   = get_post_meta( $post->ID, '_wf_easypost_stored_packages', true );
		$woocommerce_countries             = $woocommerce->countries->get_allowed_countries();
		$all_usps_flat_rate_boxes          = include 'data-wf-flat-rate-boxes.php';
		// Third Party Billing Checkout Page Details Fetching.
		if ( 'yes' === $label_settings['third_party_billing'] && 'yes' === $label_settings['third_party_checkout'] ) {
			$third_party_account_check = ! empty( get_post_meta( $order->get_id(), 'third_party_bill_account_checkout', true ) ) ? get_post_meta( $order->get_id(), 'third_party_bill_account_checkout', true ) : '';
			$third_party_country_check = ! empty( get_post_meta( $order->get_id(), 'third_party_bill_country_checkout', true ) ) ? get_post_meta( $order->get_id(), 'third_party_bill_country_checkout', true ) : '';
			$third_party_zipcode_check = ! empty( get_post_meta( $order->get_id(), 'third_party_bill_zipcode_checkout', true ) ) ? get_post_meta( $order->get_id(), 'third_party_bill_zipcode_checkout', true ) : '';
		}
		if ( 'yes' === $label_settings['fedex_third_party_billing'] && 'yes' === $label_settings['fedex_third_party_checkout'] ) {
			$fedex_third_party_account_check = ! empty( get_post_meta( $order->get_id(), 'fedex_third_party_bill_account_checkout', true ) ) ? get_post_meta( $order->get_id(), 'fedex_third_party_bill_account_checkout', true ) : '';
			$fedex_third_party_country_check = ! empty( get_post_meta( $order->get_id(), 'fedex_third_party_bill_country_checkout', true ) ) ? get_post_meta( $order->get_id(), 'fedex_third_party_bill_country_checkout', true ) : '';
			$fedex_third_party_zipcode_check = ! empty( get_post_meta( $order->get_id(), 'fedex_third_party_bill_zipcode_checkout', true ) ) ? get_post_meta( $order->get_id(), 'fedex_third_party_bill_zipcode_checkout', true ) : '';
		}
		$third_party_account_detail = ! empty( $third_party_account_check ) ? $third_party_account_check : $label_settings['third_party_apikey'];
		$third_party_country_detail = ! empty( $third_party_country_check ) ? $third_party_country_check : $label_settings['third_party_country'];
		$third_party_zipcode_detail = ! empty( $third_party_zipcode_check ) ? $third_party_zipcode_check : $label_settings['third_party_zip'];
		$fedex_third_party_account_detail = ! empty( $fedex_third_party_account_check ) ? $fedex_third_party_account_check : $label_settings['fedex_third_party_apikey'];
		$fedex_third_party_country_detail = ! empty( $fedex_third_party_country_check ) ? $fedex_third_party_country_check : $label_settings['fedex_third_party_country'];
		$fedex_third_party_zipcode_detail = ! empty( $fedex_third_party_zipcode_check ) ? $fedex_third_party_zipcode_check : $label_settings['fedex_third_party_zip'];
		$total_packages             = is_array( $stored_packages ) ? count( $stored_packages ) : 0;
		$this->elex_ep_add_new_package( $total_packages, $shipping_price, $all_usps_flat_rate_boxes, $service_customer_selected_default, $order_id, $custom_description );
		$this->elex_ep_update_service();
		$this->elex_ep_create_shipment( $post );

		if ( empty( $easypost_labels ) ) {
			if ( ELEX_EASYPOST_RETURN_ADDON_STATUS && isset( $return_settings['enable_return_label'] ) && $return_settings['enable_return_label'] ) {
				include ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH . 'includes/html-easypost-print-return-labels.php';
			}
			if ( empty( $stored_packages ) && ! is_array( $stored_packages ) ) {
				echo '<strong>' . esc_attr( 'Auto generate packages.', 'wf-easypost' ) . '</strong></br>';
				?>
				<a class="button button-primary tips easypost_generate_packages" href="<?php echo esc_url( wp_nonce_url( admin_url( '/?wf_easypost_generate_packages=' . base64_encode( $post->ID ) ) , '_wpnonce' ) ); ?>" data-tip="<?php echo esc_html( 'Generate Packages', 'wf-easypost' ); ?>">
					<?php echo esc_attr( 'Generate Packages', 'wf-easypost' ); ?>
				</a>
				<?php
			} else {
				?>
				<a class="easypost_generate_packages button" href="<?php echo esc_attr( wp_nonce_url( admin_url( '/?wf_easypost_generate_packages=' . esc_attr( base64_encode( $post->ID ) ) ), '_wpnonce' ) ); ?>" data-tip="<?php echo esc_attr( 'Generate Packages', 'wf-easypost' ); ?>" style="float: right;overflow: hidden;">
					<span class="dashicons dashicons-update help_tip" data-tip="<?php echo esc_attr( 'Re-generate the Packages', 'wf-easypost' ); ?> "  style="padding-top: 2px;"></span>

				</a>
				<?php
				if ( 'yes' === $label_settings['third_party_billing'] ) {
					?>
				<div class ="elex_extra_api">
					<h4>EasyPost Third Party Account Details UPS:</h4>
						
						<?php esc_attr( 'Account No', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Enter the UPS third party account details."></span> <input type="text" name="third_party_billing_api" id="elex_easypost_third_party_billing_api" value=<?php echo esc_attr( $third_party_account_detail ); ?>> <br>

					<h4>Account Address:</h4>

						<?php esc_attr( 'Country', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Select the UPS third party country."></span> <select name="third_party_billing_country" id="elex_easypost_third_party_billing_country" value=<?php echo esc_attr( $third_party_country_detail ); ?>>
						<?php
						foreach ( $woocommerce_countries as $key => $value ) {
							if ( $key === $third_party_country_detail ) {
								?>
								<option selected value=<?php echo esc_attr( $key ); ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							} else {
								?>
								<option value=<?php echo esc_attr( $key ); ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							}
						}
						?>
						</select></br></br>
						
						<?php esc_attr( 'Zipcode', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Enter the postcode/zipcode for the UPS third party."></span> <input type="text" name="third_party_billing_zip" id="elex_easypost_third_party_billing_zipcode"  value=<?php echo esc_attr( $third_party_zipcode_detail ); ?>><br>
				</div>   
					<?php
				}
				if ( 'yes' === $label_settings['fedex_third_party_billing'] ) {
					?>
				<div class ="elex_extra_api">
					<h4>EasyPost Third Party Account Details FedEx:</h4>
						
						<?php esc_attr( 'Account No', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Enter the FedEx third party account details."></span> <input type="text" name="fedex_third_party_billing_api" id="elex_easypost_fedex_third_party_billing_api" value=<?php echo esc_attr( $fedex_third_party_account_detail ); ?>> <br>

					<h4>Account Address:</h4>

						<?php esc_attr( 'Country', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Select the FedEx third party country."></span> <select name="fedex_third_party_billing_country" id="elex_easypost_fedex_third_party_billing_country" value=<?php echo esc_attr( $fedex_third_party_country_detail ); ?>>
						<?php
						foreach ( $woocommerce_countries as $key => $value ) {
							if ( $key === $fedex_third_party_country_detail ) {
								?>
								<option selected value=<?php echo esc_attr( $key ); ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							} else {
								?>
								<option value=<?php echo esc_attr( $key ); ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							}
						}
						?>
						</select></br></br>
						
						<?php esc_attr( 'Zipcode', 'wf-easypost' ); ?><span class="woocommerce-help-tip" data-tip="Enter the postcode/zipcode for the FedEx third party."></span> <input type="text" name="fedex_third_party_billing_zip" id="elex_easypost_fedex_third_party_billing_zipcode"  value=<?php echo esc_attr( $fedex_third_party_zipcode_detail ); ?>><br>
				</div>   
					<?php
				}
				?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					<?php if ( 'yes' === $label_settings['third_party_billing'] || 'yes' === $label_settings['fedex_third_party_billing'] ) { ?>
						jQuery(".elex_extra_api").css('display', 'inline-block');
					<?php } ?>
			'</a>'

				});
			</script>
				<?php
				if ( 'yes' === $label_settings['third_party_billing'] || 'yes' === $label_settings['fedex_third_party_billing'] ) {
					echo '<br><br>';
				}
				echo '<strong>' . esc_attr( 'Initiate your shipment.', 'wf-easypost' ) . '</strong></br>';
				echo '<ul class ="elex_ep_package_data_list">';
				$all_usps_flat_rate_boxes = include 'data-wf-flat-rate-boxes.php';
				$selected_flat_rate_box   = isset( $rates_settings['selected_flat_rate_boxes'] ) ? $rates_settings['selected_flat_rate_boxes'] : array();
				$wf_easypost              = new WF_Easypost();
				$selected_flat_rate_box   = $wf_easypost->selected_flat_rate_box_format_data( $selected_flat_rate_box );
				$default_shipping_method  = '';
				if ( ! empty( $selected_flat_rate_box ) ) {
					$shipping_methods = $order->get_shipping_methods();
					if ( ! empty( $shipping_methods ) ) {
						$shipping_method         = array_shift( $shipping_methods );
						$default_shipping_method = $shipping_method['method_id'];
					}
				}
				$count                       = 0;
				$easypost_flat_rate_box_name = get_post_meta( $post->ID, 'easypost_flat_rate_box_name', true );
			
				$total_packages              = count( $stored_packages );

				$order_id = $order->get_id();
				$this->elex_ep_status_logger( $stored_packages, $order_id, 'Package Data', $elex_ep_status_log );
				if ( $this->vendor_check ) {
					$vendor_shipping_service = array();
					$line_items_shipping     = $order->get_items( 'shipping' );
					foreach ( $line_items_shipping as $item ) {

						$meta_data = $item->get_meta( 'seller_id' );
						if ( empty( $meta_data ) ) {
							$meta_data = $item->get_meta( 'vendor_id' );
						}
						if ( $meta_data ) {
							$vendor_shipping_service[ $meta_data ] = array(
								'seller_id'             => $meta_data,
								'shipping_method_id '   => $item->get_method_id(),
								'shipping_service_name' => $item->get_name(),
							);
						}
					}
				}
				foreach ( $stored_packages as $stored_package_key => $stored_package ) {

					if ( $this->vendor_check && isset( $stored_package['origin'] ) && isset( $stored_package['origin']['vendor_id'] ) && ! empty( $stored_package['origin']['vendor_id'] ) && ! empty( $vendor_shipping_service ) && isset( $vendor_shipping_service[ $stored_package['origin']['vendor_id'] ] ) && isset( $vendor_shipping_service[ $stored_package['origin']['vendor_id'] ]['shipping_service_name'] ) && ! empty( $vendor_shipping_service[ $stored_package['origin']['vendor_id'] ]['shipping_service_name'] ) ) {
						$service_customer_selected = $vendor_shipping_service[ $stored_package['origin']['vendor_id'] ]['shipping_service_name'];
					
					} else {
						$service_customer_selected = $service_customer_selected_default;
					}
					$check_flat_rate = $this->elex_check_flat_rate_service( $service_customer_selected );
				
					$order_id = $order->get_id();
					$this->elex_ep_status_logger( $stored_package['BoxCount'], $order_id, 'Package BOX', $elex_ep_status_log );

					for ( $i = 1; $i <= $stored_package['BoxCount']; $i++ ) {
						$package_number = $count + 1;
						$columns        = array();
						/**
						 * Get metabox coloumn of multiwarehouse.
						 * 
						 * @since 2.1.0
						 */
						$columns        = apply_filters( 'easypost_metabox_columns', $columns );
						echo '<li>';
						echo '<h4>' . esc_attr( 'Package #' . esc_attr( $package_number ), 'wf-easypost' ) . '</h4>';
						?>
					<div style="overflow-x: scroll;" id="<?php echo esc_attr( $count ) . 'wf_easypost_package_list'; ?>">
					<table id="<?php echo esc_attr( $count ) . 'wf_easypost_package_list'; ?>" class='wf-shipment-package-table'  style="border:1px solid #ddd; width:100%; height: auto; font-size:medium !important; margin-bottom:5px;border-radius: 8px;'>
										  <?php
											echo '<tr style="background: #212931; text-align: center;">';
											echo '<th>' . esc_attr( 'Wt.', 'wf-easypost' ) . '(' . esc_attr( $this->weight_ounce_unit ) . ')</th>';
											echo '<th>' . esc_attr( 'L', 'wf-easypost' ) . '(' . esc_attr( $this->dim_unit ) . ')</th>';
											echo '<th>' . esc_attr( 'W', 'wf-easypost' ) . '(' . esc_attr( $this->dim_unit ) . ')</th>';
											echo '<th>' . esc_attr( 'H', 'wf-easypost' ) . '(' . esc_attr( $this->dim_unit ) . ')</th>';
											echo '<th>' . esc_attr( 'PREFERRED SERVICE', 'wf-easypost' ) . '</th>';
											echo '<th>' . esc_attr( 'RATE', 'wf-easypost' ) . '</th>';
											echo '<th>  </th>';
											echo '<th>' . esc_attr( 'FLAT RATE BOX', 'wf-easypost' ) . '</th>';
											echo '<th>' . esc_attr( 'CUSTOM DESCRIPTION', 'wf-easypost' ) . '</th>';
											echo '<th>' . wp_kses_post( 'SIGNATURE', 'wf-easypost' ) . '</th>';
											if ( ! empty( $columns ) ) {
												foreach ( $columns as $column ) {
													echo '<th>' . esc_attr( $column['title'], 'wf-easypost' ) . '</th>';
												}
											}
											echo '<th>' . esc_attr( 'INSURANCE', 'wf-easypost' ) . '</th>';
											echo '<th>';
											echo '</th>';
											echo '</tr>';
											$dimensions = $this->get_dimension_from_package( $stored_package, $easypost_customer );
											if ( is_array( $dimensions ) ) {
												?>
						<tr>
						   
							<td style="text-align: center;"><input type="text" id="easypost_manual_weight" name="easypost_manual_weight[]" size="3" value="<?php echo esc_attr( $dimensions['WeightOz'] ); ?>" /></td>     
							<td style="text-align: center;"><input type="text" id="easypost_manual_length" name="easypost_manual_length[]" size="3" value="<?php echo esc_attr( $dimensions['Length'] ); ?>" /></td>
							<td style="text-align: center;"><input type="text" id="easypost_manual_width" name="easypost_manual_width[]" size="2" value="<?php echo esc_attr( $dimensions['Width'] ); ?>" /></td>
							<td style="text-align: center;"><input type="text" id="easypost_manual_height" name="easypost_manual_height[]" size="3" value="<?php echo esc_attr( $dimensions['Height'] ); ?>" /></td>
							<td style="text-align: center;">
							<input type="hidden" id="easypost_manual_order_id" name="easypost_manual_order_id[]" size="3" value="<?php echo esc_attr( $order->get_id() ); ?>" />
												<?php
												$enabled_services = array();

												foreach ( array_keys( $this->easypost_services )  as $key => $carrier_name ) {
													if ( ! empty( $rates_settings['easypost_carrier'] )
														&& in_array( $carrier_name, $rates_settings['easypost_carrier'] )
														&& isset( $rates_settings['services'][ $carrier_name ] )
														&& is_array( $rates_settings['services'][ $carrier_name ] ) ) {
														foreach ( $rates_settings['services'][ $carrier_name ] as $service_code => $service_data ) {

															if ( in_array( 'UPS', $rates_settings['easypost_carrier'] ) && in_array( 'UPSDAP', $rates_settings['easypost_carrier'] ) ) {
																if ( 'UPS' === $carrier_name ) {
																	$service_data['enabled'] = 0;
																}
															}
															if ( empty( $service_data['enabled'] ) ) {
																continue;
															}

															$service_name = ! empty( $service_data['name'] )
															? $service_data['name']
															: $this->easypost_services[ $carrier_name ]['services'][ $service_code ];
															// conflict between priority mail express and UPSDAP expressand
															if ( 'Express (UPSDAP)' === $service_customer_selected ) {
																if ( 'USPS' === $carrier_name && 'Express' === $service_code ) {
																	continue;
																}
															}
															if ( 'Express (UPS)' === $service_customer_selected ) {
																if ( 'USPS' === $carrier_name && 'Express' === $service_code ) {
																	continue;
																}
															}
															if ( empty( $enabled_services[ $service_code ] ) ) {
																$enabled_services[ $service_code ] = $service_name;
															}
														}
													}
												}

												$available_services = array_keys( $enabled_services );

												if ( empty( $available_services ) && empty( $rates_settings['flat_rate_boxes_domestic_carrier'] ) ) {
													echo "<span style='color: #f00'>Please enable some service from plugin settings page</span>";
													return;
												} else {
													if ( $easypost_flat_rate_box_name && $check_flat_rate ) {
														$customer_type_service_name              = $service_customer_selected;
														$flat_rate_box_service_customer_selected = $this->wf_label_generation_flat_service( $service_customer_selected );
													}
													?>


								<li class="wide">
								   
														<?php
														// To select default service as customer selected service//18-12
														$service_carrier_name   = '';
														$service_selected_name  = '';
														$service_selected_names = '';
														$default_service_check  = true;
														update_option( 'easypost_enabled_services', $enabled_services );
														 // To Select the flat rate services Name even though Non-flat service is disable
														if ( $easypost_flat_rate_box_name && $check_flat_rate ) {
															foreach ( $this->easypost_services as $key => $service_code ) {
																foreach ( $service_code['services'] as $service => $value ) {
																	if ( $value === $flat_rate_box_service_customer_selected ) {
																		 $service_carrier_name   = $service;
																		 $service_selected_names = $customer_type_service_name;
																		echo '<label class="easypost_manual_service"
                                                     value="' . esc_attr( $service ) . '" name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $customer_type_service_name ) . '</label>';
																	}
																}
															}
														}   
													
														foreach ( $enabled_services as $service_code => $service_name ) {
															
															if ( ( empty( $easypost_flat_rate_box_name ) || ! $check_flat_rate ) && $service_name === $service_customer_selected ) {
																$default_service_check      = false;
																	$service_selected_name  = $service_code;
																	$service_selected_names = $service_name;
																	
																	echo '<label class="easypost_manual_service"
                                                         value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
															}
														}
														// To Get the Default shipment services
															 $flag = 0;// For removing conflict between priority USPS and Priority CanadaPost
														if ( $default_service_check && ( empty( $easypost_flat_rate_box_name ) || ! $check_flat_rate ) ) {
															$current_order_id = get_option( 'current_order_id_easypost_elex' );
															$current_order    = $this->wf_load_order( $current_order_id );
															$domestic         = array( $rates_settings['country'] );// sender country
															if ( 'US' === $rates_settings['country'] ) {
																$domestic = array( 'US', 'PR', 'VI' );// If sender is US consider US territories as domestic
															}
															if ( 'None' === $label_settings['easypost_default_domestic_shipment_service'] || 'None' === $label_settings['easypost_default_international_shipment_service'] ) {
																echo '<label class="easypost_manual_service" value="" name= "easypost_manual_service[]" id="' . esc_attr( $count ) . 'elex_easypost_update_services"></label>';
															}
															foreach ( $this->easypost_services as $key => $service_code ) {
																foreach ( $service_code['services'] as $service_code => $service_name ) {
																	if ( in_array( $current_order->shipping_country, $domestic ) ) {

																		if ( 1 !== $flag && in_array( 'UPSDAP', $rates_settings['easypost_carrier'] ) && is_int( strpos( $service_name, '(UPSDAP)' ) ) && $service_code === $label_settings['easypost_default_domestic_shipment_service'] ) {
																				 $service_carrier_name   = $service_code;
																				 $service_selected_names = $service_name;
																				 echo '<label class="easypost_manual_service"
																		 value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" 	' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																					 break;
																		} elseif ( 1 !== $flag && ! in_array( 'UPSDAP', $rates_settings['easypost_carrier'] ) && is_int( strpos( $service_name, '(UPS)' ) ) && $service_code === $label_settings['easypost_default_domestic_shipment_service'] ) {
																			$service_carrier_name   = $service_code;
																			$service_selected_names = $service_name;
																			echo '<label class="easypost_manual_service"
															value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																					 break;
																		} elseif ( 1 !== $flag && ! is_int( strpos( $service_name, '(UPSDAP)' ) ) && ! is_int( strpos( $service_name, '(UPS)' ) ) && $service_code === $label_settings['easypost_default_domestic_shipment_service'] ) {
																			if ( 'Priority' === $service_code ) {
																				$flag = 1;
																			}
																			$service_carrier_name   = $service_code;
																			$service_selected_names = $service_name;
																			echo '<label class="easypost_manual_service"
                                                                 value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																			break;
																		}
																	} else {
																		// to remove conflict between upsdap and ups services
																		if ( in_array( 'UPSDAP', $rates_settings['easypost_carrier'] ) && is_int( strpos( $service_name, '(UPSDAP)' ) ) && $service_code === $label_settings['easypost_default_international_shipment_service'] ) {
																							   $service_carrier_name   = $service_code;
																							   $service_selected_names = $service_name;
																							   echo '<label class="easypost_manual_service"
                                                            value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																								  break;
																		} elseif ( ! in_array( 'UPSDAP', $rates_settings['easypost_carrier'] ) && is_int( strpos( $service_name, '(UPS)' ) ) && $service_code === $label_settings['easypost_default_international_shipment_service'] ) {
																								   $service_carrier_name   = $service_code;
																								   $service_selected_names = $service_name;
																								   echo '<label class="easypost_manual_service"
															value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																							   break;
																		} elseif ( ! is_int( strpos( $service_name, '(UPSDAP)' ) ) && ! is_int( strpos( $service_name, '(UPS)' ) ) && $service_code === $label_settings['easypost_default_international_shipment_service'] ) {
																			$service_carrier_name   = $service_code;
																			$service_selected_names = $service_name;
																			echo '<label class="easypost_manual_service"
															value="' . esc_attr( $service_code ) . '"name= "easypost_manual_service[]" id=' . esc_attr( $count ) . '"elex_easypost_update_services">' . esc_attr( $service_name ) . '</label>';
																			break;
																		}
																	}
																}
															}
														}
												}
												?>
						 </li>

						</td>
						<td style="padding-bottom:8px; text-align: center;">
												<?php
												echo '<label class= easypost_services_rates value= ' . esc_attr( $shipping_price ) . ' name= easypost_services_rates[] id = ' . esc_attr( $count ) . 'easypost_services_rates >' . esc_attr( $shipping_price ) . '</label>';
												?>
							 </td>
						   
												<?php
												$url = untrailingslashit( plugins_url() ) . '/easypost-woocommerce-shipping/resources/images/menu-513.png';
												?>
						<td  align="center" >
												<?php
												echo '<img src=' . esc_url( $url ) . "  title =' Show Available Services/Rates 'style='height:20px;' class='cost' id='" . esc_attr( $count ) . "elex_easypost_update_services'value =" . esc_attr( $service_selected_name ) . " name='easypost_manual_service[]'  onclick='updateServicesFunction(this.id) '>";
												?>
						</td>
						
						<td align="center" id = "<?php echo esc_attr( $count ) . 'easypost_flatrate_box'; ?>">
															<?php
															echo '<li class="wide">
                            <select class="select easypost_flatrate_box" id=' . esc_attr( $count ) . ' style="overflow:scroll">';

															$empty_flat_rate                     = esc_attr( 'None', 'wf-easypost' );
															$international_flat_rate_restriction = array( 'RegionalRateBoxA-1', 'RegionalRateBoxA-2', 'RegionalRateBoxB-1', 'RegionalRateBoxB-2' );
															$selected_flatrate_box_status        = false;

															if ( $easypost_flat_rate_box_name && $check_flat_rate ) {

																$service_key = '';
																foreach ( $this->easypost_services as $key => $service_code ) {
																	if ( 'FedEx' === $key ) {
																		$customer_service_name = explode( '(', $customer_type_service_name );
																		foreach ( $service_code['services'] as $service_code => $service_name ) {
																			if ( $customer_service_name[0] === $service_code ) {
																				$service_key = $key;
																				break;
																			}
																		}
																	}
																}

																foreach ( $easypost_flat_rate_box_name as $key => $box_name ) {
																	foreach ( $all_usps_flat_rate_boxes as $boxes => $box_data ) {
																		foreach ( $box_data as $box_no => $box ) {
																			if ( 'FedEx' !== $service_key && 'USPS:' . esc_attr( $box_no ) === $easypost_flat_rate_box_name[ $key ] ) {
																				echo '<option value="' . esc_attr( $box_no ) . '" ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																				$selected_flatrate_box_status = true;
																				break;
																			} elseif ( 'FedEx' === $service_key && 'FedEx:' . esc_attr( $box_no ) === $easypost_flat_rate_box_name[ $key ] ) {
																				echo '<option value="' . esc_attr( $box_no ) . ' " ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																				$selected_flatrate_box_status = true;
																				break;
																			}
																		}
																	}

																	if ( true === $selected_flatrate_box_status ) {
																		echo '<option value="">' . esc_attr( $empty_flat_rate ) . '</option>';
																		foreach ( $all_usps_flat_rate_boxes as $boxes => $box_data ) {
																			foreach ( $box_data as $box_no => $box ) {
																				if ( 'FedEx' !== $service_key && 'USPS:' . $box_no !== $easypost_flat_rate_box_name[ $key ] ) {
																					echo '<option value="' . esc_attr( $box_no ) . '" ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																					$selected_flatrate_box_status = false;
																				} elseif ( 'FedEx' === $service_key && 'FedEx:' . $box_no !== $easypost_flat_rate_box_name[ $key ] ) {
																					echo '<option value="' . esc_attr( $box_no ) . '" ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																					$selected_flatrate_box_status = false;
																				}
																			}
																		}
																	}
																}
															} else {
																foreach ( $all_usps_flat_rate_boxes as $boxes => $box_data ) {
																	echo '<option value="">' . esc_attr( $empty_flat_rate ) . '</option>';
																	foreach ( $box_data as $box_no => $box ) {
																		if ( $order->get_shipping_country() !== 'US' ) {
																			if ( ! in_array( $box_no, $international_flat_rate_restriction ) ) {
																				echo '<option value="' . esc_attr( $box_no ) . '" ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																			}
																		} else {

																			echo '<option value="' . esc_attr( $box_no ) . '" ' . selected( $default_shipping_method, WF_EASYPOST_ID . ':' . $boxes . ':' . $box_no ) . '>' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['name'], 1 ) ) . ':: ' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['length'], 1 ) ) . 'L -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['width'], 1 ) ) . 'W -' . esc_attr( print_r( $all_usps_flat_rate_boxes[ $boxes ][ $box_no ]['height'], 1 ) ) . 'H</option>';
																		}
																	}
																}
															}

															echo '</select></li>';
															?>
						
						</td>
												<?php
												if ( ! empty( $columns ) ) {
													foreach ( $columns as $column ) {

														$options    = array();
														/**
														 * Get metabox coloumn of multi warehouse addon
														 * 
														 * @since 2.1.0
														 */
														$option_val = apply_filters( 'easypost_metabox_column_data_warehouse', $options );

														echo '<td id ="elex_multiwarehouse_data">';
														echo '<li class="wide"><select class="select easypost_multiwarehouse_box" id="easypost_multiwarehouse_box">';
														echo '<option value="">' . esc_attr( $empty_flat_rate ) . '</option>';
														if ( ! empty( $option_val ) ) {
															foreach ( $option_val as $option_data ) {
																echo '<option value="' . esc_attr( $option_data ) . '"' . selected( $option_data ) . '>' . esc_attr( $option_data ) . '</option>';
															}
														}
														echo '</select></li>';
														echo '</td>';
													}
												}

												?>
						<td style="text-align: center;" class='sd'><input type="text" id="easypost_manual_custom_desc" name="easypost_manual_custom_desc[]" size="10" value="<?php echo esc_attr( $custom_description ); ?>" /></td>
						<td style="text-align: center;" class='sd'><input type="checkbox" id="easypost_signature_option" name="easypost_signature_option" size="5" value=<?php echo checked( ( isset( $label_settings['signature_option'] ) && ! empty( $label_settings['signature_option'] ) && 'yes' === $label_settings['signature_option'] ), true ); ?> /></td>
						<td style="text-align: center;" class='sd'><input type="text" id="easypost_manual_insurance" name="easypost_manual_insurance[]" size="2" value="<?php echo esc_attr( $dimensions['InsuredValue'] ); ?>" /></td>
						   <td style="text-align: center;"><a class="wf_easypost_package_line_remove">&#x26D4;</a></td>
						   <td style="text-align: center;"><input type="hidden" name="manual_package_nonce" id="manual_package_nonce" value="<?php echo esc_attr( wp_create_nonce( '_wpnonce' ) ); ?>" /></td>
						</tr>
												<?php
											}
											echo '</table>';
											?>
					<td>
					 <img src=" <?php echo esc_attr( untrailingslashit( plugins_url() ) ) . '/easypost-woocommerce-shipping/resources/images/load.gif'; ?>"  align="center" style="padding-left:390px;height:150px" id="<?php echo 'rates_loader_img' . esc_attr( $count ); ?>" class="rates_loader_img">
					 </td>
						<?php

								update_option( 'packing_request_from_metabox', 'yes' );
									// here comes the code for showing rates table
									$availabale_service_html          = '<div class="elex_easypost_available_services" id = "elex_easypost_available_services">';
									$availabale_service_html         .= '<span id="elex_easypost_available_services_table_title" style="font-weight:bold;">' . esc_attr( 'Available Service/Rates', 'wf-easypost-woocommerce' ) . ':</span>';
									$availabale_service_html         .= '<span class="arrow-down-easypost-elex dashicons dashicons-arrow-down" style="align: left;" ></span>';
									$availabale_service_html         .= '<span class="arrow-up-easypost-elex dashicons dashicons-arrow-up" style="align: left;" ></span>';
									$availabale_service_html         .= '<div class="elex-easypost-shipment-package-div">';
									$availabale_service_html         .= '<table id="wf_easypost_service_select_' . $count . '" class="wf-shipment-package-table" style="margin-bottom: 5px;margin-top: 10px;box-shadow:.10px .10px 10px lightgrey;">';
										$availabale_service_html     .= '<tr>';
											$availabale_service_html .= '<th></th>';
											$availabale_service_html .= '<th></th>';
											$availabale_service_html .= '<th style="text-align:left;padding:5px;">' . esc_attr( 'Service Name', 'wf-easypost-woocommerce' ) . '</th>';
											$availabale_service_html .= '<th style="text-align:left;">' . esc_attr( 'Cost (USD)', 'wf-easypost-woocommerce' ) . ' </th>';
										$availabale_service_html     .= '</tr>';
									   $availabale_service_html      .= '</table>';
									$availabale_service_html         .= '</div>';
									$availabale_service_html         .= '</div>';
									echo wp_kses_post( $availabale_service_html );
									$count++;

					}
				}
				echo '</div>';
				echo '</li>';
				echo '</ul>';
				echo '<hr style="border-color:#0074a2">';
				echo '<a class="wf-action-button wf-add-button" style="font-size: 12px;" id="wf_easypost_add_package">Add Package</a>';

				update_option( 'packing_request_from_metabox', 'no' );

				?>

			<a class="button tips onclickdisable elex_easypost_create_shipment button-primary" href="<?php echo esc_attr( wp_nonce_url( $download_url . 'create', '_wpnonce' ) ); ?>" data-tip="<?php esc_attr( 'Create Shipment', 'wf-easypost' ); ?>"><?php echo esc_attr( 'Create Shipment', 'wf-easypost' ); ?></a><hr style="border-color:#0074a2">
	</div>

			
	
			<script type="text/javascript">

				jQuery(document).ready(function(){
					jQuery('.rates_loader_img').hide();
					jQuery('.arrow-down-easypost-elex').hide();
					jQuery('.arrow-up-easypost-elex').hide();
					jQuery('.elex_easypost_available_services').hide();
				});

				jQuery(document).on('click', '.arrow-up-easypost-elex', function(){
					jQuery(this).siblings('div').slideUp("slow");
					jQuery(this).hide();
					jQuery(this).siblings('.arrow-down-easypost-elex').show();
				});

				 jQuery(document).on('click', '.arrow-down-easypost-elex', function(){
					jQuery(this).hide();
					jQuery(this).siblings('.arrow-up-easypost-elex').show();
					jQuery(this).siblings('div').slideDown("slow");
				});

			</script>
				<?php
			}
		} else {
			if ( ELEX_EASYPOST_RETURN_ADDON_STATUS && $return_settings['enable_return_label'] ) {
				include ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH . 'includes/html-wf-create-return-label.php';
				include ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH . 'includes/html-easypost-generate-return-labels.php';
			}
			foreach ( $easypost_labels as $easypost_label ) {
				?>
				<strong>Tracking No: </strong><?php echo esc_attr( $easypost_label['tracking_number'] ); ?><br/>
				<a href="<?php echo esc_attr( $easypost_label['url'] ); ?>" target="_blank" class="button button-primary tips" data-tip="<?php esc_attr( 'Print Label ', 'wf-easypost' ); ?>">
					<?php
					if ( strstr( $easypost_label['url'], '.png' ) || strstr( $easypost_label['url'], '.pdf' ) || strstr( $easypost_label['url'], '.zpl' ) ) :
						?>
						 <?php echo esc_attr( 'Print Label', 'wf-easypost' ); ?>
					<?php else : ?>
						<?php echo esc_attr( 'View Label', 'wf-easypost' ); ?>
					<?php endif; ?>
				</a>
				<br/>
				<?php
				if ( isset( $easypost_label['commercial_invoice_url'] ) && ! empty( $easypost_label['commercial_invoice_url'] ) ) {
					?>
				
				<strong>Commercial Invoice: </strong><br/>
				<a href="<?php echo esc_attr( $easypost_label['commercial_invoice_url'] ); ?>" target="_blank" class="button button-primary tips" data-tip="<?php esc_attr( 'Print Label ', 'wf-easypost' ); ?>">
					<?php
					if ( strstr( $easypost_label['commercial_invoice_url'], '.png' ) || strstr( $easypost_label['commercial_invoice_url'], '.pdf' ) || strstr( $easypost_label['commercial_invoice_url'], '.zpl' ) ) :
						?>
						 <?php echo esc_attr( 'Print Commercial Invoice', 'wf-easypost' ); ?>
					<?php else : ?>
						<?php echo esc_attr( 'View Commercial Invoice', 'wf-easypost' ); ?>
					<?php endif; ?>
				</a>
				<br/>
					<?php
				}
			}
			?>
			<hr style="border-color:#0074a2">
			<?php
			if ( ELEX_EASYPOST_RETURN_ADDON_STATUS && $easypost_return_labels ) {

				include ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH . 'includes/html-easypost-print-return-labels.php';
			}
			$download_url = wp_nonce_url( admin_url( '/?wf_easypost_void_shipment=' . base64_encode( $post->ID ) ), '_wpnonce' );
			?>
			<strong>Cancel The Shipment Created</strong><br>
			<a class="button tips" href="<?php echo esc_attr( $download_url ); ?>" data-tip="<?php esc_attr( 'Cancel Label(s)', 'wf-easypost' ); ?>">Cancel Shipment</a><hr style="border-color:#0074a2">
			<?php
		}
	}
	public function elex_check_flat_rate_service( $shipping_service ) {
		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		if ( ! empty( $shipping_service ) ) {
			if ( $shipping_service === $rates_settings['flat_rate_boxes_text'] || $shipping_service === $rates_settings['flat_rate_boxes_express_text'] || $shipping_service === $rates_settings['flat_rate_boxes_first_class_text'] || strpos( $shipping_service , $rates_settings['flat_rate_boxes_fedex_one_rate_text'] ) !== false ) {
				return true;
			} elseif ( $shipping_service === $rates_settings['flat_rate_boxes_text_international_mail'] || $shipping_service === $rates_settings['flat_rate_boxes_text_international_express'] || $shipping_service === $rates_settings['flat_rate_boxes_text_first_class_mail_international'] ) {
			  return true;
			} else {
				return false;
			}
		}
	}
	private function wf_get_shipment_description( $order ) {
		$shipment_description = '';
		$order_items          = $order->get_items();

		foreach ( $order_items as $order_item ) {
			$product_data          = wc_get_product( $order_item['variation_id'] ? $order_item['variation_id'] : $order_item['product_id'] );
			$title                 = $product_data->get_title();
			$shipment_description .= $title . ', ';
		}

		if ( '' === $shipment_description ) {
			$shipment_description = 'Package/customer supplied.';
		}

		return $shipment_description;
	}

	// To view the values on Status Log
	public function elex_ep_status_logger( $message = '', $order_id = '', $type = '', $elex_ep_status_log = false ) {
		if ( $elex_ep_status_log ) {
			$log         = wc_get_logger();
			   $head     = '<------------------- Easypost ' . $order_id . $type . ' ------------------->/n';
			   $log_text = $head . print_r( (object) $message, true );
			   $context  = array( 'source' => 'eh_easypost_log' . $order_id );
			   $log->log( 'debug', $log_text, $context );
		}

	}
	public function wf_get_package_data( $order, $return_addon = '' ) {
		$easypost_packing_settings = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$easypost_packing_method   = isset( $easypost_packing_settings['packing_method'] ) ? $easypost_packing_settings['packing_method'] : 'per_item';
		if ( '' !== $return_addon ) {
			$easypost_packing_method = 'per_item';
		}
		$package     = $this->wf_create_package( $order );
		$wf_easypost = new WF_Easypost();

		// if multi-vendor
		if ( $wf_easypost->vendor_check ) {
			/**
			 * To get label packages.
			 * 
			 * @since 1.1.0
			 */
			$package            = apply_filters( 'elex_easypost_filter_label_packages', array( $package ) );
			$package_data_array = array();
			foreach ( $package as $key => $val ) {
		
				$package_data_array_temp = $wf_easypost->wf_get_api_rate_box_data( $val, $easypost_packing_method );
				foreach ( $package_data_array_temp as $package_count => $package_data ) {
					$package_data['origin'] = $val['origin'];
					$package_data_array[]   = $package_data;
				}
			}
		} else {
			$package_data_array = $wf_easypost->wf_get_api_rate_box_data( $package, $easypost_packing_method );
		}

		return $package_data_array;
	}

	public function wf_create_package( $order ) {

		$parts      = isset( $_SERVER['REQUEST_URI'] ) ? map_deep( wp_unslash( parse_url( sanitize_text_field( $_SERVER['REQUEST_URI'] ) ) ), 'sanitize_text_field' ) : '';
		$query_data = isset( $parts['query'] ) ? sanitize_text_field( $parts['query'] ) : '';
		parse_str( $query_data, $query );
		$count                 = 0;
		$orderItems            = $order->get_items();
		$orderId               = $order->get_id();
		/**
		 * Get elex easypost order package.
		 * 
		 * @since 1.0.0
		 */
		$orderItems            = apply_filters( 'elex_easypost_order_package', $orderItems, $orderId );
		$shipping_method_title = '';
		foreach ( $order->get_items( 'shipping' ) as $shipping_item_obj ) {
			if ( $this->vendor_check ) {
				$vendor_id = ! empty( get_post_meta( $orderId, 'elex_vendor_id', true ) ) ? get_post_meta( $orderId, 'elex_vendor_id', true ) : ( ! empty( $shipping_item_obj->get_meta( 'seller_id' ) ) ? $shipping_item_obj->get_meta( 'seller_id' ) : $shipping_item_obj->get_meta( 'vendor_id' ) );
				update_post_meta( $orderId, 'wf_elex_ep_vendor_shipping_service' . $vendor_id, $shipping_item_obj->get_method_title() );
			}
			
			$shipping_method_title = $shipping_item_obj->get_method_title();
		}

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_wpnonce' ) ) {
			if ( isset( $_GET['product_id'] ) ) {
				$return_product_id       = isset( $_GET['product_id'] ) ? map_deep( wp_unslash( array( stripslashes( sanitize_text_field( $_GET['product_id'] ) ) ) ), 'sanitize_text_field' ) : '';
				$return_product_id       = str_replace( array( ']', '[', '"' ), '', $return_product_id );
				$return_product_id       = explode( ',', $return_product_id[0] );
				$return_product_quantity = isset( $_GET['quantity'] ) ? map_deep( wp_unslash( array( stripslashes( sanitize_text_field( $_GET['quantity'] ) ) ) ), 'sanitize_text_field' ) : '';
				$return_product_quantity = str_replace( array( ']', '[', '"' ), '', $return_product_quantity );
				$return_product_quantity = explode( ',', $return_product_quantity[0] );
			}
		}
		foreach ( $orderItems as $orderItem ) {
			$item_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
			if ( isset( $query['button_name'] ) && isset( $query['button_name'] ) && 'return' === $query['button_name'] ) {
				if ( in_array( $item_id, $return_product_id ) ) {
					$product_data      = wc_get_product( $item_id );
					$name              = $orderItem->get_name();
					$items[ $item_id ] = array(
						'data'     => $product_data,
						'quantity' => $return_product_quantity[ $count ],
					);
					$count++;
				}
			} else {
				$product_data      = wc_get_product( $item_id );
				$name              = $orderItem->get_name();
				$items[ $item_id ] = array(
					'data'       => $product_data,
					'quantity'   => $orderItem['qty'],
					'product_id' => $item_id,
				);
			}
		}
		$package['contents']    = $items;
		$package['id']          = $item_id;
		$package['destination'] = array(
			'country' => $order->shipping_country,
			'state'   => $order->shipping_state,
			'zip'     => $order->shipping_postcode,
			'city'    => $order->shipping_city,
			'street1' => $order->shipping_address_1,
			'street2' => $order->shipping_address_2,
		);
		$package['orderId']     = $orderId;
		$package['title']       = $shipping_method_title;
		return $package;
	}


	/*
	 function to handle bulk actions
	 *
	 * @ since  1.7.2
	 * @ access Public
	 */
	public function wf_easypost_orders_bulk_action( $post_id = '', $label_type = '' ) {
		$return_settings = get_option( 'woocommerce_WF_EASYPOST_ID_return_settings', null );
		if ( $post_id ) {
			$value                        = array();
			$easypost_label_details_array = get_post_meta( $post_id, 'wf_easypost_labels', true );
			if ( empty( $easypost_label_details_array ) || ( 'yes' === $return_settings['enable_return_label'] && 'yes' === $return_settings['enable_auto_return_label'] ) ) {
				$this->bulk_label  = true;
				$this->error_email = true;
				$this->wf_easypost_generate_packages( $post_id );
				$this->wf_easypost_shipment_confirm( $post_id, $label_type );
				return;
			}
		} else {
			$this->error_email = false;
			$wp_list_table     = _get_list_table( 'WP_Posts_List_Table' );
			$action            = $wp_list_table->current_action();
			if ( 'create_shipment_easypost' === $action ) {
				$label_exist_for    = '';
				$no_default_service = '';
				if ( isset( $_REQUEST['post'] ) ) {
					foreach ( map_deep( wp_unslash( $_REQUEST['post'] ), 'sanitize_text_field' ) as $post_id ) {
						$easypost_label_details_array = get_post_meta( $post_id, 'wf_easypost_labels', true );
						if ( ! empty( $easypost_label_details_array ) ) {
							$label_exist_for .= $post_id . ', ';
						} else {
							$this->bulk_label = true;
							$this->wf_easypost_generate_packages( $post_id );
							$this->wf_easypost_shipment_confirm( $post_id );
						}
					}

					$sendback = add_query_arg(
						array(
							'easypost_bulk_label' => 1,
							'ids'                 => join( ',', map_deep( wp_unslash( $_REQUEST['post'] ), 'sanitize_text_field' ) ),
							'already_exist'       => rtrim( $label_exist_for, ', ' ),
							'no_default_service'  => rtrim( $no_default_service, ', ' ),
						),
						admin_url( 'edit.php?post_type=shop_order' )
					);
					wp_redirect( $sendback );
					exit();
				}
			}
		}
	}


	public function bulk_easypost_label_admin_notice() {
		global $post_type, $pagenow;
		$order_ids = array();
		if ( 'edit.php' === $pagenow && 'shop_order' === $post_type && isset( $_REQUEST['easypost_bulk_label'] ) ) {
			if ( isset( $_REQUEST['ids'] ) ) {
				$order_ids = explode( ',', map_deep( wp_unslash( $_REQUEST['ids'] ), 'sanitize_text_field' ) );
			}
			$faild_ids_str     = '';
			$success_ids_str   = '';
			$already_exist_arr = isset( $_REQUEST['already_exist'] ) ? explode( ',', map_deep( wp_unslash( $_REQUEST['already_exist'] ), 'sanitize_text_field' ) ) : '';
			if ( is_array( $order_ids ) && ! empty( $order_ids ) ) {
				foreach ( $order_ids as $key => $id ) {
					$easypost_shipment = get_post_meta( $id, 'wf_easypost_shipment_source', true );

					if ( empty( $easypost_shipment ) ) {
						$faild_ids_str .= $id . ', ';
					} elseif ( ! empty( $already_exist_arr ) && ! in_array( $id, $already_exist_arr ) ) {
						$success_ids_str .= $id . ', ';
					}
				}
			}

			$faild_ids_str   = rtrim( $faild_ids_str, ', ' );
			$success_ids_str = rtrim( $success_ids_str, ', ' );

			if ( ! empty( $faild_ids_str ) ) {
				echo '<div class="error"><p>' . esc_attr( 'Create shipment is failed for following order(s) ' . $faild_ids_str, 'wf-easypost' ) . '</p></div>';
			}

			if ( ! empty( $success_ids_str ) ) {
				echo '<div class="updated"><p>' . esc_attr( 'Successfully created shipment for following order(s) ' . $success_ids_str, 'wf-easypost' ) . '</p></div>';
			}

			if ( isset( $_REQUEST['already_exist'] ) && ! empty( map_deep( wp_unslash( $_REQUEST['already_exist'] ), 'sanitize_text_field' ) ) ) {
				echo '<div class="notice notice-success"><p>' . esc_attr( 'Shipment already exist for following order(s) ' . map_deep( wp_unslash( $_REQUEST['already_exist'] ), 'sanitize_text_field' ), 'wf-easypost' ) . '</p></div>';
			}
		}
	}
	// Function to add third party billing details on checkout page.
	public function elex_easypost_third_party_billing_checkout_content( $checkout ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		if ( isset( $label_settings['third_party_billing'] ) && 'yes' === $label_settings['third_party_billing'] && isset( $label_settings['third_party_checkout'] ) && 'yes' === $label_settings['third_party_checkout'] ) {

			echo '<div id="third_party_billing_checkout"><h3>' . esc_attr( 'UPS Third Party Billing Details' ) . '</h3>';
			woocommerce_form_field(
				'third_party_bill_account_checkout',
				array(
					'type'        => 'text',
					'label'       => esc_attr( 'Third Party Account No' ),
					'placeholder' => esc_attr( 'Enter the UPS third party account' ),
				),
				$checkout->get_value( 'third_party_bill_account_checkout' )
			);
			woocommerce_form_field(
				'third_party_bill_country_checkout',
				array(
					'type'  => 'country',
					'label' => esc_attr( 'Third Party Country' ),
				),
				$checkout->get_value( 'third_party_bill_country_checkout' )
			);
			woocommerce_form_field(
				'third_party_bill_zipcode_checkout',
				array(
					'type'        => 'text',
					'label'       => esc_attr( 'Third Party Zip Code' ),
					'placeholder' => esc_attr( 'Enter the UPS third party zip code' ),
				),
				$checkout->get_value( 'third_party_bill_zipcode_checkout' )
			);
			echo '</div>';
		}
		if ( isset( $label_settings['fedex_third_party_billing'] ) && 'yes' === $label_settings['fedex_third_party_billing'] && isset( $label_settings['fedex_third_party_checkout'] ) && 'yes' === $label_settings['fedex_third_party_checkout'] ) {

			echo '<div id="fedex_third_party_billing_checkout"><h3>' . esc_attr( 'FedEx Third Party Billing Details' ) . '</h3>';
			woocommerce_form_field(
				'fedex_third_party_bill_account_checkout',
				array(
					'type'        => 'text',
					'label'       => esc_attr( 'Third Party Account No' ),
					'placeholder' => esc_attr( 'Enter the FedEx third party account' ),
				),
				$checkout->get_value( 'fedex_third_party_bill_account_checkout' )
			);
			woocommerce_form_field(
				'fedex_third_party_bill_country_checkout',
				array(
					'type'  => 'country',
					'label' => esc_attr( 'Third Party Country' ),
				),
				$checkout->get_value( 'fedex_third_party_bill_country_checkout' )
			);
			woocommerce_form_field(
				'fedex_third_party_bill_zipcode_checkout',
				array(
					'type'        => 'text',
					'label'       => esc_attr( 'Third Party Zip Code' ),
					'placeholder' => esc_attr( 'Enter the FedEx third party zip code' ),
				),
				$checkout->get_value( 'fedex_third_party_bill_zipcode_checkout' )
			);
			echo '</div>';
		}
	}
	// save third party billing values
	public function elex_easypost_save_third_party_billing_checkout_details( $order_id ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		if ( ! ( isset( $_POST['woocommerce-process-checkout-nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce-process-checkout-nonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
		if ( isset( $label_settings['third_party_billing'] ) && 'yes' === $label_settings['third_party_billing'] && isset( $label_settings['third_party_checkout'] ) && 'yes' === $label_settings['third_party_checkout'] ) {
			if ( ! empty( $_POST['third_party_bill_account_checkout'] ) ) {
				update_post_meta( $order_id, 'third_party_bill_account_checkout', sanitize_text_field( $_POST['third_party_bill_account_checkout'] ) );
			}

			if ( ! empty( $_POST['third_party_bill_country_checkout'] ) ) {
				update_post_meta( $order_id, 'third_party_bill_country_checkout', sanitize_text_field( $_POST['third_party_bill_country_checkout'] ) );
			}

			if ( ! empty( $_POST['third_party_bill_zipcode_checkout'] ) ) {
				update_post_meta( $order_id, 'third_party_bill_zipcode_checkout', sanitize_text_field( $_POST['third_party_bill_zipcode_checkout'] ) );
			}
		}
		if ( isset( $label_settings['fedex_third_party_billing'] ) && 'yes' === $label_settings['fedex_third_party_billing'] && isset( $label_settings['fedex_third_party_checkout'] ) && 'yes' === $label_settings['fedex_third_party_checkout'] ) {
			if ( ! empty( $_POST['fedex_third_party_bill_account_checkout'] ) ) {
				update_post_meta( $order_id, 'fedex_third_party_bill_account_checkout', sanitize_text_field( $_POST['fedex_third_party_bill_account_checkout'] ) );
			}

			if ( ! empty( $_POST['fedex_third_party_bill_country_checkout'] ) ) {
				update_post_meta( $order_id, 'fedex_third_party_bill_country_checkout', sanitize_text_field( $_POST['fedex_third_party_bill_country_checkout'] ) );
			}

			if ( ! empty( $_POST['fedex_third_party_bill_zipcode_checkout'] ) ) {
				update_post_meta( $order_id, 'fedex_third_party_bill_zipcode_checkout', sanitize_text_field( $_POST['fedex_third_party_bill_zipcode_checkout'] ) );
			}
		}
	}
	public function elex_easypost_third_party_checkout_field_display_admin_order_meta( $order ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		if ( isset( $label_settings['third_party_billing'] ) && 'yes' === $label_settings['third_party_billing'] && isset( $label_settings['third_party_checkout'] ) && 'yes' === $label_settings['third_party_checkout'] ) {
			echo '<p><strong>' . esc_attr( 'Third party account UPS' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'third_party_bill_account_checkout', true ) ) . '</p>';
			echo '<p><strong>' . esc_attr( 'Third party country UPS' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'third_party_bill_country_checkout', true ) ) . '</p>';
			echo '<p><strong>' . esc_attr( 'Third party zipcode UPS' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'third_party_bill_zipcode_checkout', true ) ) . '</p>';
		}
		if ( isset( $label_settings['fedex_third_party_billing'] ) && 'yes' === $label_settings['fedex_third_party_billing'] && isset( $label_settings['fedex_third_party_checkout'] ) && 'yes' === $label_settings['fedex_third_party_checkout'] ) {
			echo '<p><strong>' . esc_attr( 'Third party account FedEx' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'fedex_third_party_bill_account_checkout', true ) ) . '</p>';
			echo '<p><strong>' . esc_attr( 'Third party country FedEx' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'fedex_third_party_bill_country_checkout', true ) ) . '</p>';
			echo '<p><strong>' . esc_attr( 'Third party zipcode FedEx' ) . ':</strong> <br/>' . esc_attr( get_post_meta( $order->get_id(), 'fedex_third_party_bill_zipcode_checkout', true ) ) . '</p>';
		}
	}

	private function get_special_rates_eligibility( $service ) {
		if ( 'MediaMail' === $service ) {
			$special_rates = 'USPS.MEDIAMAIL';

		} elseif ( 'LibraryMail' === $service ) {
			$special_rates = 'USPS.LIBRARYMAIL';

		} else {
			$special_rates = false;
		}
		return $special_rates;
	}

	private function get_package_signature( $order ) {
		$order_items             = $order->get_items();
		$higher_signature_option = 0;
		foreach ( $order_items as $order_item ) {
			$signature = get_post_meta( $order_item['product_id'], '_wf_easypost_signature', 1 );

			if ( empty( $signature ) || ! is_numeric( $signature ) ) {
				$signature = 0;
			}
			if ( $signature > $higher_signature_option ) {
				$higher_signature_option = $signature;
			}
		}
		return $this->signature_options[ $higher_signature_option ];
	}
	private function get_package_dry_ice( $order ) {
		$order_items             = $order->get_items();
		$higher_signature_option = 0;
		foreach ( $order_items as $order_item ) {
			$dry_ice = get_post_meta( $order_item['product_id'], '_wf_dry_ice_code' );
		}
		return $dry_ice;
	}

	public function get_failed_shipment_email( $post_id ) {
		$return_settings       = get_option( 'woocommerce_WF_EASYPOST_ID_return_settings', null );
		$autogenerate_settings = get_option( 'woocommerce_WF_EASYPOST_ID_auto_generate_settings', null );
		if ( true === $this->error_email ) {
			if ( isset( $return_settings['enable_failed_email'] ) && 'yes' === $return_settings['enable_failed_email'] ) {

				$to            = get_option( 'admin_email' );
				$email_subject = $return_settings['failed_email_subject'];
				$email_content = $return_settings['failed_email_content'];
				wp_mail( $to, $email_subject . ' [' . $post_id . ']', $email_content, '', '' );
				return;
			}
			if ( isset( $autogenerate_settings['auto_label_enable_failed_email'] ) && 'yes' === $autogenerate_settings['auto_label_enable_failed_email'] ) {

				$to            = get_option( 'admin_email' );
				$email_subject = $autogenerate_settings['auto_label_failed_email_subject'];
				$email_content = $autogenerate_settings['auto_label_failed_email_content'];
				wp_mail( $to, $email_subject . ' [' . $post_id . ']', $email_content, '', '' );
				return;
			}
			return;
		}
	}

	public function wf_easypost_shipment_confirm( $post_id = '', $label_type = '' ) {

		// return false;
		// }
		$packing_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_packing_settings', null );
		$rates_settings     = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$general_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$label_settings     = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$return_settings    = get_option( 'woocommerce_WF_EASYPOST_ID_return_settings', null );
		$packing_method     = isset( $packing_settings['packing_method'] ) ? $packing_settings['packing_method'] : 'per_item';
		$easypost_debug     = isset( $general_settings['debug_mode'] ) && 'yes' === $general_settings ['debug_mode'] ? true : false;
		$elex_ep_status_log = isset( $general_settings ['status_log'] ) && 'yes' === $general_settings ['status_log'] ? true : false;
		$parts              = isset( $_SERVER['REQUEST_URI'] ) ? map_deep( wp_unslash( parse_url( sanitize_text_field( $_SERVER['REQUEST_URI'] ) ) ), 'sanitize_text_field' ) : '';
		$query_data         = isset( $parts['query'] ) ? sanitize_text_field( $parts['query'] ) : '';
		parse_str( $query_data, $query );
		if ( ! ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK && ! ELEX_EASYPOST_RETURN_ADDON_STATUS ) {
			if ( ! $this->wf_user_check() ) {
				echo "You don't have admin privileges to view this page.";
				exit;
			}
		}
		$wfeasypostmsg                = '';
		$wf_easypost_selected_service = '';
		// Load Easypost.com Settings.
		$api_mode = isset( $general_settings['api_mode'] ) ? $general_settings['api_mode'] : 'Live';
		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_wpnonce' ) ) { // Input var okay.
			if ( ! $this->bulk_label ) {
				$query_string = isset( $_GET['wf_easypost_shipment_confirm'] ) ? explode( '|', base64_decode( map_deep( wp_unslash( $_GET['wf_easypost_shipment_confirm'] ), 'sanitize_text_field' ) ) ) : '';
				$post_id      = $query_string[1];
			}

			$wf_easypost_selected_service = isset( $_GET['wf_easypost_service'] ) ? map_deep( wp_unslash( $_GET['wf_easypost_service'] ), 'sanitize_text_field' ) : '';
			$selected_flatrate_box        = isset( $_GET['wf_easypost_flatrate_box'] ) ? map_deep( wp_unslash( $_GET['wf_easypost_flatrate_box'] ), 'sanitize_text_field' ) : '';
			update_post_meta( $post_id, 'wf_easypost_selected_flat_rate_service', $selected_flatrate_box );
			if ( ! $this->bulk_label && isset( $query['button_name'] ) && 'return' === $query['button_name'] ) {
				$order = $this->wf_load_order( $post_id );
				if ( $order->shipping_country === $rates_settings['country'] ) {
					$wf_easypost_return_selected_service = json_encode( array( $return_settings['easypost_default_domestic_return_shipment_service'] ) );
				} else {
					$wf_easypost_return_selected_service = json_encode( array( $label_settings['easypost_default_international_shipment_service'] ) );
				}

				update_post_meta( $post_id, 'wf_easypost_selected_service', $wf_easypost_return_selected_service );

			} else {

				update_post_meta( $post_id, 'wf_easypost_selected_service', $wf_easypost_selected_service );

			}
		}

		$order = $this->wf_load_order( $post_id );
		if ( ! $order ) {
			return;
		}
		$package_data_per_item = array();
		$package_data_array    = $this->wf_get_package_data( $order );

		$index = 0;
		if ( 'per_item' === $packing_method ) {
			foreach ( $package_data_array as $key => $value ) {
				if ( $package_data_array[ $key ]['BoxCount'] > 1 ) {
					for ( $i = 0;$i < $package_data_array[ $key ]['BoxCount'];$i++ ) {
						$package_data_per_item [ $index ] = $package_data_array[ $key ];
						$index++;
					}
				} else {
					$package_data_per_item[ $index ] = $package_data_array[ $key ];
					$index++;
				}
			}
			$package_data_array = $package_data_per_item;
		}if ( isset( $query['button_name'] ) ) {
			if ( 'return' === $query['button_name'] ) {
				foreach ( $package_data_array as $key => $value ) {
					if ( $package_data_array[ $key ]['BoxCount'] > 1 ) {
						for ( $i = 0;$i < $package_data_array[ $key ]['BoxCount'];$i++ ) {
							$package_data_per_item [ $index ] = $package_data_array[ $key ];
							$index++;
						}
					} else {
						$package_data_per_item[ $index ] = $package_data_array[ $key ];
						$index++;
					}
				}
				$package_data_array = $package_data_per_item;
			}
		}
		$package_data_array = $this->manual_packages( $package_data_array ); // Filter data with manual packages
		if ( empty( $package_data_array ) ) {
			return false;
		}

		$easypost_printLabelType = isset( $label_settings['printLabelType'] ) ? $label_settings['printLabelType'] : 'PNG';
		$easypost_packing_method = isset( $packing_settings['packing_method'] ) ? $packing_settings['packing_method'] : 'per_item';
	
		$message          = '';
		$shipment_details = array();

		$shipping_service_data = $this->wf_get_shipping_service_data( $order );
		$default_service_type  = $shipping_service_data['shipping_service'];
		$carrier_services_bulk = include 'data-wf-services.php';
		$bulk_service          = array();
		$service_selected      = false;
		$carrier_name          = '';
		foreach ( $carrier_services_bulk as $service => $code ) {
			if ( $this->bulk_label ) {

				// Bulk action Flat rate label generation
				$shipping_service_data['shipping_service_name'] = $this->wf_label_generation_flat_service( $shipping_service_data['shipping_service_name'] );
				if ( 'Local pickup' === $shipping_service_data['shipping_service_name'] ) {
					$service_selected = true;
					$default_service_type  = 'local_pickup' ;
					break;
				} elseif ( in_array( $shipping_service_data['shipping_service_name'], $code['services'] ) ) {
					$service_selected = true;
					$bulk_service = $code['services'];
					foreach ( $bulk_service as $key => $value ) {
						if ( $value === $shipping_service_data['shipping_service_name'] ) {
							$default_service_type  = $key ;
							update_post_meta( $post_id, 'wf_easypost_selected_service', $key );
							$carrier_name = $service;
					
						}
					}
				} elseif ( ! empty( $rates_settings['services'] ) ) {
					foreach ( $rates_settings['services'][ $service ] as $service_code => $service_data ) {
						if ( ! empty( $service_data['name'] ) && $shipping_service_data['shipping_service_name'] === $service_data['name'] ) {
							$service_selected = true;
							$default_service_type  = $service_code ;
							update_post_meta( $post_id, 'wf_easypost_selected_service', $service_code );
							$carrier_name = $service;  

								
						}  
					}
				} 
				if ( false === $service_selected ) {
					// For bulk shipment When Customer choose Flate rate service or Free Shipping.
					if ( $this->bulk_label ) {
						if ( $order->shipping_country === $rates_settings['country'] ) {
							$default_service_type = $label_settings['easypost_default_domestic_shipment_service'];
						} else {
							$default_service_type = $label_settings['easypost_default_international_shipment_service'];
						}
						if ( array_key_exists( $default_service_type, $code['services'] ) ) {
							update_post_meta( $post_id, 'wf_easypost_selected_service', $default_service_type );
							$carrier_name = $service;
							break;
						}                   
					}
				}
			}
		}
		if ( ! $this->bulk_label ) {
				$carrier_name = array();

			foreach ( $carrier_services_bulk as $service => $code ) {
				   $service_codes         = get_post_meta( $order->id, 'wf_easypost_selected_service', true );
				   $decoded_service_array = json_decode( $service_codes );
				foreach ( $decoded_service_array as $key => $value ) {
					if ( array_key_exists( $value, $code['services'] ) ) {
						$carrier_name[] = $service;
					}
				}
				if ( isset( $query['button_name'] ) ) {
					if ( 'return' === $query['button_name'] ) {
						if ( 'manual' === $return_settings['return_address_addon'] ) {
							$country = $return_settings['return_country_addon'];
						} else {
							$country = $rates_settings['country'];
						}
						if ( $order->shipping_country === $country ) {
							$default_service_type = json_encode( array( $return_settings['easypost_default_domestic_return_shipment_service'] ) );
						} else {
							$default_service_type = json_encode( array( $return_settings['easypost_default_international_return_shipment_service'] ) );
						}
						$bulk_service = $code['services'];
						if ( array_key_exists( $default_service_type, $code['services'] ) ) {
							update_post_meta( $post_id, 'wf_easypost_selected_service', $default_service_type );
							$carrier_name = $service;
							break;
						}
					}
				}
			}
		}

		$shipment_details['options']['print_custom_1'] = $order->id;
		$shipment_details['options']['label_format']   = $easypost_printLabelType;

		// Signature option
		$signature_option         = $this->get_package_signature( $order );
		$product_signature_check  = $label_settings['signature_option'];
		$product_signature_option = $this->get_package_signature( $order );

		if ( 'yes' === $product_signature_check ) {
			$shipment_details['options']['delivery_confirmation'] = 'ADULT_SIGNATURE';
		} elseif ( ! empty( $product_signature_option ) ) {
			$shipment_details['options']['delivery_confirmation'] = $product_signature_option;
		}
		
		$specialrate = $this->get_special_rates_eligibility( $default_service_type );
		if ( ! empty( $specialrate ) ) {

			$shipment_details['options']['special_rates_eligibility'] = $specialrate;

		}
		$european_union_countries = array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' );
		$destination_country      = isset( $order->shipping_country ) ? $order->shipping_country : '';
		$ioss_number              = $label_settings['ioss_number'];
		if ( in_array( $destination_country, $european_union_countries ) && ! empty( $ioss_number ) ) {
			$shipment_details['options']['import_federal_tax_id'] = $ioss_number;
		}

		$shipping_first_name = $order->shipping_first_name;
		$shipping_last_name  = $order->shipping_last_name;
		$shipping_full_name  = $shipping_first_name . ' ' . $shipping_last_name;
		if ( isset( $query['button_name'] ) ) {
			if ( 'return' === $query['button_name'] && 'manual' === $return_settings['return_address_addon'] ) { // Addon address for return label.
				$shipment_details['from_address']['name']    = isset( $return_settings['return_name_addon'] ) ? $return_settings['return_name_addon'] : '';
				$shipment_details['from_address']['company'] = isset( $return_settings['return_company_addon'] ) ? $return_settings['return_company_addon'] : '';
				$shipment_details['from_address']['street1'] = isset( $return_settings['return_street1_addon'] ) ? $return_settings['return_street1_addon'] : '';
				$shipment_details['from_address']['street2'] = isset( $return_settings['return_street2_addon'] ) ? $return_settings['return_street2_addon'] : '';
				$shipment_details['from_address']['city']    = isset( $return_settings['return_city_addon'] ) ? $return_settings['return_city_addon'] : '';
				$shipment_details['from_address']['state']   = isset( $return_settings['return_state_addon'] ) ? $return_settings['return_state_addon'] : '';
				$shipment_details['from_address']['zip']     = isset( $return_settings['return_zip_addon'] ) ? $return_settings['return_zip_addon'] : '';
				$shipment_details['from_address']['email']   = isset( $return_settings['return_email_addon'] ) ? $return_settings['return_email_addon'] : '';
				$shipment_details['from_address']['phone']   = isset( $return_settings['return_phone_addon'] ) ? $return_settings['return_phone_addon'] : '';
				$shipment_details['from_address']['country'] = isset( $return_settings['return_country_addon'] ) ? $return_settings['return_country_addon'] : '';
			} else {
				$shipment_details['from_address']['name']    = isset( $label_settings['name'] ) ? $label_settings['name'] : '';
				$shipment_details['from_address']['company'] = isset( $label_settings['company'] ) ? $label_settings['company'] : '';
				$shipment_details['from_address']['street1'] = isset( $label_settings['street1'] ) ? $label_settings['street1'] : '';
				$shipment_details['from_address']['street2'] = isset( $label_settings['street2'] ) ? $label_settings['street2'] : '';
				$shipment_details['from_address']['city']    = isset( $label_settings['city'] ) ? $label_settings['city'] : '';
				$shipment_details['from_address']['state']   = isset( $rates_settings['state'] ) ? $rates_settings['state'] : '';
				$shipment_details['from_address']['zip']     = isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '';
				$shipment_details['from_address']['email']   = isset( $label_settings['email'] ) ? $label_settings['email'] : '';
				$shipment_details['from_address']['phone']   = isset( $label_settings['phone'] ) ? $label_settings['phone'] : '';
				$shipment_details['from_address']['country'] = isset( $rates_settings['country'] ) ? $rates_settings['country'] : '';
			}
		}
		if ( $this->bulk_label && ELEX_EASYPOST_RETURN_ADDON_STATUS && 'yes' === $return_settings['enable_auto_return_label'] && 'manual' === $return_settings['return_address_addon'] && isset( $return_settings['return_street1_addon'] ) && '' !== $return_settings['return_street1_addon'] ) {
			$shipment_details['from_address']['name']    = isset( $return_settings['return_name_addon'] ) ? $return_settings['return_name_addon'] : '';
			$shipment_details['from_address']['company'] = isset( $return_settings['return_company_addon'] ) ? $return_settings['return_company_addon'] : '';
			$shipment_details['from_address']['street1'] = isset( $return_settings['return_street1_addon'] ) ? $return_settings['return_street1_addon'] : '';
			$shipment_details['from_address']['street2'] = isset( $return_settings['return_street2_addon'] ) ? $return_settings['return_street2_addon'] : '';
			$shipment_details['from_address']['city']    = isset( $return_settings['return_city_addon'] ) ? $return_settings['return_city_addon'] : '';
			$shipment_details['from_address']['state']   = isset( $return_settings['return_state_addon'] ) ? $return_settings['return_state_addon'] : '';
			$shipment_details['from_address']['zip']     = isset( $return_settings['return_zip_addon'] ) ? $return_settings['return_zip_addon'] : '';
			$shipment_details['from_address']['email']   = isset( $return_settings['return_email_addon'] ) ? $return_settings['return_email_addon'] : '';
			$shipment_details['from_address']['phone']   = isset( $return_settings['return_phone_addon'] ) ? $return_settings['return_phone_addon'] : '';
			$shipment_details['from_address']['country'] = isset( $return_settings['return_country_addon'] ) ? $return_settings['return_country_addon'] : '';
		} elseif ( $this->bulk_label ) {
			$shipment_details['from_address']['name']    = isset( $label_settings['name'] ) ? $label_settings['name'] : '';
			$shipment_details['from_address']['company'] = isset( $label_settings['company'] ) ? $label_settings['company'] : '';
			$shipment_details['from_address']['street1'] = isset( $label_settings['street1'] ) ? $label_settings['street1'] : '';
			$shipment_details['from_address']['street2'] = isset( $label_settings['street2'] ) ? $label_settings['street2'] : '';
			$shipment_details['from_address']['city']    = isset( $label_settings['city'] ) ? $label_settings['city'] : '';
			$shipment_details['from_address']['state']   = isset( $rates_settings['state'] ) ? $rates_settings['state'] : '';
			$shipment_details['from_address']['zip']     = isset( $rates_settings['zip'] ) ? $rates_settings['zip'] : '';
			$shipment_details['from_address']['email']   = isset( $label_settings['email'] ) ? $label_settings['email'] : '';
			$shipment_details['from_address']['phone']   = isset( $label_settings['phone'] ) ? $label_settings['phone'] : '';
			$shipment_details['from_address']['country'] = isset( $rates_settings['country'] ) ? $rates_settings['country'] : '';
		}
		$shipment_details['to_address']['name']        = isset( $shipping_full_name ) ? $shipping_full_name : '';
		$shipment_details['to_address']['street1']     = isset( $order->shipping_address_1 ) ? $order->shipping_address_1 : '';
		$shipment_details['to_address']['street2']     = isset( $order->shipping_address_2 ) ? $order->shipping_address_2 : '';
		$shipment_details['to_address']['city']        = isset( $order->shipping_city ) ? $order->shipping_city : '';
		$shipment_details['to_address']['company']     = isset( $order->shipping_company ) ? $order->shipping_company : '';
		$shipment_details['to_address']['state']       = isset( $order->shipping_state ) ? $order->shipping_state : '';
		$shipment_details['to_address']['zip']         = isset( $order->shipping_postcode ) ? $order->shipping_postcode : '';
		$shipment_details['to_address']['email']       = isset( $order->billing_email ) ? $order->billing_email : '';
		$shipment_details['to_address']['phone']       = isset( $order->billing_phone ) ? $order->billing_phone : '';
		$shipment_details['to_address']['country']     = isset( $order->shipping_country ) ? $order->shipping_country : '';
		$shipment_details['to_address']['residential'] = isset( $rates_settings['show_rates'] ) && 'residential' === $rates_settings['show_rates'] ? true : '';
		if ( isset( $label_settings['return_address'] ) && 'yes' === $label_settings['return_address'] && 'return' !== $label_type && isset( $query['button_name'] ) && 'return' !== $query['button_name'] ) {
			$shipment_details['return_address']['name']    = isset( $label_settings['return_name'] ) ? $label_settings['return_name'] : '';
			$shipment_details['return_address']['company'] = isset( $label_settings['return_company'] ) ? $label_settings['return_company'] : '';
			$shipment_details['return_address']['street1'] = isset( $label_settings['return_street1'] ) ? $label_settings['return_street1'] : '';
			$shipment_details['return_address']['street2'] = isset( $label_settings['return_street2'] ) ? $label_settings['return_street2'] : '';
			$shipment_details['return_address']['city']    = isset( $label_settings['return_city'] ) ? $label_settings['return_city'] : '';
			$shipment_details['return_address']['state']   = isset( $label_settings['return_state'] ) ? $label_settings['return_state'] : '';
			$shipment_details['return_address']['zip']     = isset( $label_settings['return_zip'] ) ? $label_settings['return_zip'] : '';
			$shipment_details['return_address']['email']   = isset( $label_settings['return_email'] ) ? $label_settings['return_email'] : '';
			$shipment_details['return_address']['phone']   = isset( $label_settings['return_phone'] ) ? $label_settings['return_phone'] : '';
			$shipment_details['return_address']['country'] = isset( $label_settings['return_country'] ) ? $label_settings['return_country'] : '';
		}

		// need to find some solution for intnat
		
		if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
		}
		if ( 'Live' === $general_settings['api_mode'] ) {
			\EasyPost\EasyPost::setApiKey( $general_settings['api_key'] );
		} else {
			\EasyPost\EasyPost::setApiKey( $general_settings['api_test_key'] );
		}
		$easypost_labels       = array();
		$index                 = 0;
		$package_count         = 0;
		$selected_flatrate_box = get_post_meta( $post_id, 'wf_easypost_selected_flat_rate_service', true );
		$default_service_type  = str_replace( '[', '', $default_service_type );
		$default_service_type  = str_replace( ']', '', $default_service_type );
		$default_service_type  = str_replace( '"', '', $default_service_type );
		$default_service_type  = explode( ',', $default_service_type );
		$selected_flatrate_box = str_replace( '[', '', $selected_flatrate_box );
		$selected_flatrate_box = str_replace( ']', '', $selected_flatrate_box );
		$selected_flatrate_box = str_replace( '"', '', $selected_flatrate_box );
		$selected_flatrate_box = explode( ',', $selected_flatrate_box );
		$service_count         = 0;
		$check_ups_service     = array(
			'Ground'            => 'Ground (UPS)',
			'3DaySelect'        => '3 Day Select (UPS)',
			'2ndDayAirAM'       => '2nd Day Air AM (UPS)',
			'2ndDayAir'         => '2nd Day Air (UPS)',
			'NextDayAirSaver'   => 'Next Day Air Saver (UPS)',
			'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPS)',
			'NextDayAir'        => 'Next Day Air (UPS)',
			'Express'           => 'Express (UPS)',
			'Expedited'         => 'Expedited (UPS)',
			'ExpressPlus'       => 'Express Plus (UPS)',
			'UPSSaver'          => 'UPS Saver (UPS)',
			'UPSStandard'       => 'UPS Standard (UPS)',
		);
		$check_fedex_service  = array(
			'FIRST_OVERNIGHT'        => 'First Overnight (FedEx)',
			'PRIORITY_OVERNIGHT'     => 'Priority Overnight (FedEx)',
			'STANDARD_OVERNIGHT'     => 'Standard Overnight (FedEx)',
			'FEDEX_2_DAY_AM'         => 'FedEx 2 Day AM (FedEx)',
			'FEDEX_2_DAY'            => 'FedEx 2 Day (FedEx)',
			'FEDEX_EXPRESS_SAVER'    => 'FedEx Express Saver (FedEx)',
			'GROUND_HOME_DELIVERY'   => 'FedEx Ground Home Delivery (FedEx)',
			'FEDEX_GROUND'           => 'FedEx Ground (FedEx)',
			'INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',
			'INTERNATIONAL_ECONOMY'  => 'FedEx International Economy (FedEx)',
			'INTERNATIONAL_FIRST'    => 'FedEx International First (FedEx)',
			'FEDEX_INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',
			'FEDEX_INTERNATIONAL_CONNECT_PLUS' => 'FedEX International Connect Plus (FedEx)',
		);

		foreach ( $package_data_array as $package_data ) {

			// For checking ups service to send thirdparty account details.
			$international                = false;
			$eligible_for_customs_details = $this->is_eligible_for_customs_details( $shipment_details['from_address']['country'], $shipment_details['to_address']['country'], $shipment_details['to_address']['city'] );
			if ( $eligible_for_customs_details ) {
				$international     = true;
				$custom_line_array = array();
				$custom_line_array = $this->elex_check_international( $eligible_for_customs_details, $order, $shipment_details, $package_data );
				// dry_ice
				$dry_ices = $this->get_package_dry_ice( $order );
				if ( 'yes' === $dry_ices ) {
					$shipment_details['options']['dry_ice']        = 'true';
					$shipment_details['options']['dry_ice_weight'] = $custom_line['weight'];
				}
				//Added Commercial_invoice parameter for international order
				$shipment_details['options']['commercial_invoice_signature'] = 'IMAGE_1';
				$shipment_details['options']['commercial_invoice_letterhead'] = 'IMAGE_2';
				// for International shipping only
				$shipment_details['customs_info']['customs_certify']      = true;
				$shipment_details['customs_info']['customs_signer']       = isset( $label_settings['customs_signer'] ) ? $label_settings['customs_signer'] : '';
				$shipment_details['customs_info']['contents_type']        = 'merchandise';
				$shipment_details['customs_info']['contents_explanation'] = '';
				$shipment_details['customs_info']['restriction_type']     = 'none';
				$shipment_details['customs_info']['eel_pfc']              = 'NOEEI 30.37(a)';
			}
			$ups = false;
			$fedex = false;
			$inc = 0;
			if ( is_array( $carrier_name ) || ! empty( $carrier_name ) ) {
				if ( 'label_type' !== $label_settings['elex_shipping_label_size'] ) {
					if ( 'USPS' === $carrier_name[ $service_count ] ) {
						$shipment_details['options']['label_size'] = $label_settings['elex_shipping_label_size_usps'];
					} elseif ( 'UPS' === $carrier_name[ $service_count ] ) {
						$shipment_details['options']['label_size'] = $label_settings['elex_shipping_label_size_ups'];
					} elseif ( 'FedEx' === $carrier_name[ $service_count ] ) {
						$shipment_details['options']['label_size'] = $label_settings['elex_shipping_label_size_fedex'];
					} else {
						$shipment_details['options']['label_size'] = $label_settings['elex_shipping_label_size_canadapost'];
					}
				}
			}

			$fedex_ups_service = json_decode( stripslashes( html_entity_decode( $wf_easypost_selected_service ) ) );

			if ( isset( $fedex_ups_service ) && is_array( $fedex_ups_service ) && ! empty( $fedex_ups_service ) ) {
				if ( array_key_exists( $fedex_ups_service[ $inc ], $check_ups_service ) ) {
					if ( 'UPS' === $carrier_name[0] ) {
						$ups = true;
					} else {
						$ups = false;
					}
				} elseif ( array_key_exists( $fedex_ups_service[ $inc ], $check_fedex_service ) ) {
					
					if ( 'FedEx' === $carrier_name[0] ) {
						$fedex = true;
					} else {
						$fedex = false;
					}
				}
			}

			$inc++;

			// Multi-Vendor support
			if ( isset( $package_data['origin'] ) && get_option( 'wc_settings_wf_vendor_addon_allow_vedor_api_key' ) === 'yes' ) {
				$easypost_api_key = get_user_meta( $package_data['origin']['vendor_id'], 'vendor_easypost_api_key', true );
			} else {
				if ( 'Live' === $general_settings['api_mode'] ) {
					$easypost_api_key = $general_settings['api_key'];
				} else {
					$easypost_api_key = $general_settings['api_test_key'];
				}
			}
			// Third Party Billing Request options.
			if ( 'yes' === $label_settings['third_party_billing'] && ( $ups ) ) {
				$shipment_details['options']['bill_third_party_account']     = isset( $_GET['wf_elex_easypost_third_party_billing_api_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_third_party_billing_api_str'] ) ) ) ) : '';
				$shipment_details['options']['bill_third_party_country']     = isset( $_GET['wf_elex_easypost_third_party_billing_country_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_third_party_billing_country_str'] ) ) ) ) : '';
				$shipment_details['options']['bill_third_party_postal_code'] = isset( $_GET['wf_elex_easypost_third_party_billing_zipcode_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_third_party_billing_zipcode_str'] ) ) ) ) : '';
			} elseif ( 'yes' === $label_settings['third_party_billing'] && ( $fedex ) ) {
				$shipment_details['options']['bill_third_party_account']     = isset( $_GET['wf_elex_easypost_fedex_third_party_billing_api_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_fedex_third_party_billing_api_str'] ) ) ) ) : '';
				$shipment_details['options']['bill_third_party_country']     = isset( $_GET['wf_elex_easypost_fedex_third_party_billing_country_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_fedex_third_party_billing_country_str'] ) ) ) ) : '';
				$shipment_details['options']['bill_third_party_postal_code'] = isset( $_GET['wf_elex_easypost_fedex_third_party_billing_zipcode_str'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_elex_easypost_fedex_third_party_billing_zipcode_str'] ) ) ) ) : '';
			}

			\EasyPost\EasyPost::setApiKey( $easypost_api_key );
			if ( isset( $package_data['origin'] ) ) {
				$shipment_details['from_address']['name']    = $package_data['origin']['first_name'];
				$shipment_details['from_address']['company'] = $package_data['origin']['company'];
				$shipment_details['from_address']['street1'] = $package_data['origin']['address_1'];
				$shipment_details['from_address']['street2'] = $package_data['origin']['address_2'];
				$shipment_details['from_address']['city']    = $package_data['origin']['city'];
				$shipment_details['from_address']['state']   = $package_data['origin']['state'];
				$shipment_details['from_address']['zip']     = $package_data['origin']['postcode'];
				$shipment_details['from_address']['email']   = $package_data['origin']['email'];
				$shipment_details['from_address']['phone']   = $package_data['origin']['phone'];
				$shipment_details['from_address']['country'] = $package_data['origin']['country'];
			}

			// Warehouse address as from address
			if ( isset( $package_data['Signature'] ) && 'yes' === $package_data['Signature'] ) {
				$shipment_details['options']['delivery_confirmation'] = 'ADULT_SIGNATURE';
			}
			if ( isset( $package_data['warehouse_data'] ) && ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {
				$warehouse_address = ! empty( get_option( 'woocommerce_wf_multi_warehouse_settings' ) ) ? get_option( 'woocommerce_wf_multi_warehouse_settings' ) : array();
				if ( ! empty( $warehouse_address ) ) {
					foreach ( $warehouse_address as $warehouse_boxes => $warehouse_boxes_data ) {
						if ( $package_data['warehouse_data'] === $warehouse_boxes_data['address_title'] ) {
							$shipment_details['from_address']['name']    = $warehouse_boxes_data['origin_name'];
							$shipment_details['from_address']['company'] = $warehouse_boxes_data['address_title'];
							$shipment_details['from_address']['street1'] = $warehouse_boxes_data['origin_line_1'];
							$shipment_details['from_address']['street2'] = $warehouse_boxes_data['origin_line_2'];
							$shipment_details['from_address']['city']    = $warehouse_boxes_data['origin_city'];
							$shipment_details['from_address']['state']   = $warehouse_boxes_data['origin_state'];
							$shipment_details['from_address']['zip']     = $warehouse_boxes_data['origin'];
							$shipment_details['from_address']['email']   = $warehouse_boxes_data['shipper_email'];
							$shipment_details['from_address']['phone']   = $warehouse_boxes_data['shipper_phone_number'];
							$shipment_details['from_address']['country'] = $warehouse_boxes_data['country'];
						}
					}
				}
				$eligible_for_customs_details = $this->is_eligible_for_customs_details( $shipment_details['from_address']['country'], $shipment_details['to_address']['country'], $shipment_details['to_address']['city'] );

				if ( $eligible_for_customs_details ) {
					$international = true;
					// dry_ice
					$custom_line_array = array();
					$custom_line_array = $this->elex_check_international( $eligible_for_customs_details, $order, $shipment_details, $package_data );
					$dry_ices          = $this->get_package_dry_ice( $order );
					if ( 'yes' === $dry_ices ) {
						$shipment_details['options']['dry_ice']        = 'true';
						$shipment_details['options']['dry_ice_weight'] = $custom_line['weight'];
					}
					// for International shipping only
					$shipment_details['customs_info']['customs_certify']      = true;
					$shipment_details['customs_info']['customs_signer']       = isset( $label_settings['customs_signer'] ) ? $label_settings['customs_signer'] : '';
					$shipment_details['customs_info']['contents_type']        = 'merchandise';
					$shipment_details['customs_info']['contents_explanation'] = '';
					$shipment_details['customs_info']['restriction_type']     = 'none';
					$shipment_details['customs_info']['eel_pfc']              = 'NOEEI 30.37(a)';
				}
			}

			if ( 'yes' === $label_settings['third_party_billing'] && ( $ups ) ) {
				$shipment_details['options']['payment']['type'] = 'THIRD_PARTY';
			} elseif ( 'yes' === $label_settings['fedex_third_party_billing'] && ( $fedex ) ) {
				$shipment_details['options']['payment']['type'] = 'THIRD_PARTY';
			} else {
				$shipment_details['options']['payment']['type'] = 'SENDER';
			}

			// Third Party payment details

			if ( 'yes' === $label_settings['third_party_billing'] && ( $ups ) ) {
				$shipment_details['options']['payment']['account']     = isset( $shipment_details['options']['bill_third_party_account'] ) ? $shipment_details['options']['bill_third_party_account'] : '';
				$shipment_details['options']['payment']['country']     = isset( $shipment_details['options']['bill_third_party_country'] ) ? $shipment_details['options']['bill_third_party_country'] : '';
				$shipment_details['options']['payment']['postal_code'] = isset( $shipment_details['options']['bill_third_party_postal_code'] ) ? $shipment_details['options']['bill_third_party_postal_code'] : '';
			} elseif ( 'yes' === $label_settings['third_party_billing'] && ( $fedex ) ) {
				$shipment_details['options']['payment']['account']     = isset( $shipment_details['options']['bill_third_party_account'] ) ? $shipment_details['options']['bill_third_party_account'] : '';
				$shipment_details['options']['payment']['country']     = isset( $shipment_details['options']['bill_third_party_country'] ) ? $shipment_details['options']['bill_third_party_country'] : '';
				$shipment_details['options']['payment']['postal_code'] = isset( $shipment_details['options']['bill_third_party_postal_code'] ) ? $shipment_details['options']['bill_third_party_postal_code'] : '';
			}
			if ( $this->vendor_check && get_option( 'wc_settings_wf_vendor_addon_splitcart' ) !== 'sum_cart' ) {
				$default_service = $this->get_multivendor_packages_service( $post_id, $package_data, $order, $service_count, $this->bulk_label );
			
				if ( 'local_pickup' === $default_service ) {
					continue;
				}
			} else {
				$default_service = $default_service_type[ $service_count ];
			}
			if ( 'pack_simple' === $packing_settings['weight_packing_process'] ) {
				$custom_line['weight'] = $package_data['WeightOz'];
			}
			$tx_id = uniqid( 'wf_' . $order->id . '_' );
			update_post_meta( $order->id, 'wf_last_label_tx_id', $tx_id );
			if ( ! empty( $selected_flatrate_box[ $service_count ] ) ) {
				$selected_flatrate_box[ $service_count ]          = rtrim( $selected_flatrate_box[ $service_count ], '-2' );
				$shipment_details['parcel']['predefined_package'] = $selected_flatrate_box[ $service_count ];
			} else {
				unset( $shipment_details['parcel']['predefined_package'] );
				$shipment_details['parcel']['length'] = $package_data['Length'];
				$shipment_details['parcel']['width']  = $package_data['Width'];
				$shipment_details['parcel']['height'] = $package_data['Height'];

			}
			if ( ! $this->bulk_label ) {
				$service_count++;
			}
			$shipment_details['parcel']['weight']                     = $package_data['WeightOz'];
			$shipment_details['options']['special_rates_eligibility'] = 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL';
			// $shipment_details['parcel']['predefined_package'] = 'letter';
			if ( ( $shipment_details['from_address']['country'] !== $shipment_details['to_address']['country'] ) && ( 'none' !== $rates_settings['ex_easypost_duty'] ) ) {
				$shipment_details['options']['incoterm'] = $rates_settings['ex_easypost_duty'];
			}
			// below lines for International shipping - + customs info
			if ( $international ) {
				$m = 0;
				$shipment_details['customs_info']['customs_items'] = array();
				// if multi-vendor
				if ( isset( $package_data['origin'] ) ) {
					$index = 0;
				}
				if ( ! empty( $package_data['PackedItem'] ) ) {

					for ( $m = 0; $m < count( $package_data['PackedItem'] ); $m++ ) {
						// In box packing algorithm the individual product details are stored in object named 'meta'
						if ( 'weight_based_packing' === $packing_method ) {// weight based packing don't need any dimentions.
							$item = isset( $package_data['PackedItem'][ $index ]->meta ) ? $package_data['PackedItem'][ $index ]->meta : $package_data['PackedItem'][ $index ];
							$index++;
						} else {
							$item = isset( $package_data['PackedItem'][ $m ]->meta ) ? $package_data['PackedItem'][ $m ]->meta : $package_data['PackedItem'][ $m ];
						}
						$product_id_customs = $item->get_parent_id();
						$item               = $this->wf_load_product( $item );
						if ( isset( $package_data['Description'] ) && ! empty( $package_data['Description'] ) ) {
							$prod_title = $package_data['Description'];
						} elseif ( ! empty( $label_settings['customs_description'] ) ) {
							$prod_title = $label_settings['customs_description'];
						} else {
							$prod_title = $item->get_title();
						}
						$shipment_desc = ( strlen( $prod_title ) >= 50 ) ? substr( $prod_title, 0, 45 ) . '...' : $prod_title;
						$shipment_details['customs_info']['customs_items'][ $m ]['description'] = $shipment_desc;
						$shipment_details['customs_info']['customs_items'][ $m ]['quantity']    = 1; // $quantity;
						$shipment_details['customs_info']['customs_items'][ $m ]['value']       = $item->get_price();
						$wf_hs_code                    = get_post_meta( $item->id, '_wf_hs_code', 1 );
						$product_custom_declared_value = get_post_meta( $item->id, '_wf_easypost_custom_declared_value', true );
						if ( $product_custom_declared_value ) {
							$shipment_details['customs_info']['customs_items'][ $m ]['value'] = $product_custom_declared_value;
						} else {
							$product_custom_declared_value = get_post_meta( $product_id_customs, '_wf_easypost_custom_declared_value', true );
							if ( $product_custom_declared_value ) {
								$shipment_details['customs_info']['customs_items'][ $m ]['value'] = $product_custom_declared_value;
							}
						}

						if ( ! empty( $wf_hs_code ) ) {
							$shipment_details['customs_info']['customs_items'][ $m ]['hs_tariff_number'] = $wf_hs_code;
						}
						if ( WC()->version < '3.0' ) {
							$weight_to_send = woocommerce_get_weight( $item->weight, 'Oz' );
						} else {
							$weight_to_send = wc_get_weight( $item->weight, 'Oz' );
						}
						$shipment_details['customs_info']['customs_items'][ $m ]['weight']         = $weight_to_send;
						$shipment_details['customs_info']['customs_items'][ $m ]['origin_country'] = $shipment_details['from_address']['country'];
					}
				} else { // if($packing_method === 'per_item'){ // PackedItem will be empty , also each item will be shipped separately
					$shipment_details['customs_info']['customs_items'][0] = $custom_line_array[ $package_count ];
				}
			}
			if ( $this->bulk_label ) {
				$easypost_debug = false;
			}
			try {
				try {

					if ( isset( $query['button_name'] ) ) {
						if ( 'return' === $query['button_name'] ) {
							$shipment_details['is_return'] = true;
						}
					}
					if ( 'return' === $label_type ) {
						$shipment_details['is_return'] = true;
					}

						$this->elex_ep_status_logger( $shipment_details, $post_id, 'Request', $elex_ep_status_log );

						$shipment = \EasyPost\Shipment::create( $shipment_details );
						$this->elex_ep_status_logger( $shipment_details, $post_id, 'Response', $elex_ep_status_log );

					$this->wf_debug( "<h3>Debug mode is Enabled. Please Disable it in the <a href='" . get_site_url() . "/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wf_easypost' tartget='_blank'>settings  page</a> if you do not want to see this.</h3>" );
					$this->wf_debug( 'EasyPost CREATE SHIPMENT REQUEST: <pre style="background: rgba(158, 158, 158, 0.30);width: 90%; display: block; margin: auto; padding: 15;">' . print_r( $shipment_details, true ) . '</pre>' );

					$this->wf_debug( 'EasyPost CREATE SHIPMENT OBJECT: <pre style="background: rgba(158, 158, 158, 0.30);width: 90%; display: block; margin: auto; padding: 15;">' . print_r( $shipment, true ) . '</pre>' );
					$this->check = 'verified';

				} catch ( Exception $e ) {
					if ( ! empty( $shipment ) ) {
						$shipment_obj_array = array(
							'rate' => $shipment->lowest_rate( array_keys( $this->easypost_services ), array( $default_service ) ),
						);

						$this->wf_debug( '<br><br>EasyPost REQUEST (Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">' . print_r( $shipment_obj_array, true ) . '</pre>' );
						$this->elex_ep_status_logger( $shipment_obj_array, $post_id, 'Service select Label', $elex_ep_status_log );
						$response_obj = $shipment->buy( $shipment_obj_array );
						$shipment_id  = $shipment->id;
						if ( (float) $package_data['InsuredValue'] > 0 ) {
							$purchased_shipment = \EasyPost\Shipment::retrieve( $shipment_id );
							$response_obj       = $purchased_shipment->insure( array( 'amount' => (float) $package_data['InsuredValue'] ) );

						}

						$this->elex_ep_status_logger( $response_obj, $post_id, 'EasyPost Response But Label', $elex_ep_status_log );
						if ( isset( $this->error_email ) && true === $this->error_email ) {
							if ( isset( $response_obj ) ) {
								$this->check = 'successfull';
							} else {
								$this->check = 'Failed';
							}
						}
						if ( isset( $this->error_email ) && 'verified' !== $this->check ) {
							$email_msg = $this->get_failed_shipment_email( $post_id );
							return $email_msg;
						}
						$message      .= esc_attr( 'Create shipment failed. ', 'wf-easypost' );
						$message      .= $e->getMessage() . ' ';
						$_SESSION['wfeasypostmsg'] = 6;
						update_post_meta( $post_id, 'wfeasypostmsg', $message );
						$this->wf_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
						echo wp_kses_post( $message );
						exit;
					}
				}
				$srvc = $default_service;

				try {
					if ( ! empty( $shipment ) ) {
						$shipment_obj_array = array(
							'rate' => $shipment->lowest_rate( array_keys( $this->easypost_services ), array( $srvc ) ),
						);

						$this->wf_debug( '<br><br>EasyPost REQUEST (Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">' . print_r( $shipment_obj_array, true ) . '</pre>' );

						$this->elex_ep_status_logger( $shipment_obj_array, $post_id, 'Service select Label', $elex_ep_status_log );

						$response_obj = $shipment->buy( $shipment_obj_array );
						$shipment_id  = $shipment->id;
						if ( (float) $package_data['InsuredValue'] > 0 ) {
							$purchased_shipment = \EasyPost\Shipment::retrieve( $shipment_id );
							$response_obj       = $purchased_shipment->insure( array( 'amount' => (float) $package_data['InsuredValue'] ) );
						}
							$this->elex_ep_status_logger( $response_obj, $post_id, 'EasyPost Response But Label', $elex_ep_status_log );

						if ( isset( $this->error_email ) && true === $this->error_email ) {
							if ( isset( $response_obj ) ) {
								$this->check = 'successfull';
							} else {
								$this->check = 'Failed';
							}
						}
					} else {
						$this->wf_debug( '<pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;"> <center><font size="5">Seems like there is a connection problem. Please check your internet connection </center> </font></pre>' );
					}
				} catch ( Exception $e ) {
					if ( isset( $this->error_email ) && 'Failed' === $this->check ) {
						$email_msg = $this->get_failed_shipment_email( $post_id );
						return $email_msg;
					}
					$carrier_services = include 'data-wf-services.php';
					$carrier;
					$carrier_name;
					foreach ( $carrier_services as $service => $code ) {
						if ( array_key_exists( $default_service, $code['services'] ) ) {
							$carrier_name = $service;
						}
					}
					
					$message .= esc_attr( 'Something went wrong. ', 'wf-easypost' );
					if ( empty( $e->getMessage() ) ) {
						$message .= esc_attr( ' Normally this could happen because of the Wrong API credentials or Unfinished Settings. Double-check your <a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_easypost_id&subtab=general' ) . '">Settings Page</a>. Also, you may refer our <a href="https://elextensions.com/knowledge-base/troubleshooting-elex-easypost-fedex-ups-canada-post-usps-shipping-label-printing-plugin/">Trouble Shooting Document</a>. Please contact our <a href=" https://support.elextensions.com/">support</a> if you are still facing this issue.', 'wf-easypost' );
					} else {
						$message .= wp_kses_post( $e->getMessage() ) . ' ';
					}

					// This error occurs while generating shipping label.
					if ( 'SHIPMENT.POSTAGE.FAILURE' === $e->ecode && ( 'UPS' === $carrier_name || 'UPSDAP' === $carrier_name ) ) {
						$response_obj = '';
						$message     .= esc_attr( '<br>The UPS account tied to the Shipper Number you are using is not yet fully set up. Please contact UPS.', 'wf-easypost' );
						if ( isset( $this->error_email ) ) {
							$email_msg = $this->get_failed_shipment_email( $post_id );
							return $email_msg;
						}
					} else {
						$_SESSION['wfeasypostmsg'] = 6;
						update_post_meta( $post_id, 'wfeasypostmsg', $message );
						if ( isset( $this->error_email ) ) {
							$email_msg = $this->get_failed_shipment_email( $post_id );
							return $email_msg;
						} else {
							$this->wf_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
							echo wp_kses_post( $message );
							exit;
						}
					}
				}
			} catch ( Exception $e ) {
				$message .= esc_attr( 'Unable to get information at this point of time. ', 'wf-easypost' );
				$message .= wp_kses_post( $e->getMessage() ) . ' ';
			}

			if ( isset( $response_obj ) ) {

				$this->wf_debug( '<br><br>EasyPost RESPONSE OBJECT(Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">' . print_r( $response_obj, true ) . '</pre>' );
				// $easypost_authenticator   = ( string ) $response_obj->Authenticator;
				$tracking_link    = (string) isset( $response_obj->tracker->public_url ) ? $response_obj->tracker->public_url : '' ;
				$label_url        = (string) isset( $response_obj->postage_label->label_url ) ? $response_obj->postage_label->label_url : '';
				$carrier_selected = (string) isset( $response_obj->selected_rate->carrier ) ? $response_obj->selected_rate->carrier : '';
				$form_url         = (string) isset( $response_obj->forms[0]->form_url ) ? $response_obj->forms[0]->form_url : '';
				$zip_code_label   = (string) isset( $response_obj->from_address->zip ) ? $response_obj->from_address->zip : '';
				$warehouse        = (string) isset( $response_obj->from_address->company ) ? $response_obj->from_address->company : '';
				if ( ! empty( $label_url ) ) {
					$easypost_label                           = array();
					$easypost_label['url']                    = $label_url;
					$easypost_label['commercial_invoice_url'] = isset( $form_url ) ? $form_url : '';
					$easypost_label['tracking_number']        = (string) $response_obj->tracking_code;
					$easypost_label['integrator_txn_id']      = isset( $shipment_details['IntegratorTxID'] ) ? $shipment_details['IntegratorTxID'] : ''; // (string) $response_obj->reference;
					$easypost_label['easypost_tx_id']         = (string) $response_obj->tracker->id;
					$easypost_label['shipment_id']            = $shipment->id;
					$easypost_label['zip_code']               = $zip_code_label;
					$easypost_label['warehouse']              = $warehouse;
					$easypost_label['order_date']             = gmdate( 'Y-m-d', strtotime( (string) $response_obj->updated_at ) );
					$easypost_label['carrier']                = $carrier_selected;
					$easypost_label['link']                   = $tracking_link;
					$easypost_labels[]                        = $easypost_label;
					if ( isset( $package_data['origin'] ) && get_option( 'wc_settings_wf_vendor_addon_email_labels_to_vendors' ) === 'yes' ) {
						$label_url_html = '<html><body>' . $label_url . '</body></html>';
						wp_mail( $package_data['origin']['email'], 'Shipment Label - ' . $order->id, 'Label ' . $label_url_html, '', '' );
					}
				}
			} else {
				$message .= esc_attr( 'Sorry. Something went wrong:', 'wf-easypost' ) . '<br/>';
			}
			$package_count++;
		}
		if ( isset( $carrier_selected ) ) {
			switch ( $carrier_selected ) {
				case 'UPSDAP':
					$carrier = 'upsdap';
					break;

				case 'UPS':
					$carrier = 'ups';
					break;

				case 'USPS':
					$carrier = 'united-states-postal-service-usps';
					break;

				case 'FedEx':
					$carrier = 'fedex';
					break;

				case 'CanadaPost':
					$carrier = 'canada-post';
					break;

				case 'UPSSurePost':
					$carrier = 'upssurepost';
					break;

			}
		} else {
			$carrier = 'united-states-postal-service-usps';
		}

		if ( isset( $easypost_labels ) && ! empty( $easypost_labels ) ) {
			
			// Update post \
			if ( isset( $query['button_name'] ) || 'return' === $label_type ) {
				if ( 'return' === $label_type || 'return' === $query['button_name'] ) {
					$previous_label = get_post_meta( $post_id, 'wf_easypost_return_labels', true );
					if ( is_array( $previous_label ) ) {
						foreach ( $previous_label as $key => $value ) {
							array_push( $easypost_labels, $value );
						}
					} elseif ( ! empty( $previous_label ) ) {
						array_push( $easypost_labels, $previous_label );
					}
					update_post_meta( $post_id, 'wf_easypost_return_labels', $easypost_labels );
				} else {
					update_post_meta( $post_id, 'wf_easypost_labels', $easypost_labels );
				}
			} else {
				update_post_meta( $post_id, 'wf_easypost_labels', $easypost_labels );
			}
			$return_label = get_post_meta( $post_id, 'wf_easypost_return_labels', true );
			if ( ELEX_EASYPOST_RETURN_ADDON_STATUS && ! empty( $return_label ) ) {
				$order_id           = $order->get_id();
				$email_return_label = new WF_Auto_Generate_Return_Labels();
				$email_return_label->elex_mail_addon( $order_id, $response_obj );
			}
			// Auto fill tracking info.
			$shipment_id_cs = '';
			foreach ( $easypost_labels as $easypost_label ) {
				$shipment_id_cs .= $easypost_label['tracking_number'] . ',';
			}
			// Shipment Tracking (Auto)
			$admin_notice = '';
			try {
					$admin_notice = EasypostWfTrackingUtil::update_tracking_data( $post_id, $shipment_id_cs, $carrier, WF_Tracking_Admin_EasyPost::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin_EasyPost::SHIPMENT_RESULT_KEY );
			} catch ( Exception $e ) {
					$admin_notice = '';
					// Do nothing.
			}
			// Shipment Tracking (Auto)
			if ( ! empty( $admin_notice ) && ! $easypost_debug ) {
				if ( $this->bulk_label ) {
					return;
				}

					WF_Tracking_Admin_EasyPost::display_admin_notification_message( $post_id, $admin_notice );
			} elseif ( '' !== $message ) {
				// Do your plugin's desired redirect.
				$_SESSION['wfeasypostmsg'] = 2;
				update_post_meta( $post_id, 'wfeasypostmsg', $message );
				$this->wf_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
				exit;
			}
		} else {
			delete_post_meta( $post_id, 'wf_easypost_labels' );
			delete_post_meta( $post_id, 'wf_easypost_return_labels' );
		}
		
		if ( '' !== $message ) {
			$_SESSION['wfeasypostmsg'] = 2;
			update_post_meta( $post_id, 'wfeasypostmsg', $message );
			$this->wf_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}
		// Shipment Tracking (Auto)
		if ( ! empty( $admin_notice ) && ! $easypost_debug ) {
			if ( $this->bulk_label ) {
				return;
			}
			WF_Tracking_Admin_Easypost::display_admin_notification_message( $post_id, $admin_notice );
		} else {
			$_SESSION['wfeasypostmsg'] = 1;
			$this->wf_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}
	}

	public function get_multivendor_packages_service( $post_id, $package_data, $order, $service_count, $bulk_label ) {
		$shipping_methods = $order->get_shipping_methods();
		$label_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$rates_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		if ( ! $shipping_methods ) {
			return false;
		}

		$carrier_services_bulk = include 'data-wf-services.php';
		
		foreach ( $shipping_methods as $key => $value ) {
			$vendor_id = $value->get_meta( 'seller_id' );
			if ( empty( $vendor_id ) ) {
				$vendor_id = $value->get_meta( 'vendor_id' );
			}
	 
			if ( $vendor_id === $package_data['origin']['vendor_id'] ) {

				if ( 'Local pickup' === $value['name'] ) {
					return 'local_pickup';
				}
				$service_selected = false;

				foreach ( $carrier_services_bulk as $service => $code ) {
				
					if ( $bulk_label ) {
			
						$bulk_service = $code['services'];
						foreach ( $bulk_service as $bulk_key => $bulk_val ) {
							if ( $value['name'] === $bulk_val ) {
								$service_selected = true;
				 
								$default_service_type = $bulk_key;
								update_post_meta( $post_id, 'wf_easypost_selected_service', $bulk_key );
								break;
							}
						}
						if ( false === $service_selected ) {
							//custom service name
							if ( ! empty( $rates_settings['services'] ) ) {
								foreach ( $rates_settings['services'][ $service ] as $service_code => $service_data ) {
									if ( ! empty( $service_data['name'] ) && $value['name'] === $service_data['name'] ) {
										$service_selected = true;
							  
										$default_service_type  = $service_code ;
										update_post_meta( $post_id, 'wf_easypost_selected_service', $service_code );
										$carrier_name = $service; 
										break; 
											
									}  
								}
							}                       
						}                   
					} elseif ( ! empty( $package_data['Shipping_service'] ) && array_key_exists( $package_data['Shipping_service'], $code['services'] ) ) {
						
						$service_selected = true;
						if ( array_key_exists( $package_data['Shipping_service'], $code['services'] ) ) {
							$default_service_type = $package_data['Shipping_service'];
							update_post_meta( $post_id, 'wf_easypost_selected_service', $package_data['Shipping_service'] );
							
						}                   
					} 
					if ( false === $service_selected ) {
						if ( $order->shipping_country === $rates_settings['country'] ) {
							$default_service_type = $label_settings['easypost_default_domestic_shipment_service'];
							if ( array_key_exists( $default_service_type, $code['services'] ) ) {
								update_post_meta( $post_id, 'wf_easypost_selected_service', $default_service_type );
								
							}                       
						} else {
							if ( 'NA' !== $label_settings['easypost_default_international_shipment_service'] ) {
								$default_service_type = $label_settings['easypost_default_international_shipment_service'];
								if ( array_key_exists( $default_service_type, $code['services'] ) ) {
									update_post_meta( $post_id, 'wf_easypost_selected_service', $default_service_type );
									
								}                           
							}
						}
					}
				}
			
				return $default_service_type;
			}
		}

	}
	public function elex_check_international( $eligible_for_customs_details, $order, $shipment_details, $package_data_array ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		if ( $eligible_for_customs_details ) {
			$international     = true;
			$order_items       = $order->get_items();
			$custom_line_array = array();
			foreach ( $order_items as $order_item ) {
				for ( $i = 0; $i < $order_item['qty']; $i++ ) {
					$product_data = wc_get_product( $order_item['variation_id'] ? $order_item['variation_id'] : $order_item['product_id'] );
					$title        = $product_data->get_title();
					if ( WC()->version < '3.0' ) {
						$weight = woocommerce_get_weight( $product_data->get_weight(), 'lbs' );
					} else {
						$weight = wc_get_weight( $product_data->get_weight(), 'lbs' );
					}
					$shipment_description = $title;
					if ( isset( $package_data_array['Description'] ) && ! empty( $package_data_array['Description'] ) ) {
						$shipment_description = $package_data_array['Description'];
					} elseif ( ! empty( $label_settings['customs_description'] ) ) {
						$shipment_description = $label_settings['customs_description'];
					}
					$shipment_description = ( strlen( $shipment_description ) >= 50 ) ? substr( $shipment_description, 0, 45 ) . '...' : $shipment_description;
					$quantity             = $order_item['qty'];
					$value                = $order_item['line_subtotal'];

					$custom_line                   = array();
					$custom_line['description']    = $shipment_description;
					$custom_line['quantity']       = 1;
					$custom_line['value']          = $value / $quantity;
					$custom_line['weight']         = (string) ( $weight * 16 );
					$custom_line['origin_country'] = $shipment_details['from_address']['country'];
					$wf_hs_code                    = get_post_meta( $order_item['product_id'], '_wf_hs_code', 1 );
					if ( ! empty( $wf_hs_code ) ) {
						$custom_line['hs_tariff_number'] = $wf_hs_code;
					}
					if ( $order_item['variation_id'] ) {
						$product_id_customs = $order_item['variation_id'];
					} else {
						$product_id_customs = $order_item['product_id'];
					}
					$product_custom_declared_value = get_post_meta( $product_id_customs, '_wf_easypost_custom_declared_value', true );
					if ( $product_custom_declared_value ) {
						$custom_line['value'] = $product_custom_declared_value;
					} else {
						$product_custom_declared_value = get_post_meta( $order_item['product_id'], '_wf_easypost_custom_declared_value', true );
						if ( $product_custom_declared_value ) {
							$custom_line['value'] = $product_custom_declared_value;
						}
					}
				}
				$custom_line_array[] = $custom_line;
			}
		}
		return $custom_line_array;
	}
	public function wf_easypost_generate_packages( $post_id = '' ) {

		if ( ! ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK && ! ELEX_EASYPOST_RETURN_ADDON_STATUS ) {
			if ( ! $this->wf_user_check() ) {
				echo "You don't have admin privileges to view this page.";
				exit;
			}
		}

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_wpnonce' ) ) { // Input var okay.
			if ( ! $this->bulk_label ) {
				$post_id = isset( $_GET['wf_easypost_generate_packages'] ) ? base64_decode( map_deep( wp_unslash( $_GET['wf_easypost_generate_packages'] ), 'sanitize_text_field' ) ) : '';

			}
		}
		$order = $this->wf_load_order( $post_id );
		if ( ! $order ) {
			return;
		}
		$package_data_array = $this->wf_get_package_data( $order );
		update_post_meta( $post_id, '_wf_easypost_stored_packages', $package_data_array );
		if ( ! $this->bulk_label ) {
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}
	}

	private function wf_redirect( $url ) {
		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$easypost_debug   = isset( $general_settings['debug_mode'] ) && 'yes' === $general_settings ['debug_mode'] ? true : false;
		if ( ! $easypost_debug ) {
			wp_redirect( $url );
			
		}
	}

	public function get_client_side_reset_warning_message() {

		$current_page_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		$href_url         = $current_page_uri . '&client_reset';
		$message          = '</br>' . wp_kses_post( 'You can remove the labels manually by login to <a href="http://www.easypost.com?utm_source=elextensions" target="_blank">easypost.com</a> and only then, proceed with clicking on ', 'wf-easypost' ) . '<a href="' . $href_url . '" class="button button-primary" >' . esc_attr( 'client side label reset.', 'wf-easypost' ) . '</a>.';
		return $message;
	}

	public function is_eligible_for_customs_details( $from_country, $to_country, $to_city ) {
		$eligible_cities = array( 'APO', 'FPO', 'DPO' );
		if ( ( $from_country !== $to_country ) || ( in_array( strtoupper( $to_city ), $eligible_cities ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function wf_easypost_void_shipment() {
		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		if ( ! $this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}

		if ( ! ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_wpnonce' ) ) ) { // Input var okay.
			return false;
		}
		$post_id = isset( $_GET['wf_easypost_void_shipment'] ) ? base64_decode( map_deep( wp_unslash( $_GET['wf_easypost_void_shipment'] ), 'sanitize_text_field' ) ) : '';

		if ( isset( $_GET['client_reset'] ) ) {
			delete_post_meta( $post_id, 'wf_easypost_labels' );
			delete_post_meta( $post_id, 'wf_easypost_return_labels' );
			$_SESSION['wfeasypostmsg'] = 6;
			$message       = esc_attr( 'Client side reset is complete', 'wf-easypost' );
			update_post_meta( $post_id, 'wfeasypostmsg', $message );
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}

		$easypost_labels = get_post_meta( $post_id, 'wf_easypost_labels', true );

		if ( empty( $easypost_labels ) ) {
			$_SESSION['wfeasypostmsg'] = 2;
			$message       = esc_attr( 'Unable to reset label(s)', 'wf-easypost' );
			$message      .= $this->get_client_side_reset_warning_message();
			update_post_meta( $post_id, 'wfeasypostmsg', $message );
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}

		$wf_easypost = new WF_Easypost();
		$message     = '';
		foreach ( $easypost_labels as $easypost_label ) {
			$request = array();
			if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
			}
			if ( 'Live' === $general_settings['api_mode'] ) {
				\EasyPost\EasyPost::setApiKey( $general_settings['api_key'] );

			} else {
				\EasyPost\EasyPost::setApiKey( $general_settings['api_test_key'] );
			}
			$shipment                = \EasyPost\Shipment::retrieve( $easypost_label['shipment_id'] );
			$request['EasypostTxID'] = $easypost_label['easypost_tx_id'];
			try {
				$response_obj = $shipment->refund();
			} catch ( Exception $e ) {
				$message .= esc_attr( 'Unable to get information at this point of time. ', 'wf-easypost' );
				$message .= wp_kses_post( $e->getMessage() ) . ' ';
			}
			if ( isset( $response_obj ) ) {
				// Success.
				$response_obj;
			} else {
				$message .= esc_attr( 'Unknown error while Cancel Indicium - Please cross check settings.', 'wf-easypost' );
			}
		}

		if ( '' !== $message ) {
			$_SESSION['wfeasypostmsg'] = 2;
			$message       = esc_attr( 'There were errors while cancelling labels. Please remove those labels manually by login to easypost.com.', 'wf-easypost' ) . '<br/> Error: ' . $message;
			$message      .= $this->get_client_side_reset_warning_message();
			update_post_meta( $post_id, 'wfeasypostmsg', $message );
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}

		delete_post_meta( $post_id, 'wf_easypost_labels' );
		delete_post_meta( $post_id, 'wf_easypost_return_labels' );
		$_SESSION['wfeasypostmsg'] = 4;
		wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );
		exit;
	}

	public function wf_load_order( $orderId ) {
		if ( ! class_exists( 'WC_Order' ) ) {
			return false;
		}
		return ( WC()->version < '2.7.0' ) ? new WC_Order( $orderId ) : new Wf_Order( $orderId );
	}

	public function wf_user_check() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}

	public function wf_get_shipping_service_data( $order ) {
		$label_settings   = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$shipping_methods = $order->get_shipping_methods();
		if ( ! $shipping_methods ) {
			return false;
		}

		$shipping_method           = array_shift( $shipping_methods );
		$shipping_service_tmp_data = explode( ':', $shipping_method['method_id'] );
		if ( WC()->version < '3.4.0' ) {
			$shipping_service = $shipping_service_tmp_data[1];
		} else {
			$shipping_service = $shipping_method['instance_id'];
		}

		$wf_easypost_selected_service = '';
		$wf_easypost_selected_service = get_post_meta( $order->id, 'wf_easypost_selected_service', true );
		if ( '' !== $wf_easypost_selected_service ) {
			$shipping_service_data['shipping_method']       = WF_EASYPOST_ID;
			$shipping_service_data['shipping_service']      = $wf_easypost_selected_service;
			$shipping_service_data['shipping_service_name'] = $shipping_method['name'];
		} elseif ( ! isset( $shipping_service_tmp_data[0] ) ||
				( isset( $shipping_service_tmp_data[0] ) && WF_EASYPOST_ID !== $shipping_service_tmp_data[0] ) ) {
			$shipping_service_data['shipping_method']       = WF_EASYPOST_ID;
			$shipping_service_data['shipping_service']      = '';
			$shipping_service_data['shipping_service_name'] = $shipping_method['name'];
		} else {
			$shipping_service_data['shipping_method']       = $shipping_service_tmp_data[0];
			$shipping_service_data['shipping_service']      = $shipping_service;
			$shipping_service_data['shipping_service_name'] = $shipping_method['name'];
		}
		return $shipping_service_data;
	}

	private function get_dimension_from_package( $package, $easypost_customer = '' ) {
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$dimensions     = array(
			'Length'       => 0,
			'Width'        => 0,
			'Height'       => 0,
			'WeightOz'     => 0,
			'InsuredValue' => 0,
		);
		if ( ! is_array( $package ) || ! isset( $package['WeightOz'] ) ) {
			return $dimensions;
		}
		$dimensions['WeightOz']     = $package['WeightOz'];
		$dimensions['Length']       = isset( $package['Length'] ) ? $package['Length'] : 0;
		$dimensions['Width']        = isset( $package['Width'] ) ? $package['Width'] : 0;
		$dimensions['Height']       = isset( $package['Height'] ) ? $package['Height'] : 0;
		$dimensions['InsuredValue'] = isset( $package['InsuredValue'] ) ? $package['InsuredValue'] : 0;
		if ( ELEX_EASYPOST_RETURN_ADDON_STATUS ) {
			 $dimensions['product_id'] = isset( $package['PackedItem'][0]->id ) ? $package['PackedItem'][0]->id : '';
		}
		if ( '0' === $easypost_customer ) {
			$dimensions['InsuredValue'] = 0;
		}
		if ( '0' !== $easypost_customer && '1' !== $easypost_customer ) {
			if ( 'yes' === $label_settings['insurance'] ) {
				$dimensions['InsuredValue'] = isset( $package['InsuredValue'] ) ? $package['InsuredValue'] : 0;
			}
		}
		return $dimensions;
	}

	/**
	 * Function to return updated services for provided weight, dimensions and package type
	 *
	 * @return updated services json
	 */
	public function elex_easypost_update_shipping_services() {
		$rates_settings        = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$general_settings      = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$label_settings        = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$this->timezone_offset = ! empty( $this->settings['timezone_offset'] ) ? intval( $this->settings['timezone_offset'] ) * 60 : 0;
		if ( ! ( isset( $_POST['nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
		$elex_ep_status_log = isset( $general_settings ['status_log'] ) && 'yes' === $general_settings ['status_log'] ? true : false;
		update_option( 'from_update_shipping_service', 'yes' );
		$current_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$enabled_services = get_option( 'easypost_enabled_services' );
		$current_order    = $this->wf_load_order( $current_order_id );
		$package          = array();
		$stored_packages  = get_post_meta( $current_order_id, '_wf_easypost_stored_packages', true );

			$zipcode = isset( $stored_packages[ $_POST['packageOrder'] ]['origin'] ) ? $stored_packages[ sanitize_text_field( $_POST['packageOrder'] ) ]['origin']['postcode'] : $rates_settings['zip'] ;
			$from_country = isset( $stored_packages[ $_POST['packageOrder'] ]['origin'] ) ? $stored_packages[ sanitize_text_field( $_POST['packageOrder'] ) ]['origin']['country'] : $rates_settings['country'];
			$from_state = isset( $stored_packages[ $_POST['packageOrder'] ]['origin'] ) ? $stored_packages[ sanitize_text_field( $_POST['packageOrder'] ) ]['origin']['state'] : $rates_settings['state'];
	
		// $this->elex_easypost_restore_package_dimensions($current_order_id,$stored_packages,$_POST);
		$wf_usps_easypost = new WF_Easypost();
		if ( 'yes' !== $label_settings['insurance'] ) {
			 $_POST['package_price'] = '';
		}
		$domestic = array( 'US', 'PR', 'VI' );
		if ( in_array( $current_order->shipping_country, $domestic ) ) {
			$country = 'US';
		} else {
			$country = $current_order->shipping_country;
		}
		$flat_rate = array();
		$package_number = isset( $_POST['packageOrder'] ) ? sanitize_text_field( $_POST['packageOrder'] ) : 0;
		$all_usps_fedex_flat_rate_boxes          = include 'data-wf-flat-rate-boxes.php';
		$flat_rate[0]   = isset( $_POST['flateRate'][ $package_number ] ) ? map_deep( wp_unslash( $_POST['flateRate'][ $package_number ] ), 'sanitize_text_field' ) : '';
		$warehouse_data = isset( $_POST['warehouse'] ) ? sanitize_text_field( $_POST['warehouse'] ) : '';
		$payload        = array();
		$dimensions     = array();
		if ( isset( $flat_rate[0] ) && ! empty( $flat_rate[0] ) && isset( $all_usps_fedex_flat_rate_boxes['USPS'][ $flat_rate[0] ] ) ) {
			$dimensions = $all_usps_fedex_flat_rate_boxes['USPS'][ $flat_rate[0] ];
		} elseif ( isset( $flat_rate[0] ) && ! empty( $flat_rate[0] ) && isset( $all_usps_fedex_flat_rate_boxes['FedEx'][ $flat_rate[0] ] ) ) {
			$dimensions = $all_usps_fedex_flat_rate_boxes['USPS'][ $flat_rate[0] ];
		}
		if ( ! empty( $dimensions ) && ( ( isset( $_POST['length'] ) && $_POST['length'] > $dimensions['length'] ) || ( isset( $_POST['width'] ) && $_POST['width'] > $dimensions['width'] ) || ( isset( $_POST['height'] ) && $_POST['height'] > $dimensions['height'] ) ) ) {
			return;
		}
		$warehouse_details = ! empty( get_option( 'woocommerce_wf_multi_warehouse_settings' ) ) ? get_option( 'woocommerce_wf_multi_warehouse_settings' ) : array();
		if ( ! empty( $warehouse_details ) && ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK ) {
			foreach ( $warehouse_details as $warehouse_boxes => $warehouse_boxes_data ) {

				if ( $warehouse_data === $warehouse_boxes_data['address_title'] ) {
					$zipcode                 = $warehouse_boxes_data['origin'];
					$payload['from_address'] = array(
						'name'    => $warehouse_boxes_data['origin_name'],
						'company' => $warehouse_boxes_data['address_title'],
						'street1' => $warehouse_boxes_data['origin_line_1'],
						'street2' => $warehouse_boxes_data['origin_line_2'],
						'city'    => $warehouse_boxes_data['origin_city'],
						'state'   => $warehouse_boxes_data['origin_state'],
						'zip'     => $warehouse_boxes_data['origin'],
						// adding country
						'country' => $warehouse_boxes_data['country'],
					);
				} elseif ( empty( $warehouse_data ) ) {
					$zipcode                 = '';
					$payload['from_address'] = array(
						'name'    => '',
						'company' => '',
						'street1' => '',
						'street2' => '',
						'city'    => '',
						'state'   => '',
						'zip'     => '',
						// adding country
						'country' => '',
					);
				}
			}
		}
		$package_request = array(

			'Rate' => array(
				'FromZIPCode'       => str_replace( ' ', '', strtoupper( $zipcode ) ),
				'Fromcountry'       => $from_country,
				'Fromstate'         => $from_state,
				'ToZIPCode'         => $current_order->shipping_postcode,
				'WeightLb'          => '',
				'Amount'            => isset( $_POST['package_price'] ) ? sanitize_text_field( $_POST['package_price'] ) : 0,
				'WeightOz'          => isset( $_POST['weight'] ) ? sanitize_text_field( $_POST['weight'] ) : '',
				'Length'            => isset( $_POST['length'] ) ? sanitize_text_field( $_POST['length'] ) : '',
				'Width'             => isset( $_POST['width'] ) ? sanitize_text_field( $_POST['width'] ) : '',
				'Height'            => isset( $_POST['height'] ) ? sanitize_text_field( $_POST['height'] ) : '',
				'Signature'         => isset( $_POST['signature'] ) ? sanitize_text_field( $_POST['signature'] ) : '',
				'ShipDate'          => gmdate( 'Y-m-d', ( current_time( 'timestamp' ) + $this->timezone_offset ) ),
				'InsuredValue'      => isset( $_POST['package_price'] ) ? sanitize_text_field( $_POST['package_price'] ) : 0,
				'RectangularShaped' => 'false',
				'ToCountry'         => $country,
			),
		);
	
		if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/easypost.php';
		}
		if ( 'Live' === $general_settings['api_mode'] ) {
			$easypost_api_key = $general_settings['api_key'];
		} else {
			$easypost_api_key = $general_settings['api_test_key'];
		}

		\EasyPost\EasyPost::setApiKey( $easypost_api_key );

			$this->elex_ep_status_logger( $package_request, $current_order_id, 'Preferred Service Request', $elex_ep_status_log );

		if ( '' !== $flat_rate ) {
			$responses[] = $this->get_results( $package_request, $flat_rate, $payload );
		} else {
			$responses[] = $this->get_results( $package_request, $flat_rate, $payload );
		}
	
		$this->elex_ep_status_logger( $responses, $current_order_id, 'Preferred Service Response', $elex_ep_status_log );
		$updated_rates = array();
		foreach ( $responses as $key => $value ) {

			$response_obj = isset( $value['response'] ) ? $value['response'] : '';

			if ( isset( $response_obj->rates ) ) {
				if ( is_array( $response_obj->rates ) ) {
					foreach ( $response_obj->rates as $key => $value ) {
						foreach ( $enabled_services as $service_key => $service_value ) {
							if ( $service_key === $value->service ) {
								// To remove conflict between ups express and priority mail express
								if ( 'Express' === $value->service && 'UPS' === $value->carrier && is_int( strpos( $service_value, '(USPS)' ) ) ) {
									  continue;
								}
								$label         = $service_value;
								$service_names = $value->service;
								$service_rates = $value->rate;
								array_push(
									$updated_rates,
									array(
										'label' => $label,
										'cost'  => $service_rates,
										'key'   => $service_names,
									)
								);
							}
						}
					}
				}
			} else {
					echo esc_attr( 'Unable to get the rates for perferred services', 'wf-easypost' );
			}
		}

		// update_option('package_number',$_POST['packageOrder']);
		if ( isset( $_POST['packageOrder'] ) ) {
			update_post_meta( $current_order_id, 'package_rates_' . sanitize_text_field( $_POST['packageOrder'] ), $updated_rates );
		}
		update_option( 'from_update_shipping_service', 'no' );
		wp_die(
			json_encode(
				array(
					'status'         => 200,
					'rates_response' => $updated_rates,
				)
			)
		);
	}


	 // Update Package dimensions.
	public function elex_easypost_restore_package_dimensions( $current_order_id, $stored_packages, $data ) {
		foreach ( $stored_packages as $stored_package_key => $stored_package ) {
			if ( $stored_package_key === $data['packageOrder'] ) {
				$stored_package['WeightLb'] = $data['weight'];
				$stored_package['Length']   = $data['length'];
				$stored_package['Width']    = $data['width'];
				$stored_package['Height']   = $data['height'];
				$package                    = $stored_package;
				break;
			}
		}
		// Restore the package dimensions.
		update_post_meta( $current_order_id, '_wf_easypost_stored_packages', $stored_packages );
	}


	private function get_results( $package_request, $flat_rate = '', $payload = array() ) {
		// Get rates.
		$rates_settings = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
		$label_settings = get_option( 'woocommerce_WF_EASYPOST_ID_labels_settings', null );
		$senderCountry  = isset( $rates_settings['country'] ) ? $rates_settings['country'] : '';
		try {
			if ( empty( $payload ) ) {
				$payload['from_address'] = array(
					'name'    => $label_settings['name'],
					'company' => $label_settings['company'],
					'street1' => $label_settings['street1'],
					'street2' => $label_settings['street2'],
					'city'    => $label_settings['city'],
					'state'   => $package_request['Rate']['Fromstate'],
					'zip'     => $package_request['Rate']['FromZIPCode'],
					// adding country
					'country' => $package_request['Rate']['Fromcountry'],
				);
			}

			$payload['to_address'] = array(
				// Name and Street1 are required fields for getting rates.
				// But, at this point, these details are not available.
				'name'        => '-',
				'street1'     => '-',
				'residential' => 'residential' === $rates_settings['show_rates'] ? true : '',
				'zip'         => $package_request['Rate']['ToZIPCode'],
				'country'     => $package_request['Rate']['ToCountry'],
			);

			if ( 'CA' === $payload['from_address']['country'] && 'CA' !== $payload['to_address']['country'] ) {

				$payload['customs_info']['customs_certify']      = true;
				$payload['customs_info']['customs_signer']       = isset( $label_settings['customs_signer'] ) ? $label_settings['customs_signer'] : '';
				$payload['customs_info']['contents_type']        = 'merchandise';
				$payload['customs_info']['contents_explanation'] = '';
				$payload['customs_info']['restriction_type']     = 'none';
				$payload['customs_info']['eel_pfc']              = 'NOEEI 30.37(a)';
			}

			if ( ! empty( $package_request['Rate']['WeightOz'] ) ) {
				$package_request['request']['Rate']['WeightOz'] = $package_request['Rate']['WeightOz'];
			}
			if ( isset( $flat_rate[0] ) && '' !== $flat_rate[0] ) {
				$payload['parcel'] = array(
					'length'             => $package_request['Rate']['Length'],
					'width'              => $package_request['Rate']['Width'],
					'height'             => $package_request['Rate']['Height'],
					'weight'             => $package_request['Rate']['WeightOz'],
					'predefined_package' => $flat_rate[0],
				);
			} else {
				$payload['parcel'] = array(
					'length' => $package_request['Rate']['Length'],
					'width'  => $package_request['Rate']['Width'],
					'height' => $package_request['Rate']['Height'],
					'weight' => $package_request['Rate']['WeightOz'],
				);
			}
			$payload['options'] = array(
				'special_rates_eligibility' => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL',

			);

			if ( isset( $package_request['Rate']['Signature'] ) && 'yes' === $package_request['Rate']['Signature'] ) {
				$payload['options']['delivery_confirmation'] = 'ADULT_SIGNATURE';
			}

			$shipment                 = \EasyPost\Shipment::create( $payload );
			$response                 = json_decode( $shipment );
			$response_ele             = array();
			$response_ele['response'] = $response;
		} catch ( Exception $e ) {

			if ( strpos( $e->getMessage(), 'Could not connect to EasyPost' ) !== false ) {
				echo esc_attr( 'Unable to get Auth information at this point of time. ', 'wf-easypost' );
			}

			return false;
		}

		return $response_ele;
	}

	public function manual_packages( $packages ) {
		// If manual values not provided
		if ( ! ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), '_wpnonce' ) ) ) { // Input var okay.
			return $packages;
		}
		if ( ! isset( $_GET['weight'] ) ) {
			return $packages;
		}

		// Get manual values
		$length_arr      = isset( $_GET['length'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['length'] ) ) ) ) : '';
		$width_arr       = isset( $_GET['width'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['width'] ) ) ) ) : '';
		$height_arr      = isset( $_GET['height'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['height'] ) ) ) ) : '';
		$weight_arr      = isset( $_GET['weight'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['weight'] ) ) ) ) : '';
		$shipping_service_arr = isset( $_GET['wf_easypost_service'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_easypost_service'] ) ) ) ) : '';
		$description_arr = isset( $_GET['description'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['description'] ) ) ) ) : '';
		$insurance_arr   = isset( $_GET['insurance'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['insurance'] ) ) ) ) : '';
		$signature_arr   = isset( $_GET['signature'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['signature'] ) ) ) ) : '';
		$warehouse_data  = isset( $_GET['wf_easypost_warehouse_box'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( $_GET['wf_easypost_warehouse_box'] ) ) ) ) : '';

		// If extra values provided, then add it with the package list
		$no_of_package_entered = count( $weight_arr );
		$no_of_packages        = count( $packages );

		if ( $no_of_package_entered > $no_of_packages ) {
			$package_clone = current( $packages );

			if ( isset( $package_clone['PackedItem'] ) ) { // Everything clone except packed items
				unset( $package_clone['PackedItem'] );
			}

			for ( $i = $no_of_packages; $i < $no_of_package_entered; $i++ ) {
				$packages[ $i ] = $package_clone;
			}
		}

		// Overridding package values
		foreach ( $packages as $key => $package ) {
			if ( isset( $weight_arr[ $key ] ) ) {
				$packages[ $key ]['WeightOz']       = $weight_arr[ $key ];
				$packages[ $key ]['Length']         = $length_arr[ $key ];
				$packages[ $key ]['Width']          = $width_arr[ $key ];
				$packages[ $key ]['Height']         = $height_arr[ $key ];
				$packages[ $key ]['Shipping_service']    = isset( $shipping_service_arr[ $key ] ) ? $shipping_service_arr[ $key ] : '';
				$packages[ $key ]['Description']    = isset( $description_arr[ $key ] ) ? $description_arr[ $key ] : '';
				$packages[ $key ]['InsuredValue']   = isset( $insurance_arr[ $key ] ) ? $insurance_arr[ $key ] : '';
				$packages[ $key ]['Signature']      = isset( $signature_arr[ $key ] ) ? $signature_arr[ $key ] : '';
				$packages[ $key ]['warehouse_data'] = isset( $warehouse_data[ $key ] ) ? $warehouse_data[ $key ] : '';
			} else {
				unset( $packages[ $key ] );
			}
		}

		return $packages;
	}

	private function wf_debug( $message ) {
		$general_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );
		$easypost_debug   = isset( $general_settings['debug_mode'] ) && 'yes' === $general_settings['debug_mode'] ? true : false;
		if ( $easypost_debug ) {
			echo wp_kses_post( $message );
		}
		return;
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
	public function elex_easypost_order_package( $orderItems, $orderId ) {
		$refunded_ordered_items = $this->get_refunded_order_items( $orderId );
		if ( ! empty( $refunded_ordered_items ) ) {
			foreach ( $orderItems as $key => $item ) {
				$order_item_id = $item->get_ID();
				if ( isset( $refunded_ordered_items[ $order_item_id ] ) ) {
					$item_quantity = $item->get_quantity();
					if ( ( $refunded_ordered_items[ $order_item_id ]['quantity'] + $item_quantity ) === 0 ) {
						unset( $orderItems[ $key ] );
					} else {
						$orderItems[ $key ]->set_quantity( $orderItems[ $key ]->get_quantity() + $refunded_ordered_items[ $order_item_id ]['quantity'] );
					}
				}
			}
		}
		return $orderItems;
	}
	public function get_refunded_order_items( $order_id ) {
		$order          = wc_get_order( $order_id );
		$order_refunds  = $order->get_refunds();
		$refunded_items = array();
		// Loop through the order refunds array
		foreach ( $order_refunds as $refund ) {
			// Loop through the order refund line items
			foreach ( $refund->get_items() as $item_id => $item ) {
				$refunded_quantity      = $item->get_quantity(); // Quantity: zero or negative integer
				$refunded_line_subtotal = $item->get_subtotal(); // line subtotal: zero or negative number
				// Get the original refunded item ID
				$refunded_item_id = $item->get_meta( '_refunded_item_id' ); // line subtotal: zero or negative number
				if ( isset( $refunded_items[ $refunded_item_id ] ) ) {
					$refunded_items[ $refunded_item_id ]['quantity'] += $refunded_quantity;
				} else {
					$refunded_items[ $refunded_item_id ] = array(
						'item_id'  => $refunded_item_id,
						'quantity' => $refunded_quantity,

					);
				}
			}
		}
		return $refunded_items;
	}
}

new WF_Shipping_Easypost_Admin();
