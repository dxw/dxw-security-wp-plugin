<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Recommendation_Dialog {
  private $name;
  private $review_data;
  private $slug;

  public function __construct($id, $review_data) {
    $this->id          = $id;
    $this->review_data = $review_data;
    $this->slug        = $review_data->slug;
  }

  public function render(){
    ?>
      <div id="<?php echo esc_attr($this->id); ?>" style="display:none;" class="dialog review-message <?php echo esc_attr($this->slug); ?>">

        <div class="inner">
          <?php print_r($this->review_data->render()) ?>
        </div>

      </div>
    <?php
  }
}
?>
