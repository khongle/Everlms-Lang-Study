/**
 * @file
 * "Use this example" / "Copy" buttons for the JSON import tip block.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsFlashcardImportExample = {
    attach: function (context) {
      once('lms-flashcard-import-example', '.lms-flashcard-import-form', context).forEach(function (form) {
        var exampleCode = form.querySelector('#lms-flashcard-example-json');
        var textarea = form.querySelector('#edit-json-text');
        var newModeRadio = form.querySelector('#edit-mode-new');
        var copyBtn = form.querySelector('[data-flashcard-copy-example]');
        var useBtn = form.querySelector('[data-flashcard-use-example]');

        if (!exampleCode) {
          return;
        }

        function flashLabel(button, message) {
          var original = button.textContent;
          button.textContent = message;
          window.setTimeout(function () {
            button.textContent = original;
          }, 1500);
        }

        if (copyBtn) {
          copyBtn.addEventListener('click', function () {
            var text = exampleCode.textContent;
            if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(text).then(function () {
                flashLabel(copyBtn, Drupal.t('Copied!'));
              });
            }
          });
        }

        if (useBtn && textarea) {
          useBtn.addEventListener('click', function () {
            textarea.value = exampleCode.textContent;
            if (newModeRadio) {
              newModeRadio.checked = true;
              newModeRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }
            textarea.focus();
            flashLabel(useBtn, Drupal.t('Added!'));
          });
        }
      });
    },
  };

})(Drupal, once);
