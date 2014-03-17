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
  if( 'plugins.php' != $hook )
    return;
  $stylesheet_url = plugins_url( '/styles/style.css' , __FILE__ );
  wp_enqueue_style( 'dxw-security-plugin-styles', $stylesheet_url );
} );

add_action('admin_init', function() {
  $column = new Dxw_Security_Column;
});


class Dxw_Security_Column {
  // Track the number of failed requests so that we can stop trying after a certain number.
  var $dxw_security_failures = 0;

  public function __construct() {
    add_filter('manage_plugins_columns', function($columns) {
      return insertAt(1, array(DXW_SECURITY_COLUMN_NAME => ""), $columns);
    });

    add_action('manage_plugins_custom_column',function($column_name, $plugin_file, $plugin_data){
      if($column_name == DXW_SECURITY_COLUMN_NAME) {
        # TODO: Currently this only handles codex plugins
        $plugin_link = 'http://wordpress.org/plugins/' . explode('/',$plugin_file)[0] . '/';
        $plugin_version = $plugin_data['Version'];

        $this->securityColumnContent($plugin_link, $plugin_version);
      };
    }, 10, 3);
  }


  function securityColumnContent($plugin_link, $plugin_version) {
    if ( $this->dxw_security_failures >= DXW_SECURITY_FAILURE_lIMIT ) {
      echo $this->fatalError();
      return;
    }

    $response = $this->getPluginReview($plugin_link, $plugin_version);

    if ( is_wp_error($response) ) {
      # TODO: in future we should provide some way for users to give us back some useful information when they get this error
      echo $this->fatalError();

    } else {

      switch ( $response['response']['code'] ) {
        case 200:
          $response_body = json_decode( $response['body'] )->review;
          echo $this->displayStatusIcon($response_body->review_link, $response_body->recommendation);
          break;
        case 404:
          echo $this->noInfo();
          break;
        // TODO: Perhaps the following won't ever happen? Will just be caught by WP_Error?
        //   Perhaps we should behave differently in different cases
        case 400:
          echo $this->fatalError();
          break;
        case 500:
          echo $this->fatalError();
          break;
        default:
          // A redirect would end up here - is it possible to get one??
          echo $this->fatalError();
      };
    };
  }

  private function getPluginReview($plugin_url, $plugin_version) {
    $api_root = DXW_SECURITY_API_ROOT;
    $api_path = "/reviews";
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

    $result = wp_remote_get($url);
    return $result;
  }

  private function displayStatusIcon($link, $recommendation) {
    switch ($recommendation) {
      case 'green':
        return $this->noIssuesFound($link);
        break;
      case 'yellow':
        return $this->useWithCaution($link);
        break;
      case 'red':
        return $this->potentiallyUnsafe($link);
        break;
      // default:
      //   TODO: error??? This is definitely an exception case;
    };
  }

  private function noIssuesFound($link) {
    return $this->statusIcon($link, "&#10003;", "status no_issues_found", "No issues found");
  }
  private function useWithCaution($link) {
    return $this->statusIcon($link, "?", "status use_with_caution", "Use with caution");
  }
  private function potentiallyUnsafe($link) {
    return $this->statusIcon($link, "&#10007;", "status potentially_unsafe", "Potentially unsafe");
  }
  private function noInfo() {
    return $this->statusIcon(DXW_SECURITY_PLUGINS_URL, "-", "status no_info", "This plugin has not yet been reviewed by dxw");
  }
  private function fatalError() {
    $this->dxw_security_failures++;
    return $this->statusIcon(DXW_SECURITY_PLUGINS_URL, "??", "fatal_error", "Sorry - we couldn't connect to the dxw Security service. Please try again later");
  }


  private function statusIcon($link, $character, $class, $title_text) {
    return "<a href='" . $link . "' title='" . esc_html($title_text)
     . "' class='" . $class . "''>" . $character . "</a>";
  }
}

// Surely this already exists???
function insertAt($index, $element, $array) {
  $array =
    array_slice($array, 0, $index, true) +
    $element +
    array_slice($array, $index , count($array) - 1, true);
  return $array;
}