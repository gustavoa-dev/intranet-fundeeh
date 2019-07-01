<?php
namespace GDCalendar\Controllers\Admin;

use GDCalendar\Core\ErrorHandling\ErrorBag;
use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Calendar;

class AdminController{

    /**
     * @var array
     */
    public $Pages = array();

    public function __construct()
    {
	    if (!session_id()){
		    session_start();
        }

        $this->Pages = array('gd_events', 'gd_calendar', 'gd_organizers', 'gd_venues');
        add_action( 'admin_menu', array( $this, 'adminMenu' ) );
        add_filter('admin_head', array($this, 'topBanner'));
	    add_action('admin_init', array( $this, 'delayNotices'), 1);
        add_filter('screen_options_show_screen', array($this, 'removeScreenOptions'));
        add_filter('manage_edit-gd_calendar_columns', array(__CLASS__, 'calendarColumns'));
	    add_action('load-post-new.php',  array(__CLASS__, 'menuStyle') );
        add_action('manage_gd_calendar_posts_custom_column', array($this, 'calendarColumnsData'), 10, 2);
        add_action('save_post', array(__CLASS__, 'setDefaultObjectTerms'), 99, 2);
	    add_action( 'admin_notices', array($this, 'errorMessages'), 100, 2);

	    new AdminAssetsController();
        new MetaBoxesController();
        new ShortcodeController();
    }

	public static function menuStyle(){
		echo '<style>#adminmenu .wp-submenu li.wp-first-item a{color: #fff;font-weight: 600;}</style>';
	}

    public function removeScreenOptions(){
        global $current_screen;
        $type = $current_screen->post_type;
        $page = $current_screen->id;

        if(!in_array($type, $this->Pages) && $page !== 'gd_events_page_gd_events_featured_plugins' && $page !== 'gd_events_page_gd_events_themes'){
            return true;
        }
        return false;
    }

    public function errorMessages(){
	    if ( array_key_exists( 'errors', $_SESSION ) && !empty($_SESSION['errors']) ) {
	        ?>
            <div class="error">
                <p><?php
                    $errors = $_SESSION['errors'];
                        foreach ($errors as $error){
                            echo $error . '</br>';
                        }
                    ?>
                </p>
            </div><?php
		    unset( $_SESSION['errors'] );
	    }
    }

    public function topBanner(){
        global $taxnow;
        global $current_screen;

	    $type = $current_screen->post_type;
        $page = $current_screen->id;
        $base = $current_screen->base;

        if ( $type !== '' && in_array($type, $this->Pages) && $page !== 'gd_events_page_gd_events_settings' || $page === 'gd_events_page_gd_events_themes') {
	        if( $taxnow === '' && $base !== 'edit' ){
		        echo '<style>.wrap h1.wp-heading-inline{display:inline-block;}</style>';
	        }
        ?>
            <div class="gd_calendar_top_banner_container">
            <?php
            if (get_option('gd_calendar_review_notice_ignore') || get_option('gd_calendar_review_notice_delayed') &&
                (strtotime('now') - strtotime(get_option('gd_calendar_review_notice_delayed'))) < 604800 ||
                (strtotime('now') - strtotime(get_option('gd_calendar_plugin_installed'))) < 604800) {
            } else {
                View::render( 'admin/ask-for-review.php' );
            }
                View::render( 'admin/top-banner.php', array(
                    'taxonomy' => $taxnow,
                    'current_screen' => $current_screen,
                    'page' => $page
                ));
        ?>
            </div>
            <?php
        }
    }

    /* Ask user for review */
    public function delayNotices(){
        if ( isset( $_GET['gd_calendar_delay_notice'] ) ) {
            update_option('gd_calendar_review_notice_delayed', date('Y-m-d H:i:s'));

            $redirectLink = remove_query_arg( array( 'gd_calendar_delay_notice' ) );
            wp_redirect( $redirectLink );
            exit;
        } else if ( isset( $_GET['gd_calendar_ignore_notice'] ) ) {
            update_option('gd_calendar_review_notice_ignore', 1 );
            $redirectLink = remove_query_arg( array( 'gd_calendar_ignore_notice' ) );
            wp_redirect( $redirectLink );
            exit;
        }
    }

    public function adminMenu()
    {
        remove_submenu_page( 'edit.php?post_type=gd_events', 'post-new.php?post_type=gd_events' );
	    $this->Pages['themes'] = add_submenu_page( 'edit.php?post_type=gd_events', __('Themes', 'gd-calendar'), __('Themes', 'gd-calendar'), 'manage_options', 'gd_events_themes', array(__CLASS__, 'calendarThemes'));
	   // $this->Pages['settings'] = add_submenu_page( 'edit.php?post_type=gd_events', __('Settings', 'gd-calendar'), __('Settings', 'gd-calendar'), 'manage_options', 'gd_events_settings', array(__CLASS__, 'calendarSettings'));
        $this->Pages['featured_plugins'] = add_submenu_page( 'edit.php?post_type=gd_events', __('Featured Plugins', 'gd-calendar'), __('Featured Plugins', 'gd-calendar'), 'manage_options', 'gd_events_featured_plugins', array(__CLASS__, 'calendarFeaturedPlugins'));
    }

    public static function calendarColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'gd-calendar'),
            'featured_image' => __('Featured Image', 'gd-calendar'),
            'shortcode' => __('Shortcode', 'gd-calendar'),
            'theme' => __('Theme', 'gd-calendar'),
            'date' => __('Date', 'gd-calendar'),
        );
        return $columns;
    }

    public static function calendarColumnsData($column, $post_id)
    {
        switch ( $column ) {
            case 'shortcode' :
            	echo '<span class="gd_calendar_textarea_box">[gd_calendar id="' . $post_id . '"]</span><span class="calendar_use_another_shortcode">or use php shortcode</span>';
            	echo '<span>&lt;?php echo do_shortcode("[gd_calendar id=\'' . $post_id .'\']"); ?&gt;</span>';
                break;
            case 'theme' :
                $all_themes = Calendar::$themes;
                $calendar = new Calendar($post_id);
	            if (array_key_exists($calendar->get_theme(), $all_themes)){
		            _e( $all_themes[$calendar->get_theme()], 'gd-calendar' );
	            }
	            else{ _e( 'Default Theme', 'gd-calendar' ); }
                break;
            case 'featured_image' :
                $calendar = new Calendar($post_id);
                echo '<a href="'. $calendar->get_edit_link() . '">' . get_the_post_thumbnail( $post_id, array(50, 50)) . '</a>' ;
                break;
        }
    }

    public static function setDefaultObjectTerms($post_id, $post)
    {
        if ( 'publish' === $post->post_status && get_post_type($post_id) == 'gd_events') {
            $defaults = array( 'event_category' => 'Uncategorized' );
            $taxonomy = 'event_category';
            $terms = wp_get_post_terms( $post_id, $taxonomy );

            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                $affected_ids = wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
                if( is_array( $affected_ids ) && !empty( $affected_ids ) ){
                    update_option('default_event_category', $affected_ids[0]);
                }
            }
        }
    }

    public static function calendarThemes(){
        ?>
        <form action="edit.php?post_type=gd_events&amp;page=gd_events_themes&amp;id=2&amp;save_data_nonce=285ff03d27" method="post" name="gd_calendar_theme_save_form" id="gd_calendar_theme_save_form">

            <div class="gd_calendar_theme_options_container">
                <div class="theme-save-head">
                    <input type="submit" class="theme-save" value="Save Theme">
                    <span class="spinner"></span>
                    <p class="pro_save_message" style="display: none;">This action is available in PRO version</p>
                </div>
                <div class="gd_calendar_theme_option_box">
                    <div class="gd_calendar_theme_option_header">
                        <label>Theme Name</label>
                        <input disabled="disabled"  value="Your Custom Theme" type="text">
                    </div>
                    <div class="pro_message">These options are available in PRO version. Go PRO to open theme. <a href="https://grandwp.com/wordpress-event-calendar?utm_source=free-plugin&utm_medium=calendar&utm_campaign=get-pro" target="_blank">Upgrade</a>
                    </div>
                    <div class="gd_calendar_theme_option_body" style="position: relative; height: 1545px;">
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 0px; top: 30px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Sidebar <span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Header Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Header background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_header_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Day Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_current_date_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Content Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_bg_color]" value="0B1C24" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(11, 28, 36); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_first_circle_bg_color]" value="EF1C51" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(239, 28, 81); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_second_circle_bg_color]" value="1E0BAB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(30, 11, 171); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Sidebar][sidebar_third_circle_bg_color]" value="00AEEF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(0, 174, 239); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 818px; top: 30px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Menu Bar <span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Active Button Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Active Button Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][active_button_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">View Button(s) Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set View Button(s) Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][view_button_bg_color]" value="EBEBEB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(235, 235, 235); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">View Button On-Hover Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set View Button On-Hover Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][view_button_hover_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Datepicker Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Datepicker Current Day Background Color for Day, Week views</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][datepicker_current_day_active_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Datepicker Selected Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Datepicker Selected Day Background Color for Day, Week views</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][datepicker_current_day_selected_bg_color]" value="C88195" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(200, 129, 149); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Datepicker Selected Value Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Datepicker Button Background Color for Month, Year views</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Menu Bar][datepicker_button_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 0px; top: 443px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Day View <span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Day Background Color (above the calendar table)</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_today_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_first_border_left]" value="EF1C51" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(239, 28, 81); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_second_border_left]" value="1E0BAB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(30, 11, 171); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_third_border_left]" value="00AEEF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(0, 174, 239); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Weekend Column Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Weekend Table Column Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_table_weekend_columns_background_color]" value="F9F9F9" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(249, 249, 249); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_first_bg_color]" value="FCD2DC" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(252, 210, 220); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_second_bg_color]" value="DBD7F2" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(219, 215, 242); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Day][day_event_third_bg_color]" value="B8E7F8" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(184, 231, 248); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 818px; top: 443px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Week View<span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Day Background Color (above the calendar table)</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_current_day_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Week Text Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set CW Text Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_number_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_first_border_left]" value="EF1C51" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(239, 28, 81); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_second_border_left]" value="1E0BAB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(30, 11, 171); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Event Left Border Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Event Left Border Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_third_border_left]" value="00AEEF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(0, 174, 239); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Table Content Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Table Full Content Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_table_background_color]" value="FFFFFF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">View All Link Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set View All Link Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_see_all_link_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Weekend Column Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Weekend Column Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_table_weekend_columns_background_color]" value="F9F9F9" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(249, 249, 249); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_first_bg_color]" value="FCD2DC" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(252, 210, 220); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_second_bg_color]" value="DBD7F2" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(219, 215, 242); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Week][week_event_third_bg_color]" value="B8E7F8" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(184, 231, 248); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 0px; top: 958px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Month View<span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Day Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_current_date_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Table Content Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Table Full Content Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_table_background_color]" value="FFFFFF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Weekend Column Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Weekend Column Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_table_weekend_columns_background_color]" value="F9F9F9" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(249, 249, 249); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">View All Link Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set View All Link Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_see_all_link_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_first_circle_bg_color]" value="EF1C51" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(239, 28, 81); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_second_circle_bg_color]" value="1E0BAB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(30, 11, 171); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_third_circle_bg_color]" value="00AEEF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(0, 174, 239); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_event_first_bg_color]" value="FCD2DC" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(252, 210, 220); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Second Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_event_second_bg_color]" value="DBD7F2" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(219, 215, 242); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Event Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Event Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Month][month_event_third_bg_color]" value="B8E7F8" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(184, 231, 248); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="gd_calendar_theme_section_wrap active" style="position: absolute; left: 818px; top: 1111px;">
                            <div class="gd_calendar_theme_section_heading">
                                <div class="gd_calendar_theme_section_heading_inner">
                                    <h3>Year View<span class="pro_label">Pro</span></h3>
                                    <span class="gd_calendar_theme_section_arrow">
                                            <svg id="Layer_1" x="0px" y="0px" viewBox="0 0 491.996 491.996" style="enable-background:new 0 0 491.996 491.996;" xml:space="preserve" width="12px" height="12px"><g><g><path d="M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848    L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128    c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084    c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224    C491.996,136.902,489.204,130.046,484.132,124.986z" fill="rgba(0,0,0,0.65)"></path></g></g></svg>
                                        </span>
                                </div>
                            </div>
                            <div class="gd_calendar_theme_section_content">
                                <div class="settings_disable_layer"></div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Day Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Day Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_current_date_bg_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Current Year Text Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Current Year Text Color (above calendar table)</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_title_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Table Content Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Table Full Content Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_current_month_color]" value="A5173F" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(165, 23, 63); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Days Background Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Days Background Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_table_days_background_color]" value="FFFFFF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">First Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set First Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_first_circle_bg_color]" value="EF1C51" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(239, 28, 81); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Second Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Second Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_second_circle_bg_color]" value="1E0BAB" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(30, 11, 171); color: rgb(255, 255, 255);">
                                    </label>
                                </div>
                                <div class="gd_calendar_theme_option_field">
                                    <label class="gd_calendar_theme_option_label">
                                                <span class="gd_calendar_theme_option_name">Third Bullet Color
                                                    <span class="gd_calendar_theme_option_help">
                                                        <span class="gd_calendar_theme_option_help_icon">?</span>
                                                        <span class="gd_calendar_theme_option_help_text_wrap">
                                                            <span class="gd_calendar_theme_option_help_text">Set Third Bullet Color</span>
                                                            <span class="gd_calendar_theme_option_help_text_tooltip"></span>
                                                        </span>
                                                    </span>

                                                </span>
                                        <input type="text" name="gd_calendar_theme_type[Year][year_third_circle_bg_color]" value="00AEEF" class="jscolor gd_calendar_theme_option_value" autocomplete="off" style="background-image: none; background-color: rgb(0, 174, 239); color: rgb(0, 0, 0);">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }
    public static function calendarSettings() {
        View::render('admin/calendar-settings.php');
    }

    public static function calendarFeaturedPlugins(){
        View::render('admin/calendar-featured-plugins.php');
    }
}