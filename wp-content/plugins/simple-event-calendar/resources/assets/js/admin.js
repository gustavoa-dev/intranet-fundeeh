"use strict";

/**
 *  Remove Parameter from URL when error required field
 */
function removeUrlParameter() {
    var location = window.location.href;

    if (location.indexOf("&wp-post-new-reload=true") !== -1){
        location = location.replace("&wp-post-new-reload=true", "");
        window.history.replaceState( null, null, location );
    }
}

jQuery( document ).ready(function() {
    /**
     * Settings Checkbox all checked
     */

    var all_checkbox_select = jQuery("#all_checkbox_select");

    all_checkbox_select.change(function () {
        jQuery(".cat").prop('checked', jQuery(this).prop("checked"));
    });
    jQuery('.cat').on('change', function () {
        all_checked();
    });

    /**
     * Set default value All checked
     */
    if(jQuery(".cat:checked").length === 0 && !all_checkbox_select.is(":checked")){
        all_checkbox_select.attr("checked", "checked");
        all_checkbox_select.trigger("change");
    }

    /**
     * Set month as default value for view
     */
    var all_checkbox_view = jQuery("#all_checkbox_view");

    if(jQuery(".view:checked").length === 0 && !all_checkbox_view.is(":checked")){
        jQuery('input.view:checkbox[value="' + 2 + '"]').attr('checked', true);
    }

    /**
     * View Checkbox all checked
     */

    jQuery("#all_checkbox_view").change(function () {
        jQuery(".view").prop('checked', jQuery(this).prop("checked"));
    });

    jQuery('.view').on('change', function () {
        choose_view_all_checked();
    });

    /**
     * Onload view checked
     */
    choose_view_all_checked();

    /**
     * Select and checkbox ajax handler
     * */

    jQuery( "#select_events_by" ).change(function () {
        var data = {
            action: 'select_events',
            nonce: gdCalendarAjaxObj.gdNonce,
            type: jQuery(this).find('option:selected').data('type'),
            value: jQuery(this).val(),
            postId: jQuery('.calendar_page_checkbox').data('post-id')
        };

        jQuery.ajax({
            url: gdCalendarAjaxObj.ajaxUrl,
            type: 'post',
            data: data,
            dataType: 'html',
        }).done(function (response) {
            jQuery('#checkboxes_container').empty();
            all_checkbox_select.removeAttr('checked');
            jQuery('#checkboxes_container').append(response);

            if(jQuery(".cat").length == jQuery(".cat:checked").length && jQuery(".cat").length != 0) {
                all_checkbox_select.attr("checked", "checked");
            }

            jQuery('.cat').on('change', function () {
                all_checked();
            });
        });
    });

    function all_checked() {
        if(jQuery(".cat").length == jQuery(".cat:checked").length) {
            all_checkbox_select.attr("checked", "checked");
        } else {
            all_checkbox_select.removeAttr("checked");
        }
    }

    function choose_view_all_checked() {
        if(jQuery(".view").length == jQuery(".view:checked").length) {
            jQuery("#all_checkbox_view").attr("checked", "checked");
        } else {
            jQuery("#all_checkbox_view").removeAttr("checked");
        }
    }

});