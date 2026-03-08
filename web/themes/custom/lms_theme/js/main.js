/**
 * @file
 * EverLMS Theme main JavaScript.
 */

(function ($, Drupal, once) {
    'use strict';

    /**
     * Mobile navbar toggle.
     */
    Drupal.behaviors.lmsNavbar = {
        attach: function (context) {
            once('lms-navbar-toggle', '.lms-navbar__toggle', context).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var nav = document.querySelector('.lms-navbar__nav');
                    if (nav) {
                        nav.classList.toggle('lms-navbar__nav--open');
                    }
                });
            });
        }
    };

    /**
     * Smooth scroll for anchor links.
     */
    Drupal.behaviors.lmsSmoothScroll = {
        attach: function (context) {
            once('lms-smooth-scroll', 'a[href^="#"]', context).forEach(function (link) {
                link.addEventListener('click', function (e) {
                    var targetId = this.getAttribute('href');
                    if (targetId && targetId.length > 1) {
                        var target = document.querySelector(targetId);
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                });
            });
        }
    };

})(jQuery, Drupal, once);
