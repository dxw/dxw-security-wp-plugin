<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Recommendation_Panel {
  private $name;
  private $version;
  private $slug;
  private $title;
  private $dialog_id;

  public function __construct($plugin, $review_data, $dialog_id) {
    $this->name        = $plugin->name;
    $this->version     = $plugin->version;
    $this->title       = $review_data->title; // Legitimately includes html - defined within the code of this plugin
    $this->slug        = $review_data->slug;
    $this->dialog_id   = $dialog_id;
  }

  public function render() {
    ?>
      <a href="#<?php echo esc_attr($this->dialog_id); ?>" data-title="<?php echo esc_attr($this->name); ?> - <?php echo esc_attr($this->version); ?>" class="dialog-link review-message <?php echo esc_attr($this->slug) ?>">
        <h3><?php echo $this->title; ?></h3>
        <p class='more-info'>More information</p>
      </a>
    <?php
  }
}
?>
