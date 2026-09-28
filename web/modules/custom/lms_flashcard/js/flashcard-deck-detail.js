/**
 * @file
 * Interactive filters, speak audio, and card publish/unpublish toggle for Deck Detail page.
 */

(function (Drupal, once) {
  'use strict';

  /**
   * Filter & search cards in the deck detail view.
   */
  Drupal.behaviors.lmsFlashcardDeckDetailFilter = {
    attach(context) {
      once('lms-deck-detail-filters', '[data-card-filters]', context).forEach((toolbar) => {
        const page = toolbar.closest('.lms-flashcard-deck-detail-page') || document;
        const cardList = page.querySelector('[data-card-list]');
        if (!cardList) {
          return;
        }

        const cards = Array.from(cardList.querySelectorAll('[data-card-item]'));
        const searchInput = toolbar.querySelector('[data-filter-search]');
        const chips = Array.from(toolbar.querySelectorAll('[data-filter-status]'));
        const posSelect = toolbar.querySelector('[data-filter-pos]');
        const emptyEl = page.querySelector('[data-filter-empty]');

        let currentStatus = 'all';

        function applyFilter() {
          const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
          const pos = posSelect ? posSelect.value.toLowerCase() : '';
          let visibleCount = 0;

          cards.forEach((card) => {
            const term = card.dataset.term || '';
            const trans = card.dataset.translation || '';
            const def = card.dataset.definition || '';
            const cardPos = (card.dataset.pos || '').toLowerCase();
            const cardStatus = card.dataset.status || 'published';

            const matchesQuery = !query
              || term.includes(query)
              || trans.includes(query)
              || def.includes(query);

            const matchesStatus = currentStatus === 'all' || cardStatus === currentStatus;
            const matchesPos = !pos || cardPos === pos || (pos === 'phrase' && cardPos.includes('phrase'));

            const isVisible = matchesQuery && matchesStatus && matchesPos;
            card.classList.toggle('is-filtered-out', !isVisible);

            if (isVisible) {
              visibleCount++;
            }
          });

          if (emptyEl) {
            emptyEl.hidden = visibleCount > 0;
          }
        }

        if (searchInput) {
          searchInput.addEventListener('input', applyFilter);
        }

        if (posSelect) {
          posSelect.addEventListener('change', applyFilter);
        }

        chips.forEach((chip) => {
          chip.addEventListener('click', () => {
            chips.forEach((c) => c.classList.remove('is-active'));
            chip.classList.add('is-active');
            currentStatus = chip.dataset.filterStatus || 'all';
            applyFilter();
          });
        });

        // Expose applyFilter globally for toggle updates
        window._lmsDeckDetailApplyFilter = applyFilter;
      });
    },
  };

  /**
   * AJAX toggle publish/unpublish a card.
   */
  Drupal.behaviors.lmsFlashcardDeckDetailToggle = {
    attach(context) {
      once('lms-card-toggle', '[data-toggle-card]', context).forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const cardId = parseInt(btn.dataset.toggleCard, 10);
          if (!cardId || btn.disabled) {
            return;
          }

          const cardRow = btn.closest('[data-card-item]');
          if (!cardRow) {
            return;
          }

          const originalHtml = btn.innerHTML;
          btn.disabled = true;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

          fetch('/api/lms/flashcard/card/' + cardId + '/toggle-skip', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
          })
            .then((res) => res.json())
            .then((data) => {
              btn.disabled = false;
              if (data && data.status === 'ok') {
                const isSkipped = !!data.skipped;
                cardRow.dataset.status = isSkipped ? 'unpublished' : 'published';
                cardRow.classList.toggle('is-unpublished', isSkipped);

                // Update status badge
                const badge = cardRow.querySelector('[data-status-badge]');
                if (badge) {
                  badge.className = 'lms-flashcard-detail-item__status-badge ' + (isSkipped ? 'is-unpublished' : 'is-published');
                  badge.innerHTML = '<i class="fas ' + (isSkipped ? 'fa-forward' : 'fa-check-circle') + '"></i> ' +
                    '<span class="lms-flashcard-detail-item__status-text">' +
                    (isSkipped ? Drupal.t('Skipped') : Drupal.t('Studying')) +
                    '</span>';
                }

                // Update button
                btn.className = 'lms-flashcard-detail-item__toggle-btn ' + (isSkipped ? 'is-unpublished' : 'is-published');
                btn.title = isSkipped
                  ? Drupal.t('Bring this card back into your study sessions.')
                  : Drupal.t('Already know this word? Skip it so it stops showing up in study sessions.');
                btn.innerHTML = '<i class="fas ' + (isSkipped ? 'fa-undo' : 'fa-forward') + '"></i> ' +
                  '<span>' + (isSkipped ? Drupal.t('Study Again') : Drupal.t('Skip')) + '</span>';

                // Recount stats on page
                updateCounts();

                // Re-apply filter if in a specific status tab
                if (window._lmsDeckDetailApplyFilter) {
                  window._lmsDeckDetailApplyFilter();
                }
              }
              else {
                btn.innerHTML = originalHtml;
                window.alert((data && data.error) || Drupal.t('Could not update card status.'));
              }
            })
            .catch(() => {
              btn.disabled = false;
              btn.innerHTML = originalHtml;
              window.alert(Drupal.t('Network error. Please try again.'));
            });
        });
      });

      function updateCounts() {
        const page = document.querySelector('.lms-flashcard-deck-detail-page');
        if (!page) {
          return;
        }

        const cards = Array.from(page.querySelectorAll('[data-card-item]'));
        const total = cards.length;
        const published = cards.filter((c) => c.dataset.status === 'published').length;
        const unpublished = total - published;

        const statTotal = page.querySelector('[data-stat-total]');
        const statPublished = page.querySelector('[data-stat-published]');
        const statUnpublished = page.querySelector('[data-stat-unpublished]');

        const countAll = page.querySelector('[data-count-all]');
        const countPublished = page.querySelector('[data-count-published]');
        const countUnpublished = page.querySelector('[data-count-unpublished]');

        if (statTotal) statTotal.textContent = total;
        if (statPublished) statPublished.textContent = published;
        if (statUnpublished) statUnpublished.textContent = unpublished;

        if (countAll) countAll.textContent = total;
        if (countPublished) countPublished.textContent = published;
        if (countUnpublished) countUnpublished.textContent = unpublished;
      }
    },
  };

  /**
   * Web Speech API text-to-speech for term pronunciation.
   */
  Drupal.behaviors.lmsFlashcardDeckDetailSpeak = {
    attach(context) {
      if (!('speechSynthesis' in window)) {
        return;
      }

      once('lms-detail-speak', '[data-speak-term]', context).forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const term = btn.dataset.speakTerm;
          if (!term) {
            return;
          }

          window.speechSynthesis.cancel();
          const utterance = new SpeechSynthesisUtterance(term);
          utterance.lang = 'en-US';
          utterance.rate = 0.9;
          window.speechSynthesis.speak(utterance);
        });
      });
    },
  };

})(Drupal, once);
