<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/views/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_error.class.php');

class dxw_security_Plugin_Recommendation_Fetcher {
  public function __construct($plugin, $review_fetcher) {
    $this->plugin         = $plugin;
    $this->review_fetcher = $review_fetcher;
  }

  public function call() {
    try {
      $review_data = $this->review_fetcher->call();
      return new dxw_security_Plugin_Recommendation($this->plugin, $review_data);
    } catch (\Exception $e) {
      // TODO: Handle errors actually raised by us in the api class separately?
      // TODO: in future we should provide some way for users to give us back some useful information when they get an error
      return new dxw_security_Plugin_Recommendation_Error();
    }
  }
}
?>
