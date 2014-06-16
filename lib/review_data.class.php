<?php
class dxw_security_Review_Data {
  public $version;
  public $slug;
  private $message;
  private $description;
  private $reason;

  public static $dxw_security_review_statuses = array(
    'green'     => array( 'message' => "No issues found",
                          'slug' => "no-issues-found",
                          'description' => "dxw's review didn't find anything worrying in this plugin. It's probably safe."),
    'yellow'    => array( 'message' => "Use with caution",
                          'slug' => "use-with-caution",
                          'description' => "Before using this plugin, you should carefully consider the findings of dxw's review."),
    'red'       => array( 'message' => "Potentially unsafe",
                          'slug' => "potentially-unsafe",
                          'description' => "Before using this plugin, you should very carefully consider its potential problems and should conduct a thorough assessment."),
    'not-found' => array( 'message' => "Not yet reviewed",
                          'slug' => "no-info",
                          'description' => "We haven't reviewed this plugin yet. If you like we can review it for you."),
  );

  public function __construct($version, $status, $reason="", $link=DXW_SECURITY_PLUGINS_URL) {
    $this->version = $version;
    $this->reason = $reason;
    $this->link = $link;

    $review_status = self::$dxw_security_review_statuses[$status];
    $this->message       = $review_status['message'];
    $this->description   = $review_status['description'];
    $this->slug          = $review_status['slug'];
  }

  public function render() {
    // reason is retrieved from the api but might legitimately include html
    // description and heading might also legitimately include html but come from strings in this code
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
          echo("<a href='{$link}' class='read-more button-primary'> Read more...</a>");
        }
      ?>
    <?php
  }

  public function heading() {
    return "<span class='icon-{$this->slug}'></span> {$this->message}";
  }

  // Versions might be a comma separated string with no spaces e.g. "1.9.2,1.9.3"
  public function version() {
    return implode(", ", explode(",", $this->version));
  }

  // Compares a single version string to a comma separated list of versions
  public static function version_matches($version, $list) {
    $versions = explode( ',', $list );
    return in_array($version, $versions);
  }
}

class dxw_security_Other_Version_Reviews_Data {
  # Expects an array of Review_Data objects
  public function __construct($reviews) {
    $this->reviews = $reviews;
  }

  public function render() {
    // TODO - this will result in two consecutive h2 headings - not great, but works for now.
    foreach($this->reviews as &$review) {
      ?>
        <div class="other-review <?php echo $review->slug ?>">
          <h2>Version <?php echo $review->version() ?></h2>
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
}
?>