<?php

require_once(dirname(__FILE__) . '/../mongoosewp.php');

// WordPress Function Mocks:
function plugin_basename($file) {
  return "mongoosewp/mongoosewp.php";
}

function wp_enqueue_style() {}
function wp_enqueue_script() {}
function plugins_url() {}

class MongooseWP_Test extends PHPUnit_Framework_TestCase {
  private function main_class() {
    return new dxw_Security();
  }

  // Not worth testing?
  public function test_enqueue_scripts() {
    $this->main_class()->enqueue_scripts("foo");
  }

  public function test_activation_redirect() {
    $this->main_class()->activation_redirect("mongoowsewp");
  }
}
?>
