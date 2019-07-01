<?php

namespace GDCalendar\Core;

class Validator
{
    public static function validateDate($date){
        $format = array('m/d/Y h:i a', 'm/d/Y');
        $d = \DateTime::createFromFormat($format[0], $date);
        if($d !== false){
            return $d && $d->format($format[0]) == $date;
        }
        $d = \DateTime::createFromFormat($format[1], $date);
        return $d && $d->format($format[1]) == $date;
    }

}