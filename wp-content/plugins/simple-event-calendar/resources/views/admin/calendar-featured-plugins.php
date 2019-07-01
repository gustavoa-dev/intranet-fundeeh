<?php
/**
 * Template for featured plugins page
 */

function activation_link_gdwp_forms( $plugin, $action = 'activate' ) {
	if ( strpos( $plugin, '/' ) ) {
		$plugin = str_replace( '\/', '%2F', $plugin );
	}
	$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
	$_REQUEST['plugin'] = $plugin;
	$url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
	return $url;
}

?>

<div class="wrap gdfrm_featured_plugins_container">
    <div class="gdfrm_content">


        <div class="single-plugin">
            <div class="plugin-thumb">
                <img src="<?php echo GDCALENDAR_IMAGES_URL.'/grandwp_forms.png';?>">
            </div>
            <div class="plugin-info">
                <div class="plugin-name">GrandWP Form Builder</div>
                <div class="plugin-desc">
                    Form Builder - Freemium form builder plugin for WordPress.It can be used to create highly interactive form with very little effort. It allows you to quickly create beautiful contact forms and comes with all the goodies you would need from a premium form plugin. Quickly build complex forms, for everything from complex quotes to booking forms and contact forms. And you can do all this without touching any code. Create multiple forms, and customize confirmation emails. It includes extra fields of any type and also supports Akismet and CAPTCHA.
                </div>
                <div class="plugin-buttons">

					<?php

					$slug = 'easy-contact-form-builder';
					$install_url = esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug));
					$activation_url = activation_link_gdwp_forms($slug.'/index.php', 'activate');
					$go_to_plugin_url = 'gdfrm';

					$plugin_dir = ABSPATH . 'wp-content/plugins/easy-contact-form-builder/';
					if ( is_dir($plugin_dir) && !is_plugin_active( 'easy-contact-form-builder/index.php' ) ) {
						?>
                        <a class="gwp_plugin_activate " id="activate_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_forms" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Form Builder</a>
						<?php
					}
					else if( ! is_dir($plugin_dir) ) {
						?>
                        <a class="gwp_plugin_install" id="install_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Install</a>
                        <a class="gwp_plugin_activate hidden" id="activate_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_forms" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Form Builder</a>
						<?php
					}

					if ( is_plugin_active( 'easy-contact-form-builder/index.php' ) ) {
						?>
                        <a class="gwp_goto_plugin" id="go_to_forms" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Form Builder</a>
					<?php } ?>
                    <a href="https://demo.grandwp.com/wordpress-contact-form-builder-basic-contact-form/" target="_blank">
						<?php _e('Demo', 'gd-calendar');?>
                    </a>
                </div>
            </div>
        </div>

        <div class="single-plugin">
            <div class="plugin-thumb">
                <img src="<?php echo GDCALENDAR_IMAGES_URL.'/grandwp_lightbox.png';?>" alt="Plugin Icon" />
            </div>
            <div class="plugin-info">
                <div class="plugin-name">GrandWP Lightbox</div>
                <div class="plugin-desc">
                    Lightbox - Grand LIghtbox is offering a quick and simple lightbox for your pages
                    and posts. It comes with friendly appearance and wide range of settings as the
                    aforementioned plugins, but if you’re looking for a minimalist way of opening
                    your images in a lightbox style, you’ll love this one. GrandLightbox allows you
                    to add beautiful features. You can display an image separately or in a slideshow,
                    and youcan also set the transitions, animations, slideshows’ speed, overlay opacity, etc.
                </div>
                <div class="plugin-buttons">
					<?php

					$slug = 'responsive-lightbox-popup';
					$install_url = esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug));
					$activation_url = activation_link_gdwp_forms($slug.'/index.php', 'activate');
					$go_to_plugin_url = 'gd_lightbox';

					$plugin_dir = ABSPATH . 'wp-content/plugins/responsive-lightbox-popup/';
					if ( is_dir($plugin_dir) && !is_plugin_active( 'responsive-lightbox-popup/index.php' ) ) {
						?>
                        <a class="gwp_plugin_activate " id="activate_lightbox_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_lightbox" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Lightbox</a>
						<?php
					}
					else if( ! is_dir($plugin_dir) ) {
						?>
                        <a class="gwp_plugin_install" id="install_lightbox_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Install</a>
                        <a class="gwp_plugin_activate hidden" id="activate_lightbox_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_lightbox" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Lightbox</a>
						<?php
					}

					if ( is_plugin_active( 'responsive-lightbox-popup/index.php' ) ) {
						?>
                        <a class="gwp_goto_plugin" id="go_to_lightbox" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Lightbox</a>
					<?php } ?>
                    <a href="https://demo.grandwp.com/wordpress-responsive-lightbox-demo/" target="_blank"> <?php _e('Demo', 'gd-calendar');?> </a>
                </div>
            </div>

        </div>

        <div class="single-plugin">
            <div class="plugin-thumb">
                <img src="<?php echo GDCALENDAR_IMAGES_URL.'/grandwp_gallery.png';?>" alt="Plugin Icon" />
            </div>
            <div class="plugin-info">
                <div class="plugin-name">GrandWP Gallery</div>
                <div class="plugin-desc">
                    Gallery - Various adjustable options to make galleries even more personalized. GrandWP Gallery plugin stands for intuitive and modern design combined with high functionality. We have gathered all essential options in one tool to meet all kind of requirements..
                </div>
                <div class="plugin-buttons">
					<?php

					$slug = 'photo-gallery-image';
					$install_url = esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug));
					$activation_url = activation_link_gdwp_forms($slug.'/index.php', 'activate');
					$go_to_plugin_url = 'gdgallery';

					$plugin_dir = ABSPATH . 'wp-content/plugins/photo-gallery-image/';
					if ( is_dir($plugin_dir) && !is_plugin_active( 'photo-gallery-image/index.php' ) ) {
						?>
                        <a class="gwp_plugin_activate " id="activate_gallery_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_gallery" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Gallery</a>
						<?php
					}
					else if( ! is_dir($plugin_dir) ) {
						?>
                        <a class="gwp_plugin_install" id="install_gallery_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Install</a>
                        <a class="gwp_plugin_activate hidden" id="activate_gallery_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>">Activation</a>
                        <a class="gwp_goto_plugin hidden" id="go_to_gallery" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Gallery</a>
						<?php
					}

					if ( is_plugin_active( 'photo-gallery-image/index.php' ) ) {
						?>
                        <a class="gwp_goto_plugin" id="go_to_gallery" href="admin.php?page=<?php echo $go_to_plugin_url; ?>" target="_blank">Go to Gallery</a>
					<?php } ?>

                    <a href="https://demo.grandwp.com/wordpress-photo-gallery-justified/" target="_blank">
						<?php _e('Demo', 'gd-calendar');?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function install_grandwp_plugin(strThis,installUrl,activateUrl) {
        strThis.parents('.single-plugin').addClass('strloading');
        jQuery(this).prop('disable',true);
        jQuery.ajax({
            method: "POST",
            url: installUrl,
        }).done(function() {
            jQuery.ajax({
                type: 'POST',
                url: jQuery("#verifyUrl").attr('data-url'),
                error: function()
                {
                    jQuery(".error_install").show();
                },
                success: function(response)
                {
                    activate_grandwp_plugin(strThis,activateUrl);

                    strThis.parents('.plugin-buttons').find('.gwp_plugin_install').addClass('hidden');
                    strThis.parents('.plugin-buttons').find('.gwp_plugin_activate').removeClass('hidden');
                }
            });
        })
    }

    function activate_grandwp_plugin(strThis,activate_url) {
        strThis.parents('.single-plugin').addClass('strloading');
        jQuery.ajax({
            method: "POST",
            url: activate_url,
        }).done(function() {

            jQuery.ajax({
                type: 'POST',
                url: jQuery("#verifyUrl").attr('data-url'),
                error: function()
                {
                    jQuery(".error_activate").removeClass('hidden');
                },
                success: function(response)
                {
                    strThis.parents('.single-plugin').removeClass('strloading');
                    strThis.parents('.plugin-buttons').find('.gwp_plugin_install').addClass('hidden');
                    strThis.parents('.plugin-buttons').find('.gwp_plugin_activate').addClass('hidden');
                    strThis.parents('.plugin-buttons').find('.gwp_goto_plugin').removeClass('hidden');
                }
            });
        })
    }

    jQuery(".gwp_plugin_install").on("click",function(){
        install_grandwp_plugin(jQuery(this),jQuery(this).attr("data-install-url"),jQuery(this).attr("data-activate-url"));
    });
    jQuery(".gwp_plugin_activate").on("click",function(){
        activate_grandwp_plugin(jQuery(this),jQuery(this).attr("data-activate-url"))
    });
</script>