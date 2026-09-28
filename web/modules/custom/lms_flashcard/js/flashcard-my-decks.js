/**
 * @file
 * Client-side filter + view toggle for the "My Flashcards" deck grid
 * (lms-flashcard-my-decks.html.twig). All the fields the filter needs (name,
 * description, language, is_mine, due/new counts) are already rendered as
 * data-* attributes on each card, so this just shows/hides — no request
 * round trip, no new controller route. The grid/list choice is remembered
 * per browser via localStorage so it survives a reload.
 */

(function (Drupal, once) {
  const VIEW_STORAGE_KEY = 'lmsFlashcardDecksView';

  function readStoredView() {
    try {
      return window.localStorage.getItem(VIEW_STORAGE_KEY);
    }
    catch (e) {
      return null;
    }
  }

  function writeStoredView(view) {
    try {
      window.localStorage.setItem(VIEW_STORAGE_KEY, view);
    }
    catch (e) {
      // Private browsing / disabled storage: the toggle still works for the
      // rest of this page view, it just won't be remembered next time.
    }
  }

  Drupal.behaviors.lmsFlashcardMyDecksFilter = {
    attach(context) {
      once('lms-flashcard-filters', '[data-flashcard-filters]', context).forEach((root) => {
        const page = root.closest('.lms-flashcard-page') || document;
        const grid = page.querySelector('[data-flashcard-deck-grid]');
        if (!grid) {
          return;
        }

        const cards = Array.from(grid.querySelectorAll('[data-flashcard-deck]'));
        const emptyMessage = page.querySelector('[data-filter-empty]');
        const searchInput = root.querySelector('[data-filter-search]');
        const languageSelect = root.querySelector('[data-filter-language]');
        const sortSelect = root.querySelector('[data-sort-select]');
        const chips = Array.from(root.querySelectorAll('[data-filter-chip]'));
        const viewButtons = Array.from(root.querySelectorAll('[data-view-toggle]'));
        let activeChip = 'all';

        function setView(view) {
          grid.classList.toggle('lms-flashcard-decks--list', view === 'list');
          viewButtons.forEach((btn) => {
            const isActive = btn.dataset.viewToggle === view;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          });
        }

        const storedView = readStoredView();
        setView((storedView === 'list' || storedView === 'grid') ? storedView : 'grid');

        viewButtons.forEach((btn) => {
          btn.addEventListener('click', () => {
            const view = btn.dataset.viewToggle;
            setView(view);
            writeStoredView(view);
          });
        });

        function matchesChip(card) {
          switch (activeChip) {
            case 'mine':
              return card.dataset.mine === '1';

            case 'course':
              return card.dataset.course === '1';

            case 'due':
              return card.dataset.due === '1';

            case 'new':
              return card.dataset.new === '1';

            default:
              return true;
          }
        }

        function apply() {
          const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
          const language = languageSelect ? languageSelect.value : '';
          let visibleCount = 0;

          cards.forEach((card) => {
            const matchesQuery = !query
              || card.dataset.name.includes(query)
              || card.dataset.desc.includes(query);
            const matchesLanguage = !language || card.dataset.language === language;
            const visible = matchesQuery && matchesLanguage && matchesChip(card);

            card.classList.toggle('is-filtered-out', !visible);
            if (visible) {
              visibleCount += 1;
            }
          });

          if (emptyMessage) {
            emptyMessage.hidden = visibleCount !== 0;
          }
        }

        // Reorders the already-rendered card nodes in place — appendChild()
        // on a node already in the DOM moves it rather than duplicating it,
        // so walking `cards` in the desired order and re-appending each one
        // is enough; no new markup, no round trip. Runs independently of
        // apply()'s show/hide (a hidden card still gets moved to its sorted
        // position, so it's already in place if a filter change reveals it
        // again).
        function sortCards() {
          if (!sortSelect) {
            return;
          }
          const sorted = cards.slice();
          switch (sortSelect.value) {
            case 'created_desc':
              sorted.sort((a, b) => Number(b.dataset.created) - Number(a.dataset.created));
              break;

            case 'created_asc':
              sorted.sort((a, b) => Number(a.dataset.created) - Number(b.dataset.created));
              break;

            default:
              sorted.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
              break;
          }
          sorted.forEach((card) => grid.appendChild(card));
        }

        if (searchInput) {
          searchInput.addEventListener('input', apply);
        }
        if (languageSelect) {
          languageSelect.addEventListener('change', apply);
        }
        if (sortSelect) {
          sortSelect.addEventListener('change', sortCards);
          sortCards();
        }
        chips.forEach((chip) => {
          chip.addEventListener('click', () => {
            chips.forEach((other) => other.classList.remove('is-active'));
            chip.classList.add('is-active');
            activeChip = chip.dataset.filterChip;
            apply();
          });
        });
      });
    },
  };

  /**
   * "Reset Mastery" on a deck card — clears this student's own Study Now
   * mastery percentage back down (see FlashcardApiController::
   * resetMastery()) so they can go through the 3-level flow again to
   * re-earn the 100% that unlocks Spelling/Pronunciation/Reading/
   * Listening. A plain confirm() rather than a full Drupal confirmation
   * page: this only resets a personal progress flag (never the real SM-2
   * schedule — see that endpoint's docblock), so a lighter-weight prompt
   * matches how low the stakes actually are. Reloads the page on success
   * rather than patching the DOM in place, since a successful reset
   * changes both the mastery bar *and* swaps 4 other links between their
   * unlocked/locked markup — simpler and less error-prone to just let the
   * next render (from StudyController::myDecks()) settle all of that.
   */
  Drupal.behaviors.lmsFlashcardMasteryReset = {
    attach(context) {
      once('lms-flashcard-mastery-reset', '[data-flashcard-reset-mastery]', context).forEach((button) => {
        button.addEventListener('click', () => {
          const deckId = button.dataset.flashcardResetMastery;
          const deckName = button.dataset.deckName || '';
          const confirmed = window.confirm(
            Drupal.t('Reset your Study Now progress for "@deck"? You\'ll need to master it again to unlock the other study modes.', { '@deck': deckName }),
          );
          if (!confirmed) {
            return;
          }

          button.disabled = true;
          fetch('/api/lms/flashcard/reset-mastery', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data && data.status === 'ok') {
                window.location.reload();
                return;
              }
              button.disabled = false;
              window.alert((data && data.error) || Drupal.t('Something went wrong. Please try again.'));
            })
            .catch(() => {
              button.disabled = false;
              window.alert(Drupal.t('Something went wrong. Please try again.'));
            });
        });
      });
    },
  };
})(Drupal, once);
