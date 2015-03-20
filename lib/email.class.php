<?php

defined('ABSPATH') OR exit;

class dxw_security_Email {
  public static function deliver($subject, $message) {
    $to = get_option( 'admin_email' );
    $headers = array(
      'From: MongooseWP <contact@mongoosewp.com>'
      # TODO: send an appropriate list of headers given that this is an automated email.
    );
    wp_mail($to, $subject, $message, $headers);
  }
}

?>