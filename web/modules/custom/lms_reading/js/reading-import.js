/**
 * @file
 * "Use this example" / "Copy" buttons for the Reading JSON import tip
 * blocks — one pair of buttons per example (Narrative, Dialogue), each
 * pointing at its own `<code id="...">` via the button's own
 * data-reading-use-example/data-reading-copy-example attribute value, so
 * "Use this example" on the Dialogue block never accidentally loads the
 * Narrative one or vice versa.
 *
 * Mirrors lms_flashcard's js/flashcard-import.js equivalent behavior
 * (minus the deck-picker helpers, since this form has no deck <select> —
 * the target deck comes from passage.deck_machine_name inside the JSON
 * itself).
 */

(function (Drupal, once) {
  'use strict';

  /**
   * Narrative/Dialogue example tabs — one `data-reading-example-panel`
   * shown at a time (see ReadingImportForm::buildExampleTip()), toggled by
   * `data-reading-example-tab` buttons.
   */
  Drupal.behaviors.lmsReadingImportExampleTabs = {
    attach: function (context) {
      once('lms-reading-import-example-tabs', '.lms-reading-import__example-tabs', context).forEach(function (tabs) {
        var wrapper = tabs.parentNode;
        var tabButtons = Array.prototype.slice.call(tabs.querySelectorAll('[data-reading-example-tab]'));
        var panels = Array.prototype.slice.call(wrapper.querySelectorAll('[data-reading-example-panel]'));

        tabButtons.forEach(function (tabBtn) {
          tabBtn.addEventListener('click', function () {
            var target = tabBtn.dataset.readingExampleTab;

            tabButtons.forEach(function (btn) {
              var active = btn === tabBtn;
              btn.classList.toggle('is-active', active);
              btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
              panel.hidden = panel.dataset.readingExamplePanel !== target;
            });
          });
        });
      });
    },
  };

  Drupal.behaviors.lmsReadingImportExample = {
    attach: function (context) {
      once('lms-reading-import-example', '.lms-reading-import-form', context).forEach(function (form) {
        var textarea = form.querySelector('#edit-json-text');

        function flashLabel(button, message) {
          var original = button.textContent;
          button.textContent = message;
          window.setTimeout(function () {
            button.textContent = original;
          }, 1500);
        }

        Array.prototype.slice.call(form.querySelectorAll('[data-reading-copy-example]')).forEach(function (copyBtn) {
          var codeEl = document.getElementById(copyBtn.dataset.readingCopyExample);
          if (!codeEl) {
            return;
          }
          copyBtn.addEventListener('click', function () {
            var text = codeEl.textContent;
            if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(text).then(function () {
                flashLabel(copyBtn, Drupal.t('Copied!'));
              });
            }
          });
        });

        Array.prototype.slice.call(form.querySelectorAll('[data-reading-use-example]')).forEach(function (useBtn) {
          var codeEl = document.getElementById(useBtn.dataset.readingUseExample);
          if (!codeEl || !textarea) {
            return;
          }
          useBtn.addEventListener('click', function () {
            textarea.value = codeEl.textContent;
            textarea.focus();
            flashLabel(useBtn, Drupal.t('Added!'));
          });
        });
      });
    },
  };

})(Drupal, once);
