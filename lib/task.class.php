<?php

defined('ABSPATH') OR exit;

class dxw_security_Task {
  private $task;
  private $hook_name;

  public function __construct($task_class_name) {
    $this->task = array($task_class_name, "run");
    $this->hook_name = $task_class_name; // Set the hook name to the task name. This might not workin general(?)
  }

  public function schedule($frequency) {
    $this->schedule_once($frequency);
  }

  public function unschedule() {
    wp_clear_scheduled_hook($this->hook_name);
  }

  public function hook() {
    // Define a hook for wp_cron to call:
    remove_action($this->hook_name);
    add_action($this->hook_name, $this->task);
  }

  private function schedule_once($frequency) {
    if( ! $this->scheduled() ) {
      wp_schedule_event( time(), $frequency, $this->hook_name );
    }
  }

  private function scheduled() {
    return (bool)wp_next_scheduled($this->hook_name);
  }
}

?>