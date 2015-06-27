<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription_activation_form.class.php');
require_once(dirname(__FILE__) . '/views/settings_page_content.class.php');

class dxw_security_Settings_Page {

  public static $page_slug = 'mongoose-key-config';

  public static function setup() {
    add_options_page('Mongoose', 'Mongoose', 'manage_options', self::$page_slug , array(get_called_class(), 'render_content') );
    $form = new dxw_security_Subscription_Activation_Form(self::$page_slug);
    $form->setup();
  }

  public static function render_content() {
    $content = new dxw_security_Settings_Page_Content(self::$page_slug);
    $content->render();
  }

  public static function url() {
    $slug = self::$page_slug;
    return "options-general.php?page={$slug}";
  }
}

?>
