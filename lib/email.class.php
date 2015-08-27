<?php

defined('ABSPATH') OR exit;

class dxw_security_Email {
  public static function deliver($subject, $message) {
    $to = get_option( 'admin_email' );
    $headers = array(
      'From: MongooseWP <contact@mongoosewp.com>',
      "X-Auto-Response-Suppress: All",
      "List-Unsubscribe: <mailto:contact@mongoosewp.com?body=unsubscribe%20plugin%20emails>",
      "Auto-Submitted: auto-generated"
    );
    wp_mail($to, $subject, $message, $headers);
  }
}

?>
