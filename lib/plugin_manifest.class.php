<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Manifest {
  private $manifest; // An array of plugin slugs and versions

  public function __construct() {
    $plugins = $this->get_plugins();
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

  // TODO: duplicated in the review_fetcher class
  private function get_plugins() {
    if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return get_plugins(); // From core
  }
}
?>