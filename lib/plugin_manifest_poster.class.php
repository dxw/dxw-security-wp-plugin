<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_manifest.class.php');
require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/email.class.php');
require_once(dirname(__FILE__) . '/subscription.class.php');

class dxw_security_Plugin_Manifest_Poster {
  public static function run() {
    $manifest = new dxw_security_Plugin_Manifest;

    $api = new dxw_security_Manifest_API($manifest, dxw_security_Subscription::auth_token());

    // TODO: this pattern is similar to the security column code and dashboard widget
    //    It should probably be factored out into another class
    try {
      $api->call();
    } catch (\Exception $e) {
      self::handle_api_error($e);
    }
  }

  private static function handle_api_error($e) {
    $subject = "Your WordPress site failed to send a plugin list to MongooseWP";
    $error_message = self::error_message($e);
    $email = DXW_SECURITY_EMAIL; // TODO: Why do I have to assign this constant to a variable in order to output it?
    $message = <<<EOF
Your WordPress site is configured to regularly send a list of your plugins to MongooseWP.
The most recent time it tried to do this, it failed with the following message:

{$error_message}

You might want to consider forwarding this email to {$email} to see if we can diagnose the issue.
EOF;
    dxw_security_Email::deliver($subject, $message);
  }

  private static function error_message($error) {
    $message = $error->getMessage();
    if ( empty($message) ) { $message = "(no message provided)"; }
    return $message;
  }
}

?>