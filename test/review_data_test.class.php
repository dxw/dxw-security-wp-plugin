<?php

require_once(dirname(__FILE__) . '/../lib/review_data.class.php');

// WordPress Function Mocks:
function esc_url($url) {
  return($url);
}

class dxw_security_Review_Data_Test extends PHPUnit_Framework_TestCase {
  private function review_data() {
    return new dxw_security_Review_Data("1.2.3", "vulnerable", "https://foo.com", "it is awful!", "kill it with fire!");
  }

  public function test_vulnerable_slug() {
    $this->assertNotEmpty(dxw_security_Review_Data::$dxw_security_review_statuses["vulnerable"]["slug"]);
  }

  public function test_not_found_slug() {
    $this->assertNotEmpty(dxw_security_Review_Data::$dxw_security_review_statuses["not-found"]["slug"]);
  }

  public function test_render() {
    $this->review_data()->render();
  }

  public function test_heading() {
    $this->review_data()->heading();
  }

  public function test_icon() {
    $this->review_data()->icon();
  }

  public function test_version() {
    $this->review_data()->version();
  }

}
?>
