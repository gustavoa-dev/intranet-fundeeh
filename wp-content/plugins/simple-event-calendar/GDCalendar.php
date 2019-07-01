<?php

namespace GDCalendar;

use GDCalendar\Controllers\Admin\AdminController;
use GDCalendar\Controllers\Admin\AjaxController as AdminAjax;
use GDCalendar\Controllers\Frontend\AjaxController as FrontendAjax;
use GDCalendar\Controllers\Frontend\FrontendController;
use GDCalendar\Controllers\PostTypesController;
use GDCalendar\Controllers\Widgets\WidgetsController;

class GDCalendar
{
    /**
     * Version of plugin
     * @var string
     */
    public $Version = '1.0.9';

    /**
     * Instance of AdminController to manage admin
     * @var AdminController instance
     */
    public $Admin;

    /**
     * Classnames of migration classes
     *
     * @var array
     */
    private $MigrationClasses;

    /**
     * The single instance of the class.
     *
     * @var GDCalendar
     */
    protected static $_instance = null;

    /**
     * @static
     * @see \GDCalendar()
     * @return GDCalendar - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * GDCalendar Constructor.
     */
    private function __construct() {

    	$this->constants();
        $this->MigrationClasses = array(
            'GDCalendar\Controllers\PostTypesController',
        );

        add_action('init', array($this, 'init'), 0);
        add_action( 'widgets_init', array( 'GDCalendar\Controllers\Widgets\WidgetsController', 'init' ) );
    }

	public function constants(){
		define( 'GDCALENDAR_IMAGES_URL', untrailingslashit($this->pluginUrl() ) . '/resources/assets/images/');
	}

    private function checkVersion(){
        if (get_option('gd_calendar_version') !== $this->Version) {
            $this->runMigrations();
            update_option('gd_calendar_version', $this->Version);

            if(!get_option('gd_calendar_plugin_installed')){
            	add_option('gd_calendar_plugin_installed', date('Y-m-d H:i:s'));
            }
        }
    }

    public function init()
    {
        $this->checkVersion();

        if(defined('DOING_AJAX')){
            AdminAjax::init();
            FrontendAjax::init();
        }

        PostTypesController::createPostTypes();

        WidgetsController::init();

        if ( is_admin() ) {
            $this->Admin = new AdminController();
        }else{
            new FrontendController();
        }

    }

    private function runMigrations(){
        if (empty($this->MigrationClasses)) {
            return;
        }

        foreach ($this->MigrationClasses as $className) {
            if (method_exists($className, 'run')) {
                call_user_func(array($className, 'run'));
            } else {
                throw new \Exception('Specified migration class ' . $className . ' does not have "run" method');
            }
        }
    }

    /**
     * @return string
     */
    public function viewPath()
    {
        return apply_filters('gd_calendar_path', 'GDCalendar/');
    }

    /**
     * @return string
     */
    public function pluginPath()
    {
        return plugin_dir_path(__FILE__);
    }

    /**
     * @return string
     */
    public function pluginUrl()
    {
        return plugins_url('', __FILE__);
    }

    public function ajaxUrl()
    {
        return admin_url('admin-ajax.php');
    }

}