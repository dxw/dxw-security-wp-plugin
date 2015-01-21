<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/task.class.php');
// Without these requires, the tasks will silently fail to execute:
require_once(dirname(__FILE__) . '/review_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_manifest_poster.class.php');
require_once(dirname(__FILE__) . '/subscription.class.php');

class dxw_security_Cron {
  public static function schedule_tasks() {
    self::schedule_review_fetcher_task();
    if (dxw_security_Subscription::is_active()) {
      self::schedule_manifest_poster_task();
    }
  }

  public static function hook_tasks() {
    self::fetcher_task()->hook();
    self::poster_task()->hook();
  }

  public static function unschedule_tasks() {
    self::unschedule_review_fetcher_task();
    self::unschedule_manifest_poster_task();
  }

  public static function schedule_review_fetcher_task() {
    self::fetcher_task()->schedule('daily');
  }
  public static function unschedule_review_fetcher_task() {
    self::fetcher_task()->unschedule();
  }

  public static function schedule_manifest_poster_task() {
    self::poster_task()->schedule('daily');
  }
  public static function unschedule_manifest_poster_task() {
    self::poster_task()->unschedule();
  }

  private static function fetcher_task() {
    return new dxw_security_Task('dxw_security_Review_Fetcher');
  }
  private static function poster_task() {
    return new dxw_security_Task('dxw_security_Plugin_Manifest_Poster');
  }
}

?>