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
  var $dxw_security_failures = 0;

  public function __construct() {
    add_filter('plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status) {

      $plugin_meta[] = $this->security_plugin_meta($plugin_file, $plugin_data);
      return $plugin_meta;
    }, 10, 4);
  }

  function security_plugin_meta($plugin_file, $plugin_data) {
    $plugin_version = $plugin_data['Version'];
    $response = $this->get_plugin_review_response($plugin_file, $plugin_version);

    $review_link = DXW_SECURITY_PLUGINS_URL;

    if ( is_wp_error($response) ) {
      # TODO: in future we should provide some way for users to give us back some useful information when they get this error
      $message = "An error occurred - please try again later";

    } else {

      switch ( $response['response']['code'] ) {
        case 200:
          $review = json_decode( $response['body'] )->review;

          $recommendation = $review->recommendation;
          $review_link = $review->review_link;

          switch ($recommendation) {
            case 'green':
              $message = "No issues found";
              break;
            case 'yellow':
              $message = "Use with caution";
              $this->add_review_reason($plugin_file);
              break;
            case 'red':
              $message = "Potentially unsafe";
              $this->add_review_reason($plugin_file);
              break;
            // default:
            //   TODO: error??? This is definitely an exception case;
          };
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

      return "dxw Security review: <a href='{$review_link}'>{$message}</a>";
    };
  }


  // function security_column_content($plugin_file, $plugin_version) {
  //   if ( $this->dxw_security_failures >= DXW_SECURITY_FAILURE_lIMIT ) {
  //     echo $this->fatal_error();
  //     return;
  //   }

  //   $response = $this->get_plugin_review_response($plugin_file, $plugin_version);

  //   if ( is_wp_error($response) ) {
  //     # TODO: in future we should provide some way for users to give us back some useful information when they get this error
  //     echo $this->fatal_error();

  //   } else {

  //     switch ( $response['response']['code'] ) {
  //       case 200:
  //         $review = json_decode( $response['body'] )->review;
  //         echo $this->display_status_icon($plugin_file, $review);
  //         break;
  //       case 404:
  //         echo $this->no_info();
  //         break;
  //       // TODO: Perhaps the following won't ever happen? Will just be caught by WP_Error?
  //       //   Perhaps we should behave differently in different cases
  //       case 400:
  //         echo $this->fatal_error();
  //         break;
  //       case 500:
  //         echo $this->fatal_error();
  //         break;
  //       default:
  //         // A redirect would end up here - is it possible to get one??
  //         echo $this->fatal_error();
  //     };
  //   };
  // }

  private function get_plugin_review_response($plugin_file, $plugin_version) {
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
      // TODO: repeated...
      $plugin_version = $plugin_data['Version'];

      $response = $this->get_plugin_review_response($plugin_file, $plugin_version);

      if ( is_wp_error($response) ) {
        // Do nothing - shouldn't get here

      } else {

        switch ( $response['response']['code'] ) {
          case 200:
            $review = json_decode( $response['body'] )->review;

            $recommendation = $review->recommendation;
            $review_link = $review->review_link;
            $reason = $review->reason;

            switch ($recommendation) {
              case 'green':
                // Do nothing - shouldn't get here
                break;
              case 'yellow':
                $message = "Use with caution";
                $box_class = "use_with_caution";
                break;
              case 'red':
                $message = "Potentially unsafe";
                $box_class = "potentially_unsafe";
                break;
              // default:
              //   TODO: error??? This is definitely an exception case;
            };
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

      // Defaults:
      if ( empty($review_link) ) { $review_link = DXW_SECURITY_PLUGINS_URL; }
      if ( empty($reason) ) { $reason = "See the dxw Security website for details"; }

      $row_class = $this->row_class($plugin_file, $plugin_data);

      // Presumably colspanchange is something to do with responsiveness
      echo("<tr class='plugin-review-tr {$row_class}'>");
      echo("  <td colspan='4' class='plugin-review colspanchange'>");
      echo("    <div class='review-message {$box_class}'>");
      echo("      <a href='{$review_link}'><h4>dxw Security review: {$message}</h4></a>");
      print_r($reason);
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

  // private function display_status_icon($plugin_file, $review) {
  //   $link = $review->review_link;
  //   $recommendation = $review->recommendation;

  //   switch ($recommendation) {
  //     case 'green':
  //       return $this->no_issues_found($link);
  //     case 'yellow':
  //       $this->add_review_failure_reason($plugin_file);
  //       return $this->use_with_caution($link);
  //     case 'red':
  //       $this->add_review_failure_reason($plugin_file);
  //       return $this->potentially_unsafe($link);
  //     // default:
  //     //   TODO: error??? This is definitely an exception case;
  //   };
  // }

  private function no_issues_found($link) {
    return $this->status_icon($link, "&#10003;", "status no_issues_found", "No issues found");
  }
  private function use_with_caution($link) {
    return $this->status_icon($link, "?", "status use_with_caution", "Use with caution");
  }
  private function potentially_unsafe($link) {
    return $this->status_icon($link, "&#10007;", "status potentially_unsafe", "Potentially unsafe");
  }
  private function no_info() {
    return $this->status_icon(DXW_SECURITY_PLUGINS_URL, "-", "status no_info", "This plugin has not yet been reviewed by dxw");
  }
  private function fatal_error() {
    $this->dxw_security_failures++;
    return $this->status_icon(DXW_SECURITY_PLUGINS_URL, "??", "fatal_error", "Sorry - we couldn't connect to the dxw Security service. Please try again later");
  }


  private function status_icon($link, $character, $class, $title_text) {
    return "<a href='" . $link . "' title='" . esc_html($title_text)
     . "' class='" . $class . "''>" . $character . "</a>";
  }
}

// Surely this already exists???
function insert_at($index, $element, $array) {
  $array =
    array_slice($array, 0, $index, true) +
    $element +
    array_slice($array, $index , count($array) - 1, true);
  return $array;
}