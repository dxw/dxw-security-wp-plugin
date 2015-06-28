<?php

defined('ABSPATH') OR exit;

class dxw_security_Options {
  public static $page_slug = 'mongoose-key-config';

  public static function url() {
    $slug = self::$page_slug;
    return "options-general.php?page={$slug}";
  }
}

?>
