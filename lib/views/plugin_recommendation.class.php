<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_recommendation_dialog.class.php');
require_once(dirname(__FILE__) . '/plugin_recommendation_panel.class.php');

class dxw_security_Plugin_Recommendation {
  private $name;
  private $panel;
  private $dialog;

  public function __construct($name, $version, $review_data) {
    $this->name = $name;
    $this->panel  = new dxw_security_Plugin_Recommendation_Panel($name, $version, $review_data, $this->dialog_id());
    $this->dialog = new dxw_security_Plugin_Recommendation_Dialog($this->dialog_id(), $review_data);
  }

  public function render() {
    $this->panel->render();
    $this->dialog->render();
  }

  private function dialog_id() {
    return "plugin-inspection-results-" . sanitize_title($this->name);
  }
}
?>
