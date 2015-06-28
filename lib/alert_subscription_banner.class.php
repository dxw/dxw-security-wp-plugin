<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/settings_page.class.php');
require_once(dirname(__FILE__) . '/views/subscription_banner_content.class.php');

class dxw_security_Alert_Subscription_Banner {
  public static function setup() {
    add_filter('admin_notices', array(get_called_class(), 'content'));
  }

  public static function content() {
    $url = dxw_security_Settings_Page::url();
    $banner = new dxw_security_Alert_Subscription_Banner_Content($url);
    $banner->render();
  }
}

?>
