<?php

namespace GDCalendar\Controllers\Widgets;

class WidgetsController
{
    public static function init(){
        register_widget( 'GDCalendar\Controllers\Widgets\CalendarWidgetController' );
    }
}