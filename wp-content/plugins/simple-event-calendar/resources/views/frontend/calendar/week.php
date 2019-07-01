<?php
/**
 * Calendar week view
 * @var $week \GDCalendar\Helpers\Builders\CalendarBuilder
 */

$calendar_id = $week->getPostId();
$calendar = new \GDCalendar\Models\PostTypes\Calendar($calendar_id);
$views = $calendar->get_view_type();

$show_day = false;
if(!empty($views) && $views[0] === 0){
	$show_day = true;
}

if(isset($_POST['format']) && !empty($_POST['format'])){
	$date = sanitize_text_field($_POST['format']);
	$_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_POST['cookies']['week']) && !empty($_POST['cookies']['week'])){
	$date = $_POST['cookies']['week'];
	$_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif(isset($_POST['date']) && !empty($_POST['date'])){
    $date = sanitize_text_field($_POST['date']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
elseif (isset($_GET['datepicker_week']) && !empty($_GET['datepicker_week'])){
    $date = sanitize_text_field($_GET['datepicker_week']);
    $_day = sanitize_text_field(date("Y-m-d", strtotime($date)));
}
else{
    $_day = $week->getCurrentDate();
}

if(isset($_POST['search']) && !empty($_POST['search'])){
    $_GET['search'] = sanitize_text_field($_POST['search']);
}

$searched_events_id = '';
if(isset($_GET['search']) && !empty($_GET['search'])){
    if($week->getSearchedEvent() == false){
        ?>
        <div class="gd_calendar_message">
            <?php esc_html_e('No results found', 'gd-calendar'); ?>
        </div>
        <?php
    }
	$searched_events_id = array_map('absint', $week->getSearchedEvent());
}

$week_events = \GDCalendar\Helpers\Builders\CalendarBuilder::getEventByWeek($_day, $week);
$week_number = absint($week->getCurrentWeekdayNumber($_day));
echo '<h4 class="gd_calendar_week_number">'. __('CW', 'gd-calendar') . $week_number .'</h4>';

?>
<table class='gd_calendar_week_table'>
    <tr><?php
        $currentWeek = absint($week->getWeekday());
        echo '<th></th>';
        foreach($week->getDaysOfWeek() as $key => $day_name) {
            $weekday_color = "";
            if($key == 0 || $key == 6){
                $weekday_color = "gd_calendar_weekday";
            }

	        $prepared_week_number = str_pad($week_number, 2, "0", STR_PAD_LEFT);
            $day_number = date('d', strtotime($week->getYear()."W". $prepared_week_number . $key));

            $currentDayWeekFont = "";
            if ( absint($week->getCurrentWeekdayNumber()) === $week_number){
                if($key === $currentWeek) {
                    $currentDayWeekFont = "current_day_week";
                }
            }
            ?>
            <th class='gd_calendar_header_week <?php echo $weekday_color; ?>'>
                <p class="gd_calendar_day_name"><?php echo sanitize_text_field($day_name); ?></p>
                <p class="gd_calendar_day_number <?php echo $currentDayWeekFont ?>"> <?php echo absint($day_number); ?> </p>
            </th>
        <?php
        }
        ?>
    </tr>
    <?php
    foreach ($week->getHours() as $hour):
        if($hour === '12 PM'){
            $hour = 'noon';
        }
        ?>
        <tr>
            <td class="gd_calendar_hour"><?php echo $hour; ?></td>
            <?php
            $searched_week_events = array();
            foreach($week->getDaysOfWeek() as $key => $day_name) {
                $weekday_color = "";
	            $current_day_events = "";
                if($key == 0 || $key == 6){
                    $weekday_color = "gd_calendar_first_column";
                }

                $week_number = absint($week->getCurrentWeekdayNumber($_day));
                $week_date = date('Y-m-d', strtotime($week->getYear()."W". $week_number . $key));
                if(array_key_exists($key, $week_events)){
                    if(!empty($searched_events_id)){
                        foreach ($week_events[$key] as $kk => $week_event ){
                            foreach ($searched_events_id as $searched_event_id){
	                            if(array_key_exists($searched_event_id, $week_event)){
		                            if(!in_array($searched_event_id, $searched_week_events[$key][$kk])){
		                                foreach ($week_event as $event_key => $event_value){
		                                    if($event_key == $searched_event_id ){
			                                    $searched_week_events[$key][$kk][$searched_event_id] = $event_value;
                                            }
                                        }
		                            }
	                            }
                            }
                        }
                        $current_day_events = $searched_week_events[$key];
                    }
                    elseif(!isset($_GET['search']) || empty($_GET['search']) ){
                        $current_day_events = $week_events[$key];
                    }
                }
                if(empty($current_day_events) ){
                    ?>
                    <td class="gd_calendar_week_cell <?php echo $weekday_color; ?>" rel='<?php echo $week_date; ?>'></td>
                    <?php
                }
                else{
                    ?>
                    <td class="gd_calendar_week_cell <?php echo $weekday_color; ?>" rel='<?php echo $week_date; ?>'>
                        <?php
                        $counter = 0;
                        $color = '';
                        foreach ($current_day_events as $day_key => $day){

                            if ( $counter % 3 == 0) {
                                $color = 'background_first';
                            }
                            elseif ( $counter % 3 == 1){
                                $color = 'background_second';
                            }
                            elseif ( $counter % 3 == 2){
                                $color = 'background_third';
                            }

                            if(strtolower($hour) === $day_key){
                                $current_hour_events = $current_day_events[strtolower($hour)];
                                $count = 0;

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

//                                    if($count < 3){
                                        if($count % 3 === 0){
                                            $color = 'background_first';
                                        }
                                        elseif($count % 3 === 1){
                                            $color = 'background_second';
                                        }
                                        elseif($count % 3 === 2){
                                            $color = 'background_third';
                                        }

		                            if ($show_day === true && $count < 3):
			                            ?>
                                        <div class="gd_calendar_week_box <?php echo $color; ?>">
                                            <p class="gd_calendar_week_time"><?php echo esc_html($start_time) . $all_day; ?></p>
                                            <a class="gd_calendar_week_hover_link gd_calendar_event_link" href="<?php echo get_permalink($event_id) . ((strpos(get_permalink($event_id), '?')) ? '&' : '?') . "calendar=" . $calendar_id; ?>"><?php echo get_the_title($event_id); ?></a>
                                            <div class="gd_calendar_hover_box">
                                                <h3><?php echo get_the_title($event_id); ?></h3><span class="gd_calendar_hover_all"><?php echo $all_day; ?></span>
                                                <p><span class="gd_calendar_hover_date"><?php _e('Starts','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $start_date; ?></span></p>
                                                <p><span class="gd_calendar_hover_date"><?php _e('Ends','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $end_date; ?></span></p>
                                            </div>
                                        </div>
			                            <?php
                                    elseif ($show_day === false):
	                                    if ($count === 3){echo '<div class="gd_calendar_week_more_boxes" style="display: none">';}
	                                    ?>
                                        <div class="gd_calendar_week_box <?php echo $color; ?>">
                                            <p class="gd_calendar_week_time"><?php echo esc_html($start_time) . $all_day; ?></p>
                                            <a class="gd_calendar_week_hover_link gd_calendar_event_link" href="<?php echo get_permalink($event_id); ?>"><?php echo get_the_title($event_id); ?></a>
                                            <div class="gd_calendar_hover_box">
                                                <h3><?php echo get_the_title($event_id); ?></h3><span class="gd_calendar_hover_all"><?php echo $all_day; ?></span>
                                                <p><span class="gd_calendar_hover_date"><?php _e('Starts','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $start_date; ?></span></p>
                                                <p><span class="gd_calendar_hover_date"><?php _e('Ends','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $end_date; ?></span></p>
                                            </div>
                                        </div>
	                                    <?php
		                            endif;

//                                    }
                                $count++;
                                }
                                if($count > 3){

	                                if($show_day === false){ echo '</div>'; }
                                    ?>
                                    <div class="gd_calendar_see_all">
                                        <a class="gd_calendar_week_more_events" href="#">
                                            <?php _e('View all','gd-calendar'); ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                            $counter++;
                        }
                        ?>
                    </td>
                    <?php
                }
            }
            ?>
        </tr>
        <?php
    endforeach;
    ?>
</table>
