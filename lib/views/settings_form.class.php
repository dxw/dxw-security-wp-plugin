<?php

defined('ABSPATH') OR exit;

class dxw_security_Settings_Form {
  public function __construct($page_slug) {
    $this->page_slug = $page_slug;
  }

  public function render() {
    ?>
    <form action="options.php" method="POST">
      <?php settings_fields($this->page_slug) ?>
      <?php do_settings_sections($this->page_slug) ?>
      <?php submit_button("Save", "secondary") ?>
    </form>
    <?php
  }
}
?>
