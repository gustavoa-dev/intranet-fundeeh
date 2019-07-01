<?php
namespace GDCalendar\Controllers\Frontend;

class FrontendController
{

    public function __construct()
    {
        new FrontendAssetsController();
        new EventArchivesController();
        new EventsController();
        new CalendarsController();
        new OrganizersController();
        new VenuesController();
        new ShortcodeController();
    }
    
}