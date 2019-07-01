<?php
/**
 * @var $event_id
 * @var $start_time
 * @var $all_day
 * @var $start_date
 * @var $end_date
 * @var $circle
 * @var $event_background
 * @var $calendar_id
 */
$title = get_the_title($event_id);
if(strlen($title) > 20){
    $title = substr($title, 0, 20) . '..';
}

?>
<div class="gd_calendar_day_event_<?php echo $event_id . ' ' . $event_background; ?>">
    <span class="gd_calendar_circle <?php echo $circle; ?>"></span>
    <span class="gd_calendar_month_hover_box">
        <a class="gd_calendar_month_hover_link gd_calendar_event_link" href="<?php echo get_permalink($event_id) . ((strpos(get_permalink($event_id), '?')) ? '&' : '?') . "calendar=" . $calendar_id ; ?>"><?php echo $title; ?></a>
    </span>
    <span class="gd_calendar_start_time"><?php echo $start_time . $all_day; ?></span>
    <div class="gd_calendar_hover_box">
        <h3><?php echo get_the_title($event_id); ?></h3><span class="gd_calendar_hover_all"><?php echo $all_day; ?></span>
        <p><span class="gd_calendar_hover_date"><?php _e('Starts','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $start_date; ?></span></p>
        <p><span class="gd_calendar_hover_date"><?php _e('Ends','gd-calendar'); ?></span><span class="gd_calendar_hover_time">&nbsp;<?php echo $end_date; ?></span></p>
    </div>
</div>