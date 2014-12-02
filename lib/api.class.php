<?php

defined('ABSPATH') OR exit;

class dxw_security_Plugin_Review_API extends dxw_security_Cached_API {

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

class dxw_security_Registration_API extends dxw_security_API {

  private $post_data;

  public function __construct($email) {
    $this->post_data = array("email" => $email);
  }

  protected function api_path() {
    return "/registrations";
  }

  protected function request_args() {
    return array(
        'method' => 'POST',
        'body' => array("registration" => $this->post_data)
      );
  }

  // The API will return a json body. This function defines how we get the data we want out of that (once it's been parsed into a php object)
  protected function extract_data($parsed_body) {
    return $parsed_body->subscriber;
  }
}

class dxw_security_Manifest_API extends dxw_security_API {

  private $post_data;

  public function __construct($manifest) {
    $this->post_data = array(
      "manifest" => $manifest->to_json(),
      "auth_token" => $this->auth_token(),
    );
  }

  protected function api_path() {
    return "/plugin_manifests";
  }

  protected function request_args() {
    return array(
        'method' => 'POST',
        'body' => $this->post_data
      );
  }

  // The API will return a json body. This function defines how we get the data we want out of that (once it's been parsed into a php object)
  protected function extract_data($parsed_body) {
    return $parsed_body;
  }

  // TODO: this probably shouldn't live here - will ultimately need to be used by multiple endpoints:
  private function auth_token() {
    return get_option( 'dxw_security_subscription_token' );
  }
}


// php doesn't support nested classes so these need to live outside the API class
class dxw_security_API_Error extends \Exception { }
class dxw_security_API_NotFound extends dxw_security_API_Error { }
class dxw_security_API_BadData extends dxw_security_API_Error { }

class dxw_security_API {
  // TODO: re-implement as decorator pattern? How?

  public function call() {
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

    $response = wp_remote_request(esc_url($url), $this->request_args());

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
          return $data;

        case 404:
          // This should only get triggered if a bad request was made to the api - e.g. api/v2/foo
          //    In this scenario we get a usage message - could check for that but there doesn't seem to be much point.
          throw new dxw_security_API_NotFound();

        case 422:
          // This should only get triggered if invalid data was posted to the api - e.g. a missing payload or upstream validation errors
          //    In this scenario we get a json error message
          $parsed_body = $this->parse_response_body($response['body']);

          throw new dxw_security_API_BadData($this->extract_error($parsed_body));

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

  private function extract_error($parsed_body) {
    $error = $parsed_body->error;
    // TODO: handle the case where the payload doesn't include an error part

    if( is_array($error) ) { $error = implode(", ", $error); }

    return $error;
  }

  protected function request_args() {
    return array(
        'method' => 'GET'
      );
  }

  protected function api_path() {
    throw new Exception('Not implemented');
  }
  protected function cache_slug() {
    throw new Exception('Not implemented');
  }
  protected function extract_data($parsed_body) {
    throw new Exception('Not implemented');
  }
}

class dxw_security_Cached_API extends dxw_security_API{

  public function call() {
    $data = $this->retrieve_api_data();

    // TODO: Transience returns false if it doesn't have the key, but should we also try to retrieve the result if the cache returned empty?
    if($data === false) {
      $data = parent::call();
      $this->cache_api_data($data);
    }

    return $data;
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