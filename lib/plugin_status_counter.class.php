<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/models/plugin_file.class.php');

class dxw_security_Plugin_Status_Counter {
  private $vulnerable_counter;
  private $not_reviewed_counter;
  private $failed_counter;

  public function __construct() {
    $this->vulnerable_counter   = new dxw_security_Plugins_Status();
    $this->not_reviewed_counter = new dxw_security_Plugins_Status();
    $this->failed_counter       = new dxw_security_Plugins_Status();
  }

  public function get_counts($plugins) {
    foreach($plugins as $plugin_file => $data) {
      $plugin_file_object = new dxw_security_Plugin_File($plugin_file);
      $plugin_slug        = $plugin_file_object->plugin_slug;
      $installed_version  = $data["Version"];

      // TODO: this pattern is duplicated in the security column code
      // Stop making requests after a certain number of failures:
      if ($this->failed_counter->count > DXW_SECURITY_FAILURE_lIMIT) {
        $this->handle_api_fatal_error($plugin_slug);
      } else {
        $api = new dxw_security_Advisories_API($plugin_slug, $installed_version);
        try {
          $reviews = $api->call();
          $this->handle_api_response($reviews, $installed_version, $plugin_slug);
        } catch (dxw_security_API_NotFound $e) {
          $this->handle_api_not_found($plugin_slug);
        } catch (Exception $e) {
          $this->handle_api_error($e, $plugin_slug);
        }
      }
    }

    return array(
      'vulnerable'   => $this->vulnerable_counter,
      'not_reviewed' => $this->not_reviewed_counter,
      'failed'       => $this->failed_counter,
    );
  }

  private function handle_api_not_found($plugin_slug) {
    $this->not_reviewed_counter->increment($plugin_slug);
  }

  private function handle_api_response($review, $installed_version, $plugin_slug) {
    $this->vulnerable_counter->increment($plugin_slug);
  }

  private function handle_api_error($error, $plugin_slug) {
    // TODO: Handle errors actually raised by us in the api class separately from other errors?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    $this->failed_counter->increment($plugin_slug);
  }

  private function handle_api_fatal_error($plugin_slug) {
    // Assume it would have failed
    //   Keep counting because currently we're just displaying "x failed"
    $this->failed_counter->increment($plugin_slug);
    // TODO: instead throw an error here to be captured higher up.
  }
}

class dxw_security_Plugins_Status {
  public $count;
  public $first_plugin_slug;

  public function __construct() {
    $this->count = 0;
  }

  public function increment($plugin_slug) {
    $this->count++;
    if (is_null($this->first_plugin_slug)) { $this->first_plugin_slug = $plugin_slug; }
  }
}
?>
