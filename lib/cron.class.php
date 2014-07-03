<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/review_fetcher.class.php');

class dxw_security_Cron {
  public function __construct() {
    register_activation_hook( __FILE__, array($this, 'schedule' ));
    add_filter('cron_schedules', array($this, 'every_minute_interval'));

    new dxw_security_Review_Fetcher;
  }

  public function schedule() {
    // Check if the event is already scheduled
    $timestamp = wp_next_scheduled( 'dxw_security_daily_fetch_reviews' );

    if( $timestamp == false ) {
      // wp_schedule_event( time(), 'daily', 'dxw_security_daily_fetch_reviews' );
      wp_schedule_event( time(), 'every_minute', 'dxw_security_daily_fetch_reviews' );
    }
  }

  public function every_minute_interval($interval) {
    $interval['every_minute'] = array('interval' => 1*60, 'display' => 'Every minute');
    return $interval;
  }
}

?>