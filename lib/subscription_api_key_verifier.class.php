<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');

// Checks that the api key exists in the downstream subscriber records
// by attempting to post the manifest
class dxw_security_Subscription_Api_Key_Verifier {

  public static function verify($api_key, $field) {
    // TODO: this duplicates functionality of the Plugin_Manifest_Poster class
    $manifest = new dxw_security_Plugin_Manifest;
    $api = new dxw_security_Manifest_API($manifest, $api_key);

    try {
      $api->call();
    } catch (dxw_security_API_Unauthorised $e) {
      add_settings_error($field, esc_attr('unverified'), "The api key you entered doesn't match our records. Please double-check it.");
    } catch (\Exception $e) {
      add_settings_error($field, esc_attr('error'), "Sorry - there seems to be some problem with our systems. Please try again later and/or contact <a href=\"mailto:security@dxw.com\">security@dxw.com</a>");
    }
  }
}
?>