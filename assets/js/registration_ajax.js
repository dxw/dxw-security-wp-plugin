jQuery(document).ready(function($){
  $("form#subscription_form").on('submit', function(e){
    var email      = $("#email").val();
    var permission = $("#permission").val();
    var wpnonce      = $("#_wpnonce").val();
    var salt       = $("#salt").val();

    e.preventDefault();

    $.ajax({
      type:"POST",
      url: "/wp-admin/admin-ajax.php",
      data: {
        action: "subscribe",
        subscription:{
          email:email,
          permission:permission,
        },
        salt:salt,
        _wpnonce:wpnonce,
      },
      success:function(body, code, xhr){
        console.log(code, body, xhr);
        alert("success");
        alert(body);
      },
      error:function(data){
        alert("ERROR!!!");
      }
    });
    return false;
  });
});
