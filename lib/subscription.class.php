<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/cron.class.php');

class dxw_security_Subscription {

  public static $api_key_field = 'dxw_security_subscription_token';

  public static function auth_token() {
    return get_option( self::$api_key_field );
  }

  public static function is_active() {
    return (bool)get_option( self::$api_key_field );
  }

  // Tasks to be executed on activation of the subscription
  public static function activate() {
    dxw_security_Cron::schedule_manifest_poster_task();
  }

  // Cleanup that needs to happen on deactivation of the subscription
  public static function deactivate() {
    dxw_security_Cron::unschedule_manifest_poster_task();
  }
}
?>