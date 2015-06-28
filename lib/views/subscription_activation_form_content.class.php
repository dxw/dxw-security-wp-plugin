<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../models/subscription.class.php');

class dxw_security_Subscription_Activation_Form_Content_Active {
  public static function section_heading() {
    return "Your subscription is active";
  }

  public static function section_text() {
    ?>
      <p>We'll notify you by email if we any security issues are with plugins you have installed.</p>
      <p>We'll use the email address you provided when you registered. If you would like to change this,
        or if you have any problems or comments, please contact <?php $this->email_link() ?>.</p>
    <?php
  }

  public static function field_label() {
    return "Your API key:";
  }

  // TODO: Duplicated
  public static function subscription_api_key_input_field() {
    echo '<input type="text" name="'.dxw_security_Subscription::$api_key_field.'" value="'.esc_attr(dxw_security_Subscription::auth_token()).'" size="50">';
  }

  private function email_link() {
    // The closing tags need to be up against the end to avoid spaces between
    // the email and the full stop in the output
    ?>
      <a href="mailto:<?php echo constant('DXW_SECURITY_EMAIL') ?>">
        <?php echo constant('DXW_SECURITY_EMAIL') ?></a><?php
  }
}

class dxw_security_Subscription_Activation_Form_Content_Inactive {
  public function section_heading() {
    return "Activate your subscription";
  }

  public static function section_text() {
    ?>
      <p>If you already know your API key you can enter it here to activate your subscription and start receiving notifications:</p>
    <?php
  }

  public function field_label() {
    return "Enter an API key";
  }

  // TODO: Duplicated
  public static function subscription_api_key_input_field() {
    echo '<input type="text" name="'.dxw_security_Subscription::$api_key_field.'" value="'.esc_attr(dxw_security_Subscription::auth_token()).'" size="50">';
  }
}

?>
