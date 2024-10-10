jQuery(document).ready(function($) {
    $(".bms_booking_wrapper select").niceSelect();
    
    $('#bms-registration-form').on('submit', function(e) {
        e.preventDefault();
        var data = {
            action: 'bms_register_user',
            security: bms_ajax_object.bms_register_nonce,
            username: $('#bms_username').val(),
            email: $('#bms_email').val(),
            password: $('#bms_password').val()
        };
        $.post(bms_ajax_object.ajax_url, data, function(response) {
            alert(response.data);
            if (response.success) {
                location.reload();
            }
        });
    });

    $('#bms-login-form').on('submit', function(e) {
        e.preventDefault();
        var data = {
            action: 'bms_login_user',
            security: bms_ajax_object.bms_login_nonce,
            username: $('#bms_login_username').val(),
            password: $('#bms_login_password').val()
        };
        $.post(bms_ajax_object.ajax_url, data, function(response) {
            alert(response.data);
            if (response.success) {
                location.reload();
            }
        });
    });
});