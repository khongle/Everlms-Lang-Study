/**
 * @file
 * "Use this example" / "Copy" buttons for the Listening JSON import tip
 * blocks — one pair of buttons per example (Sentence, Narrative, Dialogue),
 * each pointing at its own `<code id="...">` via the button's own
 * data-listening-use-example/data-listening-copy-example attribute value.
 *
 * Mirrors lms_reading's reading-import.js (kept as an independent copy, not
 * a shared import, per this module's independence from lms_reading's own
 * code).
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsListeningImportExampleTabs = {
    attach: function (context) {
      once('lms-listening-import-example-tabs', '.lms-listening-import__example-tabs', context).forEach(function (tabs) {
        var wrapper = tabs.parentNode;
        var tabButtons = Array.prototype.slice.call(tabs.querySelectorAll('[data-listening-example-tab]'));
        var panels = Array.prototype.slice.call(wrapper.querySelectorAll('[data-listening-example-panel]'));

        tabButtons.forEach(function (tabBtn) {
          tabBtn.addEventListener('click', function () {
            var target = tabBtn.dataset.listeningExampleTab;

            tabButtons.forEach(function (btn) {
              var active = btn === tabBtn;
              btn.classList.toggle('is-active', active);
              btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
              panel.hidden = panel.dataset.listeningExamplePanel !== target;
            });
          });
        });
      });
    },
  };

  Drupal.behaviors.lmsListeningImportExample = {
    attach: function (context) {
      once('lms-listening-import-example', '.lms-listening-import-form', context).forEach(function (form) {
        var textarea = form.querySelector('#edit-json-text');

        function flashLabel(button, message) {
          var original = button.textContent;
          button.textContent = message;
          window.setTimeout(function () {
            button.textContent = original;
          }, 1500);
        }

        Array.prototype.slice.call(form.querySelectorAll('[data-listening-copy-example]')).forEach(function (copyBtn) {
          var codeEl = document.getElementById(copyBtn.dataset.listeningCopyExample);
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

        Array.prototype.slice.call(form.querySelectorAll('[data-listening-use-example]')).forEach(function (useBtn) {
          var codeEl = document.getElementById(useBtn.dataset.listeningUseExample);
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
