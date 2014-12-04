<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');

class dxw_security_Plugin_Manifest_Poster {
  public static function run() {
    $manifest = new dxw_security_Plugin_Manifest;

    $api = new dxw_security_Manifest_API($manifest);

    // TODO: this pattern is similar to the security column code and dashboard widget
    //    It should probably be factored out into another class
    try {
      $api->call();
    } catch (\Exception $e) {
      self::handle_api_error($e);
    }
  }

  private static function handle_api_error($e) {
    $to = get_option( 'admin_email' );
    $subject = "Your WordPress site failed to send a plugin list to dxw Security";
    $message = <<<EOF
Your WordPress site is configured to regularly send a list of your plugins to dxw Security.
The most recent time it tried to do this, it failed with the following message:

{$e->getMessage()}

You might want to consider forwarding this email to security@dxw.com to see if we can diagnose the issue.
EOF;
    $headers = array(
      'From: dxw Security <security@dxw.com>'
      # TODO: send an appropriate list of headers given that this is an automated email.
    );
    wp_mail($to, $subject, $message, $headers);
  }
}
?>