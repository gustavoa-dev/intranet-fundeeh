<?php

namespace GDCalendar\Controllers\Admin\MetaBoxes;

use GDCalendar\Helpers\View;
use GDCalendar\Models\PostTypes\Event;
use GDCalendar\Models\PostTypes\Organizer;

class OrganizerMetaBoxesController
{

    public function __construct()
    {
        add_action('save_post', array( __CLASS__, 'save'));
        add_action('add_meta_boxes', array(__CLASS__, 'metaBox'));
        add_action('trashed_post', array(__CLASS__, 'removeEventOrganizer'));

    }

    public static function metaBox()
    {
        add_meta_box(
            'gd_organizer_box_id',
            __('Organizers', 'gd-calendar'),
            array(__CLASS__, 'organizerBox'),
            'gd_organizers',
            'normal',
            'high'
        );
    }

    public static function organizerBox($post) {
        wp_nonce_field('gd_organizers_save', 'gd_organizers_nonce');

        $post_id = absint($post->ID);
        $organizer = new Organizer($post_id);

        View::render('admin/meta-boxes/organizer-box.php', array(
            'organizer' => $organizer
        ));
    }

    public static function save($post_id){
        $post_type = get_post($post_id)->post_type;
        global $pagenow;

        if ( defined('DOING_AJAX') || $post_type !== 'gd_organizers' || get_post_status($post_id) === 'trash' || (isset($_GET['action']) && $_GET['action'] === 'untrash') || $pagenow === 'post-new.php') {
            return;
        }
        if( !isset($_POST['gd_organizers_nonce']) || !wp_verify_nonce($_POST['gd_organizers_nonce'], 'gd_organizers_save') ){
            wp_die('Are you sure you want to do this?');
        }

        $id = absint($post_id);
        $organizer = new Organizer($id);

        $organizer->set_organized_by($_POST['organized_by'])
            ->set_organizer_address($_POST['organizer_address'])
            ->set_phone($_POST['phone'])
            ->set_website($_POST['website'])
            ->set_organizer_email($_POST['organizer_email']);
        $organizer->save();
    }

    public static function removeEventOrganizer( $post_id ){
        global $post_type;
        if ( $post_type === Organizer::get_post_type()) {
            $events = Event::get(array('post_status'=>'any'));
            foreach( $events as $event ){
                $event_organizers = $event->get_event_organizer();
                if(in_array($post_id, $event_organizers)){
                    $event->delete_event_organizer($post_id);
                }
            }
        }
    }

}