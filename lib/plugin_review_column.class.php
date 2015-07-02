<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_recommendation_fetcher.class.php');
require_once(dirname(__FILE__) . '/error_limiter.class.php');
require_once(dirname(__FILE__) . '/models/plugin_file.class.php');

require_once(dirname(__FILE__) . '/views/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_error.class.php');



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
      self::data($plugin_file, $plugin_data);
    }
  }

  private static function data($plugin_file, $plugin_data) {
    $name              = $plugin_data['Name'];
    $installed_version = $plugin_data['Version'];

    $plugin_file_object = new dxw_security_Plugin_File($plugin_file);
    $plugin_slug        = $plugin_file_object->plugin_slug;

    $api = new dxw_security_Advisories_API($plugin_slug, $installed_version);
    $recommendation = self::make_recommendation($name, $installed_version, $api);
    $recommendation->render();
  }

  private static function make_recommendation($name, $installed_version, $api) {
    $review_fetcher       = new dxw_security_Plugin_Recommendation_Fetcher($api);

    $recommendation_maker = new dxw_security_Recommendation_Maker($name, $installed_version, $review_fetcher);
    $error_handler        = new dxw_security_Recommendation_Error_Handler();
    $fatal_error_handler  = $error_handler;

    $error_handled_limited_fetcher = self::error_handled_limited_fetcher($recommendation_maker, $error_handler, $fatal_error_handler, self::$failed_requests);

    return $error_handled_limited_fetcher->call();
  }

  private static function error_handled_limited_fetcher($fetcher, $error_handler, $fatal_error_handler, &$counter) {
    $counting_error_handler        = new dxw_security_Counting_Error_Handler($error_handler, $counter);
    $error_limited_fetcher         = new dxw_security_Error_Limited_Caller($fetcher, $fatal_error_handler, $counter);

    return new dxw_security_Error_Handled_Caller($error_limited_fetcher, $counting_error_handler);
  }
}

class dxw_security_Recommendation_Maker {
  public function __construct($name, $installed_version, $review_fetcher) {
    $this->name              = $name;
    $this->installed_version = $installed_version;
    $this->review_fetcher    = $review_fetcher;
  }

  public function call() {
    $review_data = $this->review_fetcher->fetch();
    return $recommendation = new dxw_security_Plugin_Recommendation($this->name, $this->installed_version, $review_data);
  }
}


class dxw_security_Recommendation_Error_Handler {
  public function handle($error=null) {
    // TODO: Handle errors actually raised by us in the api class separately?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    return new dxw_security_Plugin_Recommendation_Error();
  }
}

class dxw_security_Fetcher_Adaptor {
  private $fetcher;

  public function __construct($fetcher) {
    $this->fetcher = $fetcher;
  }

  public function call() {
    return $this->fetcher->fetch();
  }
}

?>
