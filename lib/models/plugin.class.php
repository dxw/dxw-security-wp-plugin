<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/plugin_file.class.php');

class dxw_security_Plugin {
  public $slug;
  public $name;
  public $version;

  public function __construct($plugin_file, $plugin_data) {
    $plugin_file = new dxw_security_Plugin_File($plugin_file);
    $this->slug    = $plugin_file->plugin_slug;
    $this->name    = $plugin_data['Name'];
    $this->version = $plugin_data['Version'];
  }
}

?>
