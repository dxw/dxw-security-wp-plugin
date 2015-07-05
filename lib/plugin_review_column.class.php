<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_recommendation_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_review_fetcher.class.php');
require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/error_limiter.class.php');
require_once(dirname(__FILE__) . '/models/plugin.class.php');

class dxw_security_Plugin_Review_Column {
  // Track the number of failed requests so that we can stop trying after a certain number.
  // TODO: This should apply per page load, but ideally this behaviour might be better handled by the API class (?)
  private static $failed_requests = 0;

  public static function setup() {
    add_filter('manage_plugins_columns', array(get_called_class(), 'manage_plugins_columns'));
    add_action('manage_plugins_custom_column', array(get_called_class(), 'manage_plugins_custom_column'), 10, 3);
  }

  public static function manage_plugins_columns($columns) {
    $columns['security_review'] = "Security";
    return $columns;
  }

  public static function manage_plugins_custom_column($column_name, $plugin_file, $plugin_data) {
    if($column_name == 'security_review') {
      $plugin = new dxw_security_Plugin($plugin_file, $plugin_data);
      self::data($plugin);
    }
  }

  private static function data($plugin) {
    $api                    = new dxw_security_Advisories_API($plugin);
    $review_fetcher         = self::review_fetcher($api);
    $recommendation_fetcher = new dxw_security_Plugin_Recommendation_Fetcher($plugin, $review_fetcher);

    $recommendation = $recommendation_fetcher->call();
    $recommendation->render();
  }

  // A review fetcher which stops calling after a certain number of errors
  private static function review_fetcher($api) {
    $review_fetcher         = new dxw_security_Plugin_Review_Fetcher($api);
    // Decorate it with error limiting: stop calling after N errors:
    $review_fetcher         = new dxw_security_Error_Limited_Caller(
                                $review_fetcher,
                                self::$failed_requests
                              );
    // Count the errors to allow error limiting:
    $review_error_handler   = new dxw_security_Counting_Error_Handler(
                                new dxw_security_Raising_Error_Handler(),
                                self::$failed_requests
                              );

    // Decorate it with error *handling*:
    $review_fetcher         = new dxw_security_Error_Handled_Caller(
                                $review_fetcher,
                                $review_error_handler
                              );

    return $review_fetcher;
  }
}

?>
