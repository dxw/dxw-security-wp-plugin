<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Alert_Subscription_Controller {

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
      $response = $api->call();
    } else {
      // TODO: Errors don't work yet
      Whippet::print_r("THERE WERE ERRORS!!!!");
      Whippet::print_r($subscription_form->errors);
    }

    wp_redirect("/wp-admin/plugins.php");
  }

  private static function check_nonce() {
    $nonce      = $_POST['_wpnonce'];
    $salt       = $_POST['salt'];

    $nonce_token = dxw_security_Alert_Subscription_Form::nonce_token($salt);

    // TODO: What's a good die message?
    if ( !wp_verify_nonce($nonce, $nonce_token) ) { wp_die('Security check'); }
  }

  private static function check_permissions() {
    // TODO: Is wp_die the right way to do this?
    // TODO: What's a good die message?
    if ( !current_user_can('install_plugins') ) { wp_die('Security check'); }
  }
}
?>