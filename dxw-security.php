<?php
// Plugin Name: dxw Security
// Plugin URI: https://security.dxw.com/
// Description: Pulls plugin review information from dxw Security into the wordpress plugins screen
// Version: 0.2.0
// License: GPLv2
// Author: dxw
// Author URI: http://dxw.com/

// CONFIG:
if (!defined('DXW_SECURITY_API_ROOT')) {
  define('DXW_SECURITY_API_ROOT', 'http://app.security.dxw.com/api');
}
if (!defined('DXW_SECURITY_CACHE_RESPONSES')) {
  define('DXW_SECURITY_CACHE_RESPONSES', true);
}

// CONSTANTS:
// How many failed requests will we tolerate?
define('DXW_SECURITY_FAILURE_lIMIT', 5);
// The URL we link to when we don't have any info about a plugin
define('DXW_SECURITY_PLUGINS_URL', 'https://security.dxw.com/plugins/');

class dxw_Security {
  public function __construct() {
    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_init', array($this, 'add_security_column'));
    add_action('admin_init', array($this, 'add_dashboard_widget'));
  }

  public function enqueue_scripts($hook) {
    // TODO: find a better way to do this
    //    and/or split the css up into plugins page/dashboard/common
    //    and/or move those enqueues into the relevant classes
    // TODO: does using index.php here mean that this gets included on the user-facing index page?
    if('plugins.php' != $hook && 'index.php' != $hook) { return; }

    wp_enqueue_style('dxw-security-plugin-styles', plugins_url('/assets/main.min.css' , __FILE__));
    wp_enqueue_script('dxw-security-plugin-scripts', plugins_url('/assets/main.min.js' , __FILE__));

    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-dialog');
  }

  public function add_security_column() {
    new Plugin_Review_Column;
  }

  public function add_dashboard_widget() {
    new Dxw_Security_Dashboard_Widget;
  }
}

class Dxw_Security_Dashboard_Widget {
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

    $red = 0;
    $yellow = 0;
    $green = 0;
    $different_version = 0;
    $not_reviewed = 0;

    foreach($plugins as $path => $data) {
      $plugin_file = explode("/",$path)[0];
      $installed_version = $data["Version"];

      // HACK - strip off file extensions to make Hello Dolly not complain
      $plugin_file= preg_replace("/\\.[^.\\s]{3,4}$/", "", $plugin_file);
      //END HACK

      // TODO: duplication with the security column
      // TODO: handle errors/timeouts
      $api = new Plugin_Review_API($plugin_file);

      $reviews = $api->call();
      if (empty($reviews)) {
        $not_reviewed++;
      } else{

        $status = NULL;
        foreach($reviews as &$review) {
          // $review->version might be a list of versions, so we need to do a little work to compare it
          if ($this->version_matches($installed_version, $review->version)) {
            $status = $review->recommendation;
          }
        }

        switch ($status) {
        case "red":
          $red++;
          break;
        case "yellow":
          $yellow++;
          break;
        case "green":
          $green++;
          break;
        default:
          // Assumption: if there were some reviews, but no review of the installed version,
          //  then there must be reviews of other versions
          $different_version++;
        }
      }
    }
    $red_slug = Review_Data::$dxw_security_review_statuses["red"]["slug"];
    $yellow_slug = Review_Data::$dxw_security_review_statuses["yellow"]["slug"];
    $green_slug = Review_Data::$dxw_security_review_statuses["green"]["slug"];
    $grey_slug = Review_Data::$dxw_security_review_statuses["not-found"]["slug"];

    // Is this reliable?
    $plugins_page_url = "plugins.php";

    if ( count(get_plugins()) == 0 ) {
      // TODO: should this be right at the top?
      echo "<p>There are no plugins installed on this site.</p>";
    } else {
      echo "<p>Of the plugins installed on this site...</p>";
      echo "<ul class='review_counts'>";
      $this->plugin_review_count_box($red, $red_slug, "are potentially unsafe");
      $this->plugin_review_count_box($yellow, $yellow_slug, "should be used with caution");
      $this->plugin_review_count_box($green, $green_slug, "are probably safe");
      if ($different_version > 0) { echo "<li class='{$grey_slug}'><span class='icon-{$grey_slug}'></span><span class='count'>{$different_version}</span> have reviews for different versions </li>"; }
      if ($not_reviewed > 0) {      echo "<li class='{$grey_slug}'><span class='icon-{$grey_slug}'></span><span class='count'>{$not_reviewed}</span> have not yet been reviewed</li>"; }
      echo "</ul>";
      echo "<p><a href='{$plugins_page_url}'>Visit your plugins page for more details...</a></p>";
    }
  }

  // TODO: DUPLICATION! - move into Review_Data
  private function version_matches($version, $list) {
    $versions = explode( ',', $list );
    return in_array($version, $versions);
  }

  private function plugin_review_count_box($count, $slug, $message) {
    ?>
      <li class='<?php echo $slug ?>'>
        <div class='plugin_review_count_box'>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
          <span class='icon-<?php echo $slug ?>'></span>
        </div>
      </li>
    <?php
  }
}

class Plugin_Review_Column {
  // Track the number of failed requests so that we can stop trying after a certain number.
  // TODO: This should apply per page load, but ideally this behaviour might be better handled by the API class (?)
  private $dxw_security_failed_requests = 0;

  public function __construct() {
    add_filter('manage_plugins_columns', array($this, 'manage_plugins_columns'));
    add_action('manage_plugins_custom_column', array($this, 'manage_plugins_custom_column'), 10, 3);
  }

  public function manage_plugins_columns($columns) {
    $columns['security_review'] = "Security";
    return $columns;
  }

  public function manage_plugins_custom_column($column_name, $plugin_file, $plugin_data) {
    if($column_name == 'security_review') {
      $this->data($plugin_file, $plugin_data);
    }
  }

  private function data($plugin_file, $plugin_data) {
    $name = $plugin_data['Name'];
    $installed_version = $plugin_data['Version'];

    // Stop making requests after a certain number of failures:
    if ($this->dxw_security_failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $recommendation = new Null_Plugin_Recommendation();

    } else {
      $api = new Plugin_Review_API($plugin_file);

      try {
        $reviews = $api->call();
        if (empty($reviews)) {
          $review_data = new Review_Data($installed_version, "not-found");
          $recommendation = new Plugin_Recommendation_Reviewed($name, $installed_version, $review_data);
        } else{

          $other_version_reviews = array();
          foreach($reviews as &$r) {
            $version = $r->version;
            $status = $r->recommendation;
            $reason = $r->reason;
            $link = $r->review_link;

            $review_data = new Review_Data($version, $status, $reason, $link);

            // $r->version might be a list of versions, so we need to do a little work to compare it
            if ($this->version_matches($installed_version, $r->version)) {
              $recommendation = new Plugin_Recommendation_Reviewed($name, $installed_version, $review_data);
            } else {
              $other_version_reviews[] = $review_data;
            }
          }
          if (empty($recommendation)) {
            # TODO: We're assuming that if $recommendation is empty then there was no review for the current version, but we DID find reviews for previous versions
            #   - if something went wrong then that might not be the case ...(?)
            $other_version_reviews_data = new Other_Version_Reviews_Data(array_reverse($other_version_reviews)); // Reversed so that we get the latest review first
            $recommendation = new Plugin_Recommendation_Other_Versions_Reviewed($name, $installed_version, $other_version_reviews_data);
          }
        }
      } catch (Exception $e) {
        // TODO: Handle Dxw_Security_Error separately?
        // TODO: in future we should provide some way for users to give us back some useful information when they get an error
        $this->dxw_security_failed_requests++;
        $recommendation = new Null_Plugin_Recommendation();
      }
    }

    $recommendation->render();
  }
  private function version_matches($version, $list) {
    $versions = explode( ',', $list );
    return in_array($version, $versions);
  }
}


class Plugin_Recommendation {
  private $name;
  private $version;
  private $slug;
  private $review_data;
  private $css_class;

  public function __construct($name, $version, $slug, $review_data, $heading, $more_info="More information", $dialog_intro="", $css_class="") {
    $this->name = $name;
    $this->version = $version;
    $this->slug = $slug;
    $this->review_data = $review_data;
    $this->heading = $heading;
    $this->more_info = $more_info;
    $this->dialog_intro = $dialog_intro;
    $this->css_class = $css_class;
  }

  public function render() {
    ?>
      <a href="#<?php echo esc_attr($this->dialog_id()); ?>" data-title="<?php echo esc_attr($this->name); ?> - <?php echo esc_attr($this->version); ?>" class="dialog-link review-message <?php echo esc_attr($this->slug) ?> <?php echo esc_attr($this->css_class) ?>">
        <h3><?php echo $this->heading; ?></h3>

        <p class="more-info"><?php echo esc_html($this->more_info); ?></p>
      </a>
    <?php
    print_r($this->render_dialog());
  }

  protected function render_dialog(){
    ?>
      <div id="<?php echo esc_attr($this->dialog_id()); ?>" style="display:none;" class="dialog review-message <?php echo esc_attr($this->slug); ?> <?php echo esc_attr($this->css_class) ?>">

        <a href="http://security.dxw.com" id="dxw-sec-link"><img src="<?php echo plugins_url('/assets/dxw-logo.png' , __FILE__); ?>" alt="dxw logo" /></a>

        <div class="inner">
          <?php print_r($this->dialog_intro) ?>
          <?php print_r($this->review_data->render()) ?>
        </div>

      </div>
    <?php
  }

  protected function dialog_id() {
    return "plugin-inspection-results-" . sanitize_title($this->name);
  }
}


class Plugin_Recommendation_Reviewed {
  public function __construct($name, $version, $review_data) {
    $this->recommendation = new Plugin_Recommendation($name, $version, $review_data->slug, $review_data, $review_data->heading());
  }
  public function render() {
    $this->recommendation->render();
  }
}


class Plugin_Recommendation_Other_Versions_Reviewed {
  private $recommendation;

  public function __construct($name, $version, $other_reviews_data) {
    $latest_result = $other_reviews_data->most_recent()->slug;
    $dialog_intro =  "<p class='intro'>The installed version ({$version}) has not yet been reviewed, but here are some reviews of other versions:</p>";
    $heading = "This version ({$version}) not yet reviewed";
    $more_info =  "These versions have been reviewed: {$other_reviews_data->versions()}";
    $this->recommendation = new Plugin_Recommendation($name, $version, "other-versions-reviewed", $other_reviews_data, $heading, $more_info, $dialog_intro, "other-{$latest_result}");
  }
  public function render() {
    $this->recommendation->render();
  }
}


class Null_Plugin_Recommendation {
  public function render(){
    ?>
    <div class="review-message review-error">
      <h3><a href='<?php echo(esc_url(DXW_SECURITY_PLUGINS_URL)); ?>'>An error occurred</a></h3>
      <p>Please try again later</p>
    </div>
    <?php
  }
}


class Review_Data {
  public $version;
  public $slug;
  private $message;
  private $description;
  private $reason;

  // TODO: ideally this would be a class constant, but php doesn't support that
  public static $dxw_security_review_statuses = array(
    'green'     => array( 'message' => "No issues found",
                          'slug' => "no-issues-found",
                          'description' => "dxw's review didn't find anything worrying in this plugin. It's probably safe."),
    'yellow'    => array( 'message' => "Use with caution",
                          'slug' => "use-with-caution",
                          'description' => "Before using this plugin, you should carefully consider the findings of dxw's review."),
    'red'       => array( 'message' => "Potentially unsafe",
                          'slug' => "potentially-unsafe",
                          'description' => "Before using this plugin, you should very carefully consider its potential problems and should conduct a thorough assessment."),
    'not-found' => array( 'message' => "Not yet reviewed",
                          'slug' => "no-info",
                          'description' => "We haven't reviewed this plugin yet. If you like we can review it for you."),
  );

  public function __construct($version, $status, $reason="", $link=DXW_SECURITY_PLUGINS_URL) {
    $this->version = $version;
    $this->reason = $reason;
    $this->link = $link;

    $review_status = self::$dxw_security_review_statuses[$status];
    $this->message       = $review_status['message'];
    $this->description   = $review_status['description'];
    $this->slug          = $review_status['slug'];
  }

  public function render() {
    // reason is retrieved from the api but might legitimately include html
    // description and heading might also legitimately include html but come from strings in this code
    $link = esc_url($this->link)
    ?>
      <h2><a href="<?php echo $link ?>"><?php echo $this->heading() ?></a></h2>
      <p class="review-status-description"><?php echo $this->description ?></p>
      <?php
        if (empty($this->reason)) {
          echo("<a href='{$link}' class='read-more' >See the dxw Security website for details</a>");
        } else {
          print_r("<p>{$this->reason}</p>");
          echo("<a href='{$link}' class='read-more button-primary'> Read more...</a>");
        }
      ?>
    <?php
  }

  public function heading() {
    return "<span class='icon-{$this->slug} ?>'></span> {$this->message}";
  }

  // Versions might be a comma separated string with no spaces e.g. "1.9.2,1.9.3"
  public function version() {
    return implode(", ", explode(",", $this->version));
  }
}

class Other_Version_Reviews_Data {
  # Expects an array of Review_Data objects
  public function __construct($reviews) {
    $this->reviews = $reviews;
  }

  public function render() {
    // TODO - this will result in two consecutive h2 headings - not great, but works for now.
    foreach($this->reviews as &$review) {
      ?>
        <div class="other-review <?php echo $review->slug ?>">
          <h2>Version <?php echo $review->version ?></h2>
          <?php print_r($review->render()) ?>
        </div>
      <?php
    }
  }

  public function most_recent() {
    return current($this->reviews);
  }

  // It would be nice to use array_map for this, but it doesn't seem to be possible to do that without defining a callback function in the global namespace.
  public function versions() {
    $versions=array();
    foreach($this->reviews as &$review) {
      $versions[] = $review->version();
    }
    return implode(", ", $versions);
  }
}


class Plugin_Review_API extends Dxw_Security_API {

  private $plugin_file;

  public function __construct($plugin_file) {
    $this->plugin_file = $plugin_file;
  }

  // TODO: Currently this only handles directory plugins
  protected function api_path() {
    return "/directory_plugins/{$this->plugin_name()}/reviews/";
  }

  protected function cache_slug() {
    return $this->plugin_file;
  }

  // The API will return a json body. This function defines how we get the data we want out of that (once it's been parsed into a php object)
  protected function extract_data($parsed_body) {
    return $parsed_body->reviews;
  }

  private function plugin_name() {
    // Versions of php before 5.4 don't allow array indexes to be accessed directly on the output of functions
    //   http://www.php.net/manual/en/migration54.new-features.php - "Function array dereferencing"
    $f = explode('/', $this->plugin_file);
    return $f[0];
  }
}


// php doesn't support nested classes so these need to live outside the API class
class Dxw_Security_Error extends Exception { }

// TODO: Not sure this is the right name: this is for getting one specific plugin...
class Dxw_Security_API {
  // TODO: This class doesn't work on it's own, only when extended by a class which defines the following:
  //    functions:
  //      * $api_path()
  //      * cache_slug()
  //      * extract_data($parsed_body)
  // Is there a standard way of doing this? should it complain on construction if those things aren't defined

  public function call() {
    $data = $this->retrieve_api_data();

    // TODO: Transience returns false if it doesn't have the key, but should we also try to retrieve the result if the cache returned empty?
    if($data === false) {
      $data = $this->get();
    }
    return $data;
  }

  private function get() {
    $api_root = DXW_SECURITY_API_ROOT;
    $api_path = $this->api_path();

    // this should exist in core, but doesn't seem to:
    // $url = http_build_url(
    //   array(
    //     "host"  => $api_root,
    //     "path"  => $api_path,
    //     "query" => $query
    //   )
    // );
    $url = $api_root . $api_path;

    $response = wp_remote_get($url);

    return $this->handle_response($response);
  }

  // Either return a review or throw an error
  private function handle_response($response) {
    if (is_wp_error($response)) {
      throw new Dxw_Security_Error($response->get_error_message());

    } else {
      switch ($response['response']['code']) {
        case 200:
          $parsed_body = $this->parse_response_body($response['body']);
          $data = $this->extract_data($parsed_body);
          $this->cache_api_data($data);
          return $data;

        case 404:
          throw new Dxw_Security_NotFound();

        default:
          // TODO: handle other codes individually?
          // A redirect would end up here - is it possible to get one??
          throw new Dxw_Security_Error("Response was {$response['response']['code']}: {$response['body']}");
      };
    }
  }

  private function parse_response_body($body) {
    $parsed_body = json_decode($body);

    if (!is_null($parsed_body)) {
      return $parsed_body;
    } else {
      $truncated_body = mb_substr($body, 0, 100);
      throw new Dxw_Security_Error("Couldn't parse json body beginning: {$truncated_body}");
    }
  }

  private function cache_api_data($data) {
    if (DXW_SECURITY_CACHE_RESPONSES) {
      $slug = $this->cache_slug();
      // TODO: How long should this get cached for?
      set_transient($slug, $data, HOUR_IN_SECONDS);
    }
  }
  private function retrieve_api_data() {
    if (DXW_SECURITY_CACHE_RESPONSES) {
      $slug = $this->cache_slug();
      return get_transient($slug);
    } else {
      return false;
    }
  }
}

new dxw_Security();