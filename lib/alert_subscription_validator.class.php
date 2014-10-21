<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Validator {
  public $errors = array();

  public function __construct($email, $permission) {
    $this->email      = $email;
    $this->permission = $permission;
  }

  public function valid() {
    if ( $this->validate_email_presence() ) {
      $this->validate_email_format();
    }

    $this->validate_permission_granted();

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

  private function validate_permission_granted(){
    // TODO: is the check for false unnecessary?
    $this->validate(
      empty($this->permission) || $this->permission == false,
      "Please check the box to say that you're happy to send your list of plugins"
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