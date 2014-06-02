<?php
class dxw_security_Plugin_Recommendation {
  private $name;
  private $version;
  private $slug;
  private $review_data;
  private $css_class;

  public function __construct($name, $version, $slug, $review_data, $heading, $more_info="More information", $dialog_intro="", $css_class="") {
    $this->name = $name;
    $this->version = $version;
    $this->slug = $slug;
    $this->review_data = $review_data;
    $this->heading = $heading;
    $this->more_info = $more_info;
    $this->dialog_intro = $dialog_intro;
    $this->css_class = $css_class;
  }

  public function render() {
    ?>
      <a href="#<?php echo esc_attr($this->dialog_id()); ?>" data-title="<?php echo esc_attr($this->name); ?> - <?php echo esc_attr($this->version); ?>" class="dialog-link review-message <?php echo esc_attr($this->slug) ?> <?php echo esc_attr($this->css_class) ?>">
        <h3><?php echo $this->heading; ?></h3>

        <p class="more-info"><?php echo esc_html($this->more_info); ?></p>
      </a>
    <?php
    print_r($this->render_dialog());
  }

  protected function render_dialog(){
    ?>
      <div id="<?php echo esc_attr($this->dialog_id()); ?>" style="display:none;" class="dialog review-message <?php echo esc_attr($this->slug); ?> <?php echo esc_attr($this->css_class) ?>">

        <a href="http://security.dxw.com" id="dxw-sec-link"><img src="<?php echo plugins_url('/assets/dxw-logo.png' , dirname(__FILE__)); ?>" alt="dxw logo" /></a>

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
  public function __construct($name, $version, $review_data) {
    $this->recommendation = new dxw_security_Plugin_Recommendation($name, $version, $review_data->slug, $review_data, $review_data->heading());
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
    $heading = "<span class='icon-no-info'></span> This version ({$version}) not yet reviewed";
    $more_info =  "These versions have been reviewed: {$other_reviews_data->versions()}";
    $this->recommendation = new dxw_security_Plugin_Recommendation($name, $version, "other-versions-reviewed", $other_reviews_data, $heading, $more_info, $dialog_intro, "other-{$latest_result}");
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