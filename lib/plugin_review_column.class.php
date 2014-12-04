<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/review_data.class.php');
require_once(dirname(__FILE__) . '/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/plugin_file.class.php');


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
    $name = $plugin_data['Name'];
    $installed_version = $plugin_data['Version'];

    $plugin_file_object = new dxw_security_Plugin_File($plugin_file);
    // TODO - perhaps this function shouldn't be responsible for the following logic?
    $latest_version = $plugin_file_object->latest_version();
    if (!$latest_version) { $latest_version = $installed_version; }

    // Stop making requests after a certain number of failures:
    if (self::$failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $recommendation = self::handle_api_fatal_error();
    } else {
      $api = new dxw_security_Plugin_Review_API($plugin_file_object->plugin_slug);

      try {
        $reviews = $api->call();
        $recommendation = self::handle_api_response($reviews, $name, $installed_version, $latest_version);
      } catch (\Exception $e) {
        $recommendation = self::handle_api_error($e);
      }
    }

    $recommendation->render();
  }

  private static function handle_api_response($reviews, $name, $installed_version, $latest_version) {
    if (empty($reviews)) {
      $review_data = new dxw_security_Review_Data($installed_version, "not-found");
      // TODO - it's a bit odd that we're creating a class which implies that the plugin has been reviewed...
      $recommendation = new dxw_security_Plugin_Recommendation_Reviewed($name, $installed_version, $review_data);
    } else{

      $other_version_reviews = array();
      foreach($reviews as &$review) {
        $version = $review->version;
        $status = $review->recommendation;
        $reason = $review->reason;
        $action = $review->action;
        $link = $review->review_link;
        $review_data = new dxw_security_Review_Data($version, $status, $reason, $action, $link);

        // $review->version might be a list of versions, so we need to do a little work to compare it
        if ($review_data->version_matches($installed_version)) {
          $recommendation = new dxw_security_Plugin_Recommendation_Reviewed($name, $installed_version, $review_data);
        } else {
          $other_version_reviews[] = $review_data;
        }
      }
      if (empty($recommendation)) {
        // TODO: We're assuming that if $recommendation is empty then there was no review for the current version, but we DID find reviews for previous versions
        //   - if something went wrong then that might not be the case ...(?)
        $other_version_reviews_data = new dxw_security_Other_Version_Reviews_Data(array_reverse($other_version_reviews), $latest_version); // Reversed so that we get the latest review first
        $recommendation = new dxw_security_Plugin_Recommendation_Other_Versions_Reviewed($name, $installed_version, $other_version_reviews_data);
      }
    }
    return $recommendation;
  }

  private static function handle_api_error($error) {
    // TODO: Handle errors actually raised by us in the api class separately?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    self::$failed_requests++;
    return new dxw_security_Null_Plugin_Recommendation();
  }

  private static function handle_api_fatal_error() {
    return new dxw_security_Null_Plugin_Recommendation();
  }
}
?>