<?php
/**
 * @var Event $event
 */
?>
<table id="event_date" class="event_date">
    <tr class="event_field all_day_field">
        <td><?php _e('All Day', 'gd-calendar'); ?></td>
        <input type="hidden" name="all_day" value="0" />
        <td colspan="3">
            <input type="checkbox" id="all_day" name="all_day" value="all_day" <?php checked($event->get_all_day(), 'all_day'); ?>>
            <label class="gd_calendar_switch_checkbox" for="all_day"></label>
        </td>
    </tr>
    <tbody id="date_group">
    <tr class="event_field">
        <td><?php _e('Start', 'gd-calendar'); ?></td>
        <td colspan="3">
            <div><input autocomplete="off" type="text" id="start_date" name="start_date" value="<?php if(($event->get_start_date() == true) && ( \GDCalendar\Core\Validator::validateDate($event->get_start_date()) == true)) echo $event->get_start_date(); ?>"></div>
            <span class="error-msg error-start hide"><?php _e('Please fill start date', 'gd-calendar'); ?></span>
        </td>
    </tr>
    <tr class="event_field">
        <td><?php _e('End', 'gd-calendar'); ?></td>
        <td colspan="3">
            <div><input autocomplete="off" type="text" id="end_date" name="end_date" value="<?php if(($event->get_end_date() == true) && (\GDCalendar\Core\Validator::validateDate($event->get_end_date()) == true)) echo $event->get_end_date(); ?>"></div>
            <span class="error-msg error-end hide"><?php _e('Please fill end date', 'gd-calendar'); ?></span>
            <span class="error-msg wrong-end hide"><?php _e('Wrong end date given', 'gd-calendar'); ?></span>
        </td>
    </tr>
    </tbody>
    <tr class="event_field">
        <td><?php _e('Timezone', 'gd-calendar'); ?></td>
        <td colspan="3">
            <select id="timezone" name="timezone">
                <?php
                if($event->get_timezone() == true) {
                    echo wp_timezone_choice( $event->get_timezone() );
                }
                else {
                    echo wp_timezone_choice("UTC+0");
                }
                ?>
            </select>
        </td>
    </tr>
    <tr class="event_field repeat_field">
        <td><?php _e('Repeat', 'gd-calendar'); ?></td>
        <input type="hidden" name="repeat" value="0" />
        <td colspan="3"><input type="checkbox" id="repeat" name="repeat"  value="repeat" <?php checked($event->get_repeat(), 'repeat'); ?>>
            <label class="gd_calendar_switch_checkbox" for="repeat"></label>
            <span><?php _e('Rate', 'gd-calendar'); ?></span>
            <select name="repeat_type" id="repeat_type" disabled>
                <option value="choose_type"><?php _e('Choose', 'gd-calendar'); ?></option>
                <?php
                foreach (\GDCalendar\Models\PostTypes\Event::$repeat_types as $type_key => $type){
                    ?>
                    <option <?php selected($event->get_repeat_type(), $type_key); ?> value="<?php echo $type_key; ?>" ><?php echo $type; ?></option>
                    <?php
                }
                ?>
            </select>
            <select name="repeat_day" id="repeat_day">
                <?php
                for ($day = 1; $day <= 31; $day++)
                { ?>
                    <option <?php selected($event->get_repeat_day(), $day); ?> value="<?php echo $day; ?>" ><?php echo $day; ?></option>
                    <?php
                } ?>
            </select>
            <select name="repeat_week" id="repeat_week">
                <?php
                for ($week = 1; $week <= 52; $week++)
                { ?>
                    <option <?php selected($event->get_repeat_week(), $week); ?> value="<?php echo $week; ?>" ><?php echo $week; ?></option>
                    <?php
                } ?>
            </select>
            <select name="repeat_month" id="repeat_month">
                <?php
                for ($month = 1; $month <= 12; $month++)
                { ?>
                    <option <?php selected($event->get_repeat_month(), $month); ?> value="<?php echo $month; ?>" ><?php echo $month; ?></option>
                    <?php
                } ?>
            </select>
            <select name="repeat_year" id="repeat_year">
                <?php
                for ( $year = 1; $year <= 5; $year++)
                { ?>
                    <option <?php selected($event->get_repeat_year(), $year); ?> value="<?php echo $year; ?>" ><?php echo $year; ?></option>
                    <?php
                } ?>
            </select>
        <td/>
    </tr>
    <tr class="venue_cost">
        <td><?php _e('Cost', 'gd-calendar'); ?></td>
        <td>
            <input type="text" id="cost" name="cost" value="<?php if($event->get_cost() == true) echo esc_html($event->get_cost()); ?>">
        </td>
        <td>
            <select name="currency" id="currency">
                <option><?php _e('Select Currency', 'gd-calendar'); ?></option>
                <?php
                $currencies = \GDCalendar\Helpers\Currencies::getCurrencyName();
                foreach ($currencies as $cur_key => $cur_name) {
                    $symbol = \GDCalendar\Helpers\Currencies::getCurrencySymbol($cur_key);
                    ?>
                    <option value="<?php echo $cur_key; ?>" <?php selected($cur_key, $event->get_currency()); ?>><?php echo esc_html($cur_name) . ' (' . $symbol . ')'; ?></option>
                    <?php
                } ?>
            </select>
        </td>
        <td>
            <select name="currency_position" id="currency_position">
                <option value="before" <?php selected('before', $event->get_currency_position()); ?>><?php _e('Before', 'gd-calendar'); ?></option>
                <option value="after" <?php selected('after', $event->get_currency_position()); ?>><?php _e('After', 'gd-calendar'); ?></option>
            </select>
        </td>
    </tr>
</table>
