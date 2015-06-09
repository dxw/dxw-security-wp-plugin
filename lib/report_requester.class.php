<?php

defined('ABSPATH') OR exit;

require_once(dirname(__FILE__) . '/api.class.php');

// Sends a message to the API requesting that a report be generated for the current
// subscriber (i.e. on-demand)
class dxw_security_Report_Requester {

  public static function request($api_key) {
    $api = new dxw_security_Reports_API($api_key);

    try {
      $api->call();
    } catch (dxw_security_API_Unauthorised $e) {
      // Do nothing?
    } catch (\Exception $e) {
      // Do nothing?
    }
  }
}
?>
