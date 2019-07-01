<?php

namespace GDCalendar\Controllers\Frontend;


use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Organizer;

class OrganizersController
{

    public function __construct()
    {
        add_filter('the_content', array($this, 'maybeShow'));
    }

    public function maybeShow($content)
    {
        if (get_post_type() == Organizer::get_post_type()) {
            ob_start();
            $this->show();
            return ob_get_clean();
        } else {
            return $content;
        }
    }

    public function show()
    {
        do_action('gd_calendar_frontend_css');
        $id = absint(get_post()->ID);
        $organizer = new Organizer($id);

        View::render('frontend/organizer/show.php', array(
            'organizer' => $organizer
        ));
    }

}