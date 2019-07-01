<?php

/**
 * Welcome Page.
 *
 * @package       advanced-classifieds-and-directory-pro
 * @subpackage    advanced-classifieds-and-directory-pro/admin
 * @copyright     Copyright (c) 2015, PluginsWare
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since         1.0.0
 */
 
// Exit if accessed directly
if( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Admin_Welcome Class
 *
 * @since    1.6.1
 * @access   public
 */
class ACADP_Admin_Welcome {
	
	/**
	 * Add welcome page sub menu.
	 *
	 * @since    1.6.1
	 */
	public function add_welcome_page_menu() {
	
		add_dashboard_page(
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'acadp_welcome',
			array( $this, 'display_welcome_content' )
		);

		add_dashboard_page(
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'acadp_support',
			array( $this, 'display_welcome_content' )
		);

		add_dashboard_page(
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			__( 'Welcome - Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'acadp_video_intro',
			array( $this, 'display_welcome_content' )
		);

		// Now remove the menus so plugins that allow customizing the admin menu don't show this
		remove_submenu_page( 'index.php', 'acadp_welcome' );
		remove_submenu_page( 'index.php', 'acadp_support' );
		remove_submenu_page( 'index.php', 'acadp_video_intro' );
	
	}
	
	/**
	 * Display welcome page content.
	 *
	 * @since    1.6.1
	 */
	public function display_welcome_content() {
		$tabs = array(
			'acadp_welcome'     => __( 'Getting Started', 'advanced-classifieds-and-directory-pro' ),
			'acadp_video_intro' => __( 'Video Intro', 'advanced-classifieds-and-directory-pro' ),
			'acadp_support'     => __( 'Support', 'advanced-classifieds-and-directory-pro' )
		);
		
		$active_tab = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'acadp_welcome';

		require_once ACADP_PLUGIN_DIR . 'admin/partials/welcome/acadp-admin-welcome-display.php';		
	}
	
}
