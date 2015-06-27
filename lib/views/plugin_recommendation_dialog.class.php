<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Recommendation_Dialog {
  private $id;
  private $title;
  private $description;
  private $body;
  private $slug;

  public function __construct($id, $review_data) {
    $this->id          = $id;
    $this->title       = $review_data->linked_title;
    $this->description = $review_data->description;
    $this->body        = $review_data->body;
    $this->slug        = $review_data->slug;
  }

  public function render(){
    ?>
      <div id="<?php echo esc_attr($this->id); ?>" style="display:none;" class="dialog review-message <?php echo esc_attr($this->slug); ?>">

        <div class="inner">
          <h2><?php echo $this->title ?></h2>
          <p class='review-status-description'>
            <?php echo $this->description ?>
          </p>
          <?php $this->body->render(); ?>
        </div>

      </div>
    <?php
  }
}
?>
