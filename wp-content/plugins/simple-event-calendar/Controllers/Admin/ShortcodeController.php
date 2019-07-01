<?php

namespace GDCalendar\Controllers\Admin;

use GDCalendar\Helpers\View;

class ShortcodeController
{

    public function __construct() {
        add_action( 'admin_footer', array( $this, 'inlinePopupContent' ) );
        add_action( 'media_buttons_context', array( $this, 'addEditorMediaButton' ) );
    }
    /**
     * Add editor media button
     * @param $context
     * @return string
     */
    public function addEditorMediaButton( $context ) {
        $img          = untrailingslashit( GDCalendar()->pluginUrl() ) . "/resources/assets/images/calendar_shortcode.png";
        $container_id = 'gd_calendar';
        $title        = __( 'Insert Calendar', 'gd-calendar' );
        $button_text  = __( 'Add Calendar', 'gd-calendar' );
        $context .= '<a class="button thickbox" title="' . $title . '"    href="#TB_inline?width=400&inlineId=' . $container_id . '">
		<span class="wp-media-buttons-icon" style="background: url(' . $img . '); background-repeat: no-repeat; background-position: left bottom;background-size: 18px 18px;"></span>' . $button_text . '</a>';

        return $context;
    }

    /**
     * Inline popup contents
     * todo: restrict to posts and pages
     */
    public function inlinePopupContent() {
        View::render( 'admin/inline-popup.php' );
    }

}