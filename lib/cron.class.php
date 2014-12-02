<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/task.class.php');
require_once(dirname(__FILE__) . '/review_fetcher.class.php');
require_once(dirname(__FILE__) . '/plugin_manifest_poster.class.php');

class dxw_security_Cron {
  private $fetcher_task;
  private $poster_task;

  public function __construct() {
    $this->fetcher_task = new dxw_security_Task('dxw_security_Review_Fetcher');
    $this->poster_task  = new dxw_security_Task('dxw_security_Plugin_Manifest_Poster');

    $this->hook_tasks();

    register_activation_hook( __FILE__, array($this, 'schedule_tasks' ));
  }

  public function schedule_tasks() {
    $this->fetcher_task->schedule('daily');
    $this->poster_task->schedule('daily');
  }

  public function hook_tasks() {
    $this->fetcher_task->hook();
    $this->poster_task->hook();
  }
}

?>