<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Alert_Subscription_Controller {

  // TODO: will be called by ajax - should it be named accordingly?
  public static function create() {
    self::check_permissions();
    self::check_nonce();

    $subscription_data = $_POST['subscription'];

    $email      = $subscription_data['email'];

    $subscription_form = new dxw_security_Alert_Subscription_Form($email);

    if ( $subscription_form->valid() ){
      $api = new dxw_security_Registration_API($email);

      try {
        $response = $api->call();

        // TODO: this should probably be in a separate class concerned with model-type operations
        // TODO: using update here means we'll overwrite any existing value, but will work
        //    even if the field exists but is blank. Not sure what the correct approach is.
        update_option( 'dxw_security_subscription_token', $response->auth_token );

        self::schedule_manifest_send();

        wp_send_json_success(
          array(
            "email" => $response->email,
          )
        );

      } catch (dxw_security_API_BadData $e) {
        // This corresponds to either an upstream validation error,
        //    or the plugin calling the api incorrectly
        wp_send_json_error(array('errors' => [
          "Sorry, we couldn't complete your subscription: {$e->getMessage()}"
        ]));
      } catch (dxw_security_API_Error $e) {
        // TODO: what about dxw_security_API_NotFound? We shouldn't be able to get this, but it doesn't have a message...
        wp_send_json_error(array('errors' => ["Sorry, the subscription service doesn't seem to be available at the moment. Please try again later."]));
      }

    } else {
      wp_send_json_error(array('errors' => $subscription_form->errors()));
    }
    exit(); //All the above wp_send_json_ statements should exit anyway so we shouldn't get this far.
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

    if ( !wp_verify_nonce($nonce, $nonce_token) ) {
      // TODO: What's a good error message?
      wp_send_json_error(array('errors' => ['Security error - nonce mismatch']));
    }
  }

  private static function check_permissions() {
    if ( !current_user_can('install_plugins') ) {
      // TODO: What's a good error message?
      wp_send_json_error(array('errors' => ["Security error- you don't have permission to view this page"]));
    }
  }

  private static function schedule_manifest_send() {
    $task = new dxw_security_Task('dxw_security_Plugin_Manifest_Poster');
    $task->schedule('daily');
  }
}
?>