<?php

namespace GDCalendar\Controllers\Widgets;

use GDCalendar\Models\PostTypes\Calendar;

class CalendarWidgetController extends \WP_Widget
{

    public function __construct() {
        parent::__construct(
            'CalendarWidgetController',
            __( 'GrandWP Calendar', 'gd-calendar' ),
            array( 'description' => __( 'GranWP Calendar', 'gd-calendar' ), )
        );
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance             = array();
        $instance['gd_calendar_id'] = strip_tags( $new_instance['gd_calendar_id'] );
        $instance['title']    = strip_tags( $new_instance['title'] );

        return $instance;
    }

    /**
     * @param array $instance
     */
	public function form( $instance ) {
		?>
        <p>
        <p>
            <label><?php _e( 'Title:' ); ?></label>
            <input class="widefat" type="text" disabled/>
        </p>
        <label><?php _e( 'Select calendar:', 'gd-calendar' ); ?></label>
        <select disabled><option>My First Calendar</option></select>&nbsp;&nbsp;<span style="color: red;">(Pro)</span>
        </p>
        <?php
    }

}