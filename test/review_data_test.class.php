<?php

require_once(dirname(__FILE__) . '/../lib/review_data.class.php');

// WordPress Function Mocks:
function esc_url($url) {
  return($url);
}

class dxw_security_Review_Data_Test extends PHPUnit_Framework_TestCase {
  private function review_data() {
    return new dxw_security_Review_Data("1.2.3", "https://foo.com", "it is awful!", "kill it with fire!");
  }

  public function test_render() {
    $this->review_data()->render();
  }

  public function test_title() {
    $actual   = $this->review_data()->title;
    $expected = "<span class='icon-vulnerable' title='Vulnerable'></span> Vulnerable";
    $this->assertEquals($actual, $expected);
  }

  public function test_slug() {
    $actual   = $this->review_data()->slug;
    $expected = "vulnerable";
    $this->assertEquals($actual, $expected);
  }

  private function review_data_no_review() {
    return new dxw_security_Review_Data_No_Review();
  }

  public function test_render_no_review() {
    $this->review_data_no_review()->render();
  }

  public function test_title_no_review() {
    $actual   = $this->review_data_no_review()->title;
    $expected = "<span class='icon-no-info' title='No known vulnerabilities'></span> No known vulnerabilities";
    $this->assertEquals($actual, $expected);
  }

  public function test_slug_no_review() {
    $actual   = $this->review_data_no_review()->slug;
    $expected = "no-info";
    $this->assertEquals($actual, $expected);
  }
}
?>
