<?php

namespace GDCalendar\Controllers\Admin\MetaBoxes;

use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Calendar;

class CalendarMetaBoxesController
{

    public function __construct()
    {
        add_action('save_post', array( __CLASS__, 'save'));
        add_action('add_meta_boxes', array(__CLASS__, 'metaBox'));
    }

    public static function metaBox()
    {
        add_meta_box(
            'gd_calendar_settings_select_box_id',
            'Display Events',
            array(__CLASS__, 'calendarSettingsSelectBoxView'),
            'gd_calendar'
        );

        add_meta_box(
            'gd_calendar_view_box_id',
            __('Calendar View', 'gd-calendar'),
            array(__CLASS__, 'calendarViewBoxView'),
            'gd_calendar'
        );

        add_meta_box(
            'gd_calendar_theme_box_id',
            __('Theme', 'gd-calendar'),
            array(__CLASS__, 'calendarThemeBoxView'),
            'gd_calendar'
        );

	    add_meta_box(
		    'gd_calendar_sidebar_box_id',
		    __('Publish With ShortCodes', 'gd-calendar'),
		    array(__CLASS__, 'calendarSidebarBoxView'),
		    'gd_calendar',
		    'side'
	    );
    }

    public static function calendarSidebarBoxView($post){
	    $post_id = intval($post->ID);
	    View::render('admin/meta-boxes/calendar-sidebar-box.php', array(
	    	'post_id' => $post_id
	    ));
    }

    public static function calendarSettingsSelectBoxView($post){
        wp_nonce_field('gd_calendar_save', 'gd_calendar_nonce');

        $menu_page = array(
            get_taxonomy('event_category'),
            get_taxonomy('event_tag'),
            get_post_type_object('gd_organizers'),
            get_post_type_object('gd_venues'),
        );

        $checkbox_values = array();
        $post_id = absint($post->ID);
        foreach ($menu_page as $page) {
            $checkbox_values[$page->name] = $page instanceof \WP_Post_Type ? get_posts(array('post_type' => $page->name, 'posts_per_page' => -1)) : get_terms(array('taxonomy' => $page->name, 'hide_empty' => false,));
        }

        $calendar = new Calendar($post_id);


        View::render('admin/meta-boxes/calendar-settings-select-box.php', array(
            'menu_page' => $menu_page,
            'checkbox_values' => $checkbox_values,
            'post_id' => $post_id,
            'calendar' => $calendar
        ) );
    }

    public static function calendarViewBoxView($post){
        $post_id = absint($post->ID);
        $view_categories = array(
            __('Day', 'gd-calendar'),
            __('Week', 'gd-calendar'),
            __('Month', 'gd-calendar'),
        );

        $calendar = new Calendar($post_id);

        View::render( 'admin/meta-boxes/calendar-view-box.php', array(
            'view_categories' => $view_categories,
            'post_id' => $post_id,
            'calendar' => $calendar
        ) );
    }

    public static function calendarThemeBoxView($post){
        $post_id = absint($post->ID);
        $calendar = new Calendar($post_id);

        View::render( 'admin/meta-boxes/calendar-theme-box.php', array(
            'post_id' => $post_id,
            'calendar' => $calendar
        ) );
    }

    public static function save($post_id)
    {
        $post_type = get_post($post_id)->post_type;
        global $pagenow;

        if ( defined('DOING_AJAX') || $post_type !== 'gd_calendar' || get_post_status($post_id) === 'trash' || (isset($_GET['action']) && $_GET['action'] === 'untrash') || $pagenow === 'post-new.php' ) {
            return;
        }

        if(!isset($_POST['gd_calendar_nonce']) || !wp_verify_nonce($_POST['gd_calendar_nonce'], 'gd_calendar_save')){
            wp_die('Are you sure you want to do this?');
        }

        $id = absint($post_id);

        $calendar = new Calendar($id);
        $calendar->set_select_events_by($_POST["select_events_by"]);
        $checked_id = array();
        if (isset($_POST["cat"])) {
            $checked_id = $_POST['cat'];
        }
        $calendar->set_cat($checked_id);
        $checked_view_type = array();
        if(isset($_POST['view'])){
            $checked_view_type = $_POST['view'];
        }
        $calendar->set_view_type($checked_view_type)
            ->set_theme($_POST['theme']);
        $calendar->save();
    }

}