<?php
require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/review_data.class.php');

class dxw_security_Dashboard_Widget {
  private $red = 0;
  private $yellow = 0;
  private $green = 0;
  private $different_version = 0;
  private $not_reviewed = 0;
  private $failed_requests = 0;

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

    // Is this reliable?
    $plugins_page_url = "plugins.php";

    echo "<p>Of the {$number_of_plugins} plugins installed on this site:</p>";
    echo "<ul class='review_counts'>";
    $this->plugin_review_count_box($this->red, $red_slug, "are potentially unsafe");
    $this->plugin_review_count_box($this->yellow, $yellow_slug, "should be used with caution");
    $this->plugin_review_count_box($this->green, $green_slug, "are probably safe");
    if ($this->different_version > 0) { $this->plugin_review_not_reviewed_box($this->different_version, $grey_slug, "have reviews for different versions"); }
    if ($this->not_reviewed > 0) {      $this->plugin_review_not_reviewed_box($this->not_reviewed, $grey_slug, "have not yet been reviewed"); }
    if ($this->failed_requests > 0) {   $this->plugin_review_not_reviewed_box($this->failed_requests, $grey_slug, "could not be checked due to errors. Please try again later."); }

    echo "</ul>";
    echo "<p><a href='{$plugins_page_url}'>Visit your plugins page for more details...</a></p>";
  }

  private function get_counts($plugins) {
    foreach($plugins as $path => $data) {
      $installed_version = $data["Version"];

      // TODO: this pattern is duplicated in the security column code
      // Stop making requests after a certain number of failures:
      if ($this->failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
        $this->handle_api_fatal_error();
      } else {
        $api = new dxw_security_Plugin_Review_API($path);
        try {
          $reviews = $api->call();
          $this->handle_api_response($reviews, $installed_version);
        } catch (Exception $e) {
          $this->handle_api_error($e);
        }
      }
    }
  }

  private function handle_api_response($reviews, $installed_version) {
    if (empty($reviews)) {
      $this->not_reviewed++;
    } else{

      foreach($reviews as &$review) {
        // $review->version might be a list of versions, so we can't just do a straightforward comparison
        if (dxw_security_Plugin_Version_Comparer::version_matches($installed_version, $review->version)) {
          switch ($review->recommendation) {
          case "red":
            $this->red++;
            break;
          case "yellow":
            $this->yellow++;
            break;
          case "green":
            $this->green++;
            break;
          }
          return;
        }
      }

      // If an exact version matched then we won't get this far because of that return
      // Assumption: if there were some reviews, but no review of the installed version,
      //  then there must be reviews of other versions
      $this->different_version++;
    }
  }

  private function handle_api_error($error) {
    // TODO: Handle errors actually raised by us in the api class separately from other errors?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    $this->failed_requests++;
  }

  private function handle_api_fatal_error() {
    // Assume it would have failed
    //   Keep counting because currently we're just displaying "x failed"
    $this->failed_requests++;
    // TODO: instead throw an error here to be captured higher up.
  }

  private function plugin_review_count_box($count, $slug, $message) {
    if ($count == 0) { $slug = $slug . " none"; }
    ?>
      <li class='<?php echo $slug ?>'>
        <div class='plugin_review_count_box'>
          <span class='icon-<?php echo $slug ?>'></span>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
        </div>
      </li>
    <?php
  }

  private function plugin_review_not_reviewed_box($count, $slug, $message) {
    ?>
      <li class='<?php echo $slug ?>'>
        <span class='icon-<?php echo $slug ?>'></span>
        <span class='count'><?php echo $count ?></span>
        <?php echo $message ?>
      </li>
    <?php
  }
}
?>