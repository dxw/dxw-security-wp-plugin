<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Activation_Form {

  private static $api_key_field = 'dxw_security_subscription_token';

  public static function setup() {
    add_settings_section("activate_subscription", "Activate your subscription", array(get_called_class(),'section_text'), "dxw_security-key-config");

    add_settings_field(self::$api_key_field, 'Manually enter an API key', array(get_called_class(),'subscription_api_key_input_field'), 'dxw_security-key-config', "activate_subscription");
    register_setting( 'dxw_security-key-config', self::$api_key_field, array(get_called_class(),'validate_subscription_api_key'));
  }

  public static function section_text() {
    echo "To activate your subscription and start receiving notifications you'll need an API key:";
  }

  public static function subscription_api_key_input_field() {
    echo '<input type="text" name="'.self::$api_key_field.'" value="'.esc_attr(get_option(self::$api_key_field)).'" size="50">';
    echo '<p class="help-text">(if you already know your api key)</p>';
  }

  public static function validate_subscription_api_key($input) {

    $output = trim($input);

    if ( empty($output) ) {
      add_settings_error(self::$api_key_field, esc_attr('empty'), "Please enter an API key");
    }
    else if ( ! preg_match('/^[a-zA-Z0-9]*$/', $output) ) {
      // TODO: I can't see why that esc_attr is necessary, but it's in the example docs...
      add_settings_error(self::$api_key_field, esc_attr('not_alphanumeric'), "That doesn't look like a valid API key: subscription keys only contain numbers and letters");
    }

    return $output;
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