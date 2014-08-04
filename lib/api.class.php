<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Review_API extends dxw_security_API {

  private $plugin_slug;

  public function __construct($plugin_slug) {
    $this->plugin_slug = $plugin_slug;
  }

  // TODO: Currently this only handles directory plugins
  protected function api_path() {
    return "/plugins/{$this->plugin_slug}/reviews/";
  }

  protected function cache_slug() {
    return "dxw-security-plugin-review_{$this->plugin_slug}";
  }

  // The API will return a json body. This function defines how we get the data we want out of that (once it's been parsed into a php object)
  protected function extract_data($parsed_body) {
    return $parsed_body->reviews;
  }
}


// php doesn't support nested classes so these need to live outside the API class
class dxw_security_API_Error extends \Exception { }
class dxw_security_API_NotFound extends dxw_security_API_Error { }
class dxw_security_API {
  // TODO: This class doesn't work on it's own, only when extended by a class which defines the following:
  //    functions:
  //      * $api_path()
  //      * cache_slug()
  //      * extract_data($parsed_body)
  // Is there a standard way of doing this? should it complain on construction if those things aren't defined

  // TODO: re-implement as decorator pattern?

  public function call() {
    $data = $this->retrieve_api_data();

    // TODO: Transience returns false if it doesn't have the key, but should we also try to retrieve the result if the cache returned empty?
    if($data === false) {
      $data = $this->get();
    }
    return $data;
  }

  private function get() {
    $api_root = DXW_SECURITY_API_ROOT;
    $api_path = $this->api_path();
    $query    = "?dxwsec_version=" . DXW_SECURITY_PLUGIN_VERSION;

    // this should exist in core, but doesn't seem to:
    // $url = http_build_url(
    //   array(
    //     "host"  => $api_root,
    //     "path"  => $api_path,
    //     "query" => $query
    //   )
    // );
    $url = $api_root . $api_path . $query;

    $response = wp_remote_get(esc_url($url));

    return $this->handle_response($response);
  }

  // Either return a review or throw an error
  private function handle_response($response) {
    if (is_wp_error($response)) {
      throw new dxw_security_API_Error($response->get_error_message());

    } else {
      switch ($response['response']['code']) {
        case 200:
          $parsed_body = $this->parse_response_body($response['body']);
          $data = $this->extract_data($parsed_body);
          // TODO: Validate data and raise an error if it's invalid. Children of this class would need to implement a 'validate()' function
          $this->cache_api_data($data);
          return $data;

        case 404:
          // This should only get triggered if a bad request was made to the api - e.g. api/v2/foo
          //    In this scenario we get a usage message - could check for that but there doesn't seem to be much point.
          throw new dxw_security_API_NotFound();

        default:
          // TODO: handle other codes individually?
          // A redirect would end up here - is it possible to get one??
          throw new dxw_security_API_Error("Response was {$response['response']['code']}: {$response['body']}");
      };
    }
  }

  private function parse_response_body($body) {
    $parsed_body = json_decode($body);

    if (!is_null($parsed_body)) {
      return $parsed_body;
    } else {
      $truncated_body = mb_substr($body, 0, 100);
      throw new dxw_security_API_Error("Couldn't parse json body beginning: {$truncated_body}");
    }
  }

  private function cache_api_data($data) {
    if (DXW_SECURITY_CACHE_RESPONSES) {
      $slug = $this->cache_slug();
      // TODO: How long should this get cached for?
      set_transient($slug, $data, DAY_IN_SECONDS);
    }
  }
  private function retrieve_api_data() {
    if (DXW_SECURITY_CACHE_RESPONSES) {
      $slug = $this->cache_slug();
      return get_transient($slug);
    } else {
      return false;
    }
  }
}
?>