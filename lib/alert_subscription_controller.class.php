<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Alert_Subscription_Controller {

  public static function create() {
    // TODO: Is exit the right way to do this?
    if ( !current_user_can('install_plugins') ) { exit; }

    $subscription_form = new dxw_security_Alert_Subscription_Form($_POST['subscription']);

    if ( $subscription_form->valid() ){

      Whippet::print_r("IT WORKS!!!!");
    } else {

      Whippet::print_r("THERE WERE ERRORS!!!!");
      Whippet::print_r($subscription_form->errors);
    }

    wp_redirect("/wp-admin/plugins.php");
  }
}
?>