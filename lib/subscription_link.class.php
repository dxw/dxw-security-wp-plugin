<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/settings_page.class.php');
require_once(dirname(__FILE__) . '/subscription.class.php');

class dxw_security_Subscription_Link {

  public static function can_subscribe() {
    return (is_admin() && !dxw_security_Subscription::is_active());
  }

  public static function render() {
    $url = dxw_security_Settings_Page::url();
    ?>
      <a href="<?php echo $url ?>" class="alert_subscription_button button-primary">Subscribe to alerts</a>
    <?php
  }
}
?>