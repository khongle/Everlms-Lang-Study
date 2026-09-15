/**
 * @file
 * "Copy JSON" button on the deck export page.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsFlashcardExport = {
    attach: function (context) {
      once('lms-flashcard-export-copy', '[data-flashcard-export-copy]', context).forEach(function (button) {
        var code = document.getElementById('lms-flashcard-export-json');
        if (!code) {
          return;
        }
        button.addEventListener('click', function () {
          if (!navigator.clipboard || !navigator.clipboard.writeText) {
            return;
          }
          navigator.clipboard.writeText(code.textContent).then(function () {
            var original = button.textContent;
            button.textContent = Drupal.t('Copied!');
            window.setTimeout(function () {
              button.textContent = original;
            }, 1500);
          });
        });
      });
    },
  };

})(Drupal, once);
