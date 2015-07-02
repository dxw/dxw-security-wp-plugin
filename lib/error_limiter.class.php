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


class dxw_security_Error_Handled_Caller {
  private $callable;
  private $error_handler;

  function __construct($callable, $error_handler) {
    $this->callable      = $callable;
    $this->error_handler = $error_handler;
  }

  public function call() {
    try {
      return $this->callable->call();
    } catch (\Exception $e) {
      return $this->error_handler->handle($e);
    }
  }
}


// Only calls the (probably expensive) subject if the number of errors is below a certain limit.
class dxw_security_Error_Limited_Caller {
  private $callable;
  private $error_count;
  private $error_limit;
  private $fatal_error_handler;

  function __construct($callable, $fatal_error_handler, $error_count, $error_limit=DXW_SECURITY_FAILURE_lIMIT) {
    $this->callable            = $callable;
    $this->error_count         = $error_count;
    $this->error_limit         = $error_limit;
    $this->fatal_error_handler = $fatal_error_handler;
  }

  public function call() {
    if ($this->error_count > $this->error_limit) {
      return $this->fatal_error_handler->handle();
    } else {
      return $this->callable->call();
    }
  }
}


// Counts the number of errors which are passed to it
class dxw_security_Counting_Error_Handler {
  private $error_handler;
  private $error_counter;

  // $error_counter must be passed by reference (&$) so that it modifies the passed in count
  function __construct($error_handler, &$error_counter) {
    $this->error_handler = $error_handler;
    $this->error_counter = &$error_counter;
  }

  public function handle($error) {
    $this->error_counter++;
    return $this->error_handler->handle($error);
  }
}

?>
