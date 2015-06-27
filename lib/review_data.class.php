<?php

defined('ABSPATH') OR exit;

class dxw_security_Review_Data {
  public $version;
  public $slug;

  private $link;
  private $reason;
  private $action;

  public $title;
  public $heading;
  public $description;
  public $body;

  public function __construct($version, $link, $reason, $action) {
    $this->version = $version;
    $this->link = $link;

    $this->slug = "vulnerable";

    $title = new dxw_security_Review_Data_Title("Vulnerable", $this->slug);
    $this->title = $title->html();
    $this->heading = new dxw_security_Review_Data_Linked_Heading($title, $link);
    $this->description = new dxw_security_Review_Data_Description("This plugin has a proven vulnerability. It might be safe to use under certain conditions but you should very carefully consider the details of the vulnerability before using it.");
    $this->body = new dxw_security_Review_Data_Body($link, $reason, $action);
  }

  public function render() {
    $this->heading->render();
    $this->description->render();
    $this->body->render();
  }
}


class dxw_security_Review_Data_No_Review {
  public $slug;

  public $title;
  public $heading;
  public $description;
  public $body;

  public function __construct() {
    $this->slug = "no-info";

    $title = new dxw_security_Review_Data_Title("No known vulnerabilities", $this->slug);
    $this->title = $title->html();
    $this->heading = new dxw_security_Review_Data_Heading($title);
    $this->description = new dxw_security_Review_Data_Description("We do not know of any security issues with this plugin.");
    $this->body = new dxw_security_Null_View;
  }

  public function render() {
    $this->heading->render();
    $this->description->render();
    $this->body->render();
  }
}



class dxw_security_Review_Data_Title {
  public function __construct($message, $slug) {
    $this->message = $message;
    $this->slug = $slug;
  }

  public function html() {
    return "{$this->icon()} {$this->message}";
  }

  public function icon() {
    return "<span class='icon-{$this->slug}' title='{$this->message}'></span>";
  }
}

class dxw_security_Review_Data_Heading {
  private $title;

  public function __construct($title) {
    $this->title = $title->html();
  }

  public function render() {
    echo("<h2>{$this->title}</h2>");
  }
}

class dxw_security_Review_Data_Linked_Heading {
  private $title;
  private $link;

  public function __construct($title, $link) {
    $this->title = $title->html();
    $this->link  = $link;
  }

  public function render() {
    $link = esc_url($this->link);
    echo("<h2><a href='{$link}'>{$this->title}</a></h2>");
  }
}

class dxw_security_Review_Data_Description {
  private $description;

  public function __construct($description) {
    $this->description = $description;
  }

  public function render() {
    echo("<p class='review-status-description'>{$this->description}</p>");
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

class dxw_security_Null_View {
  public function render() {}
}

?>
