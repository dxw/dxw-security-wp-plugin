<?php

require_once(dirname(__FILE__) . '/../lib/review_fetcher.class.php');

class dxw_security_Review_Fetcher_Test extends PHPUnit_Framework_TestCase {
  # WARNING! this tries to call the actual API!!! (and will probably fail?)
  public function test_run() {
    dxw_security_Review_Fetcher::run();
  }
}
?>
