/**
 * @file
 * "Add unknown words to Flashcards" button on the Review screen.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.lmsReadingAddUnknownWords = {
    attach: function (context) {
      once('lms-reading-add-unknown-words', '[data-reading-add-unknown-words]', context).forEach(function (button) {
        var settings = (drupalSettings.lmsReading) || {};

        button.addEventListener('click', function () {
          button.disabled = true;
          var originalText = button.textContent;
          button.textContent = Drupal.t('Adding…');

          fetch(settings.addUnknownWordsUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ attempt_id: settings.attemptId }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (data.error) {
                button.disabled = false;
                button.textContent = originalText;
                return;
              }
              button.textContent = Drupal.t('Added @count word(s) to Flashcards', { '@count': data.added_count || 0 });
            })
            .catch(function () {
              button.disabled = false;
              button.textContent = originalText;
            });
        });
      });
    },
  };

})(Drupal, drupalSettings, once);
