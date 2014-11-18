<?php

defined('ABSPATH') OR exit;

class dxw_security_Alert_Subscription_Banner {
  public function __construct() {
    add_filter('admin_notices', array($this, 'render'));
  }

  public function render() {
    ?>
      <div id="dxw_security_alert_subscription_link" class="updated">
        <p>
          Want to get notified of security issues with your plugins?
          <a href="#" class="alert_subscription_button button-primary">Subscribe to alerts</a>
        </p>
      </div>
    <?php
  }
}