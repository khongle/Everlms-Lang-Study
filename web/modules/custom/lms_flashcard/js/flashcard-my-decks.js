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
        const resultsLabel = page.querySelector('[data-filter-results]');
        const resetFilterButton = root.querySelector('[data-filter-reset]');
        const searchInput = root.querySelector('[data-filter-search]');
        const languageSelect = root.querySelector('[data-filter-language]');
        const sortSelect = root.querySelector('[data-sort-select]');
        const chips = Array.from(root.querySelectorAll('[data-filter-chip]'));
        const viewButtons = Array.from(root.querySelectorAll('[data-view-toggle]'));
        let activeChip = 'all';
        function refreshHeights() {
          grid.dispatchEvent(new Event('lms-flashcard-layout'));
        }

        function setView(view) {
          grid.classList.toggle('lms-flashcard-decks--list', view === 'list');
          viewButtons.forEach((btn) => {
            const isActive = btn.dataset.viewToggle === view;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          });
          refreshHeights();
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

            case 'all':
            default:
              return card.dataset.course !== '1';
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
            if (!visible) {
              card.querySelectorAll('details[open]').forEach((details) => { details.open = false; });
            }
            if (visible) {
              visibleCount += 1;
            }
          });

          if (emptyMessage) {
            emptyMessage.hidden = visibleCount !== 0;
          }
          if (resultsLabel) {
            resultsLabel.textContent = Drupal.t('@shown of @total decks', { '@shown': visibleCount, '@total': cards.length });
          }
          refreshHeights();
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
          refreshHeights();
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
        function selectChip(value) {
          activeChip = value;
          chips.forEach((chip) => {
            const active = chip.dataset.filterChip === value;
            chip.classList.toggle('is-active', active);
            chip.setAttribute('aria-pressed', active ? 'true' : 'false');
          });
        }
        chips.forEach((chip) => {
          chip.addEventListener('click', () => {
            selectChip(chip.dataset.filterChip);
            apply();
          });
        });
        if (resetFilterButton) {
          resetFilterButton.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (languageSelect) languageSelect.value = '';
            selectChip('all');
            apply();
          });
        }
        selectChip('all');
        apply();
      });
    },
  };

  /**
   * Match the title, counters, study and footer sections within each grid row.
   * Re-measure natural heights so filtering, resizing and disclosures never
   * retain dimensions from a previous layout; list/mobile cards stay natural.
   */
  Drupal.behaviors.lmsFlashcardMatchHeight = {
    attach(context) {
      once('lms-flashcard-match-height', '[data-flashcard-deck-grid]', context).forEach((grid) => {
        const sections = [
          '.lms-flashcard-deck-card__top',
          '.lms-flashcard-deck-card__stats',
          '.lms-flashcard-deck-card__actions',
          '.lms-flashcard-deck-card__manage',
        ];
        let frame = null;
        function matchHeights() {
          frame = null;
          const cards = Array.from(grid.querySelectorAll('[data-flashcard-deck]'));
          cards.forEach((card) => sections.forEach((selector) => {
            card.querySelector(selector).style.minHeight = '';
          }));
          if (grid.classList.contains('lms-flashcard-decks--list')) return;
          const rows = [];
          cards.filter((card) => card.getClientRects().length).forEach((card) => {
            const top = card.getBoundingClientRect().top;
            let row = rows.find((item) => Math.abs(item.top - top) < 1);
            if (!row) {
              row = { top, cards: [] };
              rows.push(row);
            }
            row.cards.push(card);
          });
          rows.filter((row) => row.cards.length > 1).forEach((row) => {
            sections.forEach((selector) => {
              const elements = row.cards.map((card) => card.querySelector(selector));
              const height = Math.ceil(Math.max(...elements.map((element) => element.getBoundingClientRect().height)));
              elements.forEach((element) => { element.style.minHeight = height + 'px'; });
            });
          });
        }
        function schedule() {
          if (frame === null) frame = window.requestAnimationFrame(matchHeights);
        }
        grid.addEventListener('lms-flashcard-layout', schedule);
        grid.addEventListener('toggle', schedule, true);
        if (window.ResizeObserver) {
          let width = 0;
          const observer = new ResizeObserver((entries) => {
            const currentWidth = entries[0].contentRect.width;
            if (Math.abs(currentWidth - width) > 0.5) {
              width = currentWidth;
              schedule();
            }
          });
          observer.observe(grid);
        }
        // A breakpoint may change column count without changing grid width.
        window.addEventListener('resize', schedule);
        if (document.fonts) document.fonts.ready.then(schedule);
        schedule();
      });
    },
  };

  /**
   * Native secondary action disclosures retain keyboard activation and focus.
   */
  Drupal.behaviors.lmsFlashcardDeckMenus = {
    attach(context) {
      once('lms-flashcard-deck-menus', '.lms-flashcard-page--library', context).forEach((page) => {
        const menus = Array.from(page.querySelectorAll('.lms-flashcard-deck-card__more'));
        menus.forEach((menu) => {
          menu.addEventListener('toggle', () => {
            menu.classList.remove('lms-flashcard-deck-card__more--above');
            menu.style.removeProperty('--flashcard-menu-height');
            if (menu.open) {
              menus.forEach((other) => { if (other !== menu) other.open = false; });
              const panel = menu.querySelector('.lms-flashcard-deck-card__more-menu');
              const anchor = menu.querySelector('summary').getBoundingClientRect();
              const header = document.querySelector('.lms-navbar');
              const headerBottom = header ? Math.max(0, header.getBoundingClientRect().bottom) : 0;
              const spaceBelow = Math.max(40, window.innerHeight - anchor.bottom - 18);
              const spaceAbove = Math.max(40, anchor.top - headerBottom - 18);
              const above = panel.scrollHeight > spaceBelow && spaceAbove > spaceBelow;
              menu.classList.toggle('lms-flashcard-deck-card__more--above', above);
              menu.style.setProperty('--flashcard-menu-height', Math.floor(above ? spaceAbove : spaceBelow) + 'px');
            }
          });
        });
        document.addEventListener('click', (event) => {
          menus.forEach((menu) => {
            if (menu.open && !menu.contains(event.target)) menu.open = false;
          });
        });
        document.addEventListener('keydown', (event) => {
          if (event.key !== 'Escape') return;
          const openMenu = menus.find((menu) => menu.open);
          if (openMenu) {
            openMenu.open = false;
            openMenu.querySelector('summary').focus();
          }
        });
      });
    },
  };

  /**
   * Reset the learner's current mastery without changing earned practice access.
   * Reload after success to recompute the progress bar and personal counts.
   */
  Drupal.behaviors.lmsFlashcardMasteryReset = {
    attach(context) {
      once('lms-flashcard-mastery-reset', '[data-flashcard-reset-mastery]', context).forEach((button) => {
        button.addEventListener('click', () => {
          const deckId = button.dataset.flashcardResetMastery;
          const deckName = button.dataset.deckName || '';
          const confirmed = window.confirm(
            Drupal.t('Reset your Study Now progress for "@deck"? Practice modes you have already unlocked will stay available.', { '@deck': deckName }),
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
