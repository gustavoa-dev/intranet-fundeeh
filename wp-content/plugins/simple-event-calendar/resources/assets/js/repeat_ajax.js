"use strict";
jQuery( document ).ready(function() {
    /**
     *  Repeat rate
     */
    var repeat = jQuery("input[type='checkbox']#repeat"),
        repeat_type = jQuery("#repeat_type"),
        repeat_day = jQuery("#repeat_day"),
        repeat_week = jQuery("#repeat_week"),
        repeat_month = jQuery("#repeat_month"),
        repeat_year = jQuery("#repeat_year");

    repeatChange();
    repeat.change(function () {
        repeat_type.prop({disabled: !jQuery("#repeat_type").prop('disabled')});
        repeatChange();
    });

    repeat_type.on("change", function () {
        var data = {
            action: 'repeat_rate',
            nonce: gdCalendarRepeatAjaxObj.repeatNonce,
            type: jQuery("#repeat_type").val()
        };
        jQuery.ajax({
            url: gdCalendarRepeatAjaxObj.ajaxUrl,
            type: 'post',
            data: data,
            dataType: 'json'
        }).done(function (responce) {
            switch (responce.type) {
                case 1:
                    repeat_year.hide();
                    repeat_month.hide();
                    repeat_week.hide();
                    repeat_day.show();
                    break;
                case 2:
                    repeat_year.hide();
                    repeat_month.hide();
                    repeat_day.hide();
                    repeat_week.show();
                    break;
                case 3:
                    repeat_day.hide();
                    repeat_week.hide();
                    repeat_year.hide();
                    repeat_month.show();
                    break;
                case 4:
                    repeat_day.hide();
                    repeat_week.hide();
                    repeat_month.hide();
                    repeat_year.show();
                    break;
                default:
                    repeat_day.hide();
                    repeat_week.hide();
                    repeat_month.hide();
                    repeat_year.hide();
            }
        });
    });

    function repeatChange(){
        if(repeat.is(':checked')) {
            repeat_type.attr({disabled: false});

            if(repeat_type.find('option:selected').val() == 1){
                repeat_day.show();
            }
            else if (repeat_type.find('option:selected').val() == 2){
                repeat_week.show();
            }
            else if (repeat_type.find('option:selected').val() == 3){
                repeat_month.show();
            }
            else if (repeat_type.find('option:selected').val() == 4){
                repeat_year.show();
            }
        }
        if( false === repeat.prop( "checked" )) {
            repeat_type.attr({disabled: true});
            repeat_day.hide();
            repeat_week.hide();
            repeat_month.hide();
            repeat_year.hide();
        }
    }
});