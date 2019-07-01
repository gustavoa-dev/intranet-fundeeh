<?php

namespace GDCalendar\Controllers\Frontend;

use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Venue;

class VenuesController
{
    public function __construct()
    {
        add_filter('the_content', array($this, 'maybeShow'));
    }

    public function maybeShow($content)
    {
        if (get_post_type() == Venue::get_post_type()) {
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
        $venue = new Venue($id);

        View::render('frontend/venue/show.php', array(
            'venue' => $venue
        ));

    }


}