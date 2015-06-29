<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/views/review_data.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation.class.php');
require_once(dirname(__FILE__) . '/views/plugin_recommendation_error.class.php');

class dxw_security_Plugin_Recommendation_Fetcher {
  private $failed_requests = 0;

  private $name;
  private $installed_version;
  private $plugin_slug;

  public function __construct($name, $installed_version, $plugin_slug) {
    $this->name              = $name;
    $this->installed_version = $installed_version;
    $this->plugin_slug       = $plugin_slug;
  }

  public function recommendation() {
    // Stop making requests after a certain number of failures:
    if ($this->failed_requests > DXW_SECURITY_FAILURE_lIMIT) {
      $recommendation = $this->handle_api_fatal_error();
    } else {
      $api = new dxw_security_Advisories_API($this->plugin_slug, $this->installed_version);

      try {
        $review = $api->call();
        $recommendation = $this->handle_api_response($review, $this->name, $this->installed_version);
      } catch (dxw_security_API_NotFound $e) {
        $recommendation = $this->handle_api_not_found($this->name, $this->installed_version);
      } catch (\Exception $e) {
        $recommendation = $this->handle_api_error($e);
      }
    }

    return $recommendation;
  }

  private function handle_api_not_found($name, $installed_version) {
    $review_data = new dxw_security_Review_Data_No_Review();
    return new dxw_security_Plugin_Recommendation($name, $installed_version, $review_data);
  }

  private function handle_api_response($review, $name, $installed_version) {
    $review_data = new dxw_security_Review_Data($review);
    return new dxw_security_Plugin_Recommendation($name, $installed_version, $review_data);
  }

  private function handle_api_error($error) {
    // TODO: Handle errors actually raised by us in the api class separately?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    $this->failed_requests++;
    return new dxw_security_Plugin_Recommendation_Error();
  }

  private function handle_api_fatal_error() {
    return new dxw_security_Plugin_Recommendation_Error();
  }
}

?>
