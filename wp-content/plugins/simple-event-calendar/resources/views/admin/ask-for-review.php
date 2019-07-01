    <div class="gd_calendar_review_notice">
        <div class="gd_calendar_first_col">
        </div>
        <div class="gd_calendar_second_col">
            <div>
                <?php _e('Your opinion matters! Leave a Review!', 'gd-calendar'); ?>
            </div>
            <div>
                <?php _e('We hope you’ve enjoyed using GrandWP Event Calendar plugin! Would you consider leaving us a review on WordPress?', 'gd-calendar'); ?>
            </div>
            <div>
                <ul>
                    <li>
                        <a href="//wordpress.org/support/plugin/simple-event-calendar/reviews/"
                           target="_blank"><?php _e('Sure!', 'gd-calendar'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo add_query_arg('gd_calendar_ignore_notice', 'true'); ?>"><?php _e('Already Left :)', 'gd-calendar'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo add_query_arg('gd_calendar_delay_notice', 'true'); ?>"><?php _e('Maybe Later', 'gd-calendar'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo add_query_arg('gd_calendar_ignore_notice', 'true'); ?>"><?php _e('Don\'t Show This!', 'gd-calendar'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="gd_calebdar_third_col">
            <img src="<?php echo GDCALENDAR_IMAGES_URL . 'grandwp-bg.png'; ?>"/>
        </div>
    </div>