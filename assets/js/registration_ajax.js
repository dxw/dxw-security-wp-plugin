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
          alert("success - IMPLEMENT ME");
        } else {
          if( !body.hasOwnProperty("data") ) {
            error_messages = new Array(_fatal_error_message(_no_data_code));
          } else if ( !body["data"].hasOwnProperty("errors") ) {
            error_messages = new Array(_fatal_error_message(_no_error_code));
          } else {
            error_messages = body["data"]["errors"];
          }
          _show_errors(error_messages);
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

  var _no_data_code = 1;
  var _no_errors_code = 2;
  var _unknown_error_code = 3;

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
    return "<div class='error'>" + message + "</div>";
  }
});
