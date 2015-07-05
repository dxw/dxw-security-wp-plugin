<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');
require_once(dirname(__FILE__) . '/models/plugin.class.php');

class dxw_security_Plugin_Statuses_Counter {
  private $failed_requests = 0;

  public function __construct() {

    $this->counters = array(
      'vulnerable'   => new dxw_security_Plugins_Status(),
      'not_reviewed' => new dxw_security_Plugins_Status(),
      'failed'       => new dxw_security_Plugins_Status(),
    );
  }

  public function get_counts($plugins) {
    foreach($plugins as $plugin_file => $plugin_data) {
      $plugin = new dxw_security_Plugin($plugin_file, $plugin_data);
      $this->count_plugin($plugin);
    }

    return $this->counters;
  }

  private function count_plugin($plugin) {
    $api = new dxw_security_Advisories_API($plugin);

    $status_counter = new dxw_security_Plugin_Status_Counter($plugin->slug, $api, $this->counters);
    return $this->count_plugin_with_error_limiting($status_counter);
  }

  private function count_plugin_with_error_limiting($status_counter) {
    $adapted_status_counter = new dxw_security_Status_Counter_Adaptor($status_counter);
    $limited_status_counter = new dxw_security_Error_Limiter($adapted_status_counter, $this->failed_requests);

    return $limited_status_counter->call();
  }
}

class dxw_security_Status_Counter_Adaptor {
  private $status_counter;

  public function __construct($status_counter) {
    $this->status_counter = $status_counter;
  }

  public function call() {
    return $this->status_counter->count_plugin();
  }

  public function handle_error($error) {
    return $this->status_counter->handle_api_error($error);
  }

  public function handle_fatal_error() {
    return $this->status_counter->handle_api_fatal_error();
  }
}


class dxw_security_Plugin_Status_Counter {
  private $api;
  private $plugin_slug;
  private $counters;

  public function __construct($plugin_slug, $api, &$counters) {
    $this->api               = $api;
    $this->plugin_slug       = $plugin_slug;
    $this->counters          = &$counters;
  }

  public function count_plugin() {
    try {
      $this->api->call();
      $this->count_vulnerable();
    } catch (dxw_security_API_NotFound $e) {
      $this->count_not_found();
    }
  }

  private function count_not_found() {
    $this->counters['not_reviewed']->increment($this->plugin_slug);
  }

  private function count_vulnerable() {
    $this->counters['vulnerable']->increment($this->plugin_slug);
  }

  public function handle_api_error($error) {
    // TODO: Handle errors actually raised by us in the api class separately from other errors?
    // TODO: in future we should provide some way for users to give us back some useful information when they get an error
    $this->counters['failed']->increment($this->plugin_slug);
  }

  public function handle_api_fatal_error() {
    // Assume it would have failed
    //   Keep counting because currently we're just displaying "x failed"
    $this->counters['failed']->increment($this->plugin_slug);
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
