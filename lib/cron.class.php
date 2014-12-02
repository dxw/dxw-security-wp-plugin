<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/task.class.php');
require_once(dirname(__FILE__) . '/review_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_manifest_poster.class.php');

class dxw_security_Cron {
  public static function schedule_tasks() {
    self::fetcher_task()->schedule('daily');
    if (self::subscribed()) {
      self::poster_task()->schedule('daily');
    }
  }

  public static function hook_tasks() {
    self::fetcher_task()->hook();
    self::poster_task()->hook();
  }

  //  TODO - is it necessary to unhook??
  public static function unschedule_and_unhook_tasks() {
    self::fetcher_task()->unschedule_and_unhook();
    self::poster_task()->unschedule_and_unhook();
  }

  private static function fetcher_task() {
    return new dxw_security_Task('dxw_security_Review_Fetcher');
  }
  private static function poster_task() {
    return new dxw_security_Task('dxw_security_Plugin_Manifest_Poster');
  }

  private static function subscribed() {
    return (bool)get_option( 'dxw_security_subscription_token' );
  }
}

?>