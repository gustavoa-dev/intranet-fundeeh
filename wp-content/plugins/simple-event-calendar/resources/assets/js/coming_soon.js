"use strict";
jQuery(document).ready(function () {
    jQuery(".contact_us").on('click', function () {
        jQuery(".contact").css("display", "block");
        jQuery(document).mouseup(function(e)
        {
            var container = jQuery(".form");
            if (!container.is(e.target) && container.has(e.target).length === 0)
            {
                jQuery(".contact").css("display", "none");
            }
        });
    });

    var contact_form = jQuery('.contact_form');

            contact_form.validate({
            rules: {
                name: {
                    required: true,
                    minlength: 3
                },
                email: {
                    required: true,
                    email: true
                }
            },
            submitHandler: function(form) {
                var data = {
                    action: 'contact_us',
                    nonce: gdCalendarContactUsObj.gdNonceContactUs,
                    name: jQuery(".name_input").val(),
                    email: jQuery(".email").val(),
                    content: jQuery(".content_textarea").val(),
                };
                jQuery.ajax({
                    url: gdCalendarContactUsObj.ajaxUrl,
                    type: 'post',
                    data: data,
                    dataType: 'json'
                }).done(function (response) {

                    contact_form.parent().append('<div class="response">' + response.data + '</div>');
                    contact_form.hide();

                    setTimeout(function() {
                        jQuery('.contact').hide();
                        contact_form.parent().find('.response').remove();
                        contact_form.trigger('reset');
                        contact_form.show();
                    }, 2000);

                    return false;

                }).fail(function (response) {
                    alert(response.data);
                    return false;
                })
            }
        });


    /**
     * SUBSCRIBE FORM
     */
    var subscribe_form = jQuery('.subscribe_form');

    if (subscribe_form.length > 0) {
        // validate form
        subscribe_form.validate({
            submitHandler: function(form) {
                var data = {
                    action: 'add_subscriber',
                    nonce: gdCalendarContactUsObj.gdNonceSubscribeForm,
                    email: jQuery(".email_input").val(),
                };
                jQuery.ajax({
                    url: gdCalendarContactUsObj.ajaxUrl,
                    type: 'post',
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    jQuery('.subscribe_wrapper').html(response.data);
                    return false;
                }).fail(function (response) {
                    alert('Failed to add subscriber');
                    return false;
                })
            }
        });
    }


});