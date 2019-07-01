"use strict";

jQuery(document).ready(function () {
    /**
     * Phone number input
     */
    var telInput = jQuery("#phone"),
        errorMsg = jQuery("#error-msg"),
        validMsg = jQuery("#valid-msg");

    telInput.intlTelInput({
        autoFormat: true,
        nationalMode:false
    });

    var reset = function() {
        telInput.removeClass("error");
        errorMsg.addClass("hide");
        validMsg.addClass("hide");
    };

    telInput.blur(function() {
        reset();
        if (jQuery.trim(telInput.val())) {
            if (telInput.intlTelInput("isValidNumber")) {
                validMsg.removeClass("hide");
            } else {
                telInput.addClass("error");
                errorMsg.removeClass("hide");
            }
        }
    });

    telInput.on("keyup change", reset);

    var publish = jQuery("#publish");
        publish.on('click', function () {
            if( telInput.val() !== '' && !telInput.intlTelInput("isValidNumber")){
                removeUrlParameter();
                return false;
            }
        });

    /**
     * Phone number allow numeric
     */

    telInput.keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [109, 46, 8, 9, 32, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    jQuery('#website').on('focus keyup', function () {
        var $this = jQuery(this);
        if ($this.val() == ''){
            $this.val($this.attr('placeholder'));
        }
    });
    jQuery('#website').on('blur', function () {
        var $this = jQuery(this),
            placeholder = $this.attr('placeholder');
        if ($this.val() === placeholder) {
            $this.val('');
        }
    });
});