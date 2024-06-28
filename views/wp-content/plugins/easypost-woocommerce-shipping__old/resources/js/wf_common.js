jQuery(document).ready(function(){
	//Toggle packing methods
	wf_load_easypost_packing_method_options();
	jQuery('.packing_method').change(function(){
		wf_load_easypost_packing_method_options();
	});

	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text').val('USPS Flat Rate:Priority Mail');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_express_text').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_express_text').val('USPS Flat Rate:Priority Mail Express');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_first_class_text').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_first_class_text').val('USPS Flat Rate:First-Class Mail');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_fedex_one_rate_text').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_fedex_one_rate_text').val('FedEx Flat Rate:FedEx One Rate');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_international_mail').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_international_mail').val('USPS Flat Rate Priority Mail International');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_international_express').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_international_express').val('USPS Flat Rate International Express');
	}
	if(jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_first_class_mail_international').val()==''){
		jQuery('#woocommerce_wf_easypost_id_flat_rate_boxes_text_first_class_mail_international').val('USPS Flat Rate First-Class Mail International');
	}
	// Advance settings tab
	jQuery('.wf_settings_heading_tab').next('table').hide();
	jQuery('.wf_settings_heading_tab').click(function(event){
		event.stopImmediatePropagation();
		jQuery(this).next('table').toggle();
	});
		if(jQuery('#woocommerce_wf_easypost_id_availability').val()=='all')
		{
			
			jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').hide();
		}	
		else
		{
			jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').show();
		
		}
	
        //Specific Countries in rates tab field
    jQuery('#woocommerce_wf_easypost_id_availability').change(function(){
		if(jQuery('#woocommerce_wf_easypost_id_availability').val()=='all')
		{
			jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').hide();
		}	
		else
		{
			jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').show(); 
		}
	});
	return_address_fields();
	third_party_details();
	fedex_third_party_details();
	jQuery('#woocommerce_wf_easypost_id_return_address').change(function(){
		return_address_fields();
	});
	jQuery('#woocommerce_wf_easypost_id_third_party_billing').change(function(){
		third_party_details();
	});
	jQuery('#woocommerce_wf_easypost_id_fedex_third_party_billing').change(function(){
		fedex_third_party_details();
	});
	
        //API key Field
       jQuery(document).ready(function() {
            if(jQuery('#woocommerce_wf_easypost_id_api_mode').val() === 'Live'){
              jQuery('#woocommerce_wf_easypost_id_api_key').closest('tr').show();
              jQuery('#woocommerce_wf_easypost_id_api_test_key').closest('tr').hide();  
            }else{
                 jQuery('#woocommerce_wf_easypost_id_api_key').closest('tr').hide();
              jQuery('#woocommerce_wf_easypost_id_api_test_key').closest('tr').show();  
            }
        });

        jQuery('#woocommerce_wf_easypost_id_api_mode').change(function(){
           
            if(jQuery('#woocommerce_wf_easypost_id_api_mode').val() === 'Live'){
              jQuery('#woocommerce_wf_easypost_id_api_key').closest('tr').show();
              jQuery('#woocommerce_wf_easypost_id_api_test_key').closest('tr').hide();  
            }else{
                 jQuery('#woocommerce_wf_easypost_id_api_key').closest('tr').hide();
              jQuery('#woocommerce_wf_easypost_id_api_test_key').closest('tr').show();  
            }
        });
      
        //To show or hide Estimated Delivery Date & Cut-off time.
		jQuery(function($){
			if (jQuery('#woocommerce_wf_easypost_id_est_delivery').is(":checked")) {
				jQuery('#woocommerce_wf_easypost_id_cut_off_time').closest('tr').show();
        		jQuery('#woocommerce_wf_easypost_id_working_days').closest('tr').show();
				jQuery('#woocommerce_wf_easypost_id_lead_time').closest('tr').show();
			} else {
				jQuery('#woocommerce_wf_easypost_id_cut_off_time').closest('tr').hide();
        		jQuery('#woocommerce_wf_easypost_id_working_days').closest('tr').hide();
				jQuery('#woocommerce_wf_easypost_id_lead_time').closest('tr').hide();
			}
			jQuery("#woocommerce_wf_easypost_id_est_delivery").click(function(){
				var value=jQuery(this).is(':checked');
				if(value === true ){
					jQuery('#woocommerce_wf_easypost_id_cut_off_time').closest('tr').show();
					jQuery('#woocommerce_wf_easypost_id_working_days').closest('tr').show();
					jQuery('#woocommerce_wf_easypost_id_lead_time').closest('tr').show();
				} else {
					jQuery('#woocommerce_wf_easypost_id_cut_off_time').closest('tr').hide();
					jQuery('#woocommerce_wf_easypost_id_working_days').closest('tr').hide();
					jQuery('#woocommerce_wf_easypost_id_lead_time').closest('tr').hide();
				}
			});
		
       

        //To Show the custom label size options in label generation tab.
		
			if(jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size').val() !== 'label_type'){
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_usps').closest('tr').show();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_ups').closest('tr').show();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_fedex').closest('tr').show();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_canadapost').closest('tr').show();
			}else{
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_usps').closest('tr').hide();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_ups').closest('tr').hide();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_fedex').closest('tr').hide();
				jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_canadapost').closest('tr').hide();

			}
			jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size').change(function(){
				if(jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size').val() !== 'label_type'){
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_usps').closest('tr').show();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_ups').closest('tr').show();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_fedex').closest('tr').show();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_canadapost').closest('tr').show();
				}else{
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_usps').closest('tr').hide();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_ups').closest('tr').hide();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_fedex').closest('tr').hide();
					jQuery('#woocommerce_wf_easypost_id_elex_shipping_label_size_canadapost').closest('tr').hide();

				}
			});
		});
	});
	function return_address_fields(){
		if( jQuery('#woocommerce_wf_easypost_id_return_address').is(":checked") )
		{
			jQuery('#woocommerce_wf_easypost_id_return_name').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_street1').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_street2').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_company').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_phone').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_email').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_city').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_state').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_zip').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_return_country').closest('tr').show();
		}
		else
		{
			jQuery('#woocommerce_wf_easypost_id_return_name').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_street1').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_street2').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_company').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_phone').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_email').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_city').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_state').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_zip').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_return_country').closest('tr').hide();
		}
	}
	function third_party_details(){
		//Third Party Billing
		if(jQuery("#woocommerce_wf_easypost_id_third_party_billing").is(":checked"))
		{   
			jQuery('#woocommerce_wf_easypost_id_third_party_apikey').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_third_party_country').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_third_party_zip').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_third_party_checkout').closest('tr').show();
		}
		else
		{   
			jQuery('#woocommerce_wf_easypost_id_third_party_apikey').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_third_party_country').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_third_party_zip').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_third_party_checkout').closest('tr').hide();   
		}	
	}
	function fedex_third_party_details(){
		//Third Party Billing
		if(jQuery("#woocommerce_wf_easypost_id_fedex_third_party_billing").is(":checked"))
		{   
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_apikey').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_country').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_zip').closest('tr').show();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_checkout').closest('tr').show();
		}
		else
		{   
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_apikey').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_country').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_zip').closest('tr').hide();
			jQuery('#woocommerce_wf_easypost_id_fedex_third_party_checkout').closest('tr').hide();   
		}	
	}
	function wf_load_easypost_packing_method_options(){
		pack_method	=	jQuery('.packing_method').val();
		jQuery('#packing_options').hide();
		jQuery('.weight_based_option').closest('tr').hide();
		jQuery('#woocommerce_wf_easypost_id_packing_algorithm').closest('tr').hide();
		switch(pack_method){
			case 'per_item':
			default:
				break;	
			case 'box_packing':
				jQuery('#packing_options').show();
				jQuery('#woocommerce_wf_easypost_id_packing_algorithm').closest('tr').show();
				break;
			case 'weight_based_packing':
				jQuery('.weight_based_option').closest('tr').show();
				break;
		}
	}
