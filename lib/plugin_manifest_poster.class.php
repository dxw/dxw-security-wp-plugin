<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');

class dxw_security_Plugin_Manifest_Poster {
  private $auth_token;

  public function __construct($cron_task_name) {
    // Define a hook for wp_cron to call:
    add_action($cron_task_name, array($this, 'post') );

    // TODO: this probably shouldn't live here. In fact perhaps the whole auth token should be part of the API call (AuthenticatedAPI?)
    $this->auth_token = get_option( 'dxw_security_subscription_token' );
  }

  public function post() {
    if ($this->is_subscribed()) {
      $manifest = new dxw_security_Plugin_Manifest;

      $api = new dxw_security_Manifest_API($manifest, $this->auth_token());
      $api->call();
    }
  }

  // TODO: this probably shouldn't live here: it's a property of the user (?)
  private function is_subscribed() {
    return empty($this->auth_token);
  }
}
?>