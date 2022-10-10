(function($, Drupal) {

  if(Drupal.AjaxCommands){
    Drupal.AjaxCommands.prototype.starwarsAjaxCommand = function(ajax, response, status){
      var frontpageModal = Drupal.dialog(response.content, {
        title: 'Modal on frontpage',
        dialogClass: 'front-modal',
        width: 200,
        height: 200,
        autoResize: true,
        close: function (event) {
          $(event.target).remove();
        }
      });
      frontpageModal.showModal();
      window.location = response.url;
    }
  }
})(jQuery, Drupal);
