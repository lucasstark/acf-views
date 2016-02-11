<?php

/*
  Plugin Name: Advanced Custom Fields: Views
  Plugin URI: https://github.com/lucasstark/acf-views
  Description: Creates a function acf_view(), similar to acf_form(), which shows the data for a post rather than showing the ACF input fields. 
  Version: 0.5.0
  Author: Lucas Stark
  Author URI: https://github.com/lucasstark/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-views', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );


class ACF_Views_Main {
	public $assets_version = '1.0.0';
	
	/**
	 *
	 * @var ACF_Views_Main
	 */
	private static $instance;

	public static function register() {
		if ( self::$instance == null ) {
			self::$instance = new ACF_Views_Main();
		}
	}
	
	/**
	 * 
	 * @return ACF_Views_Main
	 */
	public static function instance(){
		self::register();
		return self::$instance;
	}

	private function __construct() {
		require_once 'inc/acf-views-conditional-logic.php';
		require_once 'inc/acf-views-api.php';
		require_once 'inc/acf-views-view.php';
		
		add_action( 'wp_enqueue_scripts', array($this, 'on_enqueue_scripts') );
	}

	public function on_enqueue_scripts() {
		wp_enqueue_style( 'acf-views-frontend', $this->plugin_url() . '/assets/css/acf-views.css', null, $this->assets_version );
	}

	/**
	 * Get the plugin url.
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

ACF_Views_Main::register();

/**
 * 
 * @return ACF_Views_Main
 */
function ACF_Views() {
	return ACF_Views_Main::instance();
}