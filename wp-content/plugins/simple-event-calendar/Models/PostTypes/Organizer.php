<?php

namespace GDCalendar\Models\PostTypes;

use GDCalendar\Core\PostTypeModel;

class Organizer extends PostTypeModel
{
    protected static $post_type = 'gd_organizers';

    /**
     * @var string
     */
    protected $organized_by;

    /**
     * @var string
     */
    protected $organizer_address;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $website;

    /**
     * @var string
     */
    protected $organizer_email;


    protected static $meta_keys = array(
        'organized_by',
        'organizer_address',
        'phone',
        'website',
        'organizer_email',
    );

    /**
     * @return string
     */
    public function get_organized_by(){
        return $this->organized_by;
    }

    /**
     * @param $organized_by
     * @return Organizer
     */
    public function set_organized_by($organized_by){
        $this->organized_by = sanitize_text_field($organized_by);
        return $this;
    }

    /**
     * @return string
     */
    public function get_organizer_address(){
        return $this->organizer_address;
    }

    /**
     * @param $organizer_address
     * @return Organizer
     */
    public function set_organizer_address($organizer_address){
        $this->organizer_address = sanitize_text_field($organizer_address);
        return $this;
    }

    /**
     * @return string
     */
    public function get_phone(){
        return $this->phone;
    }

    /**
     * @param $phone
     * @return Organizer
     */
    public function set_phone($phone){
        $this->phone = sanitize_text_field($phone);
        return $this;
    }

    /**
     * @return string
     */
    public function get_website(){
        return $this->website;
    }

    /**
     * @param $website
     * @return Organizer
     */
    public function set_website($website){
        $this->website = esc_url($website);
        return $this;
    }

    /**
     * @return string
     */
    public function get_organizer_email(){
        return $this->organizer_email;
    }

    /**
     * @param $organizer_email
     * @return Organizer
     */
    public function set_organizer_email($organizer_email){
        $this->organizer_email = sanitize_email($organizer_email);
        return $this;
    }

    /**
     * @return bool
     */
    public function save(){
        return $this->save_meta();
    }

}