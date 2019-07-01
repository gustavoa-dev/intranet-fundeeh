<?php

namespace GDCalendar\Controllers\Frontend;


use GDCalendar\Helpers\Currencies;
use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Event;
use GDCalendar\Models\PostTypes\Venue;

class EventsController
{

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_filter('the_content', array($this, 'maybeShow'));
    }

    public function maybeShow($content)
    {
        if (get_post_type() == Event::get_post_type()) {
            ob_start();
            $this->show();
            return ob_get_clean();
        }else{
            return $content;
        }
    }

    public function show()
    {
        do_action('gd_calendar_frontend_css');

	    $id = absint(get_post()->ID);
        $event = new Event($id);
        $event_venue = $event->get_event_venue();
        $venue = new Venue($event_venue);

        $symbol = '';
        if (!is_null($event->get_cost()) && $event->get_currency() !== 'Select Currency' ) {
            $symbol = Currencies::getCurrencySymbol($event->get_currency());
        } else {
            $event->set_cost(__('FREE', 'gd-calendar'));
        }

        $event_organizers = $event->get_event_organizer();

        View::render('frontend/event/show.php', array(
            'event' => $event,
            'symbol' => $symbol,
            'venue' => $venue,
            'event_organizers' => $event_organizers
        ));
    }

}