<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/link.class.php');
require_once(dirname(__FILE__) . '/null_view.class.php');

class dxw_security_Review_Data {
  public $slug;
  public $title;
  public $linked_title;
  public $description;
  public $body;

  public function __construct($review) {
    $this->slug         = "vulnerable";
    $this->title        = new dxw_security_Review_Data_Title("Vulnerable", $this->slug);
    $this->linked_title = new dxw_security_Link($this->title, $review->review_link);
    $this->description  = "This plugin has a proven vulnerability. It might be safe to use under certain conditions but you should very carefully consider the details of the vulnerability before using it.";
    $this->body         = new dxw_security_Review_Data_Body($review->review_link, $review->reason, $review->action);
  }
}


class dxw_security_Review_Data_No_Review {
  public $slug;
  public $title;
  public $linked_title;
  public $description;
  public $body;

  public function __construct() {
    $this->slug         = "no-info";
    $this->title        = new dxw_security_Review_Data_Title("No known vulnerabilities", $this->slug);
    $this->linked_title = $this->title;
    $this->description  = "We do not know of any security issues with this plugin.";
    $this->body         = new dxw_security_Null_View;
  }
}


class dxw_security_Review_Data_Title {
  private $message;
  private $slug;

  public function __construct($message, $slug) {
    $this->message = $message;
    $this->slug = $slug;
  }

  public function __toString() {
    return "{$this->icon()} {$this->message}";
  }

  private function icon() {
    return "<span class='icon-{$this->slug}' title='{$this->message}'></span>";
  }
}

class dxw_security_Review_Data_Body {
  private $link;
  private $reason;
  private $action;

  public function __construct($link, $reason, $action) {
    $this->link = $link;
    $this->reason = $reason;
    $this->action = $action;
  }

  public function render() {
    $this->render_details();
    $this->render_action();
    $this->render_read_more();
  }

  private function render_action() {
    if (!empty($this->action)) {
      echo("<h3>What should I do?</h3>");
      print_r("<p>{$this->action}</p>");
    }
  }

  private function render_details() {
    // reason is retrieved from the api but might legitimately include html so shouldn't be escaped
    if (!empty($this->reason)) {
      print_r("<h3>Details:</h3>");
      print_r("<p>{$this->reason}</p>");
    }
  }

  private function render_read_more() {
    $link = esc_url($this->link);
    if (empty($this->reason)) {
      echo("<a href='{$link}' class='read-more' >See the full security advisory for details</a>");
    } else {
      echo("<a href='{$link}' class='read-more button-primary'> Read more...</a>");
    }
  }
}

?>
