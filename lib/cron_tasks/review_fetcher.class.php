<?php
// Calling this every day (with wp_cron) has two benefits:
//   firstly it warm the cache by requesting reviews for all the installed plugins
//   secondly the api can get accurate stats about the numbers of each plugin requested, which helps with prioritising reviews.

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../api.class.php');
require_once(dirname(__FILE__) . '/../plugin_getter.class.php');
require_once(dirname(__FILE__) . '/../models/plugin_file.class.php');

class dxw_security_Review_Fetcher {
  private static $failed_requests = 0;

  public static function run() {
    $plugins = dxw_security_Plugin_Getter::get();
    foreach($plugins as $plugin_file => $data) {
      self::fetch_review($plugin_file, $data);
    }
  }

  private static function fetch_review($plugin_file, $plugin_data) {
    $plugin_file_object = new dxw_security_Plugin_File($plugin_file);
    $plugin_version = $plugin_data['Version'];

    // TODO: this pattern is duplicated in the security column code and dashboard widget
    //    It should probably be factored out into another class
    // Stop making requests after a certain number of failures:
    if (self::$failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $recommendation = self::handle_api_fatal_error();
    } else {
      $api = new dxw_security_Advisories_API($plugin_file_object->plugin_slug, $plugin_version);

      try {
        $reviews = $api->call();
      } catch (\Exception $e) {
        self::handle_api_error($e);
      }
    }
  }

  private static function handle_api_error() {
    self::$failed_requests++;
  }
  private static function handle_api_fatal_error() {
    // In this case we won't get stats, but the user's experience won't be impacted
    // except for slow loading the next time they visit the plugins page (because
    // the reviews won't be cached), at which point they'll see an error,
    // so it's probably OK to do nothing here.
  }
}
?>
