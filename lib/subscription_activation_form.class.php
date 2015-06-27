<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/models/subscription.class.php');
require_once(dirname(__FILE__) . '/models/api_key.class.php');
require_once(dirname(__FILE__) . '/subscription_activator.class.php');

class dxw_security_Subscription_Activation_Form {
  private $page_slug;

  public function __construct($page_slug) {
    $this->page_slug   = $page_slug;
  }

  public function setup() {
    add_settings_section(
      "activate_subscription",
      $this->section_heading(),
      array(get_called_class(), 'section_text'),
      $this->page_slug
    );

    add_settings_field(
      dxw_security_Subscription::$api_key_field,
      $this->field_label(),
      array(get_called_class(),'subscription_api_key_input_field'),
      $this->page_slug,
      "activate_subscription"
    );

    register_setting(
      $this->page_slug,
      dxw_security_Subscription::$api_key_field,
      array(get_called_class(),'validate_subscription_api_key')
    );
  }

  private function section_heading() {
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
          or if you have any problems or comments, please contact <?php $this->email_link() ?>.</p>
      <?php
    } else {
      ?>
        <p>If you already know your API key you can enter it here to activate your subscription and start receiving notifications:</p>
      <?php
    }
  }

  private function field_label() {
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
    $api_key = new dxw_security_API_Key($input, dxw_security_Subscription::$api_key_field);

    if ( $api_key->is_valid() ) {
      dxw_security_Subscription_Activator::activate($output);
      return $api_key;
    } else {
      dxw_security_Subscription_Activator::deactivate();
      // Don't save invalid api keys to the database:
      // TODO: Should it instead return the old value? http://kovshenin.com/2012/the-wordpress-settings-api/
      return "";
    }
  }

  private function email_link() {
    // The closing tags need to be up against the end to avoid spaces between
    // the email and the full stop in the output
    ?>
      <a href="mailto:<?php echo constant('DXW_SECURITY_EMAIL') ?>">
        <?php echo constant('DXW_SECURITY_EMAIL') ?></a><?php
  }
}
?>
