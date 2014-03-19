<?php
// Plugin Name: dxw Security
// Plugin URI: https://security.dxw.com/plugin
// Description: Pulls plugin review information from dxw Security into the wordpress plugins screen
// Version: 0.0.0
// License: GPLv2
// Author: dxw
// Author URI: http://dxw.com/

// CONFIG:
if (!defined('DXW_SECURITY_API_ROOT')) {
  define('DXW_SECURITY_API_ROOT', 'https://security.dxw.com/api');
}
if (!defined('DXW_SECURITY_CACHE_RESPONSES')) {
  define('DXW_SECURITY_CACHE_RESPONSES', true);
}

// CONSTANTS:
// How many failed requests will we tolerate?
define('DXW_SECURITY_FAILURE_lIMIT', 5);
// The URL we link to when we don't have any info about a plugin
define('DXW_SECURITY_PLUGINS_URL', 'https://security.dxw.com/plugins/');


add_action( 'admin_enqueue_scripts', function($hook) {
  if( 'plugins.php' != $hook ) { return; }

  $stylesheet_url = plugins_url( '/styles/style.css' , __FILE__ );
  wp_enqueue_style( 'dxw-security-plugin-styles', $stylesheet_url );

  // TODO: This seems like a really inefficient way to include one line of js... Is there a better way?
  $script_url = plugins_url( '/scripts/script.js' , __FILE__ );
  wp_enqueue_script( 'dxw-security-plugin-scripts', $script_url );
} );

add_action('admin_init', function() { new Dxw_Security_Review_Data; });

// TODO: this name is wrong...
class Dxw_Security_Review_Data {
  // Track the number of failed requests so that we can stop trying after a certain number.
  // This should apply per page load, but ideally this behaviour might be better handled by the API class (?)
  public $dxw_security_failed_requests = 0;

  // TODO: this should be some kind of constant, but we couldn't work out how. Static didn't work, and class consts can't contain arrays
  public $review_statuses = array(
    'green'  => array( 'message' => "No issues found",  'slug' => "no-issues-found", 'failure' => false, 'icon_fallback' => "&#10003;"),
    'yellow' => array( 'message' => "Use with caution", 'slug' => "use-with-caution", 'failure' => true, 'icon_fallback' => "?"),
    'red'    => array( 'message' => "Potentially unsafe", 'slug' => "potentially-unsafe", 'failure' => true, 'icon_fallback' => "&#10007;"),
  );

  public function __construct() {
    add_filter('plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status) {

      $plugin_meta[] = $this->security_plugin_meta($plugin_file, $plugin_data);
      return $plugin_meta;
    }, 10, 4);
  }

  function security_plugin_meta($plugin_file, $plugin_data) {
    // Stop making requests after a certain number of failures:
    if ( $this->dxw_security_failed_requests > DXW_SECURITY_FAILURE_lIMIT ) {
      $message = "An error occurred - please try again later";
    } else {

      $api = new Dxw_Security_Api($plugin_file, $plugin_data);

      try {
        $review = $api->get_plugin_review();

        $review_link = $review->review_link;

        $status = $this->review_statuses[$review->recommendation];
        $message = $status['message'];
        $slug = $status['slug'];
        if ( $status['failure'] ) { $this->add_review_reason($plugin_file); }

      } catch ( Dxw_Security_NotFound $e ) {
        $message = "No info";
        $slug = "no-info";
      } catch ( Dxw_Security_Error $e ) {
        // TODO: in future we should provide some way for users to give us back some useful information when they get an error
        $message = "An error occurred - please try again later";

        $this->dxw_security_failed_requests++;
      }
    }
    if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }
    // TODO: fallback icon for errors?
    if ( empty($slug) ) { $slug = ""; }

    // TODO: title text on this link?
    return "<span class='icon-{$slug}'></span> dxw Security recommendation: <a href='{$review_link}'>{$message}</a>";
  }

  private function add_review_reason($plugin_file) {
    // add_action( "after_plugin_row_$plugin_file", function ($file, $plugin_data) {
    add_action( "after_plugin_row_$plugin_file", function($plugin_file, $plugin_data, $status) {

      // TODO: do we need to do the "Stop making requests after a certain number of failures" thing here too?
      //   In theory we should only get successes here, but it's in principle possible to get failures - see below.

      $api = new Dxw_Security_Api($plugin_file, $plugin_data);

      try {
        $review = $api->get_plugin_review();

        $review_link = $review->review_link;
        $reason = $review->reason;

        // TODO: Needs to be a bit more Defensive? We're currently trusting that this will only ever get called with "red" or "yellow" recommendations...
        $status = $this->review_statuses[$review->recommendation];
        $message = $status['message'];
        $slug = $status['slug'];

        $row_class = $this->row_class($plugin_file, $plugin_data);

        $this->review_info_box($row_class, $slug, $review_link, $reason, $message);

      // TODO: What should we do in the error cases below? Displaying nothing would probably be fine...
      } catch ( Dxw_Security_NotFound $e ) {
        // Shouldn't get here, but it's theoretically possible, if the API starts behaving badly....
      } catch ( Dxw_Security_Error $e ) {
        // TODO: in future we should provide some way for users to give us back some useful information when they get this error
        // Shouldn't get here, but it's theoretically possible - e.g. if the cache expires AND the service goes down in-between the first request and this one...
      }

    }, 10, 3);
  }

  private function review_info_box($row_class, $slug, $review_link, $reason, $message) {
    if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }

    // TODO: title text on these links?

    // Presumably colspanchange is something to do with responsiveness
    echo("<tr class='plugin-review-tr {$row_class}'>");
    echo("  <td colspan='4' class='plugin-review colspanchange'>");
    echo("    <div class='review-message {$slug}'>");
    echo("      <a href='{$review_link}'><h4><span class='icon-{$slug}'></span> dxw Security recommendation: {$message}</h4></a>");
    if ( empty($reason) ) {
      echo("<a href='{$review_link}'>See the dxw Security website for details</a>");
    } else {
      print_r($reason);
      echo("<a href='{$review_link}'> Read more...</a>");
    }
    echo("</div></td></tr>");
  }

  private function row_class($plugin_file, $plugin_data) {
    // mostly cribbed from the WP_Plugins_List_Table:
    // TODO: This doesn't handle multisite (?)
    $class = "";
    if ( is_plugin_active( $plugin_file ) ) { $class .= 'active_plugin'; }
    if (! empty( $plugin_data['update'] ) ) { $class .= ' update_plugin'; }
    return $class;
  }
}


// php doesn't support nested classes
class Dxw_Security_NotFound extends Exception { }
class Dxw_Security_Error extends Exception { }

// TODO - not sure this is the right name: this is for getting one specific plugin...
class Dxw_Security_Api {

  public $plugin_file;
  public $plugin_version;

  public function __construct($plugin_file, $plugin_data) {
    $this->plugin_file = $plugin_file;
    $this->plugin_version = $plugin_data['Version'];
  }

  public function get_plugin_review() {
    if ( DXW_SECURITY_CACHE_RESPONSES ) {
      $response = $this->retrieve_plugin_review();
    } else {
      $response = false;
    }

    // TODO: transience returns false if it doesn't have the key, but should we also try to retrieve the result if the cache returned empty?
    if($response === false) {

      $api_root = DXW_SECURITY_API_ROOT;
      $api_path = "/reviews";

      # TODO: Currently this only handles codex plugins
      $plugin_url = 'http://wordpress.org/plugins/' . explode('/',$this->plugin_file)[0] . '/';

      $query = http_build_query(
        array(
          'codex_link'=>$plugin_url,
          'version'=>$this->plugin_version
        )
      );
      // this should exist in core, but doesn't seem to:
      // $url = http_build_url(
      //   array(
      //     "host"  => $api_root,
      //     "path"  => $api_path,
      //     "query" => $query
      //   )
      // );
      $url = $api_root . $api_path . '?' . $query;

      $response = wp_remote_get($url);

      if ( is_wp_error($response) ) {
        throw new Dxw_Security_Error( $response->get_error_message() );

      } else {

        switch ( $response['response']['code'] ) {
          case 200:
            // TODO: handle the case where we get an unparseable body
            $review = json_decode( $response['body'] )->review;

            if ( DXW_SECURITY_CACHE_RESPONSES ) {
              $this->cache_plugin_review($review);
            }
            return $review;
          case 404:
            throw new Dxw_Security_NotFound();
            break;
          default:
            // TODO: handle other codes individually?
            // A redirect would end up here - is it possible to get one??
            throw new Dxw_Security_Error( "Response was {$response['response']['code']}: {$response['body']}" );
        };
      }
    }

    return $response;
  }

  private function cache_plugin_review($response) {
    $slug = $this->plugin_review_slug();
    // TODO: How long should this get cached for?
    set_transient( $slug, $response, HOUR_IN_SECONDS );
  }
  private function retrieve_plugin_review() {
    $slug = $this->plugin_review_slug();
    return get_transient($slug);
  }
  private function plugin_review_slug() {
    return $this->plugin_file . $this->plugin_version;
  }
}


// CURRENTLY NOT USED - ditch it if it's not useful
// Surely this already exists???
function insert_at($index, $element, $array) {
  $array =
    array_slice($array, 0, $index, true) +
    $element +
    array_slice($array, $index , count($array) - 1, true);
  return $array;
}