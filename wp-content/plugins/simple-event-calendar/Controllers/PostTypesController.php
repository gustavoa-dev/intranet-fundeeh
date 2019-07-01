<?php

namespace GDCalendar\Controllers;

use GDCalendar\GDCalendar;
use GDCalendar\Models\PostTypes\Calendar;
use GDCalendar\Models\PostTypes\Event;
use GDCalendar\Models\PostTypes\Organizer;
use GDCalendar\Models\PostTypes\Venue;

class PostTypesController
{
    public static function run()
    {
        if(!get_option('gd_calendar_default_calendar')){
            update_option('gd_calendar_default_calendar', 0);
        }
        if(!get_option('gd_calendar_default_event')){
            update_option('gd_calendar_default_event', 0);
        }
        if(!get_option('gd_calendar_default_venue')){
            update_option('gd_calendar_default_venue', 0);
        }
        if(!get_option('gd_calendar_default_organizer')){
            update_option('gd_calendar_default_organizer', 0);
        }

        self::createPostTypes();
        flush_rewrite_rules();

    }

    public static function createPostTypes()
    {
        self::customPostCalendars();
        self::customPostOrganizers();
        self::customPostVenues();
        self::customPostEvents();
        self::registerTaxonomyCategory();
        self::registerTaxonomyTag();
        add_action( 'init', array(__CLASS__, 'featuredImages'));
        add_action('trashed_post', array(__CLASS__, 'removeDefaultCalendar'));
        add_action('trashed_post', array(__CLASS__, 'removeDefaultEvent'));
        add_action('trashed_post', array(__CLASS__, 'removeDefaultVenue'));
        add_action('trashed_post', array(__CLASS__, 'removeDefaultOrganizer'));
    }

    /**
     * Print Calendar Menu
     */

    public static function customPostEvents() {
        register_post_type(Event::get_post_type(),
            array(
                'labels' => array(
                    'name' => __('Events List', 'gd-calendar'),
                    'menu_name' => __('GrandWP Calendar', 'gd-calendar'),
                    'singular_name' => __('Event', 'gd-calendar'),
                    'all_items' => __('Events', 'gd-calendar'),
                    'add_new' => __('Add New Event', 'gd-calendar'),
                    'add_new_item' => __('Add New Event', 'gd-calendar'),
                    'new_item' => __('New Event', 'gd-calendar'),
                    'edit_item' => __('Edit Event', 'gd-calendar'),
                    'view_item' => __('View Event', 'gd-calendar'),
                    'view_items' => __('View Events', 'gd-calendar'),
                    'search_items' => __('Search Event', 'gd-calendar'),
                    'not_found' => __('No events found', 'gd-calendar'),
                    'not_found_in_trash' => __('No events found in Trash', 'gd-calendar'),
                ),
                'public' => true,
                'has_archive' => true,
                'menu_icon'   => \GDCalendar()->pluginUrl() . "/resources/assets/images/calendar_shortcode.png",
                'rewrite' => array('slug' => 'events'),
                'menu_position' => 80
                //'supports' => array('title', 'editor', 'excerpt')
            )
        );

        self::addDefaultEvent();
    }



    /**
     * Add default Event
     */

    public static function addDefaultEvent(){

        $title  = 'My First Event';
        $status = get_option("gd_calendar_default_event");

        if (!get_page_by_title($title, OBJECT, 'gd_events') && $status == 0) :
            $post_data = array(
                'post_title'  => $title,
                'post_status' => 'publish',
                'post_type'   => 'gd_events',
            );

            $post_id = wp_insert_post($post_data);
            $event = new Event($post_id);

            $event->set_start_date(date("m/d/Y h:i a"))
                ->set_end_date(date("m/d/Y h:i a", strtotime("tomorrow")))
                ->set_event_venue(self::addDefaultVenue())
                ->set_event_organizer(array(self::addDefaultOrganizer()));
            $event->save();
        endif;
    }

    public static function removeDefaultEvent(){
        global $post_type;
        if ( $post_type === Event::get_post_type()) {

            update_option('gd_calendar_default_event', 1);
        }
    }

	/**
	 * Register post types
	 */

	public static function customPostCalendars() {
		register_post_type(Calendar::get_post_type(),
			array(
				'labels' => array(
					'name' => __('Calendars List', 'gd-calendar'),
					'singular_name' => __( 'Calendar', 'gd-calendar' ),
					'all_items' => __('Calendars', 'gd-calendar'),
					'add_new' => __('Add New Calendar', 'gd-calendar'),
					'add_new_item' => __('Add New Calendar', 'gd-calendar'),
					'edit_item' => __('Edit Calendar', 'gd-calendar'),
					'new_item' => __('New Calendar', 'gd-calendar'),
					'view_item' => __('View Calendar', 'gd-calendar'),
					'view_items' => __('View Calendars', 'gd-calendar'),
					'search_items' => __('Search Calendar', 'gd-calendar'),
					'not_found' => __('No calendars found', 'gd-calendar'),
					'not_found_in_trash' => __('No calendars found in Trash', 'gd-calendar'),
				),
				'public' => true,
				'has_archive' => true,
				'rewrite' => array('slug' => 'calendar'),
				'show_in_menu' => 'edit.php?post_type=gd_events',
				'supports' => 'title',
			)
		);

		self::addDefaultCalendar();
	}

    /**
     * Register taxonomies
     */

    public static function registerTaxonomyCategory()
    {
        $labels = array(
            'name'              => _x('Event Categories', 'categories', 'gd-calendar'),
            'singular_name'     => _x('Event Category', 'category', 'gd-calendar'),
            'search_items'      => __('Search Category', 'gd-calendar'),
            'all_items'         => __('All Categories', 'gd-calendar'),
            'parent_item'       => __('Parent Category', 'gd-calendar'),
            'parent_item_colon' => __('Parent Category:', 'gd-calendar'),
            'edit_item'         => __('Edit Category', 'gd-calendar'),
            'update_item'       => __('Update Category', 'gd-calendar'),
            'add_new_item'      => __('Add New Category', 'gd-calendar'),
            'new_item_name'     => __('New Category Name', 'gd-calendar'),
            'menu_name'         => __('Event Categories', 'gd-calendar'),
        );
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'event-category'),
        );
        register_taxonomy('event_category', 'gd_events', $args);
    }

    public static function registerTaxonomyTag()
    {
        $labels = array(
            'name'              => _x('Event Tags', 'tags', 'gd-calendar'),
            'singular_name'     => _x('Event Tag', 'tag', 'gd-calendar'),
            'search_items'      => __('Search Tags', 'gd-calendar'),
            'all_items'         => __('All Tags', 'gd-calendar'),
            'parent_item'       => __('Parent Tag', 'gd-calendar'),
            'parent_item_colon' => __('Parent Tag:', 'gd-calendar'),
            'edit_item'         => __('Edit Tag', 'gd-calendar'),
            'update_item'       => __('Update Tag', 'gd-calendar'),
            'add_new_item'      => __('Add New Tag', 'gd-calendar'),
            'new_item_name'     => __('New Tag Name', 'gd-calendar'),
            'menu_name'         => __('Event Tags', 'gd-calendar'),
        );
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'event-tag'),
        );
        register_taxonomy('event_tag', 'gd_events', $args);
    }



    /**
     * Add default Calendar
     */

    public static function addDefaultCalendar(){

        $title  = 'My First Calendar';
        $status = get_option("gd_calendar_default_calendar");

        if (!get_page_by_title($title, OBJECT, 'gd_calendar') && $status == 0) :
            $post_data = array(
                'post_title'  => $title,
                'post_status' => 'publish',
                'post_type'   => 'gd_calendar',
            );

            $post_id = wp_insert_post($post_data);
            $calendar = new Calendar($post_id);
            $calendar->set_view_type(array(0, 1))
                ->set_theme(0);
            $calendar->save();
        endif;
    }

    public static function removeDefaultCalendar(){
        global $post_type;
        if ( $post_type === Calendar::get_post_type()) {

            update_option('gd_calendar_default_calendar', 1);
        }
    }

    public static function customPostOrganizers() {
        register_post_type(Organizer::get_post_type(),
            array(
                'labels' => array(
                    'name' => __('Organizers', 'gd-calendar'),
                    'singular_name' => __( 'Organizer', 'gd-calendar' ),
                    'all_items' => __('Organizers', 'gd-calendar'),
                    'add_new' => __('Add New Organizer', 'gd-calendar'),
                    'add_new_item' => __('Add New Organizer', 'gd-calendar'),
                    'edit_item' => __('Edit Organizer', 'gd-calendar'),
                    'new_item' => __('New Organizer', 'gd-calendar'),
                    'view_item' => __('View Organizer', 'gd-calendar'),
                    'view_items' => __('View Organizers', 'gd-calendar'),
                    'search_items' => __('Search Organizer', 'gd-calendar'),
                    'not_found' => __('No organizers found', 'gd-calendar'),
                    'not_found_in_trash' => __('No organizers found in Trash', 'gd-calendar'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'organizer'),
                'show_in_menu' => 'edit.php?post_type=gd_events',
                'supports' => array('title'),
            )
        );
    }

    /**
     * Add default Organizer
     */

    public static function addDefaultOrganizer(){

        $title  = 'My First Organizer';
        $status = get_option("gd_calendar_default_organizer");
        $post_id = '';

        if (!get_page_by_title($title, OBJECT, 'gd_organizers') && $status == 0) :
            $post_data = array(
                'post_title'  => $title,
                'post_status' => 'publish',
                'post_type'   => 'gd_organizers',
            );

            $post_id = wp_insert_post($post_data);
            $organizer = new Organizer($post_id);
            $organizer->set_organized_by('John Smith')
                ->set_organizer_address('Centre de conventions de Los Angeles, South Figueroa Street, Los Angeles, Californie')
                ->set_phone('+12016543210')
                ->set_website('http://grandwp.com')
                ->set_organizer_email('admin@grandwp.com');
            $organizer->save();
        endif;

        return $post_id;
    }

    public static function removeDefaultOrganizer(){
        global $post_type;
        if ( $post_type === Organizer::get_post_type()) {

            update_option('gd_calendar_default_organizer', 1);
        }
    }

    public static function customPostVenues() {
        register_post_type('gd_venues',
            array(
                'labels' => array(
                    'name' => __('Venues', 'gd-calendar'),
                    'singular_name' => __( 'Venue', 'gd-calendar' ),
                    'all_items' => __('Venues', 'gd-calendar'),
                    'add_new' => __('Add New Venue', 'gd-calendar'),
                    'add_new_item' => __('Add New Venue', 'gd-calendar'),
                    'edit_item' => __('Edit Venue', 'gd-calendar'),
                    'new_item' => __('New Venue', 'gd-calendar'),
                    'view_item' => __('View Venue', 'gd-calendar'),
                    'view_items' => __('View Venues', 'gd-calendar'),
                    'search_items' => __('Search Venue', 'gd-calendar'),
                    'not_found' => __('No venues found', 'gd-calendar'),
                    'not_found_in_trash' => __('No venues found in Trash', 'gd-calendar'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'venue'),
                'show_in_menu' => 'edit.php?post_type=gd_events',
                'supports' => 'title',
            )
        );
    }

    /**
     * Add default Venue
     */

    public static function addDefaultVenue(){

        $title  = 'My First Venue';
        $status = get_option("gd_calendar_default_venue");
        $post_id = '';

        if (!get_page_by_title($title, OBJECT, 'gd_venues') && $status == 0) :
            $post_data = array(
                'post_title'  => $title,
                'post_status' => 'publish',
                'post_type'   => 'gd_venues',
            );
            $post_id = wp_insert_post($post_data);
            $venue = new Venue($post_id);
            $venue->set_address('1201 South Figueroa Street, Los Angeles, California 90015, United States')
                ->set_latitude('34.0413606')
                ->set_longitude('-118.2697771');
            $venue->save();
        endif;

        return $post_id;
    }

    public static function removeDefaultVenue(){
        global $post_type;
        if ( $post_type === Venue::get_post_type()) {
            update_option('gd_calendar_default_venue', 1);
        }
    }

    public static function featuredImages()
    {
        add_post_type_support( 'gd_calendar', 'thumbnail' );
        add_post_type_support( 'gd_events', 'thumbnail' );
        add_post_type_support( 'gd_venues', 'thumbnail' );
        add_post_type_support( 'gd_organizers', 'thumbnail' );
    }

}