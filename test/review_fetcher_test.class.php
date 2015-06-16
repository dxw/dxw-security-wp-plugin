<?php

require_once(dirname(__FILE__) . '/../lib/review_fetcher.class.php');

// WordPress Function Mocks:
function get_plugins() {
  $plugin_data = array(
    'Name' => 'foo',
    'Version' => '1.2.3'
  );
  return(array($plugin_data));
}

class dxw_security_Review_Fetcher_Test extends PHPUnit_Framework_TestCase {
  # WARNING! this tries to call the actual API!!! (and will probably fail?)
  public function test_run() {
    dxw_security_Review_Fetcher::run();
  }
}
?>
