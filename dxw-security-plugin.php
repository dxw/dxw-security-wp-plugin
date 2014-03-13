<?php
// Plugin Name: dxw Security
// Plugin URI: https://security.dxw.com/plugin
// Description: Pulls plugin review information from dxw Security into the wordpress plugins screen
// Version: 0.0.0
// License: GPLv2
// Author: dxw
// Author URI: http://dxw.com/


function GetPluginReview($plugin_url, $plugin_version) {
  $api_root = "http://localhost:3000";
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
  $url = $api_root . '/' . $api_path . '?' . $query;

  $result = wp_remote_get($url);
  return $result;
}

add_action( 'admin_enqueue_scripts', function($hook) {
  if( 'plugins.php' != $hook )
    return;
  $stylesheet_url = plugins_url( '/styles/style.css' , __FILE__ );
  wp_enqueue_style( 'dxw-security-plugin-styles', $stylesheet_url );
} );

add_action('admin_init', function() {
  add_filter('manage_plugins_columns', function($columns) {
    return insert_at(1, array("Security" => ""), $columns);
  });

  add_action('manage_plugins_custom_column',function($column_name, $plugin_file, $plugin_data){
    if($column_name =='Security') {
      $plugin_link = 'http://wordpress.org/plugins/' . explode('/',$plugin_file)[0] . '/';
      $plugin_version = $plugin_data['Version'];
      security_column_content($plugin_link, $plugin_version);
    };
  }, 10, 3);
});

function security_column_content($plugin_link, $plugin_version) {
  $response = GetPluginReview($plugin_link, $plugin_version);

  switch ($response['response']['code']) {
    case 200:
      $response_body = json_decode($response['body'])->review;
      echo display_status_icon($response_body->review_link, $response_body->recommendation);
      break;
    case 404:
      echo no_info();
      break;
    case 400:
      // TODO: behave sensibly in this scenario
      echo("Argument Error");
      break;
    case 500:
      // TODO: behave sensibly in this scenario
      echo("Server Error");
      break;
    default:
      // TODO: behave sensibly in this scenario
      // possible to get a redirect??
      echo("Unknown");
  };

  // response might also return a WP error - if so this is how to handle it:
  // if ( is_wp_error( $response ) ) {
  //    $error_message = $response->get_error_message();
  //    echo "Something went wrong: $error_message";
  // } else {
  // }
}

function display_status_icon($link, $recommendation) {
  switch ($recommendation) {
    case 'green':
      return no_issues_found($link);
      break;
    case 'yellow':
      return use_with_caution($link);
      break;
    case 'red':
      return potentially_unsafe($link);
      break;
    // default:
    //   raise an error???;
  };
}

function no_issues_found($link) {
  return status_icon($link, "&#10003;", "no_issues_found", "No issues found");
}
function use_with_caution($link) {
  return status_icon($link, "?", "use_with_caution", "Use with caution");
}
function potentially_unsafe($link) {
  return status_icon($link, "&#10007;", "potentially_unsafe", "Potentially unsafe");
}
function no_info() {
  return status_icon("https://security.dxw.com/plugins/", "-", "no_info", "This plugin has not yet been reviewed by dxw");
}

function status_icon($link, $character, $class, $title_text) {
  return "<a href='" . $link . "' title='" . $title_text . "' class='" . $class . "''>" . $character . "</a>";
}

// Surely this already exists???
function insert_at($index, $element, $array) {
  $array =
    array_slice($array, 0, $index, true) +
    $element +
    array_slice($array, $index + 1 , count($array) - 1, true);
  return $array;
}