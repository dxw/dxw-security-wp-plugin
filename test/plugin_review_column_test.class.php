<?php

require_once(dirname(__FILE__) . '/../lib/plugin_review_column.class.php');

// WordPress Function Mocks:
function get_site_transient() {}

class dxw_security_Plugin_Review_Column_Test extends PHPUnit_Framework_TestCase {

  # WARNING! this tries to call the actual API!!! (and will probably fail?)
  public function test_manage_plugins_custom_column() {
    $plugin_data = array(
      'Name' => 'foo',
      'Version' => '1.2.3'
    );
    dxw_security_Plugin_Review_Column::manage_plugins_custom_column('security_review', 'foo', $plugin_data);
  }
}
?>
