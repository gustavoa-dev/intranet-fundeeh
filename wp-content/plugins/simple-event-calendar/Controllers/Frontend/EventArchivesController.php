<?php

namespace GDCalendar\Controllers\Frontend;

use GDCalendar\Helpers\View;
use GDCalendar\Models\Taxonomy\EventCategory;
use GDCalendar\Models\Taxonomy\EventTag;

class EventArchivesController
{
    public function __construct()
    {
        add_filter('template_include', array($this, 'locateTemplate'));
    }

    public function locateTemplate($template){

        if (is_tax(EventTag::get_taxonomy())) {
            do_action('gd_calendar_frontend_css');
            $new_template = View::locate('frontend/event_tag/show.php');
            if (file_exists($new_template)) {
                return $new_template;
            }
        }

        if (is_tax(EventCategory::get_taxonomy())) {
            do_action('gd_calendar_frontend_css');
            $new_template = View::locate('frontend/event_category/show.php');
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        return $template;
    }
}