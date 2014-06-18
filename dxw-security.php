<?php
/**
  * Plugin Name: dxw Security
  * Plugin URI: https://security.dxw.com/
  * Description: Pulls plugin review information from dxw Security into the wordpress plugins screen
  * Version: 0.2.4
  * License: GPLv2
  * Author: dxw
  * Author URI: http://dxw.com/
*/

// Prevent Full Path Disclosure
defined('ABSPATH') OR exit;

// CONFIG:
if (!defined('DXW_SECURITY_API_ROOT')) {
  define('DXW_SECURITY_API_ROOT', 'http://app.security.dxw.com/api');
}
if (!defined('DXW_SECURITY_CACHE_RESPONSES')) {
  define('DXW_SECURITY_CACHE_RESPONSES', true);
}

// CONSTANTS:
// How many failed requests will we tolerate?
define('DXW_SECURITY_FAILURE_lIMIT', 5);
// The URL we link to when we don't have any info about a plugin
define('DXW_SECURITY_PLUGINS_URL', 'https://security.dxw.com/plugins/');

require(dirname(__FILE__) . '/lib/dashboard_widget.class.php');
require(dirname(__FILE__) . '/lib/plugin_review_column.class.php');

class dxw_Security {
  public function __construct() {
    add_action('admin_print_scripts-index.php', array($this, 'enqueue_scripts'));
    add_action('admin_print_scripts-plugins.php', array($this, 'enqueue_scripts'));

    add_action('admin_init', array($this, 'add_security_column'));
    add_action('admin_init', array($this, 'add_dashboard_widget'));
  }

  public function enqueue_scripts($hook) {
    wp_enqueue_style('dxw-security-plugin-styles', plugins_url('/assets/main.min.css' , __FILE__));
    wp_enqueue_script('dxw-security-plugin-scripts', plugins_url('/assets/main.min.js' , __FILE__));

    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-dialog');
  }

  public function add_security_column() {
    new dxw_security_Plugin_Review_Column;
  }

  public function add_dashboard_widget() {
    new dxw_security_Dashboard_Widget;
  }
}

new dxw_Security();