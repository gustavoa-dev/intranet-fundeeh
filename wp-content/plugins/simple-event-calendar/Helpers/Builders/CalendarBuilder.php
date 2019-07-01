<?php

namespace GDCalendar\Helpers\Builders;

use GDCalendar\Helpers\EventsCrawler;
use GDCalendar\Helpers\View;

class CalendarBuilder
{

    use EventsCrawler;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $post_id;

    /**
     * @var int
     */
    private $current_date;

    /**
     * array containing abbreviations of days of week.
     * @var array
     */
    private $days_of_week = array('SUN','MON','TUE','WED','THU','FRI','SAT');

    /**
     * @var array
     */
    private $months_of_year = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

    /**
     * @var array
     */
    private static $hours = array('All-day','12 AM','1 AM','2 AM','3 AM','4 AM','5 AM','6 AM','7 AM','8 AM','9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM','9 PM','10 PM','11 PM');

    public function __construct( $month, $year , $post_id){
        $this->month = $month;
        $this->year = $year;
        $this->post_id = $post_id;
    }

    /**
     * @return string
     */
    public function getCurrentDate(){
        if(null === $this->current_date){
            $this->current_date = current_time('Y-m-d');
        }

        return $this->current_date;
    }

    /**
     * @return int
     */
    public function getWeekday() {
        return current_time('w', strtotime($this->getCurrentDate()));
    }

    /**
     * @return int
     * @var string
     */
    public function getCurrentWeekdayNumber($date = false){
        if(false === $date){
            return date("W", strtotime($this->getCurrentDate() . '+ 1 day'));
        }
        return date("W", strtotime($date . '+ 1 day'));
    }

    /**
     * @return int
     */
    public function getMonth(){
        return $this->month;
    }

    /**
     * @return int
     */
    public function getYear(){
        return $this->year;
    }

    /**
     * @return int
     */
    public function getPostId(){
        return $this->post_id;
    }

    /**
     * @return array
     */
    public function getDaysOfWeek(){
        return $this->days_of_week;
    }

    /**
     * @return array
     */
    public function getMonthsOfYear(){
        return $this->months_of_year;
    }

    /**
     * @return array
     */
    public static function getHours(){
        return self::$hours;
    }

    /**
     * What is the first day of the month in question?
     * @return false|int
     */
    public function getFirstDayOfMonth($month = false){
        if(false === $month){
            return mktime(0,0,0,$this->month,1,$this->year);
        }
        return mktime(0,0,0,$month,1,$this->year);
    }

    /**
     * What is the last day of the last month
     * @return false|int
     */
    public function getLastDayOfPreviousMonth(){
        return mktime(0, 0, 0, $this->month, 0, $this->year);
    }

    /**
     * What is the last day of the last month
     * @return false|int
     */
    public function getLastDayOfMonth(){
        return current_time('Y-m-t', strtotime($this->year.'-'.$this->month));
    }

    /**
     * What is the first day of the next month
     * @return false|int
     */
    public function getFirstDayOfNextMonth(){
        return mktime(0, 0, 0, $this->month + 1, 1, $this->year);
    }

    /**
     * How many days does this month contain?
     */
    public function getDaysCount($month = false){
        return date('t',$this->getFirstDayOfMonth($month));
    }

    public function getLastDayDateComponents()
    {
        return getdate(strtotime($this->getLastDayOfMonth(). " 00:00"));
    }

    /**
     * Retrieve some information about the first day of the
     * month in question.
     */
    public function getDateComponents($month = false){
        return getdate($this->getFirstDayOfMonth($month));
    }

    /**
     * Retrieve some information about last month
     * @return array
     */

    public function getLastDateComponents(){
        return getdate($this->getLastDayOfPreviousMonth());
    }

    /**
     * Retrieve some information about next month
     * @return array
     */

    public function getNextDateComponents(){
        return getdate($this->getFirstDayOfNextMonth());
    }

    /**
     * Print calendar month table
     */
    public function getCalendarMonth(){
        View::render('frontend/calendar/month.php', array( 'builder' => $this ) );
    }

    /**
     * Print sidebar calendar table
     */
    public function getCalendarSidebar(){
        View::render('frontend/calendar/sidebar.php', array( 'sidebar_month' => $this ) );
    }

    /**
     * Print calendar week table
     */
    public function getCalendarWeek(){
        View::render('frontend/calendar/week.php', array( 'week' => $this ) );
    }

    /**
     * Print calendar day table
     */
    public function getCalendarDay(){
        View::render('frontend/calendar/day.php', array( 'day' => $this ) );
    }

    /**
     * Print Calendar
     */
    public function show(){
        View::render('frontend/calendar/show.php', array( 'show' => $this ) );
    }

}