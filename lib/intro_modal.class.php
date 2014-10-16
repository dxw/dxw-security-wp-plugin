<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/alert_subscription_form.class.php');

class dxw_security_Intro_Modal {
  private $registration_form;

  public function __construct() {
    add_action( 'admin_notices', array( $this, 'render_dialog' ) );
    $this->registration_form = new dxw_security_Alert_Subscription_Form;
  }

  public function render_dialog(){
    $registration_form = new dxw_security_Alert_Subscription_Form
    ?>
      <div style="display:none;" class="intro-dialog" data-title="Welcome to dxw Security">

        <a href="http://security.dxw.com" id="dxw-sec-link"><img src="<?php echo plugins_url('/assets/dxw-logo.png' , dirname(__FILE__)); ?>" alt="dxw logo" /></a>

        <div class="inner">
          <h2>Sign up to receive alerts</h2>
          <p>
            When a vulnerability is discovered with any plugin you're using, it's important to know about it as soon as possible so that you can take action to protect your site.
          </p>
          <p>
            We can send you alerts by email as soon as a vulnerability is discovered - either by us or by others in the security community.
          </p>

          <?php $this->registration_form->render() ?>

          <p>We promise not to spam you with lots of emails, and we'll only use your plugin list to send you alerts, and to work out which plugins we should be reviewing next.</p>

          <h3>How does it work?</h3>
          <ol>
            <li>You subscribe to alerts using the form above</li>
            <li>Your WordPress sends us a daily snapshot of the plugins you have installed</li>
            <li>We regularly review plugins for security issues</li>
            <li>We also monitor reports from the wider security community</li>
            <li>When we find out about an issue with one of the plugins you have installed, we'll send you an email recommending a course of action to keep your site safe.</li>
          </ol>
        </div>

      </div>
    <?php
  }
}
?>