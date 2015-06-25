<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/settings_page.class.php');

class dxw_security_Subscription_Link {
  public static function render() {
    $url = dxw_security_Settings_Page::url();
    ?>
      <a href="<?php echo $url ?>" class="alert_subscription_button button-primary">Subscribe to alerts</a>
    <?php
  }
}
?>
