/**
 * @file
 * "Use this example" / "Copy" buttons for the JSON import tip block.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsFlashcardImportExample = {
    attach: function (context) {
      once('lms-flashcard-import-example', '.lms-flashcard-import-form', context).forEach(function (form) {
        var exampleCode = form.querySelector('#lms-flashcard-example-json');
        var textarea = form.querySelector('#edit-json-text');
        var newModeRadio = form.querySelector('#edit-mode-new');
        var copyBtn = form.querySelector('[data-flashcard-copy-example]');
        var useBtn = form.querySelector('[data-flashcard-use-example]');

        if (!exampleCode) {
          return;
        }

        function flashLabel(button, message) {
          var original = button.textContent;
          button.textContent = message;
          window.setTimeout(function () {
            button.textContent = original;
          }, 1500);
        }

        if (copyBtn) {
          copyBtn.addEventListener('click', function () {
            var text = exampleCode.textContent;
            if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(text).then(function () {
                flashLabel(copyBtn, Drupal.t('Copied!'));
              });
            }
          });
        }

        if (useBtn && textarea) {
          useBtn.addEventListener('click', function () {
            textarea.value = exampleCode.textContent;
            if (newModeRadio) {
              newModeRadio.checked = true;
              newModeRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }
            textarea.focus();
            flashLabel(useBtn, Drupal.t('Added!'));
          });
        }
      });
    },
  };

  /**
   * Two conveniences for the "Target deck" select, most useful once a user
   * has more than a handful of decks:
   *
   * 1. A type-to-filter search box in front of it (a plain <select> has no
   *    built-in search) — see the search input's 'input' listener below.
   * 2. Auto-detecting the right deck (and the right "Create new"/"Append"
   *    mode) straight from the pasted/uploaded JSON's own top-level
   *    "deck_id" or "deck" key, so re-importing an export from
   *    DeckExportController — which always carries its source deck_id —
   *    doesn't also require manually finding that same deck again in the
   *    dropdown. Falls back to leaving the fields alone (still manually
   *    selectable) when detection doesn't apply, e.g. deck_id refers to a
   *    deck outside this list (not owned by the user, or deleted).
   */
  Drupal.behaviors.lmsFlashcardImportDeckPicker = {
    attach: function (context) {
      once('lms-flashcard-import-deck-picker', '.lms-flashcard-import-form', context).forEach(function (form) {
        var deckSelect = form.querySelector('#edit-deck-id');
        var newModeRadio = form.querySelector('#edit-mode-new');
        var existingModeRadio = form.querySelector('#edit-mode-existing');
        var textarea = form.querySelector('#edit-json-text');
        var fileInput = form.querySelector('input[type="file"]');

        if (!deckSelect || !existingModeRadio) {
          return;
        }

        // The whole "Target deck" field (label, select, its description) —
        // toggled hidden altogether once the pasted/uploaded JSON's own
        // deck_id has matched one of its options, since manually re-picking
        // the very deck the JSON already names is a pointless extra step
        // (see autoDetect() below). `.form-item-deck-id` is Drupal's own
        // wrapper class for this field, not one added here.
        var fieldWrapper = deckSelect.closest('.form-item-deck-id') || deckSelect.parentNode;

        // --- 1. Type-to-filter the deck list -------------------------------
        var search = document.createElement('input');
        search.type = 'search';
        search.className = 'lms-flashcard-import__deck-search';
        search.placeholder = Drupal.t('Type to filter decks…');
        search.setAttribute('aria-label', Drupal.t('Filter the target deck list'));
        deckSelect.parentNode.insertBefore(search, deckSelect);

        // Shown instead of silently leaving the dropdown looking empty when
        // a search matches nothing — e.g. a typo, or matching against a
        // word that isn't literally contiguous in the deck name ("hel"
        // isn't a substring of "health", even though a person typing it
        // might expect it to match).
        var noMatches = document.createElement('p');
        noMatches.className = 'lms-flashcard-import__deck-no-match';
        noMatches.hidden = true;
        noMatches.textContent = Drupal.t('No deck names match — try a shorter search, or clear it to see the full list.');
        deckSelect.parentNode.insertBefore(noMatches, deckSelect);

        var allOptions = Array.prototype.slice.call(deckSelect.options);
        search.addEventListener('input', function () {
          var query = search.value.trim().toLowerCase();
          var visibleCount = 0;
          allOptions.forEach(function (option) {
            // The "- Select a deck -" placeholder (empty value) always stays
            // visible, same as an unfiltered dropdown would show it, and
            // never counts toward "are there any real matches".
            var isPlaceholder = option.value === '';
            option.hidden = query !== '' && !isPlaceholder && option.textContent.toLowerCase().indexOf(query) === -1;
            if (!isPlaceholder && !option.hidden) {
              visibleCount++;
            }
          });
          noMatches.hidden = query === '' || visibleCount > 0;
        });

        // --- 2. Auto-detect deck_id/deck from the JSON itself --------------
        // Sits OUTSIDE fieldWrapper (a sibling, inserted right after it) so
        // it stays visible even while fieldWrapper itself is hidden — it's
        // what a matched import shows *instead of* the manual picker.
        var matchBanner = document.createElement('div');
        matchBanner.className = 'lms-flashcard-import__deck-matched';
        matchBanner.hidden = true;
        var matchText = document.createElement('span');
        matchBanner.appendChild(matchText);
        var changeBtn = document.createElement('button');
        changeBtn.type = 'button';
        changeBtn.className = 'lms-flashcard-import__deck-change-btn';
        changeBtn.textContent = Drupal.t('Change deck');
        matchBanner.appendChild(changeBtn);
        fieldWrapper.parentNode.insertBefore(matchBanner, fieldWrapper);

        // A warning (deck_id in the JSON doesn't resolve to anything in
        // *this* list) is a different situation from a match — the manual
        // picker stays visible here since the student still has to choose
        // by hand, so this lives inside fieldWrapper instead, right above
        // the search box.
        var noMatchWarning = document.createElement('p');
        noMatchWarning.className = 'lms-flashcard-import__deck-autodetect';
        noMatchWarning.hidden = true;
        deckSelect.parentNode.insertBefore(noMatchWarning, search);

        // Set once a user explicitly asks to override a match (via
        // "Change deck") — keeps re-typing in the JSON textarea afterwards
        // from immediately re-hiding the picker they just asked to see.
        var userOverrode = false;

        function setMode(radio) {
          radio.checked = true;
          radio.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function setMatched(matched, deckName) {
          // A CSS class + `!important` rule (see flashcard-import.css)
          // rather than the `hidden` attribute or a direct style.display
          // write — Drupal core's states.js (driven by the mode radios,
          // see the field's #states in ImportForm) uses jQuery show()/hide()
          // on this same element, which sets a plain (non-!important)
          // inline display style; only an !important stylesheet rule is
          // guaranteed to win over that regardless of which one ran last.
          fieldWrapper.classList.toggle('lms-flashcard-import__deck-field--matched', matched);
          matchBanner.hidden = !matched;
          if (matched) {
            matchText.textContent = Drupal.t('Matched from JSON — importing into "@deck".', { '@deck': deckName });
          }
        }

        changeBtn.addEventListener('click', function () {
          userOverrode = true;
          setMatched(false);
        });

        function autoDetect(jsonText) {
          var data;
          try {
            data = JSON.parse(jsonText);
          }
          catch (e) {
            return;
          }
          if (!data || typeof data !== 'object') {
            return;
          }

          if (data.deck_id !== undefined && data.deck_id !== null) {
            var deckIdStr = String(data.deck_id);
            var option = allOptions.filter(function (opt) { return opt.value === deckIdStr; })[0];
            if (option) {
              setMode(existingModeRadio);
              deckSelect.value = deckIdStr;
              deckSelect.dispatchEvent(new Event('change', { bubbles: true }));
              search.value = '';
              allOptions.forEach(function (opt) { opt.hidden = false; });
              noMatchWarning.hidden = true;
              if (!userOverrode) {
                setMatched(true, option.textContent);
              }
            }
            else {
              setMatched(false);
              noMatchWarning.textContent = Drupal.t('This JSON targets deck_id @id, which isn\'t in your list above (not yours, or no longer exists) — select a deck manually if you still want to import it.', { '@id': data.deck_id });
              noMatchWarning.hidden = false;
            }
          }
          else if (data.deck && typeof data.deck === 'object' && newModeRadio) {
            setMode(newModeRadio);
            setMatched(false);
            noMatchWarning.hidden = true;
          }
        }

        if (textarea) {
          // Debounced: JSON.parse on every keystroke of a long paste is
          // wasted work, and mid-paste the text is invalid JSON anyway
          // until it's all there.
          var debounceTimer = null;
          textarea.addEventListener('input', function () {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(function () {
              autoDetect(textarea.value);
            }, 400);
          });

          // Also run once immediately on attach, not just on a future
          // keystroke/paste — a reload or back/forward navigation can leave
          // the textarea (and the mode radio) already filled in by the
          // browser's own form-state restore, which fires no 'input' event
          // for us to react to. Without this, the matched-deck field would
          // stay stuck showing the full manual picker until the student
          // edits the JSON again.
          if (textarea.value) {
            autoDetect(textarea.value);
          }
        }

        if (fileInput) {
          fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file || !window.FileReader) {
              return;
            }
            // Purely a local peek at the file's content to auto-detect the
            // deck — independent of, and not a substitute for, the actual
            // upload Drupal's managed_file widget performs via its own
            // "Upload" button.
            var reader = new FileReader();
            reader.onload = function () {
              autoDetect(String(reader.result || ''));
            };
            reader.readAsText(file);
          });
        }
      });
    },
  };

})(Drupal, once);
