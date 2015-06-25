<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription_api_key_validator.class.php');
require_once(dirname(__FILE__) . '/subscription_api_key_verifier.class.php');
require_once(dirname(__FILE__) . '/models/subscription.class.php');
require_once(dirname(__FILE__) . '/subscription_activator.class.php');

class dxw_security_Subscription_Activation_Form {

  public static function setup() {
    add_settings_section(
      "activate_subscription",
      self::section_heading(),
      array(get_called_class(), 'section_text'),
      "mongoose-key-config"
    );

    add_settings_field(
      dxw_security_Subscription::$api_key_field,
      self::field_label(),
      array(get_called_class(),'subscription_api_key_input_field'),
      'mongoose-key-config',
      "activate_subscription"
    );

    register_setting(
      'mongoose-key-config',
      dxw_security_Subscription::$api_key_field,
      array(get_called_class(),'validate_subscription_api_key')
    );
  }

  public static function section_heading() {
    if ( dxw_security_Subscription::is_active() ) {
      return "Your subscription is active";
    } else {
      return "Activate your subscription";
    }
  }

  public static function section_text() {
    if ( dxw_security_Subscription::is_active() ) {
      ?>
        <p>We'll notify you by email if we any security issues are with plugins you have installed.</p>
        <p>We'll use the email address you provided when you registered. If you would like to change this,
          or if you have any problems or comments, please contact <?php self::email_link() ?>.</p>
      <?php
    } else {
      ?>
        <p>If you already know your API key you can enter it here to activate your subscription and start receiving notifications:</p>
      <?php
    }
  }

  public static function field_label() {
    if ( dxw_security_Subscription::is_active() ) {
      return "Your API key:";
    } else {
      return "Enter an API key";
    }
  }

  public static function subscription_api_key_input_field() {
    echo '<input type="text" name="'.dxw_security_Subscription::$api_key_field.'" value="'.esc_attr(dxw_security_Subscription::auth_token()).'" size="50">';
  }

  public static function validate_subscription_api_key($input) {
    $output = trim($input);

    // Don't save invalid api keys to the database:
    // TODO: Should it instead return the old value? http://kovshenin.com/2012/the-wordpress-settings-api/
    if ( self::is_invalid($output) || self::could_not_be_verified($output) ) {
      $output = "";
      dxw_security_Subscription_Activator::deactivate();
    } else {
      dxw_security_Subscription_Activator::activate($output);
    }

    return $output;
  }

  private static function email_link() {
    // The closing tags need to be up against the end to avoid spaces between
    // the email and the full stop in the output
    ?>
      <a href="mailto:<?php echo constant('DXW_SECURITY_EMAIL') ?>">
        <?php echo constant('DXW_SECURITY_EMAIL') ?></a><?php
  }

  private static function is_invalid($value) {
    $validator = new dxw_security_Subscription_Api_Key_Validator($value, dxw_security_Subscription::$api_key_field);
    $validator->validate();
    return self::has_errors();
  }

  private static function could_not_be_verified($value) {
    // This makes a call to the api, so only run it if the value has been successfully validated
    dxw_security_Subscription_Api_Key_Verifier::verify($value, dxw_security_Subscription::$api_key_field);
    return self::has_errors();
  }

  private static function has_errors() {
    $settings_errors = get_settings_errors();
    return !empty($settings_errors);
  }

  public static function render() {
    ?>
    <form action="options.php" method="POST">
      <?php settings_fields('mongoose-key-config') ?>
      <?php do_settings_sections('mongoose-key-config') ?>
      <?php submit_button("Save", "secondary") ?>
    </form>
    <?php
  }
}

?>
