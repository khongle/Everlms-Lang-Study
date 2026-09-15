/**
 * @file
 * Client-side filter for the "Listening" passage grid
 * (lms-listening-my-passages.html.twig): search text + level + type + deck,
 * all as <select> dropdowns. All the fields the filter needs (title, deck,
 * level, format) are already rendered as data-* attributes on each card, so
 * this just shows/hides — no request round trip, no new controller route.
 *
 * Mirrors lms_reading's reading-my-passages.js filter behavior (kept as an
 * independent copy, not a shared import, per this module's independence
 * from lms_reading's own code).
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsListeningMyPassagesFilter = {
    attach(context) {
      once('lms-listening-filters', '[data-listening-filters]', context).forEach((root) => {
        const page = root.closest('.lms-listening-page') || document;
        const grid = page.querySelector('[data-listening-passage-grid]');
        if (!grid) {
          return;
        }

        const cards = Array.from(grid.querySelectorAll('[data-listening-passage]'));
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
