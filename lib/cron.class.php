<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/review_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_manifest_poster.class.php');

class dxw_security_Cron {
  public function __construct() {
    register_activation_hook( __FILE__, array($this, 'schedule_tasks' ));
    new dxw_security_Review_Fetcher('dxw_security_daily_fetch_reviews');
    new dxw_security_Plugin_Manifest_Poster('dxw_security_daily_post_plugin_manifest');
  }

  public function schedule_tasks() {
    schedule('dxw_security_daily_fetch_reviews', 'daily');
    schedule('dxw_security_daily_post_plugin_manifest', 'daily');
  }

  private function schedule($task_name, $frequency) {
    // Check if the event is already scheduled
    $timestamp = wp_next_scheduled( $task_name );

    if( $timestamp == false ) {
      wp_schedule_event( time(), $frequency, $task_name );
    }
  }
}

?>