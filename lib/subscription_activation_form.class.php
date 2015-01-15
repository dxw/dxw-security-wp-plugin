<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Activation_Form {

  public static function setup() {
    add_settings_section("activate_subscription", "Activate your subscription", array(get_called_class(),'section_heading'), "dxw_security-key-config");
    add_settings_field('dxw_security_subscription_token', 'Manually enter an API key', array(get_called_class(),'subscription_api_key_input_field'), 'dxw_security-key-config', "activate_subscription");
    register_setting( 'dxw_security-key-config', 'dxw_security_subscription_token' );
  }

  public static function section_heading() {
    echo "To activate your subscription and start receiving notifications you'll need an API key:";
  }

  public static function subscription_api_key_input_field() {
    echo '<input type="text" name="dxw_security_subscription_token" value="" size="50">';
    echo '<p class="help-text">(if you already know your api key)</p>';
  }

  public static function render() {
    ?>
    <form action="options.php" method="POST">
      <?php settings_fields('dxw_security-key-config') ?>
      <?php do_settings_sections('dxw_security-key-config') ?>
      <?php submit_button() ?>
    </form>
    <?php
  }
}

?>