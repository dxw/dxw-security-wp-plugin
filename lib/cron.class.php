<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/task.class.php');
require_once(dirname(__FILE__) . '/review_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_manifest_poster.class.php');

class dxw_security_Cron {
  public static function schedule_tasks() {
    self::fetcher_task()->schedule('daily');
    self::poster_task()->schedule('daily');
  }

  public static function hook_tasks() {
    self::fetcher_task()->hook();
    self::poster_task()->hook();
  }

  private static function fetcher_task() {
    return new dxw_security_Task('dxw_security_Review_Fetcher');
  }
  private static function poster_task() {
    return new dxw_security_Task('dxw_security_Plugin_Manifest_Poster');
  }
}

?>