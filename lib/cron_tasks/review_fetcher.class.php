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
    $installed_version  = $plugin_data['Version'];
    $plugin_slug        = $plugin_file_object->plugin_slug;

    $api = new dxw_security_Advisories_API($plugin_slug, $installed_version);

    $fetcher = new dxw_security_Plugin_Single_Review_Fetcher($api);
    self::fetch_review_with_error_limiting($fetcher);
  }

  private static function fetch_review_with_error_limiting($fetcher) {
    $adapted_fetcher = new dxw_security_Single_Review_Fetcher_Adaptor($fetcher);
    $limited_fetcher = new dxw_security_Error_Limiter($adapted_fetcher, self::$failed_requests);

    $limited_fetcher->call();
  }
}

class dxw_security_Plugin_Single_Review_Fetcher {
  private $api;

  public function __construct($api) {
    $this->api = $api;
  }

  public function fetch() {
    $this->api->call();
  }

  public function handle_api_error() {}
  public function handle_api_fatal_error() {
    // In this case we won't get stats, but the user's experience won't be impacted
    // except for slow loading the next time they visit the plugins page (because
    // the reviews won't be cached), at which point they'll see an error,
    // so it's probably OK to do nothing here.
  }
}

// TODO: This is identical to the recommendation fetcher adaptor
class dxw_security_Single_Review_Fetcher_Adaptor {
  private $fetcher;

  public function __construct($fetcher) {
    $this->fetcher = $fetcher;
  }

  public function call() {
    return $this->fetcher->fetch();
  }

  public function handle_error($error) {
    return $this->fetcher->handle_api_error($error);
  }

  public function handle_fatal_error() {
    return $this->fetcher->handle_api_fatal_error();
  }
}


?>
