(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsCommunityHomepageCarousel = {
    attach: function (context) {
      once('lms-community-hero-carousel', '#communityHeroCarousel', context).forEach(function (carouselEl) {
        var rawInterval = parseInt(carouselEl.getAttribute('data-bs-interval'), 10);
        var interval = Number.isFinite(rawInterval) && rawInterval >= 2500 ? rawInterval : 5600;

        // Prefer Bootstrap's native carousel when available.
        if (window.bootstrap && window.bootstrap.Carousel) {
          var instance = window.bootstrap.Carousel.getInstance(carouselEl);
          if (!instance) {
            instance = new window.bootstrap.Carousel(carouselEl, {
              interval: interval,
              ride: 'carousel',
              pause: false,
              wrap: true,
              touch: true
            });
          }
          instance.cycle();
          return;
        }

        // Fallback auto-rotate if Bootstrap JS is unavailable.
        var items = carouselEl.querySelectorAll('.carousel-item');
        if (items.length < 2) {
          return;
        }

        var indicators = carouselEl.querySelectorAll('[data-bs-slide-to]');
        var activeIndex = 0;

        items.forEach(function (item, index) {
          if (item.classList.contains('active')) {
            activeIndex = index;
          }
        });

        var setActive = function (nextIndex) {
          items.forEach(function (item, index) {
            var isActive = index === nextIndex;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-hidden', isActive ? 'false' : 'true');
          });

          indicators.forEach(function (indicator, index) {
            var isActive = index === nextIndex;
            indicator.classList.toggle('active', isActive);
            indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
          });

          activeIndex = nextIndex;
        };

        window.setInterval(function () {
          setActive((activeIndex + 1) % items.length);
        }, interval);
      });
    }
  };
})(Drupal, once);
