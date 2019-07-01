<?php
/**
Plugin Name: GrandWP Calendar
Plugin URI: http://grandwp.com
Description: GrandWP Calendar is a great plugin for adding specialized Calendar.
Version: 1.0.9
Author: GrandWP
Author URI: http://grandwp.com/
License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: gd-calendar
Domain Path: /languages
*/

//error_reporting(E_ALL);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function gutenberg_gd_event_calendar()
{

    wp_register_script(
        'gd-event-calendar-gutenberg',
        plugins_url('resources/assets/js/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components')
    );
    wp_register_style(
        'gd-event-calendar-gutenberg',
        plugins_url('resources/assets/style/block.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . 'resources/assets/style/block.css')
    );

    global $wpdb;


    $calendars = \GDCalendar\Models\PostTypes\Calendar::all();

    $options = array(
        array(
            'value' => '',
            'label' => 'Select Calendar'
        )
    );

    foreach ($calendars as $calendar) {
        $options[] = array(
            'value' => $calendar->get_id(),
            'label' => $calendar->get_post_data()->post_title,
        );
    }

    wp_localize_script('gd-event-calendar-gutenberg', 'gdeventcalendarblock', array(
        'gdcalendar' => $options
    ));
    if (function_exists('register_block_type')) {
        register_block_type('simple-event-calendar/index', array(
            'editor_script' => 'gd-event-calendar-gutenberg',
            'editor_style' => 'gd-event-calendar-gutenberg',
        ));
    }
}
add_action( 'init', 'gutenberg_gd_event_calendar' );

function gd_event_calendar_gutenberg_category( $categories, $post ) {
    if ( $post->post_type !== 'post' ) {
        return $categories;
    }
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'simple-event-calendar',
                'title' => __( 'GrandWP Calendar', 'gdcalendar' ),
                'icon'  => 'calendar-alt',
            ),
        )
    );
}
add_filter( 'block_categories', 'gd_event_calendar_gutenberg_category', 10, 2 );

require 'autoload.php';

require 'GDCalendar.php';

function GDCalendar(){
    return \GDCalendar\GDCalendar::instance();
}

$GLOBALS['GDCalendar'] = GDCalendar();

register_activation_hook( __FILE__, array('GDCalendar\Controllers\PostTypesController', 'run') );
