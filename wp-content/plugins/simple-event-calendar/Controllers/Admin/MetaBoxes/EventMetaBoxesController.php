<?php

namespace GDCalendar\Controllers\Admin\MetaBoxes;

use GDCalendar\Core\ErrorHandling\ErrorBag;
use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Event;

class EventMetaBoxesController
{
    public function __construct()
    {
        add_action('save_post', array( __CLASS__, 'save'));
        add_action('add_meta_boxes', array(__CLASS__, 'metaBox'));
    }

    public static function metaBox()
    {
        add_meta_box(
            'gd_calendar_event_time_box_id',
            __('General options', 'gd-calendar'),
            array(__CLASS__, 'eventTime'),
            'gd_events',
            'normal',
            'high'
        );

        add_meta_box(
            'gd_calendar_event_venue_box_id',
            __('Venue', 'gd-calendar'),
            array(__CLASS__, 'eventVenue'),
            'gd_events',
            'normal',
            'high'
        );

        add_meta_box(
            'gd_calendar_event_organizer_box_id',
            __('Organizer', 'gd-calendar'),
            array(__CLASS__, 'eventOrganizer'),
            'gd_events',
            'normal',
            'high'
        );
    }

    public static function eventTime($post){
        wp_nonce_field('gd_events_save', 'gd_events_nonce');

        $post_id = absint($post->ID);
        $event = new Event($post_id);

        View::render('admin/meta-boxes/event-time-box.php', array(
            'event' => $event
        ));
    }

    public static function eventVenue($post){
        $post_id = absint($post->ID);
        $event = new Event($post_id);

        View::render('admin/meta-boxes/event-venue-box.php', array(
            'event' => $event,
        ));
    }

    public static function eventOrganizer($post){
        $post_id = absint($post->ID);

        $organizer = new Event($post_id);
        $event_organizer = $organizer->get_event_organizer();

        View::render('admin/meta-boxes/event-organizer-box.php', array(
            'event_organizer' => $event_organizer
        ));
    }

    public static function save($post_id){
        $post_type = get_post($post_id)->post_type;
        global $pagenow;

        if ( defined('DOING_AJAX') || $post_type !== 'gd_events' || get_post_status($post_id) === 'trash' || (isset($_GET['action']) && $_GET['action'] === 'untrash') || $pagenow === 'post-new.php' ) {
            return;
        }

        if( !isset($_POST['gd_events_nonce']) || !wp_verify_nonce($_POST['gd_events_nonce'], 'gd_events_save') ){
            wp_die('Are you sure you want to do this?');
        }

        $id = absint($post_id);
        $event = new Event($id);
	    $gd_calendar_errors = new ErrorBag();

	    if(empty($_POST['start_date'])){
            $_POST['start_date'] = date("m/d/Y");
        }

        if(empty($_POST['end_date'])){
            $_POST['end_date'] = date("m/d/Y");
        }

	    try {
		    $event->set_start_date($_POST['start_date']);
	    }
	    catch (\Exception $error) {
		    $gd_calendar_errors->addError($error->getMessage());
	    }
	    try{
            $event->set_end_date($_POST['end_date']);
	    }
	    catch (\Exception $error){
		    $gd_calendar_errors->addError($error->getMessage());
	    }

        if(isset($_POST['all_day'])) {
	        try{
		        $event->set_all_day($_POST['all_day']);
	        }
	        catch (\Exception $error){
		        $gd_calendar_errors->addError($error->getMessage());
	        }
        }

        if(isset($_POST['repeat'])){
	        try{
		        $event->set_repeat($_POST['repeat']);
	        }
	        catch (\Exception $error){
		        $gd_calendar_errors->addError($error->getMessage());
	        }
        }

        if( $event->get_repeat() === 'repeat' ) {
	        try{
		        $event->set_repeat_type($_POST['repeat_type']);
	        }
	        catch (\Exception $error){
		        $gd_calendar_errors->addError($error->getMessage());
	        }

            switch($event->get_repeat_type()){
                case 1:
                    $event->set_repeat_day($_POST['repeat_day']);
                    break;
                case 2:
                    $event->set_repeat_week($_POST['repeat_week']);
                    break;
                case 3:
                    $event->set_repeat_month($_POST['repeat_month']);
                    break;
                case 4:
                    $event->set_repeat_year($_POST['repeat_year']);
                    break;
            }
        }

        $event->set_timezone($_POST['timezone'])
            ->set_currency($_POST['currency'])
            ->set_cost($_POST['cost']);

        try{
	        $event->set_currency_position($_POST['currency_position']);
	    }
	    catch (\Exception $error){
		    $gd_calendar_errors->addError($error->getMessage());
	    }

        if (isset($_POST['event_venue']) && $_POST['event_venue'] != 'choose') {
            $event->set_event_venue($_POST['event_venue']);
        }

        if (!isset($_POST['event_organizer'])) {
            $org_array = array();
        }
        else{
            $org_array = $_POST["event_organizer"];
        }

	    if(!isset($_SESSION['errors'])){
		    $_SESSION['errors'] = $gd_calendar_errors->getErrors();
	    }

        $event->set_event_organizer($org_array);
        $event->save();

    }
}