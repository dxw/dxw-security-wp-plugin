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

  $( ".intro-dialog" ).dialog({
    modal: true,
    autoOpen: true,
    dialogClass: "wp-dialog",
    width: 500,
  });

  $( ".intro-dialog" ).dialog( 'open' );
});