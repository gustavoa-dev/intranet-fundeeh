<?php
/**
 * @var $event_organizer
 */
?>
<div class="event_organizer event_block">
    <label><?php _e('Organizer', 'gd-calendar'); ?></label>
    <select size="5" multiple id="event_organizer" name="event_organizer[]" class="event_venue">
        <?php
        $organizers = \GDCalendar\Models\PostTypes\Organizer::get(array('post_type' => 'gd_organizers', 'post_status' => 'publish'));

        foreach( $organizers as $organizer ){
            $id = absint($organizer->get_id());
            $title = esc_html($organizer->get_post_data()->post_title);
            $condition = is_array($event_organizer) && isset($event_organizer) && in_array($id, $event_organizer);
            ?>
            <option value="<?php echo $id; ?>" <?php if($condition) echo 'selected'; ?>><?php echo $title; ?></option>
        <?php } ?>
    </select>
    <a href="#" class="event_add_organizer add_new" id="event_add_organizer">+<?php _e('Add organizer', 'gd-calendar'); ?></a>
    <a href="#" class="event_back" id="event_back">◄ <?php _e('Back', 'gd-calendar'); ?></a>
</div>
<div class="new_organizer_box event_block_edit">
    <table id="organizer_info" class="organizer_info">
        <tr class="organizer_field">
            <td><?php _e('Name', 'gd-calendar'); ?></td>
            <td>
                <input type="text" name="organizer_name" id="organizer_name">
                <span class="error-msg error-name-org hide"><?php _e('Please fill name field', 'gd-calendar'); ?></span>
            </td>
        </tr>
        <tr class="organizer_field">
            <td><?php _e('Organized by', 'gd-calendar'); ?></td>
            <td>
                <input type="text" id="organized_by" name="organized_by">
            </td>
        </tr>
        <tr class="organizer_field">
            <td><?php _e('Address', 'gd-calendar'); ?></td>
            <td>
                <input type="text" id="organizer_address" name="organizer_address">
            </td>
        </tr>
        <tr class="organizer_field">
            <td><?php _e('Phone', 'gd-calendar'); ?></td>
            <td>
                <input type="tel" id="phone" name="phone">
                <span id="valid-msg" class="hide">✓ <?php _e('Valid', 'gd-calendar'); ?></span>
                <span id="error-msg" class="hide"><?php _e('Invalid number', 'gd-calendar'); ?></span>
            </td>
        </tr>
        <tr class="organizer_field">
            <td><?php _e('Website', 'gd-calendar'); ?></td>
            <td>
                <input type="url" id="website" name="website" placeholder="http://">
            </td>
        </tr>
        <tr class="organizer_field">
            <td><?php _e('Email', 'gd-calendar'); ?></td>
            <td>
                <input type="email" id="organizer_email" name="organizer_email">
                <span class="error-msg error-email-org hide"><?php _e('Not a valid email address', 'gd-calendar'); ?></span>
            </td>
        </tr>
        <tr class="organizer_field">
            <td></td>
            <td>
                <div class="organizer_save">
                    <button type="button" id="create_new_organizer" class="button-primary"><?php _e('Save', 'gd-calendar'); ?></button>
                </div>
            </td>
        </tr>
    </table>
</div>
