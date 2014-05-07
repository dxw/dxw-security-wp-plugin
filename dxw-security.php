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
  }

  public function enqueue_scripts($hook) {
    if('plugins.php' != $hook) { return; }

    wp_enqueue_style('dxw-security-plugin-styles', plugins_url('/assets/main.min.css' , __FILE__));
    wp_enqueue_script('dxw-security-plugin-scripts', plugins_url('/assets/main.min.js' , __FILE__));

    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-dialog');
  }

  public function add_security_column() {
    new Plugin_Review_Column;
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
    // Stop making requests after a certain number of failures:
    if ($this->dxw_security_failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $review = new Null_Plugin_Review();

    } else {

      $name = $plugin_data['Name'];
      $api = new Plugin_Review_API($plugin_file, $plugin_data);

      try {
        $review_data = $api->call();

        $reason = $review_data->reason;
        $status = $review_data->recommendation;
        $link = $review_data->review_link;

        $review = new Plugin_Review($name, $status, $reason, $link);

      } catch (Dxw_Security_NotFound $e) {
        $review = new Plugin_Review($name, 'not-found');

      } catch (Exception $e) {
        // TODO: Handle Dxw_Security_Error separately?
        // TODO: in future we should provide some way for users to give us back some useful information when they get an error
        $this->dxw_security_failed_requests++;

        $review = new Null_Plugin_Review();
      }
    }

    $review->view();
  }
}

class Plugin_Review {
  private $name;
  private $link;
  private $reason;
  private $message;
  private $description;
  private $slug;

  public function __construct($name, $status, $reason="", $link=DXW_SECURITY_PLUGINS_URL) {
    $this->name = $name;
    $this->link = $link;
    $this->reason = $reason;

    $review_status = $this->review_statuses[$status];
    $this->message = $review_status['message'];
    $this->description = $review_status['description'];
    $this->slug = $review_status['slug'];
  }

  // TODO: this should be some kind of constant, but we couldn't work out how. Static didn't work, and class consts can't contain arrays
  private $review_statuses = array(
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


  public function view() {
    $name = esc_attr($this->name);
    $slug = esc_attr($this->slug);
    $message = esc_html($this->message);

    $dialog_id = "plugin-inspection-results" . sanitize_title($this->name);

    ?>
      <a href="#<?php echo $dialog_id; ?>" data-title="<?php echo $name; ?>" class="dialog-link review-message <?php echo $slug; ?>">
        <h3><span class='icon-<?php echo $slug; ?>'></span> <?php echo $message; ?></h3>

        <p class="more-info">More information</p>
      </a>

      <?php print_r($this->view_dialog($dialog_id)); ?>
    <?php
  }

  private function view_dialog($dialog_id){
    $dialog_id = esc_attr($dialog_id); // Trust no-one!
    $slug = esc_attr($this->slug);
    $link = esc_url($this->link);
    $message = esc_html($this->message);

    // These might legitimately contain html:
    $description = $this->description;
    $reason = $this->reason;

    ?>
      <div id="<?php echo $dialog_id; ?>" style="display:none;" class="dialog review-message <?php echo $slug; ?>">

        <a href="http://security.dxw.com" id="dxw-sec-link"><img src="<?php echo plugins_url('/assets/dxw-logo.png' , __FILE__); ?>" alt="dxw logo" /></a>

        <div class="inner">
          <h2><a href="<?php echo $link ?>"><span class="icon-<?php echo $slug ?>"></span> <?php echo $message ?></a></h2>
          <p class="review-status-description"><?php echo $description ?></p>
          <?php
            if (empty($reason)) {
              echo("<a href='{$link}' class='read-more' >See the dxw Security website for details</a>");
            } else {
              print_r("<p>{$reason}</p>");
              echo("<a href='{$link}' class='read-more button-primary'> Read more...</a>");
            }
          ?>
        </div>

      </div>
    <?php
  }
}

class Null_Plugin_Review {
  public function view(){
    ?>
    <div class="review-message review-error">
      <h3><a href='<?php echo(esc_url(DXW_SECURITY_PLUGINS_URL)); ?>'>An error occurred</a></h3>
      <p>Please try again later</p>
    </div>
    <?php
  }
}


class Plugin_Review_API extends Dxw_Security_API {

  private $plugin_file;
  private $plugin_version;

  // TODO: Currently this only handles directory plugins
  protected function api_path() {
    return "/directory_plugins/{$this->plugin_name()}/reviews/{$this->plugin_version}";
  }

  public function __construct($plugin_file, $plugin_data) {
    $this->plugin_file = $plugin_file;
    $this->plugin_version = $plugin_data['Version'];
  }

  protected function cache_slug() {
    return $this->plugin_file . $this->plugin_version;
  }

  // The API will return a json body. This function defines how we get the data we want out of that (once it's been parsed into a php object)
  protected function extract_data($parsed_body) {
    return $parsed_body->review;
  }

  private function plugin_name() {
    // Versions of php before 5.4 don't allow array indexes to be accessed directly on the output of functions
    //   http://www.php.net/manual/en/migration54.new-features.php - "Function array dereferencing"
    $f = explode('/', $this->plugin_file);
    return $f[0];
  }
}


// php doesn't support nested classes so these need to live outside the API class
class Dxw_Security_NotFound extends Exception { }
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