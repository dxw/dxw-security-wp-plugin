<?php

// All our plugin files start with:
//    defined('ABSPATH') OR exit;
// so we need to define it in order to require them
define('ABSPATH', true);

define('DXW_SECURITY_FAILURE_lIMIT', 1);
define('DXW_SECURITY_PLUGINS_URL', 'http://example.com');

// WordPress Function Mocks:
function register_activation_hook() {}
function register_deactivation_hook() {}

function add_action() {}
function remove_action() {}

function wp_next_scheduled() {}
function wp_schedule_event() {}

function get_option() {}
function update_option() {}
function get_site_option() {}
function get_transient() {}

function is_admin() { return true; }

// Until we add proper mocking, this will have to do for all tests:
function get_plugins() {
  $plugin_data = array(
    'Name' => 'foo',
    'Version' => '1.2.3'
  );
  return(array($plugin_data));
}
?>
