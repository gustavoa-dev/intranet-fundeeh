<?php
/**
 * @var $organizer
 */
?>
<div class="organizer_view gd_calendar_body">
    <?php if(has_post_thumbnail()){ ?>
        <div class="event_thumbnail">
            <?php echo the_post_thumbnail(); ?>
        </div>
    <?php } ?>
    <table id="organizer_view_front" class="organizer_view_front">
        <tr class="organizer_front_field">
            <td><?php _e('Name', 'gd-calendar'); ?>:</td>
            <td><?php echo esc_html($organizer->get_organized_by()); ?></td>
        </tr>
        <tr class="organizer_front_field">
            <td><?php _e('Organizer\'s Address', 'gd-calendar'); ?>:</td>
            <td><?php echo esc_html($organizer->get_organizer_address()); ?></td>
        </tr>
        <tr class="organizer_front_field">
            <td><?php _e('Phone', 'gd-calendar'); ?>:</td>
            <td><?php echo esc_html($organizer->get_phone()); ?></td>
        </tr>
        <tr class="organizer_front_field">
            <td><?php _e('Email', 'gd-calendar'); ?>:</td>
            <td><?php echo esc_html($organizer->get_organizer_email()); ?></td>
        </tr>
        <tr class="organizer_front_field">
            <td><?php _e('Website', 'gd-calendar'); ?>:</td>
            <td><?php echo esc_url($organizer->get_website()); ?></td>
        </tr>
    </table>
</div>