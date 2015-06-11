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
                          'description' => "We have not found any vulnerabilities in this plugin."),
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
    $link = esc_url($this->link)
    ?>
      <h2><a href="<?php echo $link ?>"><?php echo $this->heading() ?></a></h2>
      <p class="review-status-description"><?php echo $this->description ?></p>

      <?php
        if (empty($this->reason)) {
          echo("<a href='{$link}' class='read-more' >See the dxw Security website for details</a>");
        } else {
          print_r("<h3>Details:</h3>");
          print_r("<p>{$this->reason}</p>");
          if (!empty($this->action)) {
            echo("<h3>What should I do?</h3>");
            print_r("<p>{$this->action}</p>");
          }
          echo("<a href='{$link}' class='read-more button-primary'> Read more...</a>");
        }
      ?>
    <?php
    // TODO: the logic above isn't quite right: what happens if there's no reason, but an action? Probably won't happen in the short term.
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

  // Compares a single version to the versions which this review applies to (may be a comma-separated list)
  public function version_matches($version) {
    return dxw_security_Plugin_Version_Comparer::version_matches($version, $this->version);
  }
}

class dxw_security_Other_Version_Reviews_Data {
  private $reviews;
  private $latest_version;

  # Expects an array of Review_Data objects
  public function __construct($reviews, $latest_version) {
    $this->reviews = $reviews;
    $this->latest_version = $latest_version;
  }

  public function render() {
    // TODO - this will result in two consecutive h2 headings - not great, but works for now.
    foreach($this->reviews as &$review) {
      ?>
        <div class="other-review <?php echo $review->slug ?>">
          <h2>Version <?php echo esc_attr($review->version()); if ($review->version_matches($this->latest_version)) { echo " (Latest)"; }?></h2>
          <?php print_r($review->render()) ?>
        </div>
      <?php
    }
  }

  public function most_recent() {
    return current($this->reviews);
  }

  // It would be nice to use array_map for this, but it doesn't seem to be possible to do that without defining a callback function in the global namespace.
  public function versions() {
    $versions=array();
    foreach($this->reviews as &$review) {
      $versions[] = $review->version();
    }
    return implode(", ", $versions);
  }

  // Return an html fragment comprising a list of versions with icons
  //   TODO: Is there a less horrible way of doing this?
  public function render_versions() {
    $list_items = "";
    foreach($this->reviews as &$review) {
      $list_item = "<li class='{$review->slug}'>{$review->icon()} {$review->version()}";
      if ($review->version_matches($this->latest_version)) {
        $list_item .= " (Latest)";
      }
      $list_item .= "</li>";

      $list_items .= $list_item;
    }

    return "<ul class='reviewed_versions'>{$list_items}</ul>";
  }

  public function icon() {
    return "<span class='icon-no-info' title='Not yet reviewed'></span>";
  }
}

class dxw_security_Plugin_Version_Comparer {
  // Compares a single version to a comma-separated list of versions
  public static function version_matches($version, $list) {
    $versions = explode( ',', $list );
    return in_array($version, $versions);
  }
}


?>