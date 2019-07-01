<?php
/**
 * @var $post_id
 */
?>
<div class="gd_calendar_shortcode_box">
	<p>Use this shortcode to publish the project directly to WordPress post/page</p>
	<textarea readonly >[gd_calendar id="<?php echo $post_id; ?>"]</textarea>
</div>
<div class="gd_calendar_shortcode_box">
	<h4><?php _e('PHP Code', 'gd-calendar'); ?></h4>
	<p>Use this php shortcode to include the created project in your theme</p>
	<textarea readonly >&lt;?php echo do_shortcode("[gd_calendar id='<?php echo $post_id; ?>']"); ?&gt;</textarea>
</div>