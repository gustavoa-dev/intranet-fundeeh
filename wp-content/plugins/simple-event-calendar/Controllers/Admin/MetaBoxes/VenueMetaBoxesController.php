<?php

namespace GDCalendar\Controllers\Admin\MetaBoxes;

use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Venue;

class VenueMetaBoxesController
{
    public function __construct()
    {
        add_action('save_post', array( __CLASS__, 'save'));
        add_action('add_meta_boxes', array(__CLASS__, 'metaBox'));
    }

    public static function metaBox()
    {
        add_meta_box(
            'gd_venue_box_id',
            __('Venue', 'gd-calendar'),
            array(__CLASS__, 'venueBox'),
            'gd_venues'
        );
    }

    public static function venueBox($post){
        wp_nonce_field('gd_venues_save', 'gd_venues_nonce');
        $post_id = absint($post->ID);
        $location = new Venue($post_id);
        View::render('admin/meta-boxes/venue-box.php', array(
            'location' => $location
        ));
    }

    public static function save($post_id){
        $post_type = get_post($post_id)->post_type;
        global $pagenow;

        if ( defined('DOING_AJAX') || $post_type !== 'gd_venues' || get_post_status($post_id) === 'trash' || (isset($_GET['action']) && $_GET['action'] === 'untrash') || $pagenow === 'post-new.php') {
            return;
        }

        if( !isset($_POST['gd_venues_nonce']) || !wp_verify_nonce($_POST['gd_venues_nonce'], 'gd_venues_save') ){
            wp_die('Are you sure you want to do this?');
        }

        $id = absint($post_id);
        $venue = new Venue($id);

        $venue->set_address($_POST['address']);
        $venue->save();
    }

}