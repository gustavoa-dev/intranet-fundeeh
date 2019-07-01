<?php

namespace GDCalendar\Models\PostTypes;

use GDCalendar\Core\PostTypeModel;

class Venue extends PostTypeModel
{
    protected static $post_type = 'gd_venues';

    /**
     * @var string
     */
    protected $address;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;


    protected static $meta_keys = array(
        'address',
        'latitude',
        'longitude'
    );

    /**
     * @return string
     */
    public function get_address(){
        return $this->address;
    }

    /**
     * @param $address
     * @return Venue
     */
    public function set_address($address){
        $this->address = sanitize_text_field($address);
        return $this;
    }

    /**
     * @return float
     */
    public function get_latitude(){
        return $this->latitude;
    }

    /**
     * @param $latitude
     * @return Venue
     */
    public function set_latitude($latitude){
        $this->latitude = floatval($latitude);
        return $this;
    }

    /**
     * @return float
     */
    public function get_longitude(){
        return $this->longitude;
    }

    /**
     * @param $longitude
     * @return Venue
     */
    public function set_longitude($longitude){
        $this->longitude = floatval($longitude);
        return $this;
    }


    /**
     * @return bool
     */
    public function save(){
        return $this->save_meta();
    }
}