<?php
/**
 * @var Event $event
 * @var Event $symbol
 * @var Venue $venue
 * @var $event_organizers
 */
?>

<div class="event_view gd_calendar_body">

    <?php
        $calendar_id = '';
        if(isset($_GET['calendar'])) {
	        $calendar_id = absint( $_GET['calendar'] );

	        if(isset($_COOKIE['calendar_' . $calendar_id])){
                $cookies = json_decode(stripslashes($_COOKIE['calendar_' . $calendar_id]), true);

		        if(!isset($cookies['last_type']) || !isset($cookies['last_format']) || !isset($cookies['main_url'])){
			        return;
		        }

                $last_type = $cookies['last_type'];
                $last_date = $cookies['last_format'];
                $main_url = $cookies['main_url'];
                $full_url = $main_url . '#calendar_' . $calendar_id . '=' . $last_type . '&date=' . $last_date;
            }
	        ?>
            <div>
                <a class="gd_calendar_back_link"
                   href="<?php echo $full_url; ?>">&laquo; <?php _e( 'Back to Calendar', 'gd-calendar' ); ?></a>
            </div>
	        <?php
        }
        if(has_post_thumbnail()){ ?>
            <div class="event_thumbnail">
                <?php echo the_post_thumbnail( 'large' );  ?>
            </div>
        <?php }
    ?>
    <table id="event_gen_option" class="event_gen_option">
        <tr class="event_front_field">
            <td><?php _e('Start Date', 'gd-calendar'); ?>:</td>
            <td><?php echo date("jS F, Y", strtotime(esc_html($event->get_start_date()))); ?></td>
        </tr>
        <tr class="event_front_field">
            <td><?php _e('End Date', 'gd-calendar'); ?>:</td>
            <td>
                <?php echo date("jS F, Y", strtotime(esc_html($event->get_end_date()))); ?>
            </td>
        </tr>
        <tr class="event_front_field">
            <td><?php _e('Time', 'gd-calendar'); ?>:</td>
            <td><?php

                $timezone = $event->get_timezone();

                if(substr($timezone, -2) === '.5'){
	                $last_part = substr($timezone, -2);
	                $timezone = str_replace($last_part, ':30', $timezone);
                }

                if ($event->get_all_day() === 'all_day' ) { ?>
                    <span class="all_day">
                    <?php
                        _e('All Day', 'gd-calendar');
                    ?>
                    </span>
                    <?php echo ' (' . $timezone . ')'; ?>
                <?php } else {
                    echo date("h:i a", strtotime(esc_html($event->get_start_date()))) . " " . __('to', 'gd-calendar') . " " .
                         date("h:i a", strtotime(esc_html($event->get_end_date()))) . ' (' . $timezone . ')';
                } ?>
            </td>
        </tr>
        <?php if ( $event->get_repeat() === 'repeat' && $event->get_repeat_type() !== "choose_type" ) { ?>
            <tr class="event_front_field">
                <td><?php _e('Repeat', 'gd-calendar'); ?>:</td>
                <td><?php
                    _e('Event repeated every ', 'gd-calendar');
                    switch ($event->get_repeat_type()) {
                        case 1:
                            echo (absint($event->get_repeat_day()) === 1 ) ? __('day', 'gd-calendar') : $event->get_repeat_day() . ' ' . __('days', 'gd-calendar');
                            break;
                        case 2:
                            echo (absint($event->get_repeat_week()) === 1) ? __('week', 'gd-calendar') : $event->get_repeat_week()  . ' ' . __('weeks', 'gd-calendar');
                            break;
                        case 3:
                            echo (absint($event->get_repeat_month()) === 1) ? __('month', 'gd-calendar') : $event->get_repeat_month() . ' ' . __('months', 'gd-calendar');
                            break;
                        case 4:
                            echo (absint($event->get_repeat_year()) === 1) ? __('year', 'gd-calendar') : $event->get_repeat_year() . ' ' . __('years', 'gd-calendar');
                            break;
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
        <tr class="event_front_field">
            <td><?php _e('Cost', 'gd-calendar'); ?>:</td>
            <td>
                <?php
                if ( $event->get_currency_position() === 'before' ) {
                    if( isset( $symbol ) ) { echo $symbol; }
                    echo $event->get_cost();
                }
                elseif( $event->get_currency_position() === 'after' ) {
                    echo $event->get_cost();
                    if( isset( $symbol ) ) { echo $symbol; }
                }
                ?>
            </td>
        </tr>
	    <?php if(!empty($venue->get_address())):?>
        <tr class="event_front_field">
            <td><?php _e('Location', 'gd-calendar'); ?>:</td>
            <td class="venue_location_name"><?php if($venue->get_address() == true){ echo esc_html($venue->get_address());} ?></td>
        </tr>
        <?php endif; ?>
        <input id="address_view" type="hidden" value="<?php echo esc_html($venue->get_address()); ?>">
    </table>
    <?php if($event->get_post_data()->post_content){ ?>
        <div class="event_description">
            <h3><?php _e('Description', 'gd-calendar'); ?></h3>
            <hr>
            <p><?php echo wp_kses_post($event->get_post_data()->post_content); ?></p>
        </div>
    <?php }
    ?>

	<?php if(!empty($event_organizers)): ?>
    <div class="event_organizers">
        <h3><?php _e('Organizers', 'gd-calendar'); ?></h3>
        <hr>
        <table id="event_organizer_details" class="event_organizer_details">
            <tr class="event_front_field">
                <td><?php _e('Organized by', 'gd-calendar'); ?>:</td>
                <td>
                    <?php
                    if(!empty($event_organizers)){
                        foreach ($event_organizers as $organizer_key => $organizer){
                            $id = absint($organizer);
                            $org = new \GDCalendar\Models\PostTypes\Organizer($id);
                            $organizer_title = $org->get_post_data()->post_title;
                            echo '<a href="' . get_the_permalink($id) . '">'. esc_html($organizer_title) . '</a>';
                            if(count($event_organizers) - 1 != $organizer_key) {echo ', ';}
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
    <div class="posts_link">
		<?php
		(strpos(get_permalink(get_adjacent_post(false,'',true)), '?')) ? $chr = '&' : $chr = '?';
		(isset($_GET['calendar'])) ? $param = $chr . 'calendar=' . $_GET['calendar'] : $param = "";

		$adjacentPost = get_adjacent_post(false,'',true);
		if(!empty($adjacentPost )){
			$prev = get_permalink(get_adjacent_post(false,'',true)) . $param;
			$prev_title = get_adjacent_post(false,'',true)->post_title; ?>
            <span class="prev_event_button"><a href="<?php echo $prev; ?>"><i class="fa fa-arrow-left"></i> <?php echo $prev_title; ?></a></span>
			<?php
		}
		if(!empty(get_adjacent_post(false,'',false))){
			$next = get_permalink(get_adjacent_post(false,'',false)) . $param;
			$next_title = get_adjacent_post(false,'',false)->post_title; ?>
            <span class="next_event_button"><a href="<?php echo $next; ?>"><?php echo $next_title; ?> <i class="fa fa-arrow-right"></i></a></span>
			<?php
		}
		?>
    </div>
</div>
