<?php

defined('ABSPATH') OR exit;

// Decorator
class dxw_security_Link {
  private $html;
  private $link;

  public function __construct($html, $link) {
    $this->html = $html;
    $this->link  = $link;
  }

  public function __toString() {
    $link = esc_url($this->link);
    return "<a href='{$link}'>{$this->html}</a>";
  }
}

?>
