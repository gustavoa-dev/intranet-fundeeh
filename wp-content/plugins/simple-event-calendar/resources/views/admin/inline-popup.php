<?php
$calendars = \GDCalendar\Models\PostTypes\Calendar::get();
?>
<div id="gd_calendar" style="display:none;">
<?php
    if( $calendars && !empty($calendars) ){
?>
    <form method="post" action="">
        <h3><?php _e('Select the calendar to embed', 'gd-calendar'); ?></h3>
        <select id="gd_calendar_select" style="width: 100%">
        <?php
            foreach ( $calendars as $calendar ) {
                ?>
                <option value="<?php echo $calendar->get_id(); ?>"><?php echo $calendar->get_post_data()->post_title; ?></option>
                <?php
            }
        ?>
        </select>
        <button class='button primary' style="position: absolute;bottom: 25px;left: 30px" id='gd_calendar_insert'><?php _e( 'Insert Calendar', 'gd-calendar' ); ?></button>
        <a class='button primary' style="position: absolute;bottom: 25px;left: 155px" id='gd_calendar_cancel'><?php _e( 'Cancel', 'gd-calendar' ); ?></a>
    </form>
    <?php
    }else{
        printf(
            '<p>%s<a class="button" href="%s">%s</a></p>',
            __('You have not created any calendars', 'gd-calendar'),
            admin_url('post-new.php?post_type=gd_calendar'),
            __( 'Create new Calendar', 'gd-calendar' )
        );
    }
    ?>
</div>
