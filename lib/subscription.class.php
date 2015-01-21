<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription {

  private static $api_key_field = 'dxw_security_subscription_token';

  public static function auth_token() {
    return get_option( self::$api_key_field );
  }

  public static function is_active() {
    return (bool)get_option( self::$api_key_field );
  }
}
?>