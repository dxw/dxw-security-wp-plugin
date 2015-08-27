<?php

require_once(dirname(__FILE__) . '/../lib/settings_page.class.php');

// WordPress Function Mocks:
function add_options_page() {}
function settings_fields() {}
function do_settings_sections() {}
function submit_button() {}

class dxw_security_Settings_Page_Test extends PHPUnit_Framework_TestCase {
  public function test_setup() {
    dxw_security_Settings_Page::setup();
  }

  public function test_render() {
    dxw_security_Settings_Page::render_content();
  }
}

?>
