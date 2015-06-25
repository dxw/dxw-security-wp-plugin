<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/../plugin_getter.class.php');

class dxw_security_Plugin_Manifest {
  private $manifest; // An array of plugin slugs and versions

  public function __construct() {
    $plugins = dxw_security_Plugin_Getter::get();
    // Since php can't map both keys and values, we need to pass these as separate arrays
    $this->manifest = array_map(array($this, "create_manifest"), array_keys($plugins), $plugins);
  }

  public function to_json() {
    return json_encode($this->manifest);
  }

  private function create_manifest($plugin_path, $plugin_data) {
    $plugin_file = new dxw_security_Plugin_File($plugin_path);
    return array(
      "slug" => $plugin_file->plugin_slug,
      "version" => $plugin_data["Version"]
    );
  }
}
?>
