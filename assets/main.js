jQuery( document ).ready(function($) {
  $( ".dialog" ).dialog({
    modal: true,
    autoOpen: false,
    dialogClass: "wp-dialog",
    width: 480,
    title: this.title
  });

  $( ".dialog-link" ).on('click', function(e) {
    e.preventDefault();
    $( this.hash ).dialog('open');
    return false;
  });
});