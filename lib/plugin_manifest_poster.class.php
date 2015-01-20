<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/email.class.php');

class dxw_security_Plugin_Manifest_Poster {
  public static function run() {
    $manifest = new dxw_security_Plugin_Manifest;

    $api = new dxw_security_Manifest_API($manifest, self::auth_token());

    // TODO: this pattern is similar to the security column code and dashboard widget
    //    It should probably be factored out into another class
    try {
      $api->call();
    } catch (\Exception $e) {
      self::handle_api_error($e);
    }
  }

  private static function handle_api_error($e) {
    $subject = "Your WordPress site failed to send a plugin list to dxw Security";
    $error_message = self::error_message($e);
    $message = <<<EOF
Your WordPress site is configured to regularly send a list of your plugins to dxw Security.
The most recent time it tried to do this, it failed with the following message:

{$error_message}

You might want to consider forwarding this email to security@dxw.com to see if we can diagnose the issue.
EOF;
    dxw_security_Email::deliver($subject, $message);
  }

  private static function error_message($error) {
    $message = $error->getMessage();
    if ( empty($message) ) { $message = "(no message provided)"; }
    return $message;
  }

  // TODO: this probably shouldn't live here - will ultimately need to be used in many places:
  private static function auth_token() {
    return get_option( 'dxw_security_subscription_token' );
  }
}

?>