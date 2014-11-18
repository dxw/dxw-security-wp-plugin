<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscription_Link {

  public static function can_subscribe() {
    return (is_admin() && !get_option('dxw_security_subscription_token'));
  }

  public static function render() {
    ?>
      <a href="#" class="alert_subscription_button button-primary">Subscribe to alerts</a>
    <?php
  }
}
?>