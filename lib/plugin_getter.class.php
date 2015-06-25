<?php

defined('ABSPATH') OR exit;

// We might be getting loaded without the WordPress core - in which case we need to load the relevant file ourself
if ( ! function_exists( 'get_plugins' ) ) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Responsible for getting the list of plugins installed on the site
class dxw_security_Plugin_Getter {
  public static function get() {
    return get_plugins(); // From core
  }
}
?>
