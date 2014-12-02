<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');

class dxw_security_Plugin_Manifest_Poster {
  public static function run() {
    $manifest = new dxw_security_Plugin_Manifest;

    $api = new dxw_security_Manifest_API($manifest);
    $api->call();
  }
}
?>