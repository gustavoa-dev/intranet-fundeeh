jQuery(document).ready(function () {
    jQuery('#gd_calendar_insert').on('click', function () {
        var id = jQuery('#gd_calendar_select option:selected').val();
        window.send_to_editor('[gd_calendar id="' + id + '"]');
        tb_remove();
        return false;
    });
    jQuery("#gd_calendar_cancel").on('click', function () {
        jQuery( ".tb-close-icon" ).trigger( "click" );
    });
});