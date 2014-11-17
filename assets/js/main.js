jQuery( document ).ready(function($) {
  // TODO: All this should only fire on the plugins page
  $( ".dialog" ).dialog({
    modal: true,
    autoOpen: false,
    dialogClass: "wp-dialog",
    width: 480,
  });

  $( ".dialog-link" ).on('click', function(e) {
    // Override the default link behaviour:
    e.preventDefault();
    $( this.hash ).dialog( 'option', "title", $( this ).data('title') );
    $( this.hash ).dialog( 'open' );
    return false;
  });

  if( $( ".intro-dialog" ).data("activated") ) {
    intro_dialog();
  }

  $( ".alert_subscription_button" ).on('click', function(e) {
    // Override the default link behaviour:
    e.preventDefault();
    intro_dialog();
  });


  function intro_dialog() {
    node = $( ".intro-dialog" );
    $( node ).dialog({
      modal: true,
      autoOpen: true,
      dialogClass: "wp-dialog",
      width: 800,
      open: function() { $("#dxw_security_alert_subscription_banner").hide(); },
      close: function() { $("#dxw_security_alert_subscription_banner").show(); },
    });
    $( node ).dialog( 'option', 'title', $( node ).data('title') );
    $( node ).dialog( 'open' );
  }

});