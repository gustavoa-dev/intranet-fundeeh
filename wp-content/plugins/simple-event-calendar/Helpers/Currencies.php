<?php

namespace GDCalendar\Helpers;

class Currencies
{
    public static function getCurrencyName() {
        return array_unique(
            array(
	            'USD' => __( 'US Dollars', 'gd-calendar' ),
	            'EUR' => __( 'Euros', 'gd-calendar' ),
                'AED' => __( 'UAE Dirham', 'gd-calendar' ),
                'ARS' => __( 'Argentine Peso', 'gd-calendar' ),
                'AUD' => __( 'Australian Dollars', 'gd-calendar' ),
                'AMD' => __( 'Armenian Dram', 'gd-calendar' ),
                'BDT' => __( 'Bangladeshi Taka', 'gd-calendar' ),
                'BRL' => __( 'Brazilian Real', 'gd-calendar' ),
                'BGN' => __( 'Bulgarian Lev', 'gd-calendar' ),
                'CAD' => __( 'Canadian Dollars', 'gd-calendar' ),
                'CLP' => __( 'Chilean Peso', 'gd-calendar' ),
                'CNY' => __( 'Chinese Yuan', 'gd-calendar' ),
                'COP' => __( 'Colombian Peso', 'gd-calendar' ),
                'CZK' => __( 'Czech Koruna', 'gd-calendar' ),
                'DKK' => __( 'Danish Krone', 'gd-calendar' ),
                'DOP' => __( 'Dominican Peso', 'gd-calendar' ),
                'HKD' => __( 'Hong Kong Dollar', 'gd-calendar' ),
                'HRK' => __( 'Croatia kuna', 'gd-calendar' ),
                'HUF' => __( 'Hungarian Forint', 'gd-calendar' ),
                'ISK' => __( 'Icelandic krona', 'gd-calendar' ),
                'IDR' => __( 'Indonesia Rupiah', 'gd-calendar' ),
                'INR' => __( 'Indian Rupee', 'gd-calendar' ),
                'NPR' => __( 'Nepali Rupee', 'gd-calendar' ),
                'ILS' => __( 'Israeli Shekel', 'gd-calendar' ),
                'JPY' => __( 'Japanese Yen', 'gd-calendar' ),
                'KIP' => __( 'Lao Kip', 'gd-calendar' ),
                'KRW' => __( 'South Korean Won', 'gd-calendar' ),
                'MYR' => __( 'Malaysian Ringgits', 'gd-calendar' ),
                'MXN' => __( 'Mexican Peso', 'gd-calendar' ),
                'NGN' => __( 'Nigerian Naira', 'gd-calendar' ),
                'NOK' => __( 'Norwegian Krone', 'gd-calendar' ),
                'NZD' => __( 'New Zealand Dollar', 'gd-calendar' ),
                'PYG' => __( 'Paraguayan Guaraní', 'gd-calendar' ),
                'PHP' => __( 'Philippine Pesos', 'gd-calendar' ),
                'PLN' => __( 'Polish Zloty', 'gd-calendar' ),
                'GBP' => __( 'Pounds Sterling', 'gd-calendar' ),
                'RON' => __( 'Romanian Leu', 'gd-calendar' ),
                'RUB' => __( 'Russian Ruble', 'gd-calendar' ),
                'SGD' => __( 'Singapore Dollar', 'gd-calendar' ),
                'ZAR' => __( 'South African rand', 'gd-calendar' ),
                'SEK' => __( 'Swedish Krona', 'gd-calendar' ),
                'CHF' => __( 'Swiss Franc', 'gd-calendar' ),
                'TWD' => __( 'Taiwan New Dollars', 'gd-calendar' ),
                'THB' => __( 'Thai Baht', 'gd-calendar' ),
                'TRY' => __( 'Turkish Lira', 'gd-calendar' ),
                'UAH' => __( 'Ukrainian Hryvnia', 'gd-calendar' ),
                'VND' => __( 'Vietse Dong', 'gd-calendar' ),
                'EGP' => __( 'Egyptian Pound', 'gd-calendar' )
            )
        );
    }

    public static function getCurrencySymbol( $currency ) {

        switch ( $currency ) {
            case 'AED' :
                $currency_symbol = 'د.إ';
                break;
            case 'AUD' :
            case 'ARS' :
            case 'CAD' :
            case 'CLP' :
            case 'COP' :
            case 'HKD' :
            case 'MXN' :
            case 'NZD' :
            case 'SGD' :
            case 'USD' :
                $currency_symbol = '&#36;';
                break;
            case 'AMD':
                $currency_symbol = '&#1423;';
                break;
            case 'BDT':
                $currency_symbol = '&#2547;&nbsp;';
                break;
            case 'BGN' :
                $currency_symbol = '&#1083;&#1074;.';
                break;
            case 'BRL' :
                $currency_symbol = '&#82;&#36;';
                break;
            case 'CHF' :
                $currency_symbol = '&#67;&#72;&#70;';
                break;
            case 'CNY' :
            case 'JPY' :
            case 'RMB' :
                $currency_symbol = '&yen;';
                break;
            case 'CZK' :
                $currency_symbol = '&#75;&#269;';
                break;
            case 'DKK' :
                $currency_symbol = 'DKK';
                break;
            case 'DOP' :
                $currency_symbol = 'RD&#36;';
                break;
            case 'EGP' :
                $currency_symbol = 'EGP';
                break;
            case 'EUR' :
                $currency_symbol = '&euro;';
                break;
            case 'GBP' :
                $currency_symbol = '&pound;';
                break;
            case 'HRK' :
                $currency_symbol = 'Kn';
                break;
            case 'HUF' :
                $currency_symbol = '&#70;&#116;';
                break;
            case 'IDR' :
                $currency_symbol = 'Rp';
                break;
            case 'ILS' :
                $currency_symbol = '&#8362;';
                break;
            case 'INR' :
                $currency_symbol = 'Rs.';
                break;
            case 'ISK' :
                $currency_symbol = 'Kr.';
                break;
            case 'KIP' :
                $currency_symbol = '&#8365;';
                break;
            case 'KRW' :
                $currency_symbol = '&#8361;';
                break;
            case 'MYR' :
                $currency_symbol = '&#82;&#77;';
                break;
            case 'NGN' :
                $currency_symbol = '&#8358;';
                break;
            case 'NOK' :
                $currency_symbol = '&#107;&#114;';
                break;
            case 'NPR' :
                $currency_symbol = 'Rs.';
                break;
            case 'PHP' :
                $currency_symbol = '&#8369;';
                break;
            case 'PLN' :
                $currency_symbol = '&#122;&#322;';
                break;
            case 'PYG' :
                $currency_symbol = '&#8370;';
                break;
            case 'RON' :
                $currency_symbol = 'lei';
                break;
            case 'RUB' :
                $currency_symbol = '&#1088;&#1091;&#1073;.';
                break;
            case 'SEK' :
                $currency_symbol = '&#107;&#114;';
                break;
            case 'THB' :
                $currency_symbol = '&#3647;';
                break;
            case 'TRY' :
                $currency_symbol = '&#8378;';
                break;
            case 'TWD' :
                $currency_symbol = '&#78;&#84;&#36;';
                break;
            case 'UAH' :
                $currency_symbol = '&#8372;';
                break;
            case 'VND' :
                $currency_symbol = '&#8363;';
                break;
            case 'ZAR' :
                $currency_symbol = '&#82;';
                break;
            default :
                $currency_symbol = '';
                break;
        }

        return $currency_symbol;
    }
}