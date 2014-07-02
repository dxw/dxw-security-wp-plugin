<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/review_data.class.php');
require_once(dirname(__FILE__) . '/plugin_file.class.php');

class dxw_security_Dashboard_Widget {
  private $red = 0;
  private $yellow = 0;
  private $green = 0;
  private $different_version = 0;
  private $not_reviewed = 0;
  private $failed_requests = 0;

  private $first_red_slug;
  private $first_yellow_slug;
  private $first_green_slug;
  private $first_different_version_slug;
  private $first_not_reviewed_slug;
  private $first_failed_request_slug;

  public function __construct() {
    add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
  }

  // Function used in the action hook
  public function add_dashboard_widgets() {
    // could use wp_add_dashboard_widget, but that puts it at the bottom of the left column which isn't very visible;
    add_meta_box('dashboard_dxw_security', 'dxw Security', array($this, 'dashboard_widget_content'), 'dashboard', 'side', 'high');
  }

  public function dashboard_widget_content() {
    $plugins = get_plugins();
    $number_of_plugins = count($plugins);

    if ( $number_of_plugins == 0 ) {
      echo "<p>There are no plugins installed on this site.</p>";
      return;
    }

    $this->get_counts($plugins);

    $red_slug = dxw_security_Review_Data::$dxw_security_review_statuses["red"]["slug"];
    $yellow_slug = dxw_security_Review_Data::$dxw_security_review_statuses["yellow"]["slug"];
    $green_slug = dxw_security_Review_Data::$dxw_security_review_statuses["green"]["slug"];
    $grey_slug = dxw_security_Review_Data::$dxw_security_review_statuses["not-found"]["slug"];

    $first_red_plugin_link               = $this->plugin_link($this->first_red_slug);
    $first_yellow_plugin_link            = $this->plugin_link($this->first_yellow_slug);
    $first_green_plugin_link             = $this->plugin_link($this->first_green_slug);
    $first_different_version_plugin_link = $this->plugin_link($this->first_different_version_slug);
    $first_not_reviewed_plugin_link      = $this->plugin_link($this->first_not_reviewed_slug);
    $first_failed_request_plugin_link    = $this->plugin_link($this->first_failed_request_slug);

    // Is this reliable?
    $plugins_page_url = "plugins.php";

    echo "<p>Of the {$number_of_plugins} plugins installed on this site:</p>";
    echo "<ul class='review_counts'>";
    $this->plugin_review_count_box($this->red, $red_slug, $first_red_plugin_link, "are potentially unsafe");
    $this->plugin_review_count_box($this->yellow, $yellow_slug, $first_yellow_plugin_link, "should be used with caution");
    $this->plugin_review_count_box($this->green, $green_slug, $first_green_plugin_link, "are probably safe");
    if ($this->different_version > 0) { $this->plugin_review_not_reviewed_box($this->different_version, $grey_slug, $first_different_version_plugin_link, "have reviews for different versions"); }
    if ($this->not_reviewed > 0) {      $this->plugin_review_not_reviewed_box($this->not_reviewed, $grey_slug, $first_not_reviewed_plugin_link, "have not yet been reviewed"); }
    if ($this->failed_requests > 0) {   $this->plugin_review_not_reviewed_box($this->failed_requests, $grey_slug, $first_failed_request_plugin_link, "could not be checked due to errors. Please try again later."); }

    echo "</ul>";
    echo "<p><a href='{$plugins_page_url}'>Visit your plugins page for more details...</a></p>";
  }

  private function get_counts($plugins) {
    foreach($plugins as $plugin_file => $data) {
      $plugin_file_object = new dxw_security_Plugin_File($plugin_file);

      $installed_version = $data["Version"];

      // TODO: this pattern is duplicated in the security column code
      // Stop making requests after a certain number of failures:
      if ($this->failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
        $this->handle_api_fatal_error($plugin_file_object->plugin_slug);
      } else {
        $api = new dxw_security_Plugin_Review_API($plugin_file_object->plugin_slug);
        try {
          $reviews = $api->call();
          $this->handle_api_response($reviews, $installed_version, $plugin_file_object->plugin_slug);
        } catch (Exception $e) {
          $this->handle_api_error($e, $plugin_file_object->plugin_slug);
        }
      }
    }
  }

  private function handle_api_response($reviews, $installed_version, $plugin_slug) {
    if (empty($reviews)) {
      $this->not_reviewed++;
      if (is_null($this->first_not_reviewed_slug)) { $this->first_not_reviewed_slug = $plugin_slug; }
    } else{

      foreach($reviews as &$review) {
        // $review->version might be a list of versions, so we can't just do a straightforward comparison
        if (dxw_security_Plugin_Version_Comparer::version_matches($installed_version, $review->version)) {
          switch ($review->recommendation) {
          case "red":
            $this->red++;
            if (is_null($this->first_red_slug)) { $this->first_red_slug = $plugin_slug; }
            break;
          case "yellow":
            $this->yellow++;
            if (is_null($this->first_yellow_slug)) { $this->first_yellow_slug = $plugin_slug; }
            break;
          case "green":
            $this->green++;
            if (is_null($this->first_green_slug)) { $this->first_green_slug = $plugin_slug; }
            break;
          }
          return;
        }
      }

      // If an exact version matched then we won't get this far because of that return
      // Assumption: if there were some reviews, but no review of the installed version,
      //  then there must be reviews of other versions
      $this->different_version++;
      if (is_null($this->first_different_version_slug)) { $this->first_different_version_slug = $plugin_slug; }
    }
  }

  private function handle_api_error($error, $plugin_slug) {
    // TODO: Handle errors actually raised by us in the api class separately from other errors?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    $this->failed_requests++;
    if (is_null($this->first_failed_request_slug)) { $this->first_failed_request_slug = $plugin_slug; }
  }

  private function handle_api_fatal_error($plugin_slug) {
    // Assume it would have failed
    //   Keep counting because currently we're just displaying "x failed"
    $this->failed_requests++;
    if (is_null($this->first_failed_request_slug)) { $this->first_failed_request_slug = $plugin_slug; }
    // TODO: instead throw an error here to be captured higher up.
  }

  private function plugin_review_count_box($count, $css_class, $plugin_link, $message) {
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

  private function plugin_review_not_reviewed_box($count, $css_class, $plugin_link, $message) {
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

  private function plugin_link($plugin_slug) {
    if (is_null($plugin_slug)) { return; }
    // TODO: Is this reliable?
    // TODO: Also - duplication
    $plugins_page_url = "plugins.php";
    return "{$plugins_page_url}#{$plugin_slug}";
  }

}
?>