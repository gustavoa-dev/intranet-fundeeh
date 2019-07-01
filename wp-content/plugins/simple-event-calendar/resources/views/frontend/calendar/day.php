<?php
/**
 * Calendar Day View
 * @var $day \GDCalendar\Helpers\Builders\CalendarBuilder
 */

$calendar_id = $day->getPostId();

if(isset($_POST['format']) && !empty($_POST['format'])){
	$date = sanitize_text_field($_POST['format']);
	$_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_POST['cookies']['day']) && !empty($_POST['cookies']['day'])){
    $date = $_POST['cookies']['day'];
	$_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif(isset($_POST['date']) && !empty($_POST['date'])){
    $date = sanitize_text_field($_POST['date']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_GET['datepicker_day']) && !empty($_GET['datepicker_day'])){
    $date = sanitize_text_field($_GET['datepicker_day']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_POST['more_events_date']) && !empty($_POST['more_events_date'])){
    $date = sanitize_text_field($_POST['more_events_date']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_POST['more_week_events_date']) && !empty($_POST['more_week_events_date'])){
    $date = sanitize_text_field($_POST['more_week_events_date']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
else{
    $_day = $day->getCurrentDate();
}

if(isset($_POST['search']) && !empty($_POST['search'])){
    $_GET['search'] = sanitize_text_field($_POST['search']);
}

$searched_events_id = '';
if(isset($_GET['search']) && !empty($_GET['search'])){
    $searched_events_id = array_map('absint', $day->getSearchedEvent());
    if($day->getSearchedEvent() == false){
        ?>
        <div class="gd_calendar_message">
            <?php esc_html_e('No results found', 'gd-calendar'); ?>
        </div>
        <?php
    }
}

$hour_events = \GDCalendar\Helpers\Builders\CalendarBuilder::getEventByHour($_day);
?>
    <div class="gd_calendar_day_title"><?php echo date('l F j, ', strtotime($_day)); ?><span class="gd_calendar_today"><?php echo date('Y', strtotime($_day)); ?></span></div>
    <div class="gd_calendar_day_box">
        <table class="gd_calendar_list">
        <?php
        $row_count = 0;

            foreach ($day->getHours() as $hour){
                if($hour === '12 PM'){
                    $hour = 'noon';
                }
        ?>
            <tr>
                <td class="gd_calendar_hour"><?php echo $hour; ?></td>
                <?php
                $field_count = 5;
                $count = 0;
                $current_hour_events = '';
                $searched_events = array();
                $searched_hour_events = array();

                if(isset($hour_events[strtolower($hour)])){
	                $hour_events_hour = $hour_events[strtolower($hour)];
                }

                if(array_key_exists(strtolower($hour), $hour_events)){
	                if(!empty($searched_events_id)){
		                $searched_events[strtolower($hour)] = array_intersect(array_keys($hour_events_hour), $searched_events_id);
		                foreach ($searched_events as $searched_key => $searched_event){
			                foreach ($searched_event as $id){
				                if(!empty($id)){
					                foreach ($hour_events_hour as $hour_key => $hour_value) {
						                if ( $id === $hour_key ) {
							                $searched_hour_events[$searched_key][$id] = $hour_value;
						                }
					                }
				                }
			                }
		                }
		                $current_hour_events = $searched_hour_events[strtolower($hour)];
	                }
                    elseif(!isset($_GET['search']) || empty($_GET['search'])){
		                $current_hour_events = $hour_events_hour;
	                }
                }

                $count = count($current_hour_events);
                $empty_count = $field_count - $count;
                if(empty($current_hour_events) || !array_key_exists(strtolower($hour), $hour_events)){
                    ?>
                    <td class="gd_calendar_first_column"></td>
                    <td></td><td></td><td></td>
                    <td class="gd_calendar_last_column"></td>
                    <?php
                }else{
                    $counter = 1;
                    $color = '';
                    foreach ($current_hour_events as $event_id => $event_dates){
                        $get_event = new \GDCalendar\Models\PostTypes\Event($event_id);
	                    $start_time = sanitize_text_field(substr($get_event->get_start_date(), 11, 8));
	                    $end_time = sanitize_text_field(substr($get_event->get_end_date(), 11, 8));

	                    $start_date = date( (strlen($event_dates[0])>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( $event_dates[0] ) );
	                    $end_date = date( (strlen($event_dates[0])>10) ? "m/d/Y h:i a" : "m/d/Y", strtotime( end($event_dates) ) );
	                    if($end_time != ''){
		                    $end_date = substr($end_date, 0, -8) . $end_time;
	                    }
                        $all_day = "";
                        if($start_time == "" || $end_time == ""){
                            $all_day = __('All day','gd-calendar');
                        }

                        if($count === 1){
                            if ( $row_count % 3 == 0) {
                                $color = 'background_first';
                            }
                            elseif ( $row_count % 3 == 1){
                                $color = 'background_second';
                            }
                            elseif ( $row_count % 3 == 2){
                                $color = 'background_third';
                            }
                            ?>
                            <td colspan="5" class="gd_calendar_hour_event <?php echo $color; ?>" >
                                <p><?php echo esc_html($start_time) . $all_day ?></p>
                                <a class="gd_calendar_one_day_hover_link gd_calendar_event_link" href="<?php echo get_permalink($event_id) . ((strpos(get_permalink($event_id), '?')) ? '&' : '?') . "calendar=" . $calendar_id; ?>"><?php echo get_the_title($event_id); ?></a>
                            </td>
                            <td class="gd_calendar_day_hover_box_wrapper">
                                <div class="gd_calendar_day_hover_box">
                                    <h3><?php echo get_the_title($event_id); ?></h3><p><?php echo $all_day; ?></p>
                                    <p><?php _e('Starts','gd-calendar'); echo ' ' . $start_date; ?></p>
                                    <p><?php _e('Ends','gd-calendar'); echo ' ' . $end_date; ?></p>
                                </div>
                            </td>
                            <?php
                            $row_count++;
                        }else{
                            if($counter <= 5){
                                if ( $counter % 3 == 1) {
                                    $color = 'background_first';
                                }
                                elseif ( $counter % 3 == 2){
                                    $color = 'background_second';
                                }
                                elseif ( $counter % 3 == 0){
                                    $color = 'background_third';
                                }
                                ?>
                                <td class="gd_calendar_hour_event <?php echo $color; ?>" >
                                    <p><?php echo esc_html($start_time); ?></p>
                                    <a class="gd_calendar_more_day_hover_link gd_calendar_event_link" href="<?php echo get_permalink($event_id) . "?calendar=" . $calendar_id; ?>"><?php echo get_the_title($event_id); ?></a>
                                    <input class="start_event_hover" type="hidden" value="<?php _e('Starts','gd-calendar'); echo ' ' . $get_event->get_start_date(); ?>">
                                    <input class="end_event_hover" type="hidden" value="<?php _e('Ends','gd-calendar'); echo ' ' . $get_event->get_end_date(); ?>">
                                </td>
                                <?php
                                $counter++;
                            }
                        }
                    }
                    if( $count > 1 ){
                        for( $i=0; $i < $empty_count; $i++ ){
                            ?><td></td><?php
                        }
                        ?>
                        <td class="gd_calendar_day_hover_box_wrapper">
                            <div class="gd_calendar_day_hover_more_box"></div>
                        </td>
                        <?php
                    }
                }
            } ?>
            </tr>
        </table>
    </div>