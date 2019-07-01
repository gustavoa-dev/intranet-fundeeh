<?php

namespace GDCalendar\Helpers;

use GDCalendar\Models\PostTypes\Calendar;
use GDCalendar\Models\PostTypes\Event;
use GDCalendar\Models\PostTypes\Organizer;
use GDCalendar\Models\PostTypes\Venue;

trait EventsCrawler {

	/**
	 * @param $day
	 * @return array
	 * Retrieve event by day
	 * @throws \Exception
	 */
	public static function getEventByDay( $day ) {
		$day_events = array();
		if ( isset( $_GET['id'] ) ) {
			$post_id = absint( $_GET['id'] );
		} elseif ( isset( $_POST['id'] ) ) {
			$post_id = absint( $_POST['id'] );
		} elseif ( null !== get_post() ) {
			$post_id = absint( get_post()->ID );
		}

		$calendar            = new Calendar( $post_id );
		$post_type           = $calendar->get_select_events_by();
		$selected_categories = $calendar->get_cat();

		$tax_param = '';
		if ( ! empty( $selected_categories ) && taxonomy_exists( $post_type ) ) {
			$tax_param = array(
				'taxonomy'         => $post_type,
				'terms'            => $selected_categories,
				'include_children' => false,
			);
		}
		$events = Event::get( array(
				'post_status' => 'publish',
				'tax_query'   => array(
					$tax_param,
				)
			)
		);

		if ( $events && ! empty( $events ) ) {

			/* Sorting events by start datetime */

			$sort_events = array();
			foreach ($events as $key => $value){
				$sort_events[] = strtotime(substr($value->get_start_date(), 11, 8));
			}
			array_multisort($sort_events, SORT_ASC, $events);

			foreach ( $events as $event ) {
				$event_id = absint( $event->get_id() );

				if ( ! empty( $selected_categories ) ) {
					if ( $post_type === 'gd_organizers' ) {
						$organizers = $event->get_event_organizer();
						$org_result = array_intersect( $organizers, $selected_categories );
						$result     = ( ! empty( $org_result ) ) ? true : false;
					} elseif ( $post_type === 'gd_venues' ) {
						$venue  = $event->get_event_venue();
						$result = in_array( $venue, $selected_categories );
					} else {
						$result = true;
					}
				} else {
					$result = true;
				}

				if ( true === $result ) {

					if ( $event->get_repeat() === 'repeat' && $event->get_repeat_type() !== 'choose_type' ) {
						$repeat_type   = absint( $event->get_repeat_type() );
						$eventAllDay   = $event->get_all_day();
						$eventStartDay = new \DateTime( $event->get_start_date() );
						$eventEndDate  = new \DateTime( $event->get_end_date() );
						$eventInterval = intval( $eventStartDay->diff( $eventEndDate )->format( '%a' ) );
						$repeatTypeValue = Event::$repeat_types[ $repeat_type ];
						$minDate = new \DateTime( $day );
						$maxDate = new \DateTime( $day );

						if ( isset( $_POST['type'] ) ) {
							$type = $_POST['type'];
							if ( $type === 'week' ) {
								$maxDate = $maxDate->modify( '+6 day' );
							}
						}

						switch ( $repeat_type ) {
							case 1:
								$repeatValue = $event->get_repeat_day();
								break;
							case 2:
								$repeatValue = $event->get_repeat_week();
								break;
							case 3:
								$repeatValue = $event->get_repeat_month();
								break;
							case 4:
								$repeatValue = $event->get_repeat_year();
								break;
							default:
								$repeatValue = $event->get_repeat_day();
						}

						$event_dates = self::getRepeatedEventsDateRange( $eventStartDay, $eventEndDate, $eventInterval, $repeatValue, $maxDate, $minDate, $repeatTypeValue, $eventAllDay );
					} else {
						$event_dates = $event->get_date_range();
					}

					if ( ! empty( $event_dates ) ):
						foreach ( $event_dates as $event_date ):
							foreach ( $event_date as $date ) {
								if ( $day === substr( $date, 0, 10 ) ) {
									if ( isset( $day_events[ $event_id ] ) || empty( $day_events[ $event_id ] ) ) {
										$day_events[ $event_id ] = $event_date;
									}
								}
							}
						endforeach;
					endif;
				}
			}
		}

		return $day_events;
	}

	/**
	 * @param $day
	 * @return array
	 * Retrieve events by hour
	 */
	public static function getEventByHour( $day ) {
		$hour_events = array();
		$day_events  = self::getEventByDay( $day );
		foreach ( $day_events as $id => $event ) {
			$date = sanitize_text_field( substr( $event[0], 11, 8 ) );
			if ( $date !== '' ) {
				$divide     = explode( ":", $date, 2 );
				$time_digit = absint( $divide[0] );
				$period     = sanitize_text_field( substr( $divide[1], 3, 2 ) );
				$time       = $time_digit . ' ' . $period;
				$time       = strtoupper( $time ); // for sorting with hours array
			} else {
				$time = 'All-day';
			}

			if ( isset( $hour_events[ $time ] ) || empty( $hour_events[ $time ] ) ) {
				foreach ( $day_events as $key => $day_event ) {
					if ( $key == $id ) {
						$hour_events[ $time ][ $id ] = $day_event;
					}
				}
			}
		}

		$hours = self::getHours();
		$sorted_hour_events = array_merge( array_flip( $hours ), $hour_events );

		foreach ( $sorted_hour_events as $key => $value ) {
			if ( ! is_array( $value ) ) {
				unset( $sorted_hour_events[ $key ] );
			}
		}
		$sorted_hour_events = array_change_key_case( $sorted_hour_events, CASE_LOWER );

		return $sorted_hour_events;
	}

	/**
	 * @param $day
	 * @param Builders\CalendarBuilder $builder
	 * @return array Retrieve events for week by hour
	 * Retrieve events for week by hour
	 */
	public static function getEventByWeek( $day, Builders\CalendarBuilder $builder ) {
		$week_events = array();
		$week_number = absint( $builder->getCurrentWeekdayNumber( $day ) );
		$week_number = str_pad( $week_number, 2, "0", STR_PAD_LEFT );

		for ( $i = 0; $i <= 6; $i ++ ) {
			$date = date( 'Y-m-d', strtotime( $builder->getYear() . "W" . $week_number . $i ) );
			$day_events        = self::getEventByHour( $date );
			$week_events[ $i ] = $day_events;
		}

		return $week_events;
	}

	/**
	 * @return array
	 * Retrieve Get searched event with dates
	 * @throws \Exception
	 */
	public function getSearchedEvent() {
		global $wpdb;
		$searched_events_id = array();
		if ( isset( $_GET['search'] ) ) {
			$search = sanitize_text_field( $_GET['search'] );
			$search = '%' . $wpdb->esc_like( $search ) . '%';
			if ( isset( $_GET['id'] ) ) {
				$post_id = absint( $_GET['id'] );
			} elseif ( isset( $_POST['id'] ) ) {
				$post_id = absint( $_POST['id'] );
			} elseif ( null !== get_post() ) {
				$post_id = absint( get_post()->ID );
			} else {
				throw new \Exception( 'Cannot show day view for not existing post' );
			}
			$calendar            = new Calendar( $post_id );
			$post_type           = $calendar->get_select_events_by();
			$selected_categories = $calendar->get_cat();
			if ( !empty( $selected_categories ) && taxonomy_exists( $post_type ) )  {
				$taxonomies  = implode( ',', $selected_categories );
				$event_query = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "posts AS p
                                            JOIN " . $wpdb->prefix . "term_relationships AS r ON (r.object_id = p.ID)
                                            JOIN " . $wpdb->prefix . "term_taxonomy AS t ON (t.term_taxonomy_id = r.term_taxonomy_id)
                                            JOIN " . $wpdb->prefix . "terms AS tr ON (tr.term_id = t.term_id) 
                                            WHERE
                                            p.post_type = '" . Event::get_post_type() . "' AND
                                            p.post_status = 'publish' AND 
                                            t.taxonomy = '" . $post_type . "' AND 
                                            tr.term_id IN ($taxonomies) AND 
                                            p.post_title LIKE %s", $search );
			} else {
				$event_query = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "posts WHERE
                                            post_type = '" . Event::get_post_type() . "' AND
                                            post_status = 'publish' AND
                                            post_title LIKE %s", $search );
			}
			$events = $wpdb->get_results( $event_query );
			if ( $wpdb->num_rows > 0 ) {

				/* Sorting events by start datetime when search */

				$sort_events = array();
				foreach ($events as $value){
					$event_id     = absint($value->ID);
					$event_filter = new Event($event_id);
					$sort_events[] = strtotime(substr($event_filter->get_start_date(), 11, 8));
				}
				array_multisort($sort_events, SORT_ASC, $events);

				foreach ( $events as $event ) {
					$event_id     = absint($event->ID);
					$event_filter = new Event( $event_id );

					if ( ! empty( $selected_categories ) ) {
						if ( $post_type === Organizer::get_post_type() ) {
							$organizers = $event_filter->get_event_organizer();
							$org_result = array_intersect( $organizers, $selected_categories );
							$result     = ( ! empty( $org_result ) ) ? true : false;
						} elseif ( $post_type === Venue::get_post_type() ) {
							$venue  = $event_filter->get_event_venue();
							$result = in_array( $venue, $selected_categories );
						} else {
							$result = true;
						}
					} else {
						$result = true;
					}

					if ( true === $result ) {
						array_push( $searched_events_id, $event_id );
					}
				}
			}
		}

		return $searched_events_id;
	}

	/**
	 * @param $startDate \DateTime
	 * @param $endDate \DateTime
	 * @param $eventInterval
	 * @param $repeatInterval
	 * @param $maxDate \DateTime
	 * @param $repeatType
	 * @return array
	 */
	public static function getRepeatedEventsDateRange( $startDate, $endDate, $eventInterval, $repeatInterval, $maxDate, $minDate, $repeatType, $all_date ) {
		if ( $all_date === 'all_day' ) {
			$format = "m/d/Y";
		} else {
			$format = "m/d/Y h:i a";
		}
		$dateRange[] = self::eventDateRange( date( $format, strtotime( $startDate->format( 'Y-m-d h:i a' ) ) ), date( $format, strtotime( $endDate->format( 'Y-m-d h:i a' ) ) ) );

		/* week */
		if ( $repeatType === 'Week' ) {
			$repeatType     = 'Day';
			$repeatInterval = $repeatInterval * 7;
		}
		/* week end */

		$interval     = $startDate->diff( $minDate );
		$intervalDays = intval( $interval->format( '%R%a' ) );

		if ( $startDate > $minDate && $startDate < $maxDate  ) { //&& $repeatType === 'Day'
			$nextRepeatingDateInterval = $repeatInterval + $eventInterval;
		} else {
			if ( $intervalDays < 0 ) {
				return $dateRange;
			}

			$nextRepeatingDateInterval = $intervalDays - $intervalDays % ( $repeatInterval + $eventInterval );
		}

		$year = intval( $interval->format( '%y' ) );

		/* month */
		if ( $repeatType === 'Month' ):
			$month = intval( $interval->format( '%m' ) );

			if ( $year > 0 ) {
				$month = $year * 12 + $month;
			}
			$nextRepeatingDateInterval = $month + $repeatInterval - ( $month % $repeatInterval );
		/* year */
		elseif ( $repeatType === 'Year' ):
			$nextRepeatingDateInterval = $year + $repeatInterval - ( $year % $repeatInterval );
		endif;
		/* year end */

		$startDate = date( $format, strtotime( " +" . $nextRepeatingDateInterval . " " . $repeatType . $startDate->format( 'Y-m-d h:i a' ) ) );
		$endDate   = date( $format, strtotime( $startDate . " +" . $eventInterval . " days" ) );

		$endDateFormatted = date( 'Y-m-d', strtotime( $endDate ) );
		$maxDateFormatted = date( 'Y-m-d', strtotime( $maxDate->format( 'Y-m-d h:i a' ) ) );
		array_push( $dateRange, self::eventDateRange( $startDate, $endDate ) );

		$dateRangeDiff = array_diff($dateRange[0], $dateRange[1]);

		if(empty($dateRangeDiff)){
			array_splice($dateRange,1);
		}

//		if ( $dateRange[0] === $dateRange[1] ) {
//			$dateRange = array_values( array_unique( $dateRange ) );
//		}

		while ( $endDateFormatted < $maxDateFormatted ) {
			$startDate        = date( $format, strtotime( ( ( $repeatType == "Month" || $repeatType == "Year" ) ? $startDate : $endDate ) . " + " . $repeatInterval . " " . $repeatType ) );
			$endDate          = date( $format, strtotime( $startDate . " + " . $eventInterval . " days" ) );
			$endDateFormatted = date( 'Y-m-d', strtotime( $endDate ) );
			array_push( $dateRange, self::eventDateRange( $startDate, $endDate ) );
		}

		return $dateRange;
	}

	/**
	 * @param $startDate
	 * @param $endDate
	 * @return array Retrieve all dates between start and end dates
	 * Retrieve all dates between start and end dates
	 */
	public static function eventDateRange( $startDate, $endDate ) {
		$eventDate    = array();
		$start_period = substr( $startDate, 17, 2 );
		$start_hour   = 0;
		$start_min    = 0;
		if ( $start_period ) {
			$start_hour = substr( $startDate, 11, 2 );
			$start_min  = substr( $startDate, 14, 2 );
			if ( $start_period === 'pm' && $start_hour !== "12" ) {
				$start_hour += 12;
			}
			if ( $start_period === 'am' && $start_hour === "12" ) {
				$start_hour -= 12;
			}
		}

		$end_period = substr( $endDate, 17, 2 );
		$end_hour   = 0;
		$end_min    = 0;
		if ( $end_period ) {
			$end_hour = substr( $endDate, 11, 2 );
			$end_min  = substr( $endDate, 14, 2 );
			if ( $end_period === 'pm' && $end_hour !== "12" ) {
				$end_hour += 12;
			}
			if ( $end_period === 'am' && $end_hour === "12" ) {
				$end_hour -= 12;
			}
		}

		$dateFrom = mktime( $start_hour, $start_min, 0, substr( $startDate, 0, 2 ), substr( $startDate, 3, 2 ), substr( $startDate, 6, 4 ) );
		$dateTo   = mktime( $end_hour, $end_min, 0, substr( $endDate, 0, 2 ), substr( $endDate, 3, 2 ), substr( $endDate, 6, 4 ) );

		if ( date( 'Y-m-d', $dateTo ) >= date( 'Y-m-d', $dateFrom ) ) {
			array_push( $eventDate, ( $start_period ) ? date( 'Y-m-d h:i a', $dateFrom ) : date( 'Y-m-d', $dateFrom ) ); // first entry

			while ( date( 'Y-m-d', $dateFrom ) < date( 'Y-m-d', $dateTo ) ) {
				$dateFrom += 86400; // add 24 hours
				array_push( $eventDate, ( $start_period ) ? date( 'Y-m-d h:i a', $dateFrom ) : date( 'Y-m-d', $dateFrom ) );
			}
		}

		return $eventDate;
	}

}