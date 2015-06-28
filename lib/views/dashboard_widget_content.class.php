<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../models/user.class.php');
require_once(dirname(__FILE__) . '/../models/options.class.php');
require_once(dirname(__FILE__) . '/subscribe_button.class.php');


class dxw_security_Dashboard_Widget_Content {
  private $number_of_plugins;
  private $vulnerable_data;
  private $not_reviewed_data;
  private $errored_data;
  private $plugins_page_url;

  public function __construct($number_of_plugins, $vulnerable_data, $not_reviewed_data, $errored_data) {
    $this->number_of_plugins = $number_of_plugins;

    // Is this reliable?
    $this->plugins_page_url  = "plugins.php";

    $this->vulnerable                       = $vulnerable_data['count'];
    $this->first_vulnerable_plugin_link     = $this->plugin_link($vulnerable_data['plugin']);

    $this->not_reviewed                     = $not_reviewed_data['count'];
    $this->first_not_reviewed_plugin_link   = $this->plugin_link($not_reviewed_data['plugin']);

    $this->failed_requests                  = $errored_data['count'];
    $this->first_failed_request_plugin_link = $this->plugin_link($errored_data['plugin']);

    # TODO: These slugs are effectively duplicated information between this and the review_data class
    $this->vulnerable_slug = 'vulnerable';
    $this->grey_slug       = 'no-info';

  }

  public function render() {
    if( dxw_security_User::can_subscribe() ) {
      $this->subscription_link();
    }
    ?>
      <p>Of the <?php echo $this->number_of_plugins ?> plugins installed on this site:</p>
      <ul class='review_counts'>
      <?php
        self::plugin_review_count_box($this->vulnerable, $this->vulnerable_slug, $this->first_vulnerable_plugin_link, "are known to be vulnerable");
        self::plugin_review_count_box($this->not_reviewed, $this->grey_slug, $this->first_not_reviewed_plugin_link, "have no known vulnerabilities");
        if ($this->failed_requests > 0) {
          self::plugin_review_not_reviewed_box($this->failed_requests, $this->grey_slug, $this->first_failed_request_plugin_link, "could not be checked due to errors. Please try again later.");
        }
      ?>
      </ul>
      <p><a href='<?php echo $this->plugins_page_url ?>'>Visit your plugins page for more details...</a></p>
    <?php
  }

  private static function subscription_link() {
    $button = new dxw_security_Subscribe_Button(dxw_security_Options::url())
    ?>
      <div id="dxw_security_alert_subscription_link">
        <h4>Security alerts</h4>
        <p>
          Want to get notified of security issues with your plugins?
        </p>
        <?php $button->render() ?>
      </div>
    <?php
  }

  private static function plugin_review_count_box($count, $css_class, $plugin_link, $message) {
    if ($count == 0) { $css_class = $css_class . " none"; }
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    if (!is_null($plugin_link)) { print_r("<a href={$plugin_link}>"); }
    ?>
      <li class='plugin_review_count_box'>
        <div class='<?php echo $css_class ?> plugin_review_count_box_inner'>
          <span class='icon-<?php echo $css_class ?>'></span>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
        </div>
      </li>
    <?php
    if (!is_null($plugin_link)) { print_r("</a>"); }
  }

  private static function plugin_review_not_reviewed_box($count, $css_class, $plugin_link, $message) {
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    ?>
      <a href='<?php echo $plugin_link ?>'>
        <li class='<?php echo $css_class ?>'>
          <span class='icon-<?php echo $css_class ?>'></span>
          <span class='count'><?php echo $count ?></span>
          <?php echo $message ?>
        </li>
      </a>
    <?php
  }

  private function plugin_link($plugin_slug) {
    if (is_null($plugin_slug)) { return; }
    return "{$this->plugins_page_url}#{$plugin_slug}";
  }
}
?>
