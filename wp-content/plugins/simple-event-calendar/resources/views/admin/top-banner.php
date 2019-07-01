<?php
/**
 * @var $taxonomy
 * @var $current_screen
 * @var $page
 */

$name = get_admin_page_title();
$post_type = $current_screen->post_type;
$add_new_url = '';
$item = '';
$list_name_block = '';
    if(!$taxonomy){
        $add_new_url = admin_url('post-new.php?post_type=' . $post_type );
	    $post_obj = get_post_type_object($post_type);
        $item = $post_obj->labels->add_new;

	    if(isset($_GET['post'])){
		    $id = absint($_GET['post']);
		    $posts = get_posts(array('post_type' => $post_type, 'posts_per_page' => -1));

		    $list_name_block = "<ul class='gd_calendar_section_switch'>";
		    foreach ($posts as $post):
			    $post_id = $post->ID;
			    $post_name = stripslashes($post->post_title);
			    $edit_post = admin_url('post.php?post=' . $post_id .'&action=edit');

			    if ($post_id == $id):
				    $list_name_block .= "<li class='gd_calendar_active_section'>
                                            <a href='#' class='gd_calendar_section_edit_name'><i class='fa fa-pencil' aria-hidden='true'></i></a>
                                            <a href='#' class='gd_calendar_section_active_name'>". $post_name ."</a>
                                            <input type='text' name='edit_name' value='". $post_name ."' class='gd_calendar_hidden gd_calendar_edit_section_name_input'>
                                          </li>";
			    else:
				    $list_name_block .= "<li><a href='". $edit_post."'>". $post_name ."</a></li>";
			    endif;
		    endforeach;
		    $list_name_block.="</ul>";
	    }
    }
?>
    <div class="gd_calendar_top_banner">
        <i class="gd_calendar_banner_logo"></i>
        <span>GrandWP Event Calendar</span>
        <ul>
            <li>
                <a href="http://demo.grandwp.com/wordpress-event-calendar-demo/" target="_blank"><?php _e('Demo','gd-calendar');?></a>
            </li>
            <li>
                <a href="https://wordpress.org/support/plugin/simple-event-calendar" target="_blank"><?php _e('Support Forum','gd-calendar');?></a>
            </li>
            <li>
                <a href="https://grandwp.com/wordpress-event-calendar?utm_source=free-plugin&utm_medium=calendar&utm_campaign=get-pro" target="_blank"><?php _e('Go Pro','gd-calendar');?></a>
            </li>
            <li>
                <a href="https://grandwp.com/wordpress-event-calendar-user-manual?utm_source=free-plugin&utm_medium=calendar&utm_campaign=help" target="_blank"><?php _e('Help','gd-calendar');?></a>
            </li>
        </ul>
    </div>
    <?php if($taxonomy === '' && !($current_screen->base === 'post' && $current_screen->action === 'add' ) && $page !== 'gd_events_page_gd_events_featured_plugins' || $page === 'gd_events_page_gd_events_themes' ): ?>
    <div class="gd_calendar_new_list_header">
        <div>
            <h1><?php _e($name,'gd-calendar');?></h1>
            <?php
            if($page !== 'gd_events_page_gd_events_themes'):
	           // echo $list_name_block;
            ?>
                <a href="<?php echo $add_new_url; ?>" id="gd_calendar_new"><?php _e($item,'gd-calendar');?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
