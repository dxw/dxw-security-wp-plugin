<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_status_counter.class.php');
require_once(dirname(__FILE__) . '/views/dashboard_widget_content.class.php');

class dxw_security_Dashboard_Widget {
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
    $number_of_plugins = count(get_plugins());

    if ( $number_of_plugins == 0 ) {
      $content = new dxw_security_Dashboard_Widget_Content_No_Plugins();
    } else {
      $counter = new dxw_security_Plugin_Statuses_Counter();
      $plugin_status_counts = $counter->get_counts($plugins);

      $content = new dxw_security_Dashboard_Widget_Content($number_of_plugins, $plugin_status_counts);
    }
    $content->render();
  }
}

?>
