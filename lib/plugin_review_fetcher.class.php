<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php'); # Only needed for the dxw_security_API_NotFound class
require_once(dirname(__FILE__) . '/views/review_data.class.php');

class dxw_security_Plugin_Review_Fetcher {
  private $api;

  public function __construct($api) {
    $this->api = $api;
  }

  public function fetch() {
    try {
      $review = $this->api->call();
      return new dxw_security_Review_Data($review);
    } catch (dxw_security_API_NotFound $e) {
      return new dxw_security_Review_Data_No_Review();
    }
  }
}

?>
