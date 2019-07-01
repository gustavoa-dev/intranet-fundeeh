<?php

/**
 * Welcome Page.
 *
 * @link    http://pluginsware.com/
 * @since   1.6.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div id="acadp-welcome" class="wrap about-wrap full-width-layout acadp-welcome">

	<h1><?php 
printf( __( 'Advanced Classifieds & Directory Pro - %s', 'advanced-classifieds-and-directory-pro' ), ACADP_VERSION_NUM );
?></h1>
    
    <p class="about-text">
		<?php 
_e( 'Build any kind of directory site: classifieds, cars, bikes & other vehicles dealers site, pets, real estate portal, yellow pages, etc...', 'advanced-classifieds-and-directory-pro' );
?>
    </p>
        
    <?php 
?>

	<div class="wp-badge"><?php 
printf( __( 'Version %s', 'advanced-classifieds-and-directory-pro' ), ACADP_VERSION_NUM );
?></div>
    
    <h2 class="nav-tab-wrapper wp-clearfix">
		<?php 
foreach ( $tabs as $tab => $title ) {
    $class = ( $tab == $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab' );
    printf(
        '<a href="%s" class="%s">%s</a>',
        esc_url( admin_url( add_query_arg( 'page', $tab, 'index.php' ) ) ),
        $class,
        $title
    );
}
?>
    </h2>

    <?php 

if ( 'acadp_welcome' == $active_tab ) {
    ?>
        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 0 );
    ?></strong> 
            &rarr;
            <?php 
    _e( 'Install and Activate <strong>Advanced Classifieds & Directory Pro</strong>', 'advanced-classifieds-and-directory-pro' );
    ?>
        </p>

        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 1 );
    ?></strong> 
            &rarr;
            <code><?php 
    _e( 'Optional', 'advanced-classifieds-and-directory-pro' );
    ?></code>
            <a href="<?php 
    echo  esc_url( admin_url( 'edit-tags.php?taxonomy=acadp_locations&post_type=acadp_listings' ) ) ;
    ?>">
                <?php 
    _e( 'Add Locations', 'advanced-classifieds-and-directory-pro' );
    ?>
            </a>
        </p>

        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 2 );
    ?></strong> 
            &rarr;
            <a href="<?php 
    echo  esc_url( admin_url( 'edit-tags.php?taxonomy=acadp_categories&post_type=acadp_listings' ) ) ;
    ?>">
                <?php 
    _e( 'Add Categories', 'advanced-classifieds-and-directory-pro' );
    ?>
            </a>
        </p>

        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 3 );
    ?></strong> 
            &rarr;
            <code><?php 
    _e( 'Optional', 'advanced-classifieds-and-directory-pro' );
    ?></code>
            <a href="<?php 
    echo  esc_url( admin_url( 'edit.php?post_type=acadp_fields' ) ) ;
    ?>">
                <?php 
    _e( 'Add Custom Fields', 'advanced-classifieds-and-directory-pro' );
    ?>
            </a>
        </p>

        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 4 );
    ?></strong> 
            &rarr;
            <a href="<?php 
    echo  esc_url( admin_url( 'post-new.php?post_type=acadp_listings' ) ) ;
    ?>">
                <?php 
    _e( 'Add Listings', 'advanced-classifieds-and-directory-pro' );
    ?>
            </a>
        </p>

        <p class="about-description">
            <strong><?php 
    printf( __( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 5 );
    ?></strong> 
            &rarr;
            <code><?php 
    _e( 'Showing in the front-end', 'advanced-classifieds-and-directory-pro' );
    ?></code>
            <?php 
    printf( __( 'The plugin has added few pages dynamically in your website to display locations, categories, listings and to allow your users to submit their listings from the front-end of your website. You can find these pages under the <a href="%s">Pages</a> menu. Simply find and add them as menu items under <a href="%s">Appearance &rarr; menus</a>. That\'s it.', 'advanced-classifieds-and-directory-pro' ), esc_url( admin_url( 'edit.php?post_type=page' ) ), esc_url( admin_url( 'nav-menus.php' ) ) );
    ?>
        </p>

        <p>
            <?php 
    printf( __( 'These are just the basic steps to getting started with the plugin. The plugin has a lot more features, 100+ settings, widget options, etc. Please <a href="%s" target="_blank">refer</a> for more advanced tutorials.', 'advanced-classifieds-and-directory-pro' ), 'https://pluginsware.com/documentation/getting-started/' );
    ?>
        </p>
    <?php 
}

?>

    <?php 
if ( 'acadp_video_intro' == $active_tab ) {
    ?>
        <div class="headline-feature feature-video">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/0ALU7FqfGAM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    <?php 
}
?>
    
    <?php 

if ( 'acadp_support' == $active_tab ) {
    ?>
        <p class="about-description"><?php 
    _e( 'Need Help?', 'advanced-classifieds-and-directory-pro' );
    ?></p>
        
        <div class="changelog">    
            <div class="two-col">
                <div class="col">
                    <h3><?php 
    _e( 'Phenomenal Support', 'advanced-classifieds-and-directory-pro' );
    ?></h3>
                    
                    <p>
                        <?php 
    printf( __( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply submit your question using our <a href="%s" target="_blank">support form</a>.', 'advanced-classifieds-and-directory-pro' ), 'https://wordpress.org/support/plugin/advanced-classifieds-and-directory-pro' );
    ?>
                    </p>
                </div>
                
                <div class="col">
                    <h3><?php 
    _e( 'Need Even Faster Support?', 'advanced-classifieds-and-directory-pro' );
    ?></h3>
                    
                    <p>
                        <?php 
    printf( __( 'Our <a href="%s" target="_blank">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'advanced-classifieds-and-directory-pro' ), 'https://pluginsware.com/submit-a-ticket/' );
    ?>
                    </p>
                </div>                
            </div>
        </div>
    <?php 
}

?>
	
</div>