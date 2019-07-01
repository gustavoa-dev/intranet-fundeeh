<?php
/**
 * @var $event
 */
?>
<div class="event_location event_block">
    <label><?php _e('Location', 'gd-calendar'); ?></label>
    <select id="event_venue" name="event_venue" class="event_venue">
        <option value="choose"><?php _e('Choose location', 'gd-calendar'); ?></option>
        <?php
        $venues = \GDCalendar\Models\PostTypes\Venue::get(array('post_type' => 'gd_venues', 'post_status' => 'publish'));

        foreach( $venues as $venue ){
            $id = absint($venue->get_id());
            $title = esc_html($venue->get_post_data()->post_title);
            ?>
            <option value="<?php echo $id; ?>" <?php selected($event->get_event_venue(), $id); ?>><?php echo $title; ?></option>
        <?php } ?>
    </select>
    <a href="#" class="event_add_venue add_new" id="event_add_venue">+<?php _e('Add Venue', 'gd-calendar'); ?></a>
    <a href="#" class="event_back" id="event_back">◄ <?php _e('Back', 'gd-calendar'); ?></a>
</div>
<div class="new_venue_box event_block_edit">
    <table id="event_venue_info" class="event_venue_info">
        <tr class="venue_field">
            <td><?php _e('Name', 'gd-calendar'); ?></td>
            <td>
                <input type="text" name="venue_name" id="venue_name">
                <span class="error-msg error-name hide"><?php _e('Please fill name field', 'gd-calendar'); ?></span>
            </td>
        </tr>
        <tr class="venue_field">
            <td><?php _e('Address', 'gd-calendar'); ?></td>
            <td><input type="text" name="address" id="address" placeholder="<?php _e('Address', 'gd-calendar'); ?>..."></td>
        </tr>
        <tr>
            <td colspan="2">
                <img src="<?php echo GDCALENDAR_IMAGES_URL . 'venue_pro.png'; ?>"/>
            </td>
        </tr>
        <tr class="venue_field">
            <td></td>
            <td>
                <div class="venue_save">
                    <button type="button" id="create_new_venue" class="button-primary"><?php _e('Save', 'gd-calendar'); ?></button>
                </div>
            </td>
        </tr>
    </table>
    <div><img src="<?php echo GDCALENDAR_IMAGES_URL . 'venue_pro_map.png'; ?>"/></div>
</div>
