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
          var error_messages = body["data"]["errors"];
          $('#subscription_form .errors').html(
            $.map(error_messages, function(message){
              return error_div(message);
            })
          );
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

  function error_div(message) {
    return "<div class='error'>" + message + "</div>";
  }
});
