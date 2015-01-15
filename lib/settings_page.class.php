<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription_activation_form.class.php');

class dxw_security_Settings_Page {

  private static $page_slug = 'dxw_security-key-config';

  public static function setup() {
    add_options_page('dxw Security', 'dxw Security', 'manage_options', self::$page_slug , array(get_called_class(), 'render') );
  }

  public static function render() {
    dxw_security_Subscription_Activation_Form::render();
  }

  public static function url() {
    $slug = self::$page_slug;
    return "options-general.php?page={$slug}";
  }
}

?>