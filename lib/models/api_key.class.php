<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../subscription_api_key_validator.class.php');
require_once(dirname(__FILE__) . '/../subscription_api_key_verifier.class.php');

class dxw_security_API_Key {
  public function __construct($api_key, $field) {
    $this->api_key = trim($api_key);
    $this->field   = $field;
  }

  public function is_valid() {
    ! ( $this->is_invalid() || $this->could_not_be_verified() );
  }

  public function __toString() {
    return $this->api_key;
  }

  private function is_invalid() {
    $validator = new dxw_security_Subscription_Api_Key_Validator($this->api_key, $this->field);
    $validator->validate();
    return $this->has_errors();
  }

  private function could_not_be_verified() {
    // This makes a call to the api, so only run it if the value has been successfully validated
    dxw_security_Subscription_Api_Key_Verifier::verify($this->api_key, $this->field);
    return $this->has_errors();
  }

  private function has_errors() {
    $settings_errors = get_settings_errors($this->field);
    return !empty($settings_errors);
  }
}

?>
