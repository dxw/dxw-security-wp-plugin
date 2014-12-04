<?php

defined('ABSPATH') OR exit;

class dxw_security_Update_Checker {
  private static $version_key = "dxw_security_plugin_version";

  public static function updated() {
    return self::previous_version() != self::current_version();
  }

  public static function record_version() {
    update_option(self::$version_key, self::current_version());
  }

  private static function previous_version() {
    return get_site_option(self::$version_key);
  }

  private static function current_version() {
    // TODO: is this OK or should we use get_plugin_data?
    return DXW_SECURITY_PLUGIN_VERSION;
  }
}

?>