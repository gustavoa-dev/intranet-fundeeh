<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();

$queried_object = get_queried_object();
$event_tag = new \GDCalendar\Models\Taxonomy\EventTag($queried_object->term_id);

$events = \GDCalendar\Models\PostTypes\Event::get(array(
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => \GDCalendar\Models\Taxonomy\EventTag::get_taxonomy(),
            'terms' => $event_tag->get_id(),
        ),
    ),
));
?>
<section id="primary" class="content-area">
    <div id="content" class="site-content" role="main">
        <div class="gd_calendar_event_category_container gd_calendar_body">
            <?php
            if (!empty($events)):
                foreach ($events as $event) : ?>

                    <div class="event_view">
                        <a class="gd_calendar_event_title" href="<?php echo get_the_permalink($event->get_id()); ?>">
                            <h2><?php echo strtoupper($event->get_post_data()->post_title); ?></h2>
                        </a>
                        <table id="event_gen_option" class="event_gen_option">
                            <tr class="event_front_field">
                                <td><?php _e('Start Date', 'gd-calendar'); ?>:</td>
                                <td><?php echo date("jS F, Y", strtotime(esc_html($event->get_start_date()))); ?></td>
                            </tr>
                            <tr class="event_front_field">
                                <td><?php _e('End Date', 'gd-calendar'); ?>:</td>
                                <td>
                                    <?php echo date("jS F, Y", strtotime(esc_html($event->get_end_date()))); ?>
                                </td>
                            </tr>
                            <?php
                            $description = $event->get_post_data()->post_content;
                            if($description){
                                ?>
                                <tr class="event_front_field">
                                    <td><?php _e('Description', 'gd-calendar'); ?>:</td>
                                    <td>
                                        <?php echo $description; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </div>
                <?php endforeach;
            else: ?>
                <h2 class=""><?php _e('Nothing found here', 'gd-calendar'); ?></h2>
            <?php
            endif; ?>
        </div>
    </div>
</section>
<?php
get_footer();
?>
