/**
 * @file
 * Client-side filter for the "Reading" passage grid
 * (lms-reading-my-passages.html.twig): search text + level + type + deck,
 * all as <select> dropdowns (level and type used to be chip buttons —
 * switched to selects to match the deck filter and keep the toolbar to
 * one compact row). All the fields the filter needs (title, deck, level,
 * format) are already rendered as data-* attributes on each card, so this
 * just shows/hides — no request round trip, no new controller route.
 *
 * Mirrors lms_flashcard's flashcard-my-decks.js filter behavior (kept as
 * an independent copy, not a shared import, per this module's
 * independence from lms_flashcard's own code).
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsReadingMyPassagesFilter = {
    attach(context) {
      once('lms-reading-filters', '[data-reading-filters]', context).forEach((root) => {
        const page = root.closest('.lms-reading-page') || document;
        const grid = page.querySelector('[data-reading-passage-grid]');
        if (!grid) {
          return;
        }

        const cards = Array.from(grid.querySelectorAll('[data-reading-passage]'));
        const emptyMessage = page.querySelector('[data-filter-empty]');
        const searchInput = root.querySelector('[data-filter-search]');
        const levelSelect = root.querySelector('[data-filter-level]');
        const formatSelect = root.querySelector('[data-filter-format]');
        const deckSelect = root.querySelector('[data-filter-deck]');

        function apply() {
          const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
          const level = levelSelect ? levelSelect.value : '';
          const format = formatSelect ? formatSelect.value : '';
          const deck = deckSelect ? deckSelect.value.toLowerCase() : '';
          let visibleCount = 0;

          cards.forEach((card) => {
            const matchesQuery = !query
              || card.dataset.title.includes(query)
              || card.dataset.deck.includes(query);
            const matchesLevel = !level || card.dataset.level === level;
            const matchesFormat = !format || card.dataset.format === format;
            const matchesDeck = !deck || card.dataset.deck === deck;
            const visible = matchesQuery && matchesLevel && matchesFormat && matchesDeck;

            card.classList.toggle('is-filtered-out', !visible);
            if (visible) {
              visibleCount += 1;
            }
          });

          if (emptyMessage) {
            emptyMessage.hidden = visibleCount !== 0;
          }
        }

        if (searchInput) {
          searchInput.addEventListener('input', apply);
        }
        if (levelSelect) {
          levelSelect.addEventListener('change', apply);
        }
        if (formatSelect) {
          formatSelect.addEventListener('change', apply);
        }
        if (deckSelect) {
          deckSelect.addEventListener('change', apply);
        }
      });
    },
  };
})(Drupal, once);
