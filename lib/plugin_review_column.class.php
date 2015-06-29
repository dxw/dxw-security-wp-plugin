<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_recommendation_fetcher.class.php');
require_once(dirname(__FILE__) . '/models/plugin_file.class.php');


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

    $fetcher = new dxw_security_Plugin_Recommendation_Fetcher($name, $installed_version, $plugin_slug);
    $recommendation = $fetcher->recommendation();

    $recommendation->render();
  }
}

?>
