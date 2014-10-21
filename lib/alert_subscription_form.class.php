<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/alert_subscription_validator.class.php');

class dxw_security_Alert_Subscription_Form {
  private $validator;

  public function __construct($email=null, $permission=null) {
    $this->validator  = new dxw_security_Subscription_Validator($email, $permission);
  }


  public function render(){
    // TODO: Is there any point in adding this to the nonce? I believe nonces already get salted (?)
    $salt = rand();

    ?>
      <form accept-charset="UTF-8" action="/wp-admin/admin-ajax.php" id="subscription_form" method="post">
        <div class="errors">
          <?php echo $this->render_errors(); ?>
        </div>

        <div style="display:none">
          <input name="utf8" type="hidden" value="✓">

          <input name="action" type="hidden" value="subscribe">

          <input name="salt" id="salt" type="hidden" value="<?php echo $salt; ?>">
          <?php wp_nonce_field(self::nonce_token($salt)); ?>
        </div>

        <div class="field-group">
          <label for="email">Email</label>
          <input autofocus="autofocus" id="email" name="subscription[email]" type="email" value="">
          <span class="help-text">The email address you'd like to receive alerts at</span>
        </div>

        <div class="field-group">
          <label for="permission">
            <input name="subscription[permission]" type="hidden" value="0">
            <input id="permission" name="subscription[permission]" type="checkbox" value="1">
            I'm happy for this site to send a list of it's plugins to dxw Security
          </label>
        </div>

        <?php submit_button( "Subscribe to alerts", "primary") ?>
      </form>
    <?php
  }

  # TODO: I'm not sure nonce_token is an appropriate name...
  public static function nonce_token($salt) {
    return 'subscribe_'.$salt;
  }

  private function render_errors(){
    $errors = $this->errors();
    foreach ($errors as &$error) {
      echo "<div class='error'>{$error}</div>";
    }
  }

  public function valid() {
    return $this->validator->valid();
  }

  public function errors() {
    return $this->validator->errors;
  }
}
?>