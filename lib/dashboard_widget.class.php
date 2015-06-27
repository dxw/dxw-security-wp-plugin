<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/views/review_data.class.php');
require_once(dirname(__FILE__) . '/models/plugin_file.class.php');
require_once(dirname(__FILE__) . '/models/user.class.php');
require_once(dirname(__FILE__) . '/subscription_link.class.php');

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

    # TODO: These slugs are effectively duplicated information between this and the review_data class
    $vulnerable_slug = 'vulnerable';
    $grey_slug       = 'no-info';


    $first_vulnerable_plugin_link        = self::plugin_link(self::$first_vulnerable_slug);
    $first_not_reviewed_plugin_link      = self::plugin_link(self::$first_not_reviewed_slug);
    $first_failed_request_plugin_link    = self::plugin_link(self::$first_failed_request_slug);

    // Is this reliable?
    $plugins_page_url = "plugins.php";

    //  TODO - I'm not sure this decision should be made at this point
    if( dxw_security_User::can_subscribe() ) {
      print_r(self::subscription_link());
    }

    echo "<p>Of the {$number_of_plugins} plugins installed on this site:</p>";
    echo "<ul class='review_counts'>";
    self::plugin_review_count_box(self::$vulnerable, $vulnerable_slug, $first_vulnerable_plugin_link, "are known to be vulnerable");
    self::plugin_review_count_box(self::$not_reviewed, $grey_slug, $first_not_reviewed_plugin_link, "have no known vulnerabilities");
    if (self::$failed_requests > 0) {   self::plugin_review_not_reviewed_box(self::$failed_requests, $grey_slug, $first_failed_request_plugin_link, "could not be checked due to errors. Please try again later."); }

    echo "</ul>";
    echo "<p><a href='{$plugins_page_url}'>Visit your plugins page for more details...</a></p>";
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

  private static function plugin_review_count_box($count, $css_class, $plugin_link, $message) {
    if ($count == 0) { $css_class = $css_class . " none"; }
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    if (!is_null($plugin_link)) { print_r("<a href={$plugin_link}>"); }
    ?>
      <li class='plugin_review_count_box'>
        <div class='<?php echo $css_class ?> plugin_review_count_box_inner'>
          <span class='icon-<?php echo $css_class ?>'></span>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
        </div>
      </li>
    <?php
    if (!is_null($plugin_link)) { print_r("</a>"); }
  }

  private static function plugin_review_not_reviewed_box($count, $css_class, $plugin_link, $message) {
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    ?>
      <a href='<?php echo $plugin_link ?>'>
        <li class='<?php echo $css_class ?>'>
          <span class='icon-<?php echo $css_class ?>'></span>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
        </li>
      </a>
    <?php
  }

  private static function plugin_link($plugin_slug) {
    if (is_null($plugin_slug)) { return; }
    // TODO: Is this reliable?
    // TODO: Also - duplication
    $plugins_page_url = "plugins.php";
    return "{$plugins_page_url}#{$plugin_slug}";
  }

  private static function subscription_link() {
    ?>
      <div id="dxw_security_alert_subscription_link">
        <h4>Security alerts</h4>
        <p>
          Want to get notified of security issues with your plugins?
        </p>
        <?php dxw_security_Subscription_Link::render() ?>
      </div>
    <?php
  }

}
?>
