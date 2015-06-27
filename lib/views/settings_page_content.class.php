<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../models/subscription.class.php');
require_once(dirname(__FILE__) . '/settings_form.class.php');


class dxw_security_Settings_Page_Content {
  private $subscription_activation_form;

  public function __construct($page_slug) {
    $this->subscription_activation_form = new dxw_security_Settings_form($page_slug);
  }

  public function render() {
    ?>
      <div id="mongoose-settings">

        <h2>Mongoose</h2>
        <p>The MongooseWP plugin keeps an eye on plugin security issues and can let you know as soon as one of your plugins is found to be unsafe.</p>

        <div class="dxw_security_settings_box lowlight">
          <?php $this->subscription_activation_form->render(); ?>
        </div>

        <?php if ( ! dxw_security_Subscription::is_active() ) {
          $this->render_sign_up_box();
        }?>

      </div>
    <?php
  }

  private function render_sign_up_box() {
    ?>
      <div class="dxw_security_settings_box">
        <h3>Sign up</h3>
        <p>To start receiving security notifications you'll need to create an account and get your API key</p>
        <p><a href="https://www.mongoosewp.com" class="button-primary">Get your API key</a></p>
      </div>
    <?php
  }
}

?>
