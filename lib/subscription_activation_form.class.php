<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/models/subscription.class.php');
require_once(dirname(__FILE__) . '/models/api_key.class.php');
require_once(dirname(__FILE__) . '/subscription_activator.class.php');
require_once(dirname(__FILE__) . '/views/subscription_activation_form_content.class.php');

class dxw_security_Subscription_Activation_Form {
  private $page_slug;

  public function __construct($page_slug) {
    $this->page_slug   = $page_slug;
  }

  public function setup() {
    $api_key_field = dxw_security_Subscription::$api_key_field;

    if ( dxw_security_Subscription::is_active() ) {
      $view = new dxw_security_Subscription_Activation_Form_Content_Active;
    } else {
      $view = new dxw_security_Subscription_Activation_Form_Content_Inactive;
    }

    add_settings_section(
      "activate_subscription",
      $view->section_heading(),
      array(get_class($view), 'section_text'),
      $this->page_slug
    );

    add_settings_field(
      $api_key_field,
      $view->field_label(),
      array(get_class($view),'subscription_api_key_input_field'),
      $this->page_slug,
      "activate_subscription"
    );

    register_setting(
      $this->page_slug,
      $api_key_field,
      array(get_called_class(),'validate_subscription_api_key')
    );
  }

  public static function validate_subscription_api_key($input) {
    $api_key = new dxw_security_API_Key($input, dxw_security_Subscription::$api_key_field);

    if ( $api_key->is_valid() ) {
      dxw_security_Subscription_Activator::activate($output);
      return $api_key;
    } else {
      dxw_security_Subscription_Activator::deactivate();
      // Don't save invalid api keys to the database:
      // TODO: Should it instead return the old value? http://kovshenin.com/2012/the-wordpress-settings-api/
      return "";
    }
  }
}

?>
