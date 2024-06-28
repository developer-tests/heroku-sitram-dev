jQuery(function ($) {


    /**
     * Recipient and delivery settings dependencies
     * */

    $(function() {
        if ( $( 'input#ywgc_enable_send_later' ).prop('checked') ){
            $( '#ywgc_delivery_hour' ).parent().parent().parent().show();
            $( '.ywgc_update_cron_button' ).parent().parent().parent().show();
        }
        else{
            $( '#ywgc_delivery_hour' ).parent().parent().parent().hide();
            $( '.ywgc_update_cron_button' ).parent().parent().parent().hide();
        }
    });


    $('input#ywgc_enable_send_later').change(function() {

        if ( ! $( this ).hasClass( 'onoffchecked') && ! $('input#ywgc_enable_send_later').prop('checked') ){

            $( '#ywgc_delivery_hour' ).parent().parent().parent().hide();
            $( '.ywgc_update_cron_button' ).parent().parent().parent().hide();
        }
        else{
            $( '#ywgc_delivery_hour' ).parent().parent().parent().show();
            $( '.ywgc_update_cron_button' ).parent().parent().parent().show();
        }
    });


    $(document).on('click', '.ywgc_update_cron_button', function (e) {
        e.preventDefault();

        var hour = $( '#ywgc_delivery_hour').val();

        var block_zone = $( this ).parent().parent();

        block_zone.block({message: null, overlayCSS: {background: "#f1f1f1", opacity: .7}});

        var data = {
            security: ywgc_data.gift_card_nonce,
            hour: hour,
            action: 'ywgc_update_cron'
        };

        $.ajax({
            type: 'POST',
            url: ywgc_data.ajax_url,
            data: data,
            dataType: 'html',
            success: function (response) {
                block_zone.unblock();
                console.log('Cron Updated!');
            },
            error: function (response) {
                block_zone.unblock();
                console.log("ERROR");
                console.log(response);
            }
        });

    });

});
