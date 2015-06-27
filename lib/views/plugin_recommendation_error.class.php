<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Recommendation_Error {
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
