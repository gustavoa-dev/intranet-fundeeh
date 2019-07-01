<?php
/**
 * @var $menu_page array
 * @var $checkbox_values
 * @var $post_id
 * @var $calendar
 */
?>
<p>Please select the filter and choose the options you want to appear on this very calendar.</p>
<div class="display_events_filters_wrapper">
<div class="events_display_filter_block">
    <h3><?php _e('Show events by', 'gd-calendar'); ?>:</h3>
    <select name="select_events_by" id="select_events_by">
		<?php foreach ($menu_page as $page) {
			$value = esc_html($page->name);
			$name = esc_html($page->labels->singular_name);
			$type = $page instanceof WP_Post_Type ? 'post' : 'taxonomy';
			?>
            <option value='<?php echo $value; ?>' <?php selected($calendar->get_select_events_by(), $value); ?> data-type="<?php echo $type; ?>"><?php echo $name; ?></option>
		<?php } ?>
    </select>
    <i class="fa fa-arrow-right"></i>
</div>
<div class="calendar_page_checkbox" data-post-id="<?php echo $post_id; ?>">
    <label class="select_all_label"><input type="checkbox" id="all_checkbox_select" value="all" <?php checked(count($calendar->get_cat()) && count($calendar->get_cat()) === count($checkbox_values[$calendar->get_select_events_by()])) ?>><?php _e('Select All', 'gd-calendar'); ?></label>
    <div id="checkboxes_container" class="checkboxes_container">

        <?php
        if (isset($checkbox_values[$calendar->get_select_events_by()])) {
            foreach ($checkbox_values[$calendar->get_select_events_by()] as $checkbox) {
                $id = absint($checkbox instanceof WP_Term ? $checkbox->term_id : $checkbox->ID);
                $name = esc_html($checkbox instanceof WP_Term ? $checkbox->name : $checkbox->post_title);
                $check = false;
                if (in_array($id, $calendar->get_cat())){
                    $check = $id;
                }
                ?>
                <label><input type="checkbox" name="cat[]" class="cat" <?php checked($check, $id); ?> value=<?php echo $id; ?>><?php echo $name; ?></label>
                <?php
            }
        }
        ?>
    </div>
</div>
</div>