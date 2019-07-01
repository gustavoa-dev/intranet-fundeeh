<?php
/**
 * @var $date
 * @var $searched_event
 * @var $sidebar
 * @var $calendar_id
 * @var $builder \GDCalendar\Helpers\Builders\MonthCalendarBuilder
 */

?>
<div class="<?php echo (isset($sidebar)) ? 'gd_calendar_day_event_small' : 'gd_calendar_day_event'; ?>">
    <?php
    /**
     * Month search logic
     */

    if(isset($_POST['search']) && !empty($_POST['search'])){
        $_GET['search'] = sanitize_text_field($_POST['search']);
    }
    $calendar = new \GDCalendar\Models\PostTypes\Calendar($calendar_id);
    $views = $calendar->get_view_type();

    $show_day = false;
    if(!empty($views) && $views[0] === 0){
        $show_day = true;
    }

    $counter = 0;
    if(isset($_GET['search']) && isset($searched_event)){
        foreach ($searched_event as $event){
            $event_id = absint($event);
            $get_searched_event = new \GDCalendar\Models\PostTypes\Event($event_id);

	        if( $get_searched_event->get_repeat() === 'repeat' && $get_searched_event->get_repeat_type() !== "choose_type") {
		        $repeat_type = absint($get_searched_event->get_repeat_type());
		        $eventAllDay = $get_searched_event->get_all_day();
		        $eventStartDay = new DateTime($get_searched_event->get_start_date());
		        $eventEndDate = new DateTime($get_searched_event->get_end_date());
		        $eventInterval = intval($eventStartDay->diff($eventEndDate)->format('%a'));

		        $repeatTypeValue = \GDCalendar\Models\PostTypes\Event::$repeat_types[ $repeat_type ];

		        $maxDate = new DateTime($builder->getLastDay());
		        $minDate = new DateTime($builder->getFirstDay());

		        switch($repeat_type){
			        case 1:
				        $repeatValue = $get_searched_event->get_repeat_day();
				        break;
			        case 2:
				        $repeatValue = $get_searched_event->get_repeat_week();
				        break;
			        case 3:
				        $repeatValue = $get_searched_event->get_repeat_month();
				        break;
			        case 4:
				        $repeatValue = $get_searched_event->get_repeat_year();
				        break;
			        default:
				        $repeatValue = $get_searched_event->get_repeat_day();
		        }

		        if(is_null($repeatValue) || $repeatValue === 0){
			        $repeatValue = 1;
		        }

		        $event_dates = \GDCalendar\Helpers\Builders\CalendarBuilder::getRepeatedEventsDateRange($eventStartDay, $eventEndDate, $eventInterval, $repeatValue, $maxDate, $minDate, $repeatTypeValue, $eventAllDay );
	        }
	        else{
		        $event_dates = $get_searched_event->get_date_range();
            }
	        if(!empty($event_dates)):
		        foreach ($event_dates as $value){
                    foreach ($value as $event_date):
                        $start_date = date( (strlen($event_date)>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( $value[0] ) );
                        $end_date = date( (strlen($event_date)>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( end($value) ) );
	                    $start_time = sanitize_text_field(substr($get_searched_event->get_start_date(), 11, 8));
	                    $end_time = sanitize_text_field(substr($get_searched_event->get_end_date(), 11, 8));
	                    if($end_time != ''){
		                    $end_date = substr($end_date, 0, -8) . $end_time;
	                    }
                        $all_day = "";
                        if($start_time == "" || $end_time == ""){
                            $all_day = __('All day','gd-calendar');
                        }

	                    if($date === substr($event_date, 0, 10)){
//		                    if ($counter < 3) {
			                    $circle = '';
			                    $event_background = '';
			                    if($counter % 3 === 0){
				                    $circle = 'circle_first';
				                    $event_background = 'background_first';
			                    }
                                elseif($counter % 3 === 1){
				                    $circle = 'circle_second';
				                    $event_background = 'background_second';
			                    }
                                elseif($counter % 3 === 2){
				                    $circle = 'circle_third';
				                    $event_background = 'background_third';
			                    }

			                    if(isset($sidebar)){
				                    if($counter < 3) { ?><span class="<?php echo $circle; ?>"></span><?php }
			                    } else {

				                    if ($show_day === true && $counter < 3):

					                    \GDCalendar\Helpers\View::render('frontend/calendar/one-event.php', array(
						                    'event_id' => $event_id,
						                    'start_time' => $start_time,
						                    'all_day' => $all_day,
						                    'start_date' => $start_date,
						                    'end_date' => $end_date,
						                    'circle' => $circle,
						                    'event_background' => $event_background,
						                    'calendar_id' => $calendar_id
					                    ));

                                    elseif ($show_day === false):
					                    if($counter === 3){echo '<div class="gd_calendar_more_events" style="display: none">';}
					                    \GDCalendar\Helpers\View::render('frontend/calendar/one-event.php', array(
						                    'event_id' => $event_id,
						                    'start_time' => $start_time,
						                    'all_day' => $all_day,
						                    'start_date' => $start_date,
						                    'end_date' => $end_date,
						                    'circle' => $circle,
						                    'event_background' => $event_background,
						                    'calendar_id' => $calendar_id
					                    ));
				                    endif;
			                    }
//		                    }
		                    $counter++;
	                    }

                    endforeach;
		        }
            endif;
        }
        if(!isset($sidebar) && $counter > 3){

            if($show_day === false){ echo '</div>'; }

            \GDCalendar\Helpers\View::render('frontend/calendar/more-event.php', array(
                'counter' => $counter,
            ));

        }
    }
    else{

        $post_type = $calendar->get_select_events_by();
        $selected_categories = $calendar->get_cat();

        $tax_param = '';
        if(!empty($selected_categories) && taxonomy_exists($post_type)){
            $tax_param = array(
                'taxonomy' => $post_type,
                'terms' => $selected_categories,
                'include_children' => false,
            );
        }

        $events = \GDCalendar\Models\PostTypes\Event::get(array(
                'post_status' => 'publish',
                'tax_query' => array(
                    $tax_param,
                ))
        );

        if($events && !empty($events)) {
            /* Sorting events by start datetime */
            $sort_events = array();
            foreach ($events as $key => $value){
                $sort_events[] = strtotime(substr($value->get_start_date(), 11, 8));
            }
            array_multisort($sort_events, SORT_ASC, $events);

            foreach ($events as $event) {
                $event_id = absint($event->get_id());
                if (!empty($selected_categories)) {
                    if ($post_type === 'gd_organizers') {
                        $organizers = $event->get_event_organizer();
                        $org_result = array_intersect($organizers, $selected_categories);
                        $result = (!empty($org_result)) ? true : false;
                    } elseif ($post_type === 'gd_venues') {
                        $venue = $event->get_event_venue();
                        $result = in_array($venue, $selected_categories);
                    } else {
                        $result = true;
                    }
                } else {
                    $result = true;
                }
                if (true === $result) {
	                if( $event->get_repeat() === 'repeat' && $event->get_repeat_type() !== 'choose_type') {

			            $repeat_type = absint($event->get_repeat_type());
		                $eventAllDay = $event->get_all_day();
		                $eventStartDay = new DateTime($event->get_start_date());
                        $eventEndDate = new DateTime($event->get_end_date());
                        $eventInterval = intval($eventStartDay->diff($eventEndDate)->format('%a'));
                        $repeatTypeValue = \GDCalendar\Models\PostTypes\Event::$repeat_types[ $repeat_type ];

		                $maxDate = new DateTime($builder->getLastDay());
                        $minDate = new DateTime($builder->getFirstDay());

                        switch($repeat_type){
                            case 1:
                                $repeatValue = $event->get_repeat_day();
                                break;
                            case 2:
                                $repeatValue = $event->get_repeat_week();
                                break;
                            case 3:
                                $repeatValue = $event->get_repeat_month();
                                break;
                            case 4:
                                $repeatValue = $event->get_repeat_year();
                                break;
                            default:
                                $repeatValue = $event->get_repeat_day();
                        }
		                if(is_null($repeatValue) || $repeatValue === 0){
                            $repeatValue = 1;
                        }

		                $event_dates = \GDCalendar\Helpers\Builders\CalendarBuilder::getRepeatedEventsDateRange($eventStartDay, $eventEndDate, $eventInterval, $repeatValue, $maxDate, $minDate, $repeatTypeValue, $eventAllDay );
	                }else{
		                $event_dates = $event->get_date_range();
                    }



                    if(!empty($event_dates)):
                        foreach ($event_dates as $value):
                                foreach ($value as $event_date):
	                                $start_date = date( (strlen($event_date)>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( $value[0] ) );
	                                $end_date = date( (strlen($event_date)>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( end($value) ) );
	                                $start_time = sanitize_text_field(substr($event->get_start_date(), 11, 8));
	                                $end_time = sanitize_text_field(substr($event->get_end_date(), 11, 8));
	                                if($end_time != ''){
		                                $end_date = substr($end_date, 0, -8) . $end_time;
                                    }
	                                $all_day = "";
	                                if ($start_time == "" || $end_time == "") {
		                                $all_day = __('All day', 'gd-calendar');
	                                }

	                                if ($date === substr($event_date, 0, 10)) {

//		                                if ($counter < 3) {
			                                $circle = '';
			                                $event_background = '';
			                                if ($counter % 3 === 0) {
				                                $circle = 'circle_first';
				                                $event_background = 'background_first';
			                                } elseif ($counter % 3 === 1) {
				                                $circle = 'circle_second';
				                                $event_background = 'background_second';
			                                } elseif ($counter % 3 === 2) {
				                                $circle = 'circle_third';
				                                $event_background = 'background_third';
			                                }

			                                if (isset($sidebar)) {
				                                if($counter < 3) { ?><span class="<?php echo $circle; ?>"></span><?php }
			                                } else {

				                                if ($show_day === true && $counter < 3):

                                                    \GDCalendar\Helpers\View::render('frontend/calendar/one-event.php', array(
                                                        'event_id' => $event_id,
                                                        'start_time' => $start_time,
                                                        'all_day' => $all_day,
                                                        'start_date' => $start_date,
                                                        'end_date' => $end_date,
                                                        'circle' => $circle,
                                                        'event_background' => $event_background,
                                                        'calendar_id' => $calendar_id
                                                    ));

                                                elseif ($show_day === false):
                                                    if($counter === 3){echo '<div class="gd_calendar_more_events" style="display: none">';}
                                                        \GDCalendar\Helpers\View::render('frontend/calendar/one-event.php', array(
                                                            'event_id' => $event_id,
                                                            'start_time' => $start_time,
                                                            'all_day' => $all_day,
                                                            'start_date' => $start_date,
                                                            'end_date' => $end_date,
                                                            'circle' => $circle,
                                                            'event_background' => $event_background,
                                                            'calendar_id' => $calendar_id
                                                        ));
				                                endif;
			                                }
//		                                }
		                                $counter++;
	                                }

                                endforeach;
                        endforeach;
                    endif;
                }
            }
        }
        if(!isset($sidebar) && $counter > 3) {

           if($show_day === false){ ?></div><?php }

            \GDCalendar\Helpers\View::render('frontend/calendar/more-event.php', array(
                'counter' => $counter,
            ));

        }
    }
    ?>
</div>
