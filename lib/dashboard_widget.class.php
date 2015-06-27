<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/models/plugin_file.class.php');
require_once(dirname(__FILE__) . '/views/dashboard_widget_content.class.php');

class dxw_security_Dashboard_Widget {
  private static $vulnerable = 0;
  private static $not_reviewed = 0;
  private static $failed_requests = 0;

  private static $first_vulnerable_slug;
  private static $first_not_reviewed_slug;
  private static $first_failed_request_slug;

  public static function setup() {
    add_action('wp_dashboard_setup', array(get_called_class(), 'add_dashboard_widgets'));
  }

  // Function used in the action hook
  public static function add_dashboard_widgets() {
    // could use wp_add_dashboard_widget, but that puts it at the bottom of the left column which isn't very visible;
    add_meta_box('dashboard_mongoose', 'MongooseWP', array(get_called_class(), 'dashboard_widget_content'), 'dashboard', 'side', 'high');
  }

  public static function dashboard_widget_content() {
    $plugins = get_plugins();
    $number_of_plugins = count($plugins);

    if ( $number_of_plugins == 0 ) {
      echo "<p>There are no plugins installed on this site.</p>";
      return;
    }

    self::get_counts($plugins);

    $vulnerable_data   = array('count' => self::$vulnerable,      'plugin' => self::$first_vulnerable_slug);
    $not_reviewed_data = array('count' => self::$not_reviewed,    'plugin' => self::$first_not_reviewed_slug);
    $errored_data      = array('count' => self::$failed_requests, 'plugin' => self::$first_failed_request_slug);

    $content = new dxw_security_Dashboard_Widget_Content($number_of_plugins, $vulnerable_data, $not_reviewed_data, $errored_data);
    $content->render();
  }

  private static function get_counts($plugins) {
    foreach($plugins as $plugin_file => $data) {
      $plugin_file_object = new dxw_security_Plugin_File($plugin_file);

      $installed_version = $data["Version"];

      // TODO: this pattern is duplicated in the security column code
      // Stop making requests after a certain number of failures:
      if (self::$failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
        self::handle_api_fatal_error($plugin_file_object->plugin_slug);
      } else {
        $api = new dxw_security_Advisories_API($plugin_file_object->plugin_slug, $installed_version);
        try {
          $reviews = $api->call();
          self::handle_api_response($reviews, $installed_version, $plugin_file_object->plugin_slug);
        } catch (dxw_security_API_NotFound $e) {
          self::handle_api_not_found($plugin_file_object->plugin_slug);
        } catch (Exception $e) {
          self::handle_api_error($e, $plugin_file_object->plugin_slug);
        }
      }
    }
  }

  private static function handle_api_not_found($plugin_slug) {
    self::$not_reviewed++;
    if (is_null(self::$first_not_reviewed_slug)) { self::$first_not_reviewed_slug = $plugin_slug; }
  }

  private static function handle_api_response($review, $installed_version, $plugin_slug) {
    self::$vulnerable++;
    if (is_null(self::$first_vulnerable_slug)) { self::$first_vulnerable_slug = $plugin_slug; }
  }

  private static function handle_api_error($error, $plugin_slug) {
    // TODO: Handle errors actually raised by us in the api class separately from other errors?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    self::$failed_requests++;
    if (is_null(self::$first_failed_request_slug)) { self::$first_failed_request_slug = $plugin_slug; }
  }

  private static function handle_api_fatal_error($plugin_slug) {
    // Assume it would have failed
    //   Keep counting because currently we're just displaying "x failed"
    self::$failed_requests++;
    if (is_null(self::$first_failed_request_slug)) { self::$first_failed_request_slug = $plugin_slug; }
    // TODO: instead throw an error here to be captured higher up.
  }
}
?>
