<?php
// Calling this every day (with wp_cron) has two benefits:
//   firstly it warm the cache by requesting reviews for all the installed plugins
//   secondly the api can get accurate stats about the numbers of each plugin requested, which helps with prioritising reviews.

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/plugin_file.class.php');

class dxw_security_Review_Fetcher {
  private $failed_requests = 0;

  public function __construct() {
    // Define a hook for wp_cron to call:
    add_action('dxw_security_daily_fetch_reviews', array($this, 'fetch_reviews') );
  }

  public function fetch_reviews() {
    $plugins = $this->get_plugins();
    foreach($plugins as $plugin_file => $data) {
      $this->fetch_review($plugin_file, $data);
    }
  }

  private function fetch_review($plugin_file, $plugin_data) {
    $plugin_file_object = new dxw_security_Plugin_File($plugin_file);

    // TODO: this pattern is duplicated in the security column code and dashboard widget
    // It should probably be factored out into another class
    // Stop making requests after a certain number of failures:
    if ($this->failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $recommendation = $this->handle_api_fatal_error();
    } else {
      $api = new dxw_security_Plugin_Review_API($plugin_file_object->plugin_slug);

      try {
        $reviews = $api->call();
        $this->handle_api_response();
      } catch (\Exception $e) {
        $this->handle_api_error($e);
      }
    }
  }

  private function handle_api_response() {  }
  private function handle_api_error() {
    $this->failed_requests++;
  }
  private function handle_api_fatal_error() {  }

  private function get_plugins() {
    if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return get_plugins(); // From core
  }
}
?>