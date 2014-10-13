<?php

defined('ABSPATH') OR exit;

// TODO: Delete if not needed
// require_once(dirname(__FILE__) . '/api.class.php');
// require_once(dirname(__FILE__) . '/review_data.class.php');
// require_once(dirname(__FILE__) . '/plugin_recommendation.class.php');
// require_once(dirname(__FILE__) . '/plugin_file.class.php');

class dxw_security_Intro_Modal {
  public function __construct() {
    $this->render_dialog();
  }

  private function render_dialog(){
    ?>
      <div id="foo" style="display:none;" class="intro-dialog">

        <a href="http://security.dxw.com" id="dxw-sec-link"><img src="<?php echo plugins_url('/assets/dxw-logo.png' , dirname(__FILE__)); ?>" alt="dxw logo" /></a>

        <div class="inner">
          FOO!
        </div>

      </div>
    <?php
  }

}
?>