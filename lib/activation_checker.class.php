<?php

defined('ABSPATH') OR exit;

class dxw_security_Activation_Checker {
  private static $activation_key = 'Activated_Plugin';
  private static $plugin_name = 'dxw_Security';

  public static function activate() {
    add_option( self::$activation_key, self::$plugin_name );
  }

  public static function check() {
    // TODO: is there a cleaner way to do this? An equivalent of Ruby's 'tap'?
    if ( self::activated() ) {
      delete_option( self::$activation_key );
      return true;
    } else {
      return false;
    }
  }

  private static function activated() {
    return get_option( self::$activation_key ) == self::$plugin_name;
  }
}

?>