<?php

defined('ABSPATH') OR exit;

class dxw_security_Alert_Subscription_Form {

  public $errors = array();

  private $email;
  private $permission;

  public function __construct($subscription=array()) {
    $this->email      = $subscription['email'];
    $this->permission = $subscription['permission'];
  }


  public function render(){
    // TODO: Is there any point in adding this to the nonce? I believe nonces already get salted (?)
    $salt = rand();

    ?>
      <?php echo $this->render_errors(); ?>

      <form accept-charset="UTF-8" action="/wp-admin/admin-ajax.php" id="subscription_form" method="post">
        <div style="display:none">
          <input name="utf8" type="hidden" value="✓">

          <input name="action" type="hidden" value="subscribe">

          <input name="salt" type="hidden" value="<?php echo $salt; ?>">
          <?php wp_nonce_field( 'register_'.$salt ); ?>
        </div>

        <div>
          <label>
            Email
            <input autofocus="autofocus" id="subscription_email" name="subscription[email]" type="email" value="">
          </label>
          <p class="help_text">The email address you'd like to receive alerts at.</p>
        </div>

        <div>
          <label for="permission">
            <input name="subscription[permission]" type="hidden" value="0">
            <input id="permission" name="subscription[permission]" type="checkbox" value="1">
            I'm happy for this site to send a list of it's plugins to dxw Security
          </label>
        </div>

        <div>
          <?php submit_button( "Subscribe to alerts", "primary") ?>
        </div>
      </form>
    <?php
  }

  private function render_errors(){
    foreach ($this->errors as &$error) {
      echo "<div class='error'>{$error}</div>";
    }
  }

  public function valid() {
    $this->validate_email_format();
    $this->validate_email_presence();
    $this->validate_permission_granted();

    return empty($this->errors);
  }

  private function validate_email_format(){
    if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      $this->errors[]= "That doesn't look like a valid email - have you typed it correctly?";
    }
  }

  private function validate_email_presence(){
    if(empty($this->email)) {
      $this->errors[]= "Please enter an email address";
    }
  }

  private function validate_permission_granted(){
    // TODO: is the check for false unnecessary?
    if(empty($this->permission) || $this->permission == false) {
      $this->errors[]= "Please check the box to say that you're happy to send your list of plugins";
    }
  }
}
?>