<?php
/**
  * Plugin Name: dxw Security
  * Plugin URI: https://wordpress.org/plugins/dxw-security/
  * Description: Pulls plugin review information from dxw Security into the wordpress plugins screen
  * Version: 0.2.8
  * License: GPLv2
  * Author: dxw
  * Author URI: http://dxw.com/
*/
define('DXW_SECURITY_PLUGIN_VERSION', "0.2.8");

// Prevent Full Path Disclosure
defined('ABSPATH') OR exit;

// CONFIG:
if (!defined('DXW_SECURITY_API_ROOT')) {
  define('DXW_SECURITY_API_ROOT', 'https://app.security.dxw.com/api/v2');
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

require(dirname(__FILE__) . '/lib/alert_subscription_banner.class.php');

require(dirname(__FILE__) . '/lib/cron.class.php');
require(dirname(__FILE__) . '/lib/update_checker.class.php');
require_once(dirname(__FILE__) . '/lib/activation_checker.class.php');


class dxw_Security {
  public function __construct() {
    add_action('load-index.php', array($this, 'enqueue_scripts'));
    add_action('load-plugins.php', array($this, 'enqueue_scripts'));

    add_action('admin_init', array("dxw_security_Plugin_Review_Column", 'setup'));
    add_action('admin_init', array("dxw_security_Dashboard_Widget", 'setup'));

    if( dxw_security_Subscription_Link::can_subscribe() ) {
      add_action('load-plugins.php', array("dxw_security_Alert_Subscription_Banner", 'setup'));
    }

    dxw_security_Cron::hook_tasks();

    if (defined('DXW_SECURITY_DEBUG')) { $this->debug_cron(); }
  }

  public function enqueue_scripts($hook) {
    // TODO: split the css up into plugins page/dashboard/common and load in the relevant places?
    wp_enqueue_style('dxw-security-plugin-styles', plugins_url('/assets/main.min.css' , __FILE__));
    wp_enqueue_script('dxw-security-plugin-scripts', plugins_url('/assets/main.min.js' , __FILE__));

    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-dialog');
  }

  private function debug_cron() {
    add_action('wp_ajax_dxw_security_cron', array('dxw_security_Plugin_Manifest_Poster', 'run'));
    add_action('wp_ajax_dxw_security_cron', array('dxw_security_Review_Fetcher', 'run'));
  }
}
// It's not possible to directly call add_action in a function called by the register_activation_hook
//    So we need to set an option and optionally execute the contents of an add_action defined elsewhere:
//    http://codex.wordpress.org/Function_Reference/register_activation_hook#Process_Flow
register_activation_hook( __FILE__, array( "dxw_security_Activation_Checker",  'activate' ));

register_activation_hook( __FILE__, array( "dxw_security_Cron", 'schedule_tasks' ));
register_deactivation_hook( __FILE__, array( "dxw_security_Cron", 'unschedule_tasks' ));

register_activation_hook( __FILE__, array( "dxw_security_Update_Checker", 'record_version' ));
// TODO: should this `if` be in the constructor instead?
if (dxw_security_Update_Checker::updated() ) {
  dxw_security_Cron::schedule_tasks();
  dxw_security_Update_Checker::record_version(); // TODO: Should this be abstracted into the update_checker class?
}

new dxw_Security();