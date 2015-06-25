<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_File {
  private $plugin_file;
  public $plugin_slug;

  public function __construct($plugin_file) {
    $this->plugin_file = $plugin_file;
    $this->plugin_slug = $this->plugin_slug($plugin_file);
  }

  // Cribbed from wp-admin/includes/update.php in core
  public function latest_version() {
    $plugin_updates = get_site_transient( 'update_plugins' );
    if ( !isset( $plugin_updates->response[ $this->plugin_file ] ) )
      return false;

    return $plugin_updates->response[ $this->plugin_file ]->new_version;
  }

  private function plugin_slug() {
    // Versions of php before 5.4 don't allow array indexes to be accessed directly on the output of functions
    //   http://www.php.net/manual/en/migration54.new-features.php - "Function array dereferencing"
    $f = explode('/', $this->plugin_file);

    // HACK - strip off file extensions to make Hello Dolly etc. not complain
    //  we might get lucky and this actually be the slug we're looking for, but if not, the search just won't find anything
    $directory_slug = preg_replace("/\\.[^.\\s]{3,4}$/", "", $f[0]);
    // END HACK

    return $directory_slug;
  }
}
?>