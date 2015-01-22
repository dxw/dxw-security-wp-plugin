<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription_activation_form.class.php');
require_once(dirname(__FILE__) . '/subscription.class.php');

class dxw_security_Settings_Page {

  public static $page_slug = 'dxw_security-key-config';

  public static function setup() {
    add_options_page('dxw Security', 'dxw Security', 'manage_options', self::$page_slug , array(get_called_class(), 'render') );
  }

  public static function render() {
    ?>
      <div id="dxw-security-settings">

        <h2>dxw Security</h2>
        <p>The dxw Security plugin keeps an eye on plugin security issues and can let you know as soon as one of your plugins is found to be unsafe.</p>

        <?php if ( ! dxw_security_Subscription::is_active() ) {
          self::render_sign_up_box();
        }?>

        <div class="dxw_security_settings_box lowlight">
          <?php dxw_security_Subscription_Activation_Form::render(); ?>
        </div>

      </div>
    <?php
  }

  private static function render_sign_up_box() {
    ?>
      <div class="dxw_security_settings_box">
        <h3>Sign up</h3>
        <p>To start receiving security notifications you'll need to create an account and get your API key</p>
        <p><a href="mailto:security@dxw.com" class="button-primary">Get your API key</a></p>
      </div>
    <?php
  }

  public static function url() {
    $slug = self::$page_slug;
    return "options-general.php?page={$slug}";
  }
}

?>