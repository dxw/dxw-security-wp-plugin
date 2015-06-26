<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/views/plugin_recommendation_dialog.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_panel.class.php');

class dxw_security_Plugin_Recommendation {
  private $name;
  private $version;
  private $review_data;

  public function __construct($name, $version, $review_data) {
    $this->name        = $name;
    $this->version     = $version;
    $this->review_data = $review_data;
  }

  public function render() {
    $panel = new dxw_security_Plugin_Recommendation_Panel($this->name, $this->version, $this->review_data, $this->dialog_id());
    $panel->render();
    print_r($this->render_dialog());
  }

  protected function render_dialog(){
    $dialog = new dxw_security_Plugin_Recommendation_Dialog($this->dialog_id(), $this->review_data);
    $dialog->render();
  }

  protected function dialog_id() {
    return "plugin-inspection-results-" . sanitize_title($this->name);
  }
}


class dxw_security_Null_Plugin_Recommendation {
  public function render(){
    ?>
    <a href='<?php echo(esc_url(DXW_SECURITY_PLUGINS_URL)); ?>' class="review-message review-error">
      <h3>An error occurred</h3>
      <p>Please try again later</p>
    </a>
    <?php
  }
}
?>
