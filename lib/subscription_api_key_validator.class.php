<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Api_Key_Validator {
  private $value;
  private $field;

  public function __construct($value, $field) {
    $this->value = $value;
    $this->field = $field;
  }

  public function validate() {
    self::validate_presence();
    self::validate_alphanumeric();
  }

  private function validate_presence() {
    if ( empty($this->value) ) {
      add_settings_error($this->field, esc_attr('empty'), "Please enter an API key");
    }
  }

  private function validate_alphanumeric() {
    if ( ! preg_match('/^[a-zA-Z0-9_]*$/', $this->value) ) {
      // TODO: I can't see why that esc_attr is necessary, but it's in the example docs...
      add_settings_error($this->field, esc_attr('not_alphanumeric'), "That doesn't look like a valid API key: subscription keys only contain numbers, letters and underscores");
    }
  }
}

?>