(function ($) {
  Drupal.behaviors.addCartBehavior = {
    attach: function (context, settings) {
      $( "#tabs" ).tabs({
        active: 0
      });
      let get = $('#tabs').data('get');
      if(get == 'films') {
        $("#tabs").tabs("option", "active", 9);
      }
    }
  };
})(jQuery);
