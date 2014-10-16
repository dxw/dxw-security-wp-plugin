<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Alert_Subscription_Controller {

  // TODO: will be called by ajax - should it be named accordingly?
  public static function create() {
    self::check_permissions();
    self::check_nonce();

    $email      = $_POST['subscription']['email'];
    $permission = $_POST['subscription']['permission'];

    $subscription_form = new dxw_security_Alert_Subscription_Form($email, $permission);

    if ( $subscription_form->valid() ){
      $api = new dxw_security_Registration_API($email);

      // TODO: catch errors and display an appropriate message
      //    particularly 'Bad data' errors which correspond to upstream validation errors

      try {
        $response = $api->call();
        // FIXME: this doesn't work at the moment because of redirects
        add_action('admin_notices', self::render_success_notice($email));
        echo json_encode(array('email' => $email));

      } catch (dxw_security_API_BadData $e) {
        // This corresponds to an upstream validation error
        self::render_error($e->getMessage());
      } catch (dxw_security_API_Error $e) {
        // TODO: what about dxw_security_API_NotFound? We shouldn't be able to get this, but it doesn't have a message...
        self::render_error("Sorry, the subscription service doesn't seem to b available at the moment. Please try again later.");
      }


    } else {
      echo json_encode(array('errors' => $subscription_form->errors));
      // FIXME: Errors don't work yet
      Whippet::print_r("THERE WERE ERRORS!!!!");
      Whippet::print_r($subscription_form->errors);
    }

    // TODO: Is this no longer needed?
    // wp_redirect("/wp-admin/plugins.php");
    exit();
  }

  private static function render_success_notice($email) {
    $message = "You've successfully subscribed to plugin security alerts with {$email}.";
    self::render_notice("success", $message);
  }

  private static function render_error($message) {
    self::render_notice("error", $message);
  }

  // TODO: I'm surprised there isn't something like this in core...
  private static function render_notice($type, $message) {
    if ( $type == "success") {
      $class = "updated";
    } else {
      $class = "error";
    }
    ?>
    <div class="<?php echo $class; ?>">
      <p><?php echo esc_html($message); ?></p>
    </div>
    <?php
  }

  private static function check_nonce() {
    $nonce      = $_POST['_wpnonce'];
    $salt       = $_POST['salt'];

    $nonce_token = dxw_security_Alert_Subscription_Form::nonce_token($salt);
    // TODO: What's a good die message?
    if ( !wp_verify_nonce($nonce, $nonce_token) ) { wp_die('Security check - nonce mismatch'); }
  }

  private static function check_permissions() {
    // TODO: Is wp_die the right way to do this?
    // TODO: What's a good die message?
    if ( !current_user_can('install_plugins') ) { wp_die("Security check - you don't have permission to view this page"); }
  }
}
?>