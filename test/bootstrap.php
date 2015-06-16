<?php

// All our plugin files start with:
//    defined('ABSPATH') OR exit;
// so we need to define it in order to require them
define('ABSPATH', true);

require_once(dirname(__FILE__) . '/../mongoosewp.php');

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
?>
