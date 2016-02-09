<?php
/*
Plugin Name: Plivo for Wordpress
Version: 1.0
Plugin URI: http://wpdevph.com/
Description: Click to call to company number and send voice call notification.
Author: eugenealegiojo
Author URI: http://wpdevph.com
Text Domain: plivo-wp
License: GPLv2
*/

if(!defined('ABSPATH')){
    exit;
}
class Plivo_WP {
	/**
     * @var Plivo_WP Singleton implementation
     */
    private static $_instance = null;

    /**
     * @var RestAPI The plivo API.
     */
    public static $api;

    /**
     * @var string "from" phone number.
     */
    public static $main_number = "";

    /**
     * Constructor method
     *
     * Bootstraps the plugin.
     */
    function __construct() {
    	// Register the autoloader classes.
    	spl_autoload_register(array($this, 'autoload'));
    	spl_autoload_register(array($this, 'autoload_plivo'));

        // Use the fallback HTTP_Request2 if the PEAR package is not available.
        /*if(!$this->HTTP_Request2_Available()) {
            ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $this->plugin_path() . 'library');
        }*/
        
    	$this->init();
    }

    /**
     * Returns an instance of the WooCommerce_Plivo class.
     *
     * @return WooCommerce_Plivo
     */
    public static function instance() {
        if(is_null(self::$_instance)) {
            // Create instance if not set.
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Initialize.
    */
    private function init() {
    	if ( is_admin() ) {
    		$this->admin_init();
    	}

        $this->frontend_init();
    }

    /**
     * Loads the Plivo API library as soon as it is needed.
     *
     * @param $class string
     */
    public function autoload_plivo($class) {
        if($class == "RestAPI") {
            require_once $this->plugin_path() . 'library/plivo/plivo.php';
        }
    }

    /**
     * Autoloads the WooCommerce Plivo classes whenever they are needed.
     *
     * @param $class
     */
    public function autoload($class) {
        if(strpos($class, 'PWP') !== 0) {
            return;
        }

        $class_exploded = explode('_', $class);

        $filename = strtolower(implode('-', $class_exploded)) . '.php';

        // first try the directory
        $file = 'includes/' . strtolower($class_exploded[1]) . '/' . $filename;

        if(is_readable($this->plugin_path() . $file)) {
            require_once $this->plugin_path() . $file;

            return;
        }

        // try without a subdirectory
        $filename = strtolower(implode('-', $class_exploded)) . '.php';

        $file = 'includes/' . $filename;

        if(is_readable($this->plugin_path() . $file)) {
            require_once $this->plugin_path() . $file;

            return;
        }

        return;
    }

    /**
     * Checks for availability of the HTTP_Request2 PEAR package
     *
     * @return bool
     */
    private function HTTP_Request2_Available() {
        if(class_exists('HTTP_Request2', false)) return true;

        @$include = include('HTTP/Request2.php');

        return $include === 1;
    }

    /**
     * @return string The plugin URL
     */
    public static function plugin_url() {
        return plugins_url('/', __FILE__);
    }

    /**
     * @return string The plugin path
     */
    public function plugin_path() {
        return plugin_dir_path(__FILE__);
    }

    /**
     * @return string The plugin basename
     */
    public function plugin_basename() {
        return plugin_basename(__FILE__);
    }

    /**
	 * Initializes all of the admin classes.
 	 */
 	public function admin_init(){
 		new PWP_Admin_Settings();
 	}

    /**
     * Initializes all the methods for the frontend
     */
    public function frontend_init(){
        if ( PWP_Plivo_Auth::is_authenticated() ) {
            new PWP_Frontend();    
        }        
    }
}
$Plivo_WP = Plivo_WP::instance();