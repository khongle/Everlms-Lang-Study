/**
 * @file
 * Deck edit form: client-side filter for the "Cards in this deck" sidebar
 * panel (see DeckForm::buildCardsPanel()). Pure show/hide over the
 * already-rendered card items — no AJAX, no pagination; fine at this
 * panel's own scale (dozens of cards rendered at once server-side).
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsFlashcardCardsFilter = {
    attach: function (context) {
      once('lms-flashcard-cards-filter', '[data-flashcard-cards-filter]', context).forEach(function (input) {
        var panel = input.closest('.lms-flashcard-entity-form__cards-panel');
        if (!panel) {
          return;
        }
        var list = panel.querySelector('.lms-flashcard-entity-form__cards-list');
        var items = list ? Array.prototype.slice.call(list.querySelectorAll('.lms-flashcard-entity-form__card-item')) : [];
        var emptyEl = panel.querySelector('[data-flashcard-cards-filter-empty]');
        if (!items.length) {
          return;
        }

        input.addEventListener('input', function () {
          var query = input.value.trim().toLowerCase();
          var visibleCount = 0;
          items.forEach(function (item) {
            var text = (item.textContent || '').toLowerCase();
            var match = query === '' || text.indexOf(query) !== -1;
            item.hidden = !match;
            if (match) {
              visibleCount++;
            }
          });
          if (emptyEl) {
            emptyEl.hidden = !(query !== '' && visibleCount === 0);
          }
        });
      });
    },
  };
})(Drupal, once);
