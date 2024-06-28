jQuery(document).ready(function(){
	wf_services();
	update_carrier_Services();
	jQuery('#woocommerce_wf_easypost_id_easypost_carrier').on('change', function(){
		wf_services();
	});
});
//Hide UPS when UPSDAP is available
function update_carrier_Services(){
	enabledCarriers_service = jQuery('#woocommerce_wf_easypost_id_easypost_carrier').val();
	if( jQuery.inArray("UPSDAP", enabledCarriers_service) !== -1){
		const index = enabledCarriers_service.indexOf('UPSDAP');
		if (index > -1) {
			jQuery('#woocommerce_wf_easypost_id_easypost_default_domestic_shipment_service option').text(function(index,text){
				return text.replace("(UPS)","(UPSDAP)");
			});
			jQuery('#woocommerce_wf_easypost_id_easypost_default_international_shipment_service option').text(function(index,text){
				return text.replace("(UPS)","(UPSDAP)");
			});		
		}
	}
}
function wf_services(){
	enabledCarriers = jQuery('#woocommerce_wf_easypost_id_easypost_carrier').val();
    
	if(jQuery.inArray("UPS", enabledCarriers) !== -1 && jQuery.inArray("UPSDAP", enabledCarriers) !== -1){
		const index = enabledCarriers.indexOf('UPS');
		if (index > -1) {
			enabledCarriers.splice(index, 1);
		}
	}
	jQuery('.services').each(function(){	
		if( jQuery.inArray( jQuery(this).attr('carrier'), enabledCarriers ) < 0 ){
			jQuery(this).hide();
		}else{
			jQuery(this).show();
		}
	});
}
