<?php

if (!defined('DXW_SECURITY_API_ROOT')) {
  define('DXW_SECURITY_API_ROOT', 'https://app.security.dxw.com/api/v2');
}
if (!defined('DXW_SECURITY_EMAIL')) {
  define('DXW_SECURITY_EMAIL', 'contact@mongoosewp.com');
}
if (!defined('DXW_SECURITY_CACHE_RESPONSES')) {
  define('DXW_SECURITY_CACHE_RESPONSES', true);
}

// How many failed requests will we tolerate?
if (!defined('DXW_SECURITY_FAILURE_lIMIT')) {
  define('DXW_SECURITY_FAILURE_lIMIT', 5);
}

// The URL we link to when we don't have any info about a plugin
if (!defined('DXW_SECURITY_PLUGINS_URL')) {
  define('DXW_SECURITY_PLUGINS_URL', 'https://security.dxw.com/plugins/');
}
?>
