<?php
/**
  * Plugin Name: dxw Security
  * Plugin URI: https://wordpress.org/plugins/dxw-security/
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
    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_init', array($this, 'add_security_column'));
    add_action('admin_init', array($this, 'add_dashboard_widget'));
  }

  public function enqueue_scripts($hook) {
    // TODO: find a better way to do this conditional
    //    and/or split the css up into plugins page/dashboard/common
    //    and/or move those enqueues into the relevant classes
    // TODO: does using index.php here mean that this gets included on the user-facing index page?
    if('plugins.php' != $hook && 'index.php' != $hook) { return; }

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