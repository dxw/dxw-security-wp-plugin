<?php

require_once(dirname(__FILE__) . '/../../lib/models/plugin_manifest.class.php');

class dxw_security_Plugin_Manifest_Test extends PHPUnit_Framework_TestCase {

  public function test_to_json() {
    $manifest = new dxw_security_Plugin_Manifest();
    $this->assertEquals($manifest->to_json(), '[{"slug":"0","version":"1.2.3"}]');
  }
}
?>
