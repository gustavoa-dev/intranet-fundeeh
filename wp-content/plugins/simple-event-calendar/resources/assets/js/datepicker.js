"use strict";
jQuery(document).ready(function () {

    /**
     *  Jquery UI Datepicker
     */

    var start_date = jQuery("#start_date"),
        end_date = jQuery("#end_date"),
        title = jQuery("#title");

    function calendarDatePicker(start_date, end_date){
        start_date.datepicker(
        {
            dateFormat: 'mm/dd/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function (){
                end_date.datepicker('option', 'minDate', start_date.datepicker('getDate') );
            }
        });
        end_date.datepicker({
            dateFormat: 'mm/dd/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function (){
                start_date.datepicker('option', 'maxDate', end_date.datepicker('getDate') );
            }
        });
    }

    function calendarDateTimePicker(start_date, end_date){
        start_date.datetimepicker({
            timeFormat: 'hh:mm tt',
            dateFormat: 'mm/dd/yy',
            oneLine: true,
            changeMonth: true,
            changeYear: true,
            controlType: 'select',
            onSelect: function (selectedDateTime){
                end_date.datetimepicker('option', 'minDate', start_date.datetimepicker('getDate') );
            }
        });
        end_date.datetimepicker({
            timeFormat: 'hh:mm tt',
            dateFormat: 'mm/dd/yy',
            oneLine: true,
            changeMonth: true,
            changeYear: true,
            controlType: 'select',
            onSelect: function (selectedDateTime){
                start_date.datetimepicker('option', 'maxDate', end_date.datetimepicker('getDate') );
            }
        });
    }

    calendarDateTimePicker(start_date, end_date);

    var start_date_long,
        start_date_short,
        end_date_long,
        end_date_short;

    jQuery("#all_day").change(function () {

        if(start_date.val() !== ''){
            (start_date.val().length > 10) ? start_date_long = start_date.val() : start_date_short = start_date.val();
        }

        if(end_date.val() !== ''){
            (end_date.val().length > 10) ? end_date_long = end_date.val() : end_date_short = end_date.val();
        }

        if(jQuery(this).prop('checked')){
            start_date.datetimepicker('option',{
                timepicker:false,
            });
            end_date.datetimepicker('option',{
                timepicker:false,
            });
            start_date.datetimepicker('destroy');
            end_date.datetimepicker('destroy');
            calendarDatePicker(start_date, end_date);

            if(start_date_short){
                start_date_short = start_date_long.substr(0, 10);
                start_date.val(start_date_short);
                start_date_short = start_date.val();
            }

            if(end_date_short){
                end_date_short = end_date_long.substr(0, 10);
                end_date.val(end_date_short);
                end_date_short = end_date.val();
            }
        }
        else{
            start_date.datepicker('destroy');
            end_date.datepicker('destroy');
            calendarDateTimePicker(start_date, end_date);

            if(start_date_long){
                if(start_date_long.substr(0, 10) === start_date_short){
                    start_date.val(start_date_long);
                }
                else {start_date.val(start_date_short + start_date_long.substr(10,19));}
                start_date_long = start_date.val();
            }
            else{
                if(start_date_short){
                    start_date.val(start_date_short + ' 12:00 am');
                    start_date_long = start_date.val();
                }
            }

            if(end_date_long){

                if(end_date_long.substr(0, 10) === end_date_short){
                    end_date.val(end_date_long);
                }
                else {end_date.val(end_date_short + end_date_long.substr(10,19));}
                end_date_long = end_date.val();
            }
            else{
                if(end_date_short) {
                    end_date.val(end_date_short + ' 12:00 am');
                    end_date_long = end_date.val();
                }
            }

        }
    });

    if(jQuery("#all_day").is(':checked')) {


        start_date.datetimepicker('option',{
            timepicker:false,
        });
        end_date.datetimepicker('option',{
            timepicker:false,
        });

        start_date.datetimepicker('destroy');
        end_date.datetimepicker('destroy');
        calendarDatePicker(start_date, end_date);
    }

    /**
     * Error for empty date
     */

    var publish = jQuery("#publish"),
        errorStart = jQuery(".error-start"),
        errorEnd = jQuery(".error-end"),
        wrongEnd = jQuery(".wrong-end");


    title.after( '<span class="error-msg error-title hide">' + orderL10n.titleError + '</span>' );
    title.on('blur', function () {
        if (jQuery(this).val() != "") {
            jQuery("body").find(".error-title").addClass('hide');
        }
    });
    start_date.on('blur', function () {
        if (jQuery(this).val() != "") {
            errorStart.addClass('hide');
        }
    });
    end_date.on('blur change', function () {
        if (jQuery(this).val() != "") {
            errorEnd.addClass('hide');
        }
        if(true === checkEndDate(start_date.val(), end_date.val())){
            wrongEnd.addClass('hide');
        }
    });


    function checkEndDate(start_date, end_date) {
        var start_date_val = new Date(Date.parse(start_date)),
            end_date_val = new Date(Date.parse(end_date));

        if(end_date_val < start_date_val){
            return false;
        }
        else {
            return true;
        }
    }

    publish.on('click', function () {


        if(title.val() === ""){
            jQuery("body").find(".error-title").removeClass('hide');
            removeUrlParameter();
            return false;
        }
        if (start_date.val() === "") {
            errorStart.removeClass('hide');
            removeUrlParameter();
            jQuery("html, body").animate({ scrollTop: jQuery("#event_date").offset().top }, 500);
            return false;
        }
        if (end_date.val() === "") {
            errorEnd.removeClass('hide');
            removeUrlParameter();
            jQuery("html, body").animate({ scrollTop: jQuery("#event_date").offset().top }, 500);
            return false;
        }
        if (false === checkEndDate(start_date.val(),end_date.val())){
            wrongEnd.removeClass('hide');
            removeUrlParameter();
            jQuery("html, body").animate({ scrollTop: jQuery("#event_date").offset().top }, 500);
            return false;
        }
    });
});