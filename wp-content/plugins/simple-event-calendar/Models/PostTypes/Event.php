<?php

namespace GDCalendar\Models\PostTypes;

use GDCalendar\Core\Validator;
use GDCalendar\Core\PostTypeModel;
use GDCalendar\Helpers\Builders\CalendarBuilder;

class Event extends PostTypeModel
{

    protected static $post_type = 'gd_events';
    public static $repeat_types = array(
        1 => 'Day',
        2 => 'Week',
        3 => 'Month',
        4 => 'Year'
    );

    /**
     * @var string
     */
    protected $start_date;

    /**
     * @var string
     */
    protected $end_date;

	/**
	 * @var array
	 */
    protected $date_range = array();

    /**
     * @var string
     */
    protected $all_day;

    /**
     * @var string
     */
    protected $repeat;

    /**
     * @var int
     */
    protected $repeat_type;

    /**
     * @var int
     */
    protected $repeat_day;

    /**
     * @var int
     */
    protected $repeat_week;

    /**
     * @var int
     */
    protected $repeat_month;

    /**
     * @var int
     */
    protected $repeat_year;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $cost;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $currency_position;

    /**
     * @var int
     */
    protected $event_venue;

    /**
     * @var array
     */
    protected $event_organizer = array();

    protected static $meta_keys = array(
        'start_date',
        'end_date',
        'all_day',
        'repeat',
        'repeat_type',
        'repeat_day',
        'repeat_week',
        'repeat_month',
        'repeat_year',
        'timezone',
        'cost',
        'currency',
        'currency_position',
        'event_venue',
        'event_organizer',
	    'date_range'
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->event_organizer = $this->get_meta_value('organizer');
	    if($this->get_start_date() != null && $this->get_end_date() != null){
	        $this->date_range[] = CalendarBuilder::eventDateRange($this->get_start_date(), $this->get_end_date());
        }
    }

    /**
     * @return string
     */
    public function get_start_date()
    {
        return $this->start_date;
    }

    /**
     * @param string $start_date
     * @return Event
     * @throws \Exception
     */
    public function set_start_date($start_date)
    {
        if (Validator::validateDate($start_date) == false) {
            throw new \Exception('Wrong start date format given');
        }
        $this->start_date = sanitize_text_field($start_date);
        return $this;
    }

    /**
     * @return string
     */
    public function get_end_date()
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     * @return Event
     * @throws \Exception
     */
    public function set_end_date($end_date)
    {
        if (Validator::validateDate($end_date) == false) {
            throw new \Exception('Wrong end date format given');
        }
        $this->end_date = sanitize_text_field($end_date);
        return $this;
    }

	/**
	 * @return array
	 */
    public function get_date_range(){
    	return $this->date_range;
    }

    /**
     * @return string
     */

    public function get_all_day()
    {
        return $this->all_day;
    }

    /**
     * @param $all_day
     * @return Event
     * @throws \Exception
     */
    public function set_all_day($all_day)
    {
        if (!empty($all_day) && $all_day != 'all_day') {
            throw new \Exception('Wrong all day value given');
        }
        $this->all_day = $all_day;
        return $this;
    }

    /**
     * @return string
     */
    public function get_repeat()
    {
        return $this->repeat;
    }

    /**
     * @param $repeat
     * @return Event
     * @throws \Exception
     */

    public function set_repeat($repeat)
    {
        if (!empty($repeat) && $repeat != 'repeat') {
            throw new \Exception('Wrong repeat value given');
        }

        $this->repeat = $repeat;
        return $this;
    }

    /**
     * @return int
     */

    public function get_repeat_type()
    {
        return $this->repeat_type;
    }

    /**
     * @param $repeat_type
     * @return Event
     * @throws \Exception
     */

    public function set_repeat_type($repeat_type)
    {
        if ($repeat_type != 'choose_type' && !array_key_exists($repeat_type, self::$repeat_types)) {
            throw new \Exception('Wrong repeat type selected');
        }

        $this->repeat_type = $repeat_type;
        return $this;
    }

    /**
     * @return int
     */

    public function get_repeat_day()
    {
        return intval($this->repeat_day);
    }

    /**
     * @param $repeat_day
     * @return Event
     */

    public function set_repeat_day($repeat_day)
    {
        $this->repeat_day = absint($repeat_day);
        return $this;
    }

    /**
     * @return int
     */
    public function get_repeat_week(){
        return $this->repeat_week;
    }

    /**
     * @param $repeat_week
     * @return Event
     */
    public function set_repeat_week($repeat_week){
        $this->repeat_week = absint($repeat_week);
        return $this;
    }

    /**
     * @return int
     */
    public function get_repeat_month()
    {
        return $this->repeat_month;
    }

    /**
     * @param $repeat_month
     * @return Event
     */
    public function set_repeat_month($repeat_month)
    {
        $this->repeat_month = absint($repeat_month);
        return $this;
    }

    /**
     * @return int
     */
    public function get_repeat_year()
    {
        return $this->repeat_year;
    }

    /**
     * @param $repeat_year
     * @return Event
     */
    public function set_repeat_year($repeat_year)
    {
        $this->repeat_year = absint($repeat_year);
        return $this;
    }

    /**
     * @return string
     */
    public function get_timezone()
    {
        return $this->timezone;
    }

    /**
     * @param $timezone
     * @return Event
     */
    public function set_timezone($timezone)
    {
        $this->timezone = sanitize_text_field($timezone);
        return $this;
    }

    /**
     * @return string
     */
    public function get_currency()
    {
        return $this->currency;
    }

    /**
     * @param $currency
     * @return Event
     */
    public function set_currency($currency)
    {
        $this->currency = sanitize_text_field($currency);
        return $this;
    }

    /**
     * @return string
     */
    public function get_cost()
    {
        return $this->cost;
    }

    /**
     * @param $cost
     * @return Event
     */
    public function set_cost($cost)
    {
        $this->cost = sanitize_text_field($cost);
        return $this;
    }

    /**
     * @return string
     */
    public function get_currency_position()
    {
        return $this->currency_position;
    }

    /**
     * @param $currency_position
     * @return Event
     * @throws \Exception
     */
    public function set_currency_position($currency_position)
    {
        if ($currency_position !== 'after' && $currency_position !== 'before') {
            throw new \Exception('Wrong currency position selected');
        }
        $this->currency_position = $currency_position;
        return $this;
    }

    /**
     * @return int
     */
    public function get_event_venue()
    {
        return $this->event_venue;
    }

    /**
     * @param $event_venue
     * @return Event
     */
    public function set_event_venue($event_venue)
    {
        $this->event_venue = absint($event_venue);
        return $this;
    }

    /**
     * @return array
     */
    public function get_event_organizer()
    {
        return $this->event_organizer;
    }

    /**
     * @param $event_organizer
     * @return Event
     */
    public function set_event_organizer($event_organizer)
    {
        $this->event_organizer = array_filter($event_organizer, 'absint');
        return $this;
    }

    /**
     * @param $organizer_id
     *
     */
    public function delete_event_organizer($organizer_id)
    {
        $this->delete_meta_value('organizer', $organizer_id);
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->save_meta();
        $this->delete_meta_value('organizer');

        foreach ($this->event_organizer as $organizer) {
            $organizer = absint($organizer);
            $this->add_meta_value('organizer', $organizer);
        }
        return true;
    }

}