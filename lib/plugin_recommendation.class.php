<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Recommendation {
  private $name;
  private $version;
  private $slug;
  private $body;
  private $review_data;
  private $heading;
  private $dialog_intro;

  public function __construct($name, $version, $slug, $review_data, $heading, $body, $dialog_intro="") {
    $this->name = $name;
    $this->version = $version;
    $this->slug = $slug;
    $this->body = $body; // Legitimately includes html - defined in this file
    $this->review_data = $review_data;
    $this->heading = $heading; // Legitimately includes html - defined within the code of this plugin
    $this->dialog_intro = $dialog_intro;
  }

  public function render() {
    ?>
      <a href="#<?php echo esc_attr($this->dialog_id()); ?>" data-title="<?php echo esc_attr($this->name); ?> - <?php echo esc_attr($this->version); ?>" class="dialog-link review-message <?php echo esc_attr($this->slug) ?>">
        <h3><?php echo $this->heading; ?></h3>

        <?php echo $this->body; ?>
      </a>
    <?php
    print_r($this->render_dialog());
  }

  protected function render_dialog(){
    ?>
      <div id="<?php echo esc_attr($this->dialog_id()); ?>" style="display:none;" class="dialog review-message <?php echo esc_attr($this->slug); ?>">

        <div class="inner">
          <?php print_r($this->dialog_intro) ?>
          <?php print_r($this->review_data->render()) ?>
        </div>

      </div>
    <?php
  }

  protected function dialog_id() {
    return "plugin-inspection-results-" . sanitize_title($this->name);
  }
}


class dxw_security_Plugin_Recommendation_Reviewed {
  private $recommendation;

  public function __construct($name, $version, $review_data) {
    $body = "<p class='more-info'>More information</p>";
    $this->recommendation = new dxw_security_Plugin_Recommendation($name, $version, $review_data->slug, $review_data, $review_data->heading(), $body);
  }
  public function render() {
    $this->recommendation->render();
  }
}


class dxw_security_Plugin_Recommendation_Other_Versions_Reviewed {
  private $recommendation;

  public function __construct($name, $version, $other_reviews_data) {
    $latest_result = $other_reviews_data->most_recent()->slug;
    $dialog_intro =  "<p class='intro'>The installed version ({$version}) has not yet been reviewed, but here are some reviews of other versions:</p>";
    $heading = "{$other_reviews_data->icon()} Not yet reviewed";
    $body =  "<p class='more-info'>Reviewed versions:</p> {$other_reviews_data->render_versions()}"; // TODO: Passing around a chunk of html is kind of nasty
    $this->recommendation = new dxw_security_Plugin_Recommendation($name, $version, "other-versions-reviewed", $other_reviews_data, $heading, $body, $dialog_intro);
  }
  public function render() {
    $this->recommendation->render();
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
