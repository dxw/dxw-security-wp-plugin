<?php

defined('ABSPATH') OR exit;

class dxw_security_Alert_Subscription_Banner_Content {
  public function __construct($url) {
    $this->button = new dxw_security_Subscribe_Button($url);;
  }

  public function render() {
    ?>
      <div id="dxw_security_alert_subscription_link" class="updated">
        <p>
          Want to get notified of security issues with your plugins?
          <?php $this->button->render() ?>
        </p>
      </div>
    <?php
  }
}

?>
