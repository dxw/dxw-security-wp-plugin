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
          alert("success");
        } else {
          error_messages = body["data"]["errors"];
          _show_errors(error_messages)
        }
      },
      error:function(data){
        // TODO: what should happen here?
        alert("ERROR!!!");
        console.log(data);
      }
    });
    return false;
  });

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
