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

add_action('admin_init', function() { new Dxw_Security_Column; });

class Dxw_Security_Column {
  // Track the number of failed requests so that we can stop trying after a certain number.
  public $dxw_security_failures = 0;

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
    $response = $this->get_plugin_review_response($plugin_file, $plugin_data);

    if ( is_wp_error($response) ) {
      # TODO: in future we should provide some way for users to give us back some useful information when they get this error
      $message = "An error occurred - please try again later";

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
        // TODO: Perhaps the following won't ever happen? Will just be caught by WP_Error?
        //   Perhaps we should behave differently in different cases
        case 400:
          $message = "An error occurred - please try again later";
          break;
        case 500:
          $message = "An error occurred - please try again later";
          break;
        default:
          // A redirect would end up here - is it possible to get one??
          $message = "An error occurred - please try again later";
      };

      if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }

      return "dxw Security recommendation: <a href='{$review_link}'>{$message}</a>";
    };
  }


  private function get_plugin_review_response($plugin_file, $plugin_data) {
    $plugin_version = $plugin_data['Version'];
    $response = $this->retrieve_plugin_review_response($plugin_file, $plugin_version);

    if($response === false) {

      $api_root = DXW_SECURITY_API_ROOT;
      $api_path = "/reviews";

      # TODO: Currently this only handles codex plugins
      $plugin_url = 'http://wordpress.org/plugins/' . explode('/',$plugin_file)[0] . '/';

      $query = http_build_query(
        array(
          'codex_link'=>$plugin_url,
          'version'=>$plugin_version
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
      $this->cache_plugin_review_response($plugin_file, $plugin_version, $response);
    }

    return $response;
  }

  private function cache_plugin_review_response($plugin_file, $plugin_version, $response) {
    $slug = $this->plugin_review_response_slug($plugin_file, $plugin_version);
    set_transient( $slug, $response, DAY_IN_SECONDS );
  }
  private function retrieve_plugin_review_response($plugin_file, $plugin_version) {
    $slug = $this->plugin_review_response_slug($plugin_file, $plugin_version);
    return get_transient($slug);
  }
  private function plugin_review_response_slug($plugin_file, $plugin_version) {
    return $plugin_file . $plugin_version;
  }

  private function add_review_reason($plugin_file) {
    // add_action( "after_plugin_row_$plugin_file", function ($file, $plugin_data) {
    add_action( "after_plugin_row_$plugin_file", function($plugin_file, $plugin_data, $status) {
      $response = $this->get_plugin_review_response($plugin_file, $plugin_data);

      if ( is_wp_error($response) ) {
        // Do nothing - shouldn't get here

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
            // Do nothing - shouldn't get here
            break;
          // TODO: Perhaps the following won't ever happen? Will just be caught by WP_Error?
          //   Perhaps we should behave differently in different cases
          case 400:
            // Do nothing - shouldn't get here
            break;
          case 500:
            // Do nothing - shouldn't get here
            break;
          default:
            // A redirect would end up here - is it possible to get one??
            // Do nothing - shouldn't get here
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

// CURRENTLY NOT USED - ditch it if it's not useful
// Surely this already exists???
function insert_at($index, $element, $array) {
  $array =
    array_slice($array, 0, $index, true) +
    $element +
    array_slice($array, $index , count($array) - 1, true);
  return $array;
}