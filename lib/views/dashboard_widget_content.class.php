<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../models/user.class.php');
require_once(dirname(__FILE__) . '/../models/options.class.php');
require_once(dirname(__FILE__) . '/subscribe_button.class.php');
require_once(dirname(__FILE__) . '/null_view.class.php');


class dxw_security_Dashboard_Widget_Content {
  private $number_of_plugins;
  private $vulnerable_data;
  private $not_reviewed_data;
  private $failed_data;

  public function __construct($number_of_plugins, $plugin_status_counts) {
    $this->number_of_plugins = $number_of_plugins;

    $this->vulnerable_data   = new dxw_security_Plugin_Link_Presenter($plugin_status_counts['vulnerable']);
    $this->not_reviewed_data = new dxw_security_Plugin_Link_Presenter($plugin_status_counts['not_reviewed']);
    $this->failed_data       = new dxw_security_Plugin_Link_Presenter($plugin_status_counts['failed']);

    # TODO: These slugs are effectively duplicated information between this and the review_data class
    $this->vulnerable_slug = 'vulnerable';
    $this->grey_slug       = 'no-info';
  }

  public function render() {
    $vulnerable_box = new dxw_security_Plugin_Review_Count_Box(
                        $this->vulnerable_data,
                        $this->vulnerable_slug,
                        "are known to be vulnerable"
                      );

    $not_reviewed_box = new dxw_security_Plugin_Review_Count_Box(
                        $this->not_reviewed_data,
                        $this->grey_slug,
                        "have no known vulnerabilities"
                      );

    if ($this->failed_data->count > 0) {
      $failed_box = new dxw_security_Plugin_Review_Failed_Count_Box(
                          $this->failed_data,
                          $this->grey_slug,
                          "could not be checked due to errors. Please try again later."
                        );
    } else {
      $failed_box = new dxw_security_Null_View();
    }
    $plugins_page_url = 'plugins.php';

    if( dxw_security_User::can_subscribe() ) {
      $this->subscription_link();
    }
    ?>
      <p>Of the <?php echo $this->number_of_plugins ?> plugins installed on this site:</p>
      <ul class='review_counts'>
      <?php
        $vulnerable_box->render();
        $not_reviewed_box->render();
        $failed_box->render();
      ?>
      </ul>
      <p><a href='<?php echo $plugins_page_url ?>'>Visit your plugins page for more details...</a></p>
    <?php
  }

  private function subscription_link() {
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
}

// Accept an object with a 'first_plugin_slug' attribute
// and decorate it with a 'plugin_link()' method
class dxw_security_Plugin_Link_Presenter {
  private $object;
  public  $plugin_link;

  public function __construct($object) {
    $this->object = $object;
    $this->plugin_link = $this->plugin_link();
  }

  private function plugin_link() {
    if (is_null($this->object->first_plugin_slug)) { return; }
    return "plugins.php#{$this->object->first_plugin_slug}";
  }

  # defer attributes onto the object
  public function __get($attribute) {
    return $this->object->$attribute;
  }
}

class dxw_security_Plugin_Review_Count_Box {
  public function __construct($data, $css_class, $message) {
    $this->count       = $data->count;
    $this->link        = $data->plugin_link;
    $this->message     = $message;

    $this->icon_class  = "icon-{$css_class}";
    $this->inner_class = $css_class;
    if ($this->count == 0) { $this->inner_class = $this->inner_class . " none"; }
  }

  public function render(){
    if (!is_null($this->link)) {
      $this->render_linked_box();
    } else {
      $this->render_box();
    }
  }

  private function render_linked_box() {
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    ?>
      <a href='<?php echo $this->link; ?>'>
        <?php $this->render_box() ?>
      </a>
    <?php
  }

  private function render_box() {
    ?>
      <li class='plugin_review_count_box'>
        <div class='<?php echo $this->inner_class ?> plugin_review_count_box_inner'>
          <span class='<?php echo $this->icon_class ?>'></span>
          <span class='count'><?php echo $this->count ?></span>
          <?php echo $this->message ?>
        </div>
      </li>
    <?php
  }
}

class dxw_security_Plugin_Review_Failed_Count_Box {
  public function __construct($data, $css_class, $message) {
    $this->count       = $data->count;
    $this->link        = $data->plugin_link;
    $this->message     = $message;

    $this->icon_class  = "icon-{$css_class}";
    $this->inner_class = $css_class;
  }

  public function render(){
    // TODO: Is it bad form to wrap the li in an anchor, rather than having it inside?
    ?>
      <a href='<?php echo $this->link ?>'>
        <li class='<?php echo $this->inner_class ?>'>
          <span class='<?php echo $this->icon_class ?>'></span>
          <span class='count'><?php echo $this->count ?></span>
          <?php echo $this->message ?>
        </li>
      </a>
    <?php
  }
}

class dxw_security_Dashboard_Widget_Content_No_Plugins {
  public function render() {
    echo "<p>There are no plugins installed on this site.</p>";
  }
}

?>
