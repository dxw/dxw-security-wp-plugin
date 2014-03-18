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
if (!defined('DXW_SECURITY_COLUMN_NAME')) {
  define('DXW_SECURITY_COLUMN_NAME', 'Security');
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
} );

add_action('admin_init', function() { new Dxw_Security_Review_Data; });

// TODO: this name is wrong...
class Dxw_Security_Review_Data {
  // Track the number of failed requests so that we can stop trying after a certain number.
  public $dxw_security_failed_requests = 0;

  # TODO: this should be some kind of constant, but we couldn't work out how. Static didn't work, and class consts can't contain arrays
  public $review_statuses = array(
    'green'  => array( 'message' => "No issues found",  'slug' => "no_issues_found", 'failure' => false, 'icon_fallback' => "&#10003;"),
    'yellow' => array( 'message' => "Use with caution", 'slug' => "use_with_caution", 'failure' => true, 'icon_fallback' => "?"),
    'red'    => array( 'message' => "Potentially unsafe", 'slug' => "potentially_unsafe", 'failure' => true, 'icon_fallback' => "&#10007;"),
  );

  public function __construct() {
    add_filter('plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status) {

      $plugin_meta[] = $this->security_plugin_meta($plugin_file, $plugin_data);
      return $plugin_meta;
    }, 10, 4);
  }

  function security_plugin_meta($plugin_file, $plugin_data) {
    // TODO: this isn't very dry...
    // Stop making requests after a certain number of failures
    if ( $this->dxw_security_failed_requests > DXW_SECURITY_FAILURE_lIMIT ) {
      $review_link = DXW_SECURITY_PLUGINS_URL;
      $message = "An error occurred - please try again later";
      return "dxw Security recommendation: <a href='{$review_link}'>{$message}</a>";
    }

    $api = new Dxw_Security_Api($plugin_file, $plugin_data);
    $response = $api->get_plugin_review_response();

    if ( is_wp_error($response) ) {
      # TODO: in future we should provide some way for users to give us back some useful information when they get this error
      $message = "An error occurred - please try again later";
      $dxw_security_failed_requests++;
    } else {

      switch ( $response['response']['code'] ) {
        case 200:
          $review = json_decode( $response['body'] )->review;

          $status = $this->review_statuses[$review->recommendation];
          $message = $status['message'];
          $review_link = $review->review_link;

          if ( $status['failure'] ) { $this->add_review_reason($plugin_file); }
          break;
        case 404:
          $message = "No info";
          break;
        // TODO: handle other codes individually?
        default:
          // A redirect would end up here - is it possible to get one??
          $message = "An error occurred - please try again later";
          $dxw_security_failed_requests++;
      };

      if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }

      return "dxw Security recommendation: <a href='{$review_link}'>{$message}</a>";
    };
  }

  private function add_review_reason($plugin_file) {
    // add_action( "after_plugin_row_$plugin_file", function ($file, $plugin_data) {
    add_action( "after_plugin_row_$plugin_file", function($plugin_file, $plugin_data, $status) {
      // TODO: do we need to do the "Stop making requests after a certain number of failures" thing here too?

      $api = new Dxw_Security_Api($plugin_file, $plugin_data);
      $response = $api->get_plugin_review_response();

      // TODO: What should we do in the error cases below? Displaying nothing would probably be fine...
      if ( is_wp_error($response) ) {
        // Shouldn't get here, but it IS possible.

      } else {

        switch ( $response['response']['code'] ) {
          case 200:
            $review = json_decode( $response['body'] )->review;

            // TODO: Defensive programming? We're currently trusting that this will only ever get called with "red" or "yellow" recommendations
            $status = $this->review_statuses[$review->recommendation];
            $message = $status['message'];
            $box_class = $status['slug'];
            $review_link = $review->review_link;
            $reason = $review->reason;
            break;
          case 404:
            // Shouldn't get here, but it IS possible.
            break;
          // TODO: handle other codes individually?
          default:
            // A redirect would end up here - is it possible to get one??
            // Shouldn't get here, but it IS possible.
        };
      };

      if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }

      $row_class = $this->row_class($plugin_file, $plugin_data);

      // Presumably colspanchange is something to do with responsiveness
      echo("<tr class='plugin-review-tr {$row_class}'>");
      echo("  <td colspan='4' class='plugin-review colspanchange'>");
      echo("    <div class='review-message {$box_class}'>");
      echo("      <a href='{$review_link}'><h4>dxw Security recommendation: {$message}</h4></a>");
      if ( empty($reason) ) {
        echo("<a href='{$review_link}'>See the dxw Security website for details</a>");
      } else {
        print_r($reason);
        echo("<a href='{$review_link}'> Read more...</a>");
      }
      echo("</div></td></tr>");
    }, 10, 3);
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

# TODO - not sure this is the right name: this is for getting one specific plugin...
class Dxw_Security_Api {
  public $plugin_file;
  public $plugin_version;

  public function __construct($plugin_file, $plugin_data) {
    $this->plugin_file = $plugin_file;
    $this->plugin_version = $plugin_data['Version'];
  }

  public function get_plugin_review_response() {
    $response = $this->retrieve_plugin_review_response();

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
      $this->cache_plugin_review_response($response);
    }

    return $response;
  }

  private function cache_plugin_review_response($response) {
    $slug = $this->plugin_review_response_slug();
    set_transient( $slug, $response, DAY_IN_SECONDS );
  }
  private function retrieve_plugin_review_response() {
    $slug = $this->plugin_review_response_slug();
    return get_transient($slug);
  }
  private function plugin_review_response_slug() {
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