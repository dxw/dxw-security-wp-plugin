<?php

defined('ABSPATH') OR exit;

class dxw_security_Review_Data {
  public $version;
  public $slug;
  private $message;
  private $description;
  private $reason;
  private $action;

  public static $dxw_security_review_statuses = array(
    'vulnerable'=> array( 'message' => "Vulnerable",
                          'slug' => "vulnerable",
                          'description' => "This plugin has a proven vulnerability. It might be safe to use under certain conditions but you should very carefully consider the details of the vulnerability before using it."),
    'not-found' => array( 'message' => "No known vulnerabilities",
                          'slug' => "no-info",
                          'description' => "We do not know of any security issues with this plugin."),
  );

  public function __construct($version, $status, $reason="", $action="", $link=DXW_SECURITY_PLUGINS_URL) {
    $this->version = $version;
    $this->reason = $reason;
    $this->action = $action;
    $this->link = $link;

    $review_status = self::$dxw_security_review_statuses[$status];
    $this->message       = $review_status['message'];
    $this->description   = $review_status['description'];
    $this->slug          = $review_status['slug'];
  }

  public function render() {
    // reason is retrieved from the api but might legitimately include html
    // description heading and action might also legitimately include html but come from strings in this code
    ?>
      <h2><a href="<?php echo $link ?>"><?php echo $this->heading() ?></a></h2>
      <p class="review-status-description"><?php echo $this->description ?></p>
      <?php
        if ($this->slug == 'vulnerable') {
          $this->render_details();
          $this->render_action();
          $this->render_read_more();
        }
      ?>
    <?php
  }

  private function render_action() {
    if (!empty($this->action)) {
      echo("<h3>What should I do?</h3>");
      print_r("<p>{$this->action}</p>");
    }
  }

  private function render_details() {
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

  public function heading() {
    return "{$this->icon()} {$this->message}";
  }

  public function icon() {
    return "<span class='icon-{$this->slug}' title='{$this->message}'></span>";
  }

  // Versions might be a comma separated string with no spaces e.g. "1.9.2,1.9.3"
  public function version() {
    return implode(", ", explode(",", $this->version));
  }
}
?>
