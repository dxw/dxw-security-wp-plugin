<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/subscription_link.class.php');

class dxw_security_Alert_Subscription_Banner {
  public function __construct() {
    add_filter('admin_notices', array($this, 'render'));
  }

  public function render() {
    ?>
      <div id="dxw_security_alert_subscription_link" class="updated">
        <p>
          Want to get notified of security issues with your plugins?
          <?php dxw_security_Subscription_Link::render() ?>
        </p>
      </div>
    <?php
  }
}