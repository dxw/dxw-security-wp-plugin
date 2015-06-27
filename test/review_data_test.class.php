<?php

require_once(dirname(__FILE__) . '/../lib/views/review_data.class.php');

// WordPress Function Mocks:
function esc_url($url) {
  return($url);
}

class dxw_security_Review_Data_Test extends PHPUnit_Framework_TestCase {
  private function review_data() {
    $review = (object) array(
      "review_link" => "https://foo.com",
      "reason"      => "it is awful!",
      "action"      => "kill it with fire!",
    );
    return new dxw_security_Review_Data($review);
  }

  public function test_render_body() {
    $this->review_data()->body->render();
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
    $this->review_data_no_review()->body->render();
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
