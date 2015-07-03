<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/views/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_error.class.php');

class dxw_security_Plugin_Recommendation_Fetcher {
  public function __construct($name, $installed_version, $review_fetcher) {
    $this->name              = $name;
    $this->installed_version = $installed_version;
    $this->review_fetcher    = $review_fetcher;
  }

  public function call() {
    $review_data = $this->review_fetcher->fetch();
    return $recommendation = new dxw_security_Plugin_Recommendation($this->name, $this->installed_version, $review_data);
  }
}


class dxw_security_Plugin_Recommendation_Error_Handler {
  public function handle($error=null) {
    // TODO: Handle errors actually raised by us in the api class separately?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    return new dxw_security_Plugin_Recommendation_Error();
  }
}

?>
