<?php
/**
 * @var $organizer
 */
?>
<table id="organizer_info" class="organizer_info">
    <tr class="organizer_field">
        <td><?php _e('Name', 'gd-calendar'); ?>:</td>
        <td>
            <input type="text" id="organized_by" name="organized_by" value="<?php if($organizer->get_organized_by() == true) echo esc_html($organizer->get_organized_by()); ?>">
        </td>
    </tr>
    <tr class="organizer_field">
        <td><?php _e('Address', 'gd-calendar'); ?>:</td>
        <td>
            <input type="text" id="organizer_address" name="organizer_address" value="<?php if($organizer->get_organizer_address() == true) echo esc_html($organizer->get_organizer_address()); ?>">
        </td>
    </tr>
    <tr class="organizer_field">
        <td><?php _e('Phone', 'gd-calendar'); ?>:</td>
        <td>
            <input type="tel" id="phone" name="phone" value="<?php if($organizer->get_phone() == true) echo esc_html($organizer->get_phone()); ?>">
            <span id="valid-msg" class="hide">✓ <?php _e('Valid', 'gd-calendar'); ?></span>
            <span id="error-msg" class="hide"><?php _e('Invalid number', 'gd-calendar'); ?></span>
        </td>
    </tr>
    <tr class="organizer_field">
        <td><?php _e('Website', 'gd-calendar'); ?>:</td>
        <td>
            <input type="url" id="website" name="website" placeholder="http://" value="<?php if($organizer->get_website() == true) echo esc_url($organizer->get_website()); ?>">
        </td>
    </tr>
    <tr class="organizer_field">
        <td><?php _e('Email', 'gd-calendar'); ?>:</td>
        <td>
            <input type="email" id="organizer_email" name="organizer_email" value="<?php if($organizer->get_organizer_email() == true) echo esc_html($organizer->get_organizer_email()); ?>">
        </td>
    </tr>
</table>
