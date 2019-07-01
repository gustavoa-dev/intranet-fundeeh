<?php
/**
 * Calendar Month Sidebar View
 * @var $sidebar_month \GDCalendar\Helpers\Builders\MonthCalendarBuilder
 */
    $calendar_id = $sidebar_month->getPostId();
    ?>
<!--    <div class="gd_calendar_sidebar" data-calendar-id="--><?php //echo $calendar_id; ?><!--">-->
    <?php

    $sidebar = true;
    $dateComponents = $sidebar_month->getDateComponents();
    $currentMonthName = $dateComponents['month'];
    $currentYear = $sidebar_month->getYear();
    $dayOfWeek = $dateComponents['wday'];
    $currentMonth = str_pad($sidebar_month->getMonth(), 2, "0", STR_PAD_LEFT);

    $currentDateObj = new DateTime($sidebar_month->getCurrentDate());
    $currentDate = $currentDateObj->format('m/d/Y');
    $tomorrowDateObj = new DateTime($sidebar_month->getCurrentDate());
    $tomorrowDateObj->modify('+1 day');
    $tomorrowDate = $tomorrowDateObj->format('m/d/Y');

    $hold_month = $sidebar_month->getMonth() . '/01/' . $sidebar_month->getYear();

    $first_week_number = absint($sidebar_month->getCurrentWeekdayNumber($sidebar_month->getYear()."-".$currentMonth."-01"));
    $current_week_number = absint($sidebar_month->getCurrentWeekdayNumber($currentDate));
    ?>
    <div class="gd_calendar_small_date" data-date="<?php echo $hold_month; ?>">
        <span><?php echo $currentMonthName; ?></span>
        <span class="current_year_color"><?php echo $currentYear; ?></span>
    </div>
    <div class="gd_calendar_arrow_box">
        <a href="#" id="gd_calendar_left_arrow" data-type="left_arrow"><span> &larr;</span></a>
        <a href="#" id="gd_calendar_right_arrow" data-type="right_arrow"><span>	&rarr;</span></a>
    </div>
    <div class="gd_calendar_small">
        <table class='gd_calendar_small_table'>
            <tr><?php
                foreach($sidebar_month->getDaysOfWeek() as $key => $day) {
                    ?>
                    <th class='gd_calendar_header_small'><?php echo $day; ?></th>
                    <?php
                }
                ?></tr><tr class="<?php echo ($first_week_number === $current_week_number) ? 'gd_calendar_current_week_number' : '' ?>"><?php
                for($i=1; $i <= $dayOfWeek; $i++){
                    echo '<td></td>';
                }
                $currentDay = 1;
                while ($currentDay <= $sidebar_month->getDaysCount()) {
                $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
                $date = $sidebar_month->getYear()."-$currentMonth-$currentDayRel";
                $week_number = absint($sidebar_month->getCurrentWeekdayNumber($date));
                if ($dayOfWeek == 7) {
                $dayOfWeek = 0;
                ?>
            </tr><tr class="<?php echo ($week_number === $current_week_number) ? 'gd_calendar_current_week_number' : '' ?>">
                <?php
                }
                $current_date = '';
                if($sidebar_month->getCurrentDate() === $date ) {
                    $current_date = 'gd_calendar_current_date_small';
                }
                ?>
                <td class='gd_calendar_day_small' rel='<?php echo $date; ?>'>
                    <div class="<?php echo $current_date; ?>">
                    <p><?php echo $currentDay; ?></p>
                    <?php
                    \GDCalendar\Helpers\View::render('frontend/calendar/events.php', array(
                        'searched_event' => $sidebar_month->getSearchedEvent(),
                        'date' => $date,
                        'sidebar' => $sidebar,
                        'calendar_id' => $calendar_id,
                        'builder' => $sidebar_month,
                    ));
                    ?>
                    </div>
                </td>
                <?php
                $currentDay++;
                $dayOfWeek++;
                }
                for( $i=1; $i <= (7- $dayOfWeek); $i++ ){
                    echo '<td></td>';
                }
                ?>
            </tr>
        </table>
    </div>
</div>