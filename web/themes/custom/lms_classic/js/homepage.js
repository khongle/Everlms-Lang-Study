(function (Drupal, once) {
  Drupal.behaviors.lmsHomepage = {
    attach: function (context, settings) {
      once('lmsHomepageAos', 'html', context).forEach(function () {
        if (typeof AOS !== 'undefined') {
          AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
          });
        }
      });
    }
  };
})(Drupal, once);
