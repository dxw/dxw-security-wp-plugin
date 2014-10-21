jQuery(document).ready(function($){
  $("form#subscription_form").on('submit', function(e){
    var data = $(this).serialize();

    e.preventDefault();

    $.ajax({
      type:"POST",
      url: "/wp-admin/admin-ajax.php",
      data: data,
      success:function(body){
        if( body["success"] ) {
          _handle_success(body);
        } else {
          _handle_errors(body);
        }
      },
      error:function(data){
        // TODO: what should happen here?
        error_messages = new Array(_fatal_error_message(_unknown_error_code));
        _show_errors(error_messages);
      }
    });
    return false;
  });
  function _handle_success(body) {
    if( !body.hasOwnProperty("data") ) {
      error_messages = new Array(_fatal_error_message(_no_data_code));
    } else if ( !body["data"].hasOwnProperty("email") ) {
      error_messages = new Array(_fatal_error_message(_no_email_code));
    } else {

      $('#subscription_form .errors').empty();
      $('.intro-dialog').dialog( 'close' );
      // TODO: this is a fragile way of adding notices, but I can't see a better way
      $('div.wrap h2:first').after(_success_notice("You've successfully subscribed to dxw Security alerts with "+ body["data"]["email"]));
    }
  }

  function _handle_errors(body) {
    if( !body.hasOwnProperty("data") ) {
      error_messages = new Array(_fatal_error_message(_no_data_code));
    } else if ( !body["data"].hasOwnProperty("errors") ) {
      error_messages = new Array(_fatal_error_message(_no_error_code));
    } else {
      error_messages = body["data"]["errors"];
    }
    _show_errors(error_messages);
  }

  var _no_data_code = 1;
  var _no_email_code = 2;
  var _no_errors_code = 3;
  var _unknown_error_code = 4;

  // TODO: Will this escaping break on older browsers?
  function _fatal_error_message(code) {
    return "Sorry, an error occurred. This is probably a bug.\
    If you could report it to security@dxw.com, quoting code '"+ code +"' it would\
    be much appreciated.";
  }

  function _show_errors(error_messages) {
    $('#subscription_form .errors').html(
      $.map(error_messages, function(message){
        return _error_div(message);
      })
    );
  }

  function _error_div(message) {
    return $("<div class='error'/>").append($('<p/>').text(message));
  }
  function _success_notice(message) {
    return $("<div class='updated'/>").append($('<p/>').text(message));
  }
});
