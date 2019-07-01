<?
/**
 * @var $location
 */
?>
<div class="venue_box">
    <table id="venue_info" class="venue_info">
        <tr class="venue_field">
            <td><?php _e('Address', 'gd-calendar'); ?></td>
            <td><input type="text" name="address" id="address" autocomplete="off" placeholder="<?php _e('Address...', 'gd-calendar'); ?>" value="<?php if($location->get_address() == true) echo esc_html($location->get_address()); ?>"></td>
        </tr>
    <tr>
        <td colspan="2">
            <img src="<?php echo GDCALENDAR_IMAGES_URL . 'venue_pro.png'; ?>"/>
        </td>
    </tr>
    </table>
    <div><img src="<?php echo GDCALENDAR_IMAGES_URL . 'venue_pro_map.png'; ?>"/></div>
</div>