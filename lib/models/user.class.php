<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription.class.php');

class dxw_security_User {
  public static function can_subscribe() {
    return (is_admin() && !dxw_security_Subscription::is_active());
  }
}
?>
