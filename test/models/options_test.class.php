<?php

require_once(dirname(__FILE__) . '/../../lib/models/options.class.php');

class dxw_security_Options_Test extends PHPUnit_Framework_TestCase {
  public function test_page_slug() {
    $this->assertNotEmpty(dxw_security_Options::$page_slug);
  }

  public function test_url() {
    $this->assertNotEmpty(dxw_security_Options::url());;
  }
}

?>
