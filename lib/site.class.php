<?php

defined('ABSPATH') OR exit;

// Class for generating and retrieving the site ID, used to distinguish this site
// from others which an API key might be used on, but without being able to identify
// which site a manifest came from
class dxw_security_Site {

  public static $site_hash_field = 'dxw_security_site_hash';

  public static function hash() {
    $site_hash = get_option( self::$site_hash_field );
    if (empty($site_hash)) {
      $site_hash = self::set_new_hash();
    }
    return $site_hash;
  }

  public static function set_new_hash() {
    $new_site_hash = md5(time() . get_site_url() . rand());
    update_option( self::$site_hash_field, $new_site_hash );
    return $new_site_hash;
  }
}

?>
