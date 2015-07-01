<?php

defined('ABSPATH') OR exit;

// Accepts an object with the following methods:
//    * call
//    * handle_error
//    * handle_fatal_error
//
// ...and a reference to an external counter.
//
// Errors increment the counter, and after too many failures,
// we stop trying to call.
class dxw_security_Error_Limiter {
  private $callable;
  private $error_counter;
  private $error_limit;

  // $error_counter must be passed by reference (&$) so that it modifies the passed in count
  function __construct($callable, &$error_counter, $error_limit=DXW_SECURITY_FAILURE_lIMIT) {
    $this->callable      = $callable;
    $this->error_counter = &$error_counter;
    $this->error_limit   = $error_limit;
  }

  public function call() {
    if ($this->error_counter > $this->error_limit) {
      return $this->callable->handle_fatal_error();
    } else {
      return $this->call_with_error_handling();
    }
  }

  private function call_with_error_handling() {
    try {
      return $this->callable->call();
    } catch (\Exception $e) {
      $this->error_counter++;
      return $this->callable->handle_error($e);
    }
  }
}

?>
