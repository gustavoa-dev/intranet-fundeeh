<?php

namespace GDCalendar\Controllers\Frontend;

use GDCalendar\Helpers\Builders\MonthCalendarBuilder;
use GDCalendar\Models\PostTypes\Calendar;

class CalendarsController
{

    public function __construct(){
        add_filter( 'the_content', array( $this, 'maybeShow' ));
    }

    public function maybeShow($content){

        if(get_post_type() == Calendar::get_post_type()){
            ob_start();
            $this->show(get_the_ID());
            return ob_get_clean();
        }
        else{
            return $content;
        }
    }

    public function show($post_id){
    	$id = absint($post_id);
        do_action( 'gd_calendar_frontend_css' );
        do_action( 'gd_calendar_frontend_datepicker_css' );
        do_action('gd_calendar_show_script');

        if (isset($_GET['gd_calendar_month_event_filter'])){
            $selected_month = sanitize_text_field($_GET['gd_calendar_month_event_filter']);
            $month = absint(substr($selected_month,0,2));
            $year = absint(substr($selected_month,3,4));
        }
        else{
            $month = current_time('m');
            $year = current_time('Y');
        }
	    $builder = new MonthCalendarBuilder($month,$year,$id);
        $builder->show();
    }

    public static function sidebarShow($post_id){
        $id = absint($post_id);
        do_action('gd_calendar_show_script');

        $month = absint(current_time('m'));
        $year = absint(current_time('Y'));

        $builder = new MonthCalendarBuilder($month, $year, $id);

        $calendar = new Calendar($id);
	    $theme_id = $calendar->get_theme();

        ?>
	    <div id="gd_calendar_wrapper_widget_<?php echo $id; ?>" class="gd_calendar_wrapper gd_calendar_theme_<?php echo $theme_id;?>">
		    <div class="gd_calendar_sidebar" data-calendar-id="<?php echo $id; ?>">
		    <?php
	        $builder->getCalendarSidebar();
	        ?>
		    </div>
	    </div>
	    <?php
    }
}