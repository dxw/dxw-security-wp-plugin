<?php

defined('ABSPATH') OR exit;

class dxw_security_Subscribe_Button {
  public function __construct($url) {
    $this->url = $url;
  }

  public function render() {
    ?>
      <a href="<?php echo $this->url ?>" class="alert_subscription_button button-primary">Subscribe to alerts</a>
    <?php
  }
}
?>
