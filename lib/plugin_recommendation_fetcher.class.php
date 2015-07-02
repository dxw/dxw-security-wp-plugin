<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php'); # Only needed for the dxw_security_API_NotFound class
require_once(dirname(__FILE__) . '/views/review_data.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_error.class.php');

class dxw_security_Plugin_Recommendation_Fetcher {
  private $api;
  private $name;
  private $installed_version;

  public function __construct($name, $installed_version, $api) {
    $this->api               = $api;
    $this->name              = $name;
    $this->installed_version = $installed_version;
  }

  public function fetch() {
    try {
      $review = $this->api->call();
      $review_data = new dxw_security_Review_Data($review);
    } catch (dxw_security_API_NotFound $e) {
      $review_data = new dxw_security_Review_Data_No_Review();
    }

    return new dxw_security_Plugin_Recommendation($this->name, $this->installed_version, $review_data);
  }

  public function handle_api_error($error) {
    // TODO: Handle errors actually raised by us in the api class separately?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    return new dxw_security_Plugin_Recommendation_Error();
  }

  public function handle_api_fatal_error() {
    return new dxw_security_Plugin_Recommendation_Error();
  }
}

?>
