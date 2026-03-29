/**
 * @file
 * JavaScript for Clean Content form — enables submit button only when "CLEAN" is typed.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsCleanConfirm = {
    attach: function (context) {
      once('clean-confirm', '[data-clean-confirm]', context).forEach(function (input) {
        var submitBtn = document.querySelector('[data-clean-submit]');
        if (!submitBtn) {
          return;
        }

        input.addEventListener('input', function () {
          if (input.value === 'CLEAN') {
            submitBtn.removeAttribute('disabled');
            submitBtn.classList.add('button--danger');
          }
          else {
            submitBtn.setAttribute('disabled', 'disabled');
          }
        });
      });
    }
  };

})(Drupal, once);
