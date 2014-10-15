<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Alert_Subscription_Controller {

  public static function create() {
    // TODO: Is exit the right way to do this?
    if ( !current_user_can('install_plugins') ) { exit; }

    $email      = $_POST['subscription']['email'];
    $permission = $_POST['subscription']['permission'];

    $subscription_form = new dxw_security_Alert_Subscription_Form($email, $permission);

    if ( $subscription_form->valid() ){
      $api = new dxw_security_Registration_API($email);
      $response = $api->call();
    } else {
      // TODO: Errors don't work yet
      Whippet::print_r("THERE WERE ERRORS!!!!");
      Whippet::print_r($subscription_form->errors);
    }

    wp_redirect("/wp-admin/plugins.php");
  }
}
?>