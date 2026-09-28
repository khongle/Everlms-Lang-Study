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

          document.dispatchEvent(new CustomEvent('lms-listening-filter-changed', {
            detail: { visibleCount: visibleCount }
          }));
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

  /**
   * Action buttons on the deck-specific Listening page:
   * "Passive Listening" (hands-free looping) and "Practice All" (active comprehension).
   * Gathers visible passage IDs and starts the session via /api/lms/listening/start.
   */
  Drupal.behaviors.lmsListeningDeckActions = {
    attach(context) {
      once('lms-listening-deck-actions', '[data-start-passive], [data-start-practice]', context).forEach((btn) => {
        btn.addEventListener('click', () => {
          const isPassive = btn.hasAttribute('data-start-passive');
          const deckId = parseInt(btn.getAttribute(isPassive ? 'data-start-passive' : 'data-start-practice'), 10);
          if (!deckId) {
            return;
          }

          const page = btn.closest('.lms-listening-page') || document;
          const grid = page.querySelector('[data-listening-passage-grid]');
          let passageIds = [];
          if (grid) {
            passageIds = Array.from(grid.querySelectorAll('[data-listening-passage]'))
              .map((c) => parseInt(c.dataset.id, 10))
              .filter((id) => !isNaN(id) && id > 0);
          }

          btn.disabled = true;
          const originalText = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Loading...');

          const requestBody = {
            deck_id: deckId,
            mode: isPassive ? 'passive' : 'active',
          };
          if (passageIds.length > 0) {
            requestBody.passage_ids = passageIds;
          } else {
            requestBody.levels = ['easy', 'normal', 'challenge'];
            requestBody.per_level = 20;
          }

          fetch('/api/lms/listening/start', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestBody),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
              }
              btn.disabled = false;
              btn.innerHTML = originalText;
              window.alert((data && data.error) || Drupal.t('Something went wrong. Please try again.'));
            })
            .catch(() => {
              btn.disabled = false;
              btn.innerHTML = originalText;
              window.alert(Drupal.t('Something went wrong. Please try again.'));
            });
        });
      });
    },
  };

  /**
   * Split layout behavior: clicking an item in the sidebar selects it,
   * updates the detail panel on the right, and allows seamless switching.
   */
  Drupal.behaviors.lmsListeningSidebarDetail = {
    attach(context) {
      once('lms-listening-split', '[data-listening-split]', context).forEach((split) => {
        const items = Array.from(split.querySelectorAll('[data-listening-passage]'));
        const emptyEl = split.querySelector('[data-listening-content-empty]');
        const detailEl = split.querySelector('[data-listening-content-detail]');
        if (!items.length || !detailEl) {
          return;
        }

        const levelEl = detailEl.querySelector('[data-detail-level]');
        const formatEl = detailEl.querySelector('[data-detail-format]');
        const audioIndicatorEl = detailEl.querySelector('[data-detail-audio]');
        const titleEl = detailEl.querySelector('[data-detail-title]');
        const metaEl = detailEl.querySelector('[data-detail-meta]');
        const previewEl = detailEl.querySelector('[data-detail-preview]');
        const snippetEl = detailEl.querySelector('[data-detail-snippet]');
        const audioBoxEl = detailEl.querySelector('[data-detail-audio-box]');
        const audioPlayerEl = detailEl.querySelector('[data-detail-audio-player]');
        const ctaEl = detailEl.querySelector('[data-detail-url]');

        let currentActiveItem = null;

        function selectPassage(item) {
          if (!item) {
            if (emptyEl) {
              emptyEl.hidden = false;
            }
            detailEl.hidden = true;
            if (audioPlayerEl) {
              audioPlayerEl.pause();
              audioPlayerEl.removeAttribute('src');
            }
            return;
          }

          items.forEach((i) => i.classList.remove('is-active'));
          item.classList.add('is-active');
          currentActiveItem = item;

          const title = item.dataset.passageTitle || '';
          const deck = item.dataset.passageDeck || '';
          const level = item.dataset.passageLevel || '';
          const format = item.dataset.passageFormat || '';
          const words = item.dataset.passageWords || '0';
          const questions = item.dataset.passageQuestions || '0';
          const hasAudio = item.dataset.hasAudio === '1';
          const audioUrl = item.dataset.audioUrl || '';
          const snippet = item.dataset.snippet || '';
          const url = item.dataset.url || '#';

          if (levelEl) {
            levelEl.textContent = level.charAt(0).toUpperCase() + level.slice(1);
            levelEl.setAttribute('data-level', level);
          }

          if (formatEl) {
            let iconClass = 'fa-comment-dots';
            if (format === 'dialogue') {
              iconClass = 'fa-comments';
            } else if (format === 'narrative') {
              iconClass = 'fa-align-left';
            }
            formatEl.innerHTML = '<i class="fas ' + iconClass + '"></i> ' + (format.charAt(0).toUpperCase() + format.slice(1));
          }

          if (audioIndicatorEl) {
            audioIndicatorEl.hidden = !hasAudio;
          }

          if (titleEl) {
            titleEl.textContent = title;
          }

          if (metaEl) {
            const parts = [];
            if (deck) {
              parts.push(Drupal.t('From deck: @deck', { '@deck': deck }));
            }
            parts.push(Drupal.t('@count words', { '@count': words }));
            parts.push(Drupal.t('@count questions', { '@count': questions }));
            metaEl.textContent = parts.join(' · ');
          }

          if (previewEl && snippetEl) {
            if (snippet.trim()) {
              snippetEl.textContent = snippet;
              previewEl.hidden = false;
            } else {
              previewEl.hidden = true;
            }
          }

          if (audioBoxEl && audioPlayerEl) {
            audioPlayerEl.pause();
            if (audioUrl) {
              audioPlayerEl.src = audioUrl;
              audioBoxEl.hidden = false;
            } else {
              audioPlayerEl.removeAttribute('src');
              audioBoxEl.hidden = true;
            }
          }

          if (ctaEl) {
            ctaEl.href = url;
          }

          if (emptyEl) {
            emptyEl.hidden = true;
          }
          detailEl.hidden = false;
        }

        // Attach click listeners to sidebar items
        items.forEach((item) => {
          item.addEventListener('click', (e) => {
            // Don't trigger passage select if clicking checkbox
            if (e.target && e.target.closest('[data-listening-select-checkbox]')) {
              return;
            }
            selectPassage(item);
          });
        });

        // Auto select first visible item
        const firstVisible = items.find((i) => !i.classList.contains('is-filtered-out'));
        if (firstVisible) {
          selectPassage(firstVisible);
        }

        // Listen for filter changes to update active selection
        document.addEventListener('lms-listening-filter-changed', () => {
          if (currentActiveItem && currentActiveItem.classList.contains('is-filtered-out')) {
            const nextVisible = items.find((i) => !i.classList.contains('is-filtered-out'));
            selectPassage(nextVisible || null);
          } else if (!currentActiveItem) {
            const nextVisible = items.find((i) => !i.classList.contains('is-filtered-out'));
            if (nextVisible) {
              selectPassage(nextVisible);
            }
          }
        });

        // Keyboard navigation (ArrowUp / ArrowDown) in sidebar list
        split.addEventListener('keydown', (e) => {
          if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') {
            return;
          }
          const visibleItems = items.filter((i) => !i.classList.contains('is-filtered-out'));
          if (!visibleItems.length) {
            return;
          }
          const currentIndex = visibleItems.indexOf(currentActiveItem);
          let targetIndex = 0;
          if (e.key === 'ArrowDown') {
            targetIndex = currentIndex < visibleItems.length - 1 ? currentIndex + 1 : 0;
          } else if (e.key === 'ArrowUp') {
            targetIndex = currentIndex > 0 ? currentIndex - 1 : visibleItems.length - 1;
          }
          e.preventDefault();
          selectPassage(visibleItems[targetIndex]);
          visibleItems[targetIndex].focus();
        });
      });
    },
  };
})(Drupal, once);

