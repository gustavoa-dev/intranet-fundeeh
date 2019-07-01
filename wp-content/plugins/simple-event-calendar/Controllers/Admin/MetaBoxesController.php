<?php

namespace GDCalendar\Controllers\Admin;

use GDCalendar\Controllers\Admin\MetaBoxes\EventMetaBoxesController;
use GDCalendar\Controllers\Admin\MetaBoxes\VenueMetaBoxesController;
use GDCalendar\Controllers\Admin\MetaBoxes\OrganizerMetaBoxesController;
use GDCalendar\Controllers\Admin\MetaBoxes\CalendarMetaBoxesController;

class MetaBoxesController
{
    public function __construct()
    {

        new CalendarMetaBoxesController();
        new EventMetaBoxesController();
        new VenueMetaBoxesController();
        new OrganizerMetaBoxesController();
    }
}