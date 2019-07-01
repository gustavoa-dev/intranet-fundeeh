"use strict";

jQuery(document).ready(function() {

    jQuery(".gd_calendar_section_edit_name").click(function (e) {
        e.preventDefault();
        jQuery(this).parent().find(".gd_calendar_section_active_name").hide();
        jQuery(this).parent().find(".gd_calendar_edit_section_name_input").removeClass("gd_calendar_hidden");
        jQuery(this).hide();
        var strLength = jQuery(".gd_calendar_edit_section_name_input").val().length * 2;
        jQuery(".gd_calendar_edit_section_name_input").focus();
        jQuery(".gd_calendar_edit_section_name_input")[0].setSelectionRange(strLength, strLength);
    });

    jQuery(window).click(function () {
//Hide the menus if visible
        jQuery(".gd_calendar_section_edit_name").parent().find(".gd_calendar_edit_section_name_input").addClass("gd_calendar_hidden");
        jQuery(".gd_calendar_section_edit_name").parent().find(".gd_calendar_section_active_name").show();
        jQuery(".gd_calendar_section_edit_name").show();
    });
    jQuery('.gd_calendar_section_edit_name').click(function (event) {
        event.stopPropagation();
    });
    jQuery(".gd_calendar_edit_section_name_input").bind("keyup keypress", function () {
        jQuery(".gd_calendar_section_active_name").text(jQuery(this).val());
        jQuery("#gd_calendar_theme_name").val(jQuery(this).val());
        jQuery(".gd_calendar_theme_option_header").html(jQuery(this).val());
        jQuery("#title").val((jQuery(this).val()));
    });
    jQuery("#title").bind("keyup keypress", function () {
        jQuery(".gd_calendar_section_active_name").text(jQuery(this).val());
    });


    jQuery('.gd_calendar_theme_option_body').masonry();
    jQuery('.gd_calendar_theme_section_heading').on('click', function () {
        var section = jQuery(this).closest('.gd_calendar_theme_section_wrap');
        section.toggleClass('active');
        jQuery('.gd_calendar_theme_option_body').masonry();
    });

    jQuery('.theme-save').on('click', function (e) {
        jQuery(".pro_save_message").fadeIn().delay(3000).fadeOut();
        return false;

    });

});