/**
 * @file corporate.js
 * Corporate skin JS — transparent-to-dark scroll header on the front page.
 *
 * Adds/removes the `scrolled` class on `.lms-navbar`, `.lms-header`, and
 * `.navbar` when the user scrolls past 60 px on the front page.
 */
(function (Drupal) {
  'use strict';

  Drupal.behaviors.corporateScrollHeader = {
    attach: function (context, settings) {
      // Only run on the corporate front page.
      if (!document.body.classList.contains('is-front-page')) {
        return;
      }
      if (!document.body.classList.contains('layout-corporate')) {
        return;
      }

      // Collect header elements once.
      var headers = document.querySelectorAll('.lms-navbar, .lms-header, .navbar');
      if (!headers.length) {
        return;
      }

      var SCROLL_THRESHOLD = 60;

      function updateHeaderState() {
        var scrolled = window.scrollY > SCROLL_THRESHOLD;
        headers.forEach(function (el) {
          if (scrolled) {
            el.classList.add('scrolled');
          } else {
            el.classList.remove('scrolled');
          }
        });
      }

      // Use once() via Drupal if available, otherwise guard manually.
      if (typeof once === 'function') {
        var body = once('corporate-scroll-header', 'body', context);
        if (!body.length) {
          return;
        }
      } else {
        if (window.__corporateScrollHeaderAttached) {
          return;
        }
        window.__corporateScrollHeaderAttached = true;
      }

      window.addEventListener('scroll', updateHeaderState, { passive: true });
      // Set initial state in case the page loads mid-scroll.
      updateHeaderState();
    }
  };

}(Drupal));
