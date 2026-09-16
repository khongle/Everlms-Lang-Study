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

  /**
   * "Select" mode + "Delete selected" bulk bar on the same grid — only
   * ever attached when at least one card actually rendered a checkbox
   * (deletable_count > 0 in the template; ListeningController::myPassages()'s
   * `can_delete` is real entity access, not just ownership, so staff can
   * bulk-delete passages they didn't personally import too). Mirrors
   * lms_flashcard's "Reset Mastery" confirm+POST+reload pattern
   * (flashcard-my-decks.js) rather than patching the grid in place —
   * simpler and less error-prone than keeping this filter behavior's own
   * `cards` array, the count text, and the "Select all" state all in sync
   * by hand after a delete.
   */
  Drupal.behaviors.lmsListeningBulkDelete = {
    attach(context) {
      once('lms-listening-bulk-delete', '[data-listening-select-toggle]', context).forEach((toggleBtn) => {
        const page = toggleBtn.closest('.lms-listening-page') || document;
        const grid = page.querySelector('[data-listening-passage-grid]');
        const bar = page.querySelector('[data-listening-bulk-bar]');
        const countEl = page.querySelector('[data-listening-bulk-count]');
        const deleteBtn = page.querySelector('[data-listening-bulk-delete]');
        const selectAllBtn = page.querySelector('[data-listening-select-all]');
        const cancelBtn = page.querySelector('[data-listening-select-cancel]');
        if (!grid || !bar || !deleteBtn) {
          return;
        }

        function checkboxes() {
          // Only ones the current filter (search/level/type/deck) isn't
          // hiding — selecting "everything" should mean everything visible,
          // not phantom rows the student can't even see right now.
          return Array.from(grid.querySelectorAll('[data-listening-select-checkbox]'))
            .filter((box) => !box.closest('[data-listening-passage]').classList.contains('is-filtered-out'));
        }

        function updateCount() {
          const total = checkboxes().length;
          const checked = checkboxes().filter((box) => box.checked).length;
          if (countEl) {
            countEl.textContent = Drupal.formatPlural(checked, '1 selected', '@count selected');
          }
          deleteBtn.disabled = checked === 0;
          if (selectAllBtn) {
            selectAllBtn.textContent = (total > 0 && checked === total) ? Drupal.t('Deselect all') : Drupal.t('Select all');
          }
        }

        function setSelecting(on) {
          grid.classList.toggle('is-selecting', on);
          bar.hidden = !on;
          toggleBtn.classList.toggle('is-active', on);
          if (!on) {
            checkboxes().forEach((box) => { box.checked = false; });
          }
          updateCount();
        }

        toggleBtn.addEventListener('click', () => {
          setSelecting(!grid.classList.contains('is-selecting'));
        });
        if (cancelBtn) {
          cancelBtn.addEventListener('click', () => setSelecting(false));
        }
        if (selectAllBtn) {
          selectAllBtn.addEventListener('click', () => {
            const boxes = checkboxes();
            const allChecked = boxes.length > 0 && boxes.every((box) => box.checked);
            boxes.forEach((box) => { box.checked = !allChecked; });
            updateCount();
          });
        }
        grid.addEventListener('change', (event) => {
          if (event.target.matches('[data-listening-select-checkbox]')) {
            updateCount();
          }
        });

        deleteBtn.addEventListener('click', () => {
          const ids = checkboxes().filter((box) => box.checked).map((box) => parseInt(box.value, 10));
          if (!ids.length) {
            return;
          }
          const confirmed = window.confirm(
            Drupal.formatPlural(
              ids.length,
              'Delete this listening passage? This cannot be undone.',
              'Delete these @count listening passages? This cannot be undone.',
            ),
          );
          if (!confirmed) {
            return;
          }

          deleteBtn.disabled = true;
          fetch('/api/lms/listening/bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ passage_ids: ids }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data && data.status === 'ok') {
                // Simplest correct outcome: the grid, filters, deck-name
                // list, and "Select" button's own deletable_count all come
                // straight from ListeningController::myPassages() again,
                // rather than three different pieces of client-side state
                // (this behavior's, the filter behavior's `cards` array,
                // and the toolbar's deck dropdown) needing to agree on
                // what just changed.
                window.location.reload();
                return;
              }
              deleteBtn.disabled = false;
              window.alert((data && data.error) || Drupal.t('Something went wrong. Please try again.'));
            })
            .catch(() => {
              deleteBtn.disabled = false;
              window.alert(Drupal.t('Something went wrong. Please try again.'));
            });
        });
      });
    },
  };
})(Drupal, once);
