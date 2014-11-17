<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Validator {
  public $errors = array();

  public function __construct($email) {
    $this->email      = $email;
  }

  public function valid() {
    if ( $this->validate_email_presence() ) {
      $this->validate_email_format();
    }
    return empty($this->errors);
  }

  private function validate_email_format(){
    $this->validate(
      !filter_var($this->email, FILTER_VALIDATE_EMAIL),
      "That doesn't look like a valid email - have you typed it correctly?"
    );
  }

  private function validate_email_presence(){
    $this->validate(
      empty($this->email),
      "Please enter an email address"
    );
  }

  private function validate($condition, $message) {
    if($condition) {
      $this->errors[]= $message;
      return false;
    }
    return true;
  }
}