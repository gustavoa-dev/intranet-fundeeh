"use strict";

jQuery( document ).ready(function() {

    /**
     * Event add new venue and organizer
     */

    jQuery(".add_new").on('click', function () {
        jQuery(this).hide();
        jQuery(this).closest('.event_block').siblings('.event_block_edit').show();
        jQuery(this).siblings('.event_back').css('display', 'block');
        return false;
    });

    jQuery('.event_back').on('click', function () {
        backReset();
        return false;
    });

    var new_venue = jQuery("#create_new_venue"),
        venue_name = jQuery("#venue_name"),
        error_name = jQuery(".error-name"),
        new_organizer = jQuery("#create_new_organizer"),
        organizer_name = jQuery("#organizer_name"),
        organizer_email = jQuery("#organizer_email"),
        phone = jQuery("#phone"),
        error_name_org = jQuery(".error-name-org"),
        error_email_org = jQuery(".error-email-org");

    /**
     * @type {RegExp}
     * For email validation
     */
    var re = /[A-Z0-9._%+-]+@[A-Z0-9.-]+.[A-Z]{2,4}/igm;

    venue_name.on('blur', function () {
        if(jQuery(this).val() !== "") {
            error_name.addClass('hide');
        }
    });

    organizer_name.on('blur', function () {
        if(jQuery(this).val() !== ""){
            error_name_org.addClass('hide');
            return false;
        }
    });

    organizer_email.on('blur', function () {
       if(jQuery(this).val() === "" ){
           error_email_org.addClass('hide');
           return false;
       }
    });

    /**
     * Event save venue ajax handler
     */

    new_venue.click(function () {

        if(venue_name.val() === "") {
            error_name.removeClass('hide');
            return false;
        }
        var data = {
            action: 'event_save_venue',
            nonce: gdCalendarEventAjaxObj.gdNonceSave,
            title: venue_name.val(),
            address: jQuery("#address").val(),
        };
        jQuery.ajax({
            url: gdCalendarEventAjaxObj.ajaxUrl,
            type: 'post',
            data: data,
            dataType: 'json'
        }).done(function (response) {
            if (response.status === 0) {
                return false;
            }
            jQuery("#event_venue").append(jQuery('<option>', {
                value: response.id,
                text: response.title,
                selected: 'selected'
            }));
            emptyVenueFields();
            backReset();
        });
    });

    /**
     * Event save organizer ajax handler
     */

    new_organizer.on('click', function () {

        if(organizer_name.val() === ''){
            error_name_org.removeClass('hide');
            return false;
        }
        if(phone.val() !== '' && !phone.intlTelInput("isValidNumber")){
            return false;
        }

        if(organizer_email.val() !== '' && !re.test(organizer_email.val())){
            error_email_org.removeClass('hide');
            return false;
        }

        var data = {
            action: 'event_save_organizer',
            nonce: gdCalendarEventAjaxObj.gdNonceSaveOrg,
            title: organizer_name.val(),
            organized_by: jQuery('#organized_by').val(),
            organizer_address: jQuery('#organizer_address').val(),
            phone: phone.val(),
            website: jQuery('#website').val(),
            organizer_email: jQuery('#organizer_email').val(),

        };
        jQuery.ajax({
            url: gdCalendarEventAjaxObj.ajaxUrl,
            data: data,
            type: 'post',
            dataType: 'json'
        }).done(function (response) {
            if(response.status === 0){
                return false;
            }
            jQuery("#event_organizer").append(jQuery('<option>', {
                value: response.id,
                text: response.title,
                selected: 'selected'
            }));
            emptyOrganizerFields();
            backReset();
        });
    });

    /**
     * Reset and Empty Fields
     */

    function backReset() {
        jQuery(".event_back").hide();
        jQuery(".add_new").show();
        jQuery(".event_block_edit").hide();
        if(!error_email_org.hasClass('hide')){
            error_email_org.addClass('hide');
        }
    }

    function emptyVenueFields() {
        venue_name.val('');
        jQuery("#address").val('');
    }

    function emptyOrganizerFields() {
        organizer_name.val('');
        jQuery('#organized_by').val('');
        jQuery("#organizer_address").val('');
        phone.val('');
        jQuery("#website").val('');
        jQuery("#organizer_email").val('');
    }
});
