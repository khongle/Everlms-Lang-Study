/**
 * @file
 * Progressively enhances the "Listening" link on a Flashcard deck card
 * (lms-flashcard-my-decks.html.twig) into an in-page popup instead of a
 * full-page navigation to lms_listening.start (ListeningStartForm), which
 * stays the plain href and therefore the fallback if this script doesn't
 * run (JS disabled, or an error). Only lms_listening's own code reads that
 * markup (by class name / data attribute); the Flashcard template itself
 * stays unaware of this — same decoupling as the rest of the "additive
 * link" pattern there.
 *
 * The popup posts straight to /api/lms/listening/start (see
 * ListeningApiController::start()) rather than driving the page-based
 * ListeningStartForm through Drupal's AJAX form framework, since the two
 * do the exact same one-shot "pick levels, get a redirect" job and a
 * plain JSON endpoint is much less to keep in sync.
 */

(function (Drupal, once) {
  'use strict';

  var LEVELS = [
    ['easy', function () { return Drupal.t('Easy'); }],
    ['normal', function () { return Drupal.t('Normal'); }],
    ['challenge', function () { return Drupal.t('Challenge'); }],
  ];

  var DEFAULT_PER_LEVEL = 3;

  function formatLabel(format) {
    switch (format) {
      case 'dialogue':
        return Drupal.t('Dialogue');

      case 'narrative':
        return Drupal.t('Narrative');

      case 'sentence':
        return Drupal.t('Sentence');

      default:
        return format ? format.charAt(0).toUpperCase() + format.slice(1) : Drupal.t('Sentence');
    }
  }

  /**
   * Builds the (initially hidden) overlay + card once per page, reused for
   * every deck card's "Listening" link. Level rows are rebuilt per-open
   * (see updateLevelCounts()) since the available count differs per deck.
   */
  function buildModal() {
    var overlay = document.createElement('div');
    overlay.className = 'lms-listening-start-modal-overlay';
    overlay.hidden = true;

    var modal = document.createElement('div');
    modal.className = 'lms-listening-start-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'lms-listening-start-modal-title');

    var title = document.createElement('h2');
    title.id = 'lms-listening-start-modal-title';
    title.textContent = Drupal.t('Choose levels to practice');
    modal.appendChild(title);

    // Active (today's checkboxes/count UI below) vs. Passive (no picker at
    // all — fixed 1-Easy/1-Normal/1-Challenge session, see
    // PASSIVE_LISTENING_PLAN.md). Active stays selected by default so
    // nothing changes for a student who never notices this toggle exists.
    var modeWrap = document.createElement('div');
    modeWrap.className = 'lms-listening-start-modal__mode';
    var modeActiveLabel = document.createElement('label');
    var modeActiveInput = document.createElement('input');
    modeActiveInput.type = 'radio';
    modeActiveInput.name = 'lms-listening-mode';
    modeActiveInput.value = 'active';
    modeActiveInput.checked = true;
    modeActiveLabel.appendChild(modeActiveInput);
    modeActiveLabel.appendChild(document.createTextNode(' ' + Drupal.t('Active (answer questions)')));
    var modePassiveLabel = document.createElement('label');
    var modePassiveInput = document.createElement('input');
    modePassiveInput.type = 'radio';
    modePassiveInput.name = 'lms-listening-mode';
    modePassiveInput.value = 'passive';
    modePassiveLabel.appendChild(modePassiveInput);
    modePassiveLabel.appendChild(document.createTextNode(' ' + Drupal.t('Passive (just listen)')));
    modeWrap.appendChild(modeActiveLabel);
    modeWrap.appendChild(modePassiveLabel);
    modal.appendChild(modeWrap);

    var intro = document.createElement('p');
    intro.className = 'lms-listening-start-modal__intro';
    modal.appendChild(intro);

    var passiveIntro = document.createElement('p');
    passiveIntro.className = 'lms-listening-start-modal__intro lms-listening-start-modal__intro--passive';
    passiveIntro.textContent = Drupal.t('Passages play back to back, Easy → Normal → Challenge, then loop back to the start and repeat until you press Stop — pick the levels and how many per level below (random within each), or choose exact passages further down. Just listen; no questions, nothing to answer.');
    passiveIntro.hidden = true;
    modal.appendChild(passiveIntro);

    var levelsWrap = document.createElement('div');
    levelsWrap.className = 'lms-listening-start-modal__levels';
    modal.appendChild(levelsWrap);

    var perLevelWrap = document.createElement('label');
    perLevelWrap.className = 'lms-listening-start-modal__per-level';
    var perLevelText = document.createTextNode(Drupal.t('Passages per level') + ' ');
    var perLevelInput = document.createElement('input');
    perLevelInput.type = 'number';
    perLevelInput.min = '1';
    perLevelInput.value = String(DEFAULT_PER_LEVEL);
    perLevelWrap.appendChild(perLevelText);
    perLevelWrap.appendChild(perLevelInput);
    modal.appendChild(perLevelWrap);

    // Passive-only: pick exact passages instead of a random per-level
    // count — see PASSIVE_LISTENING_PLAN.md. A <details> so it stays out
    // of the way until opened, same pattern lms_flashcard's import-example
    // tip uses. Checking any box here overrides the level/per-level
    // controls above entirely (see the Start click handler below).
    var specificWrap = document.createElement('details');
    specificWrap.className = 'lms-listening-start-modal__specific';
    specificWrap.hidden = true;
    var specificSummary = document.createElement('summary');
    specificSummary.textContent = Drupal.t('Or choose specific passages');
    specificWrap.appendChild(specificSummary);
    var specificHint = document.createElement('p');
    specificHint.className = 'lms-listening-start-modal__specific-hint';
    specificHint.textContent = Drupal.t('Checking any passage below plays exactly those (still Easy → Normal → Challenge order), instead of the random per-level pick above.');
    specificWrap.appendChild(specificHint);
    var specificListEl = document.createElement('div');
    specificListEl.className = 'lms-listening-start-modal__specific-list';
    specificWrap.appendChild(specificListEl);
    modal.appendChild(specificWrap);

    var error = document.createElement('p');
    error.className = 'lms-listening-start-modal__error';
    error.hidden = true;
    modal.appendChild(error);

    var actions = document.createElement('div');
    actions.className = 'lms-listening-start-modal__actions';
    var cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'lms-listening-start-modal__cancel';
    cancelBtn.textContent = Drupal.t('Cancel');
    var startBtn = document.createElement('button');
    startBtn.type = 'button';
    startBtn.className = 'lms-listening-start-modal__start';
    startBtn.textContent = Drupal.t('Start practicing');
    actions.appendChild(cancelBtn);
    actions.appendChild(startBtn);
    modal.appendChild(actions);

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    return {
      overlay: overlay,
      modeActiveInput: modeActiveInput,
      modePassiveInput: modePassiveInput,
      intro: intro,
      passiveIntro: passiveIntro,
      levelsWrap: levelsWrap,
      perLevelWrap: perLevelWrap,
      perLevelInput: perLevelInput,
      specificWrap: specificWrap,
      specificListEl: specificListEl,
      error: error,
      cancelBtn: cancelBtn,
      startBtn: startBtn,
    };
  }

  /**
   * Rebuilds the level checkboxes for the deck currently open, each with
   * an "(N available)" count and disabled when that count is 0 — a level
   * with nothing to practice can't be picked at all, rather than being
   * offered and only failing (or silently contributing nothing) after
   * submission.
   *
   * @param {Object} ui
   *   The object returned by buildModal().
   * @param {Object} counts
   *   Level => count, e.g. {"easy": 5, "challenge": 2} — levels absent
   *   from this object are treated as 0.
   */
  function renderLevelCheckboxes(ui, counts) {
    ui.levelsWrap.textContent = '';
    var checkboxes = [];

    LEVELS.forEach(function (pair) {
      var level = pair[0];
      var count = counts[level] || 0;

      var label = document.createElement('label');
      var input = document.createElement('input');
      input.type = 'checkbox';
      input.value = level;
      input.disabled = count === 0;
      label.appendChild(input);
      label.appendChild(document.createTextNode(
        ' ' + pair[1]() + ' ' + Drupal.t('(@count available)', { '@count': count }),
      ));
      if (count === 0) {
        label.classList.add('is-disabled');
      }
      ui.levelsWrap.appendChild(label);
      checkboxes.push(input);
    });

    return checkboxes;
  }

  /**
   * Rebuilds the "choose specific passages" checkbox list for the deck
   * currently open — grouped under a level heading (Easy/Normal/Challenge,
   * only for levels that actually have passages), each row labeled with
   * the passage's own title so the student can tell them apart.
   *
   * @param {Object} ui
   *   The object returned by buildModal().
   * @param {Array} passages
   *   [{id, title, level}, ...] — see data-passages on the Listening link
   *   (lms_listening_preprocess_lms_flashcard_my_decks() in
   *   lms_listening.module).
   *
   * @return {Array} The rendered checkbox <input> elements, each with its
   *   passage id on `dataset.passageId`.
   */
  function renderSpecificPassages(ui, passages) {
    ui.specificListEl.textContent = '';
    var checkboxes = [];

    LEVELS.forEach(function (pair) {
      var level = pair[0];
      var levelPassages = passages.filter(function (p) { return p.level === level; });
      if (!levelPassages.length) {
        return;
      }

      var heading = document.createElement('p');
      heading.className = 'lms-listening-start-modal__specific-level-heading';
      heading.textContent = pair[1]();
      ui.specificListEl.appendChild(heading);

      levelPassages.forEach(function (passage) {
        var label = document.createElement('label');
        label.className = 'lms-listening-start-modal__specific-item';
        var input = document.createElement('input');
        input.type = 'checkbox';
        input.value = passage.id;
        input.dataset.passageId = passage.id;
        var titleText = document.createElement('span');
        titleText.className = 'lms-listening-start-modal__specific-title';
        titleText.textContent = passage.title;
        var format = passage.format || 'sentence';
        var formatBadge = document.createElement('span');
        formatBadge.className = 'lms-listening-start-modal__format-badge lms-listening-start-modal__format-badge--' + format;
        formatBadge.textContent = formatLabel(format);
        label.appendChild(input);
        label.appendChild(titleText);
        label.appendChild(formatBadge);
        ui.specificListEl.appendChild(label);
        checkboxes.push(input);
      });
    });

    return checkboxes;
  }

  Drupal.behaviors.lmsListeningStartModal = {
    attach: function (context) {
      var links = once('lms-listening-start-modal', 'a.lms-flashcard-deck-card__practice-link--listening', context);
      if (!links.length) {
        return;
      }

      var ui = buildModal();
      var checkboxes = [];
      var specificCheckboxes = [];
      var fallbackHref = null;
      var deckNameForIntro = '';

      function close() {
        ui.overlay.hidden = true;
        ui.error.hidden = true;
        ui.startBtn.disabled = false;
      }

      /**
       * Swaps the intro line + Start button label, and shows/hides the
       * Passive-only "choose specific passages" section — the level
       * checkboxes and "Passages per level" count are shared by both
       * modes now (Passive originally hid them entirely — fixed at 1 per
       * level, all 3 levels — but the student asked to be able to adjust
       * both, same controls Active mode already had).
       */
      function applyMode(mode) {
        var isPassive = mode === 'passive';
        ui.intro.hidden = isPassive;
        ui.passiveIntro.hidden = !isPassive;
        ui.specificWrap.hidden = !isPassive;
        ui.startBtn.textContent = isPassive ? Drupal.t('Start passive listening') : Drupal.t('Start practicing');
        ui.error.hidden = true;
      }

      ui.modeActiveInput.addEventListener('change', function () {
        if (ui.modeActiveInput.checked) {
          applyMode('active');
        }
      });
      ui.modePassiveInput.addEventListener('change', function () {
        if (ui.modePassiveInput.checked) {
          applyMode('passive');
        }
      });

      function open(link) {
        fallbackHref = link.getAttribute('href');
        deckNameForIntro = link.dataset.deckName || Drupal.t('this deck');

        var counts = {};
        try {
          counts = JSON.parse(link.dataset.levelCounts || '{}');
        }
        catch (e) {
          // Malformed/missing data-level-counts — treat as "nothing known
          // available", same as every level actually being empty; the
          // student can still fall back to the plain page via Cancel.
        }
        checkboxes = renderLevelCheckboxes(ui, counts);

        var passages = [];
        try {
          passages = JSON.parse(link.dataset.passages || '[]');
        }
        catch (e) {
          // Malformed/missing data-passages — same "treat as none
          // available" fallback as data-level-counts above; the specific-
          // passages section just ends up empty, the random per-level
          // pick above still works.
        }
        specificCheckboxes = renderSpecificPassages(ui, Array.isArray(passages) ? passages : []);

        // Drupal.t()'s `@` placeholder HTML-escapes its value (it's meant
        // for innerHTML, not textContent) — using innerHTML here is what
        // actually decodes "Chemist's" correctly instead of showing a
        // literal "Chemist&#039;s".
        ui.intro.innerHTML = Drupal.t('Pick one or more levels to practice from @deck, and how many passages per level.', {
          '@deck': deckNameForIntro,
        });
        ui.perLevelInput.value = String(DEFAULT_PER_LEVEL);

        // No listening passages at all for this deck (every level empty) —
        // Passive mode has nothing to build a session from either, so
        // don't offer it; reset to Active in case it was left selected
        // from a previous deck that did have passages.
        var totalAvailable = Object.keys(counts).reduce(function (sum, level) { return sum + (counts[level] || 0); }, 0);
        ui.modePassiveInput.disabled = totalAvailable === 0;
        ui.modeActiveInput.checked = true;
        applyMode('active');

        ui.error.hidden = true;
        ui.overlay.hidden = false;
      }

      ui.cancelBtn.addEventListener('click', close);
      ui.overlay.addEventListener('click', function (event) {
        if (event.target === ui.overlay) {
          close();
        }
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !ui.overlay.hidden) {
          close();
        }
      });

      ui.startBtn.addEventListener('click', function () {
        var deckId = new URL(fallbackHref, window.location.origin).searchParams.get('deck');
        var isPassive = ui.modePassiveInput.checked;
        var requestBody;

        var perLevel = parseInt(ui.perLevelInput.value, 10);
        if (!perLevel || perLevel < 1) {
          perLevel = DEFAULT_PER_LEVEL;
        }

        var levels = checkboxes.filter(function (cb) { return cb.checked; }).map(function (cb) { return cb.value; });

        if (isPassive) {
          var specificIds = specificCheckboxes
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.dataset.passageId; });

          if (specificIds.length) {
            // Chosen exact passages override the level/per-level pick
            // entirely — see ListeningApiController::startPassive().
            requestBody = { deck_id: deckId, mode: 'passive', passage_ids: specificIds };
          }
          else {
            if (!levels.length) {
              ui.error.textContent = Drupal.t('Pick at least one level, or choose specific passages below.');
              ui.error.hidden = false;
              return;
            }
            requestBody = { deck_id: deckId, mode: 'passive', levels: levels, per_level: perLevel };
          }
        }
        else {
          if (!levels.length) {
            ui.error.textContent = Drupal.t('Pick at least one level.');
            ui.error.hidden = false;
            return;
          }

          requestBody = { deck_id: deckId, levels: levels, per_level: perLevel };
        }

        ui.startBtn.disabled = true;
        fetch('/api/lms/listening/start', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(requestBody),
        })
          .then(function (response) { return response.json(); })
          .then(function (data) {
            if (data.redirect_url) {
              window.location.href = data.redirect_url;
              return;
            }
            ui.startBtn.disabled = false;
            ui.error.textContent = data.error || Drupal.t('Something went wrong. Please try again.');
            ui.error.hidden = false;
          })
          .catch(function () {
            // Network hiccup — fall back to the plain page rather than
            // leave the student stuck behind a popup that can't proceed.
            // (Active mode only: a Passive-mode start has no equivalent
            // plain-page fallback URL to send them to, since fallbackHref
            // always points at the Active/no-JS form — reload the modal's
            // own page instead in that case so they aren't sent into
            // Active mode's picker by surprise.)
            if (isPassive) {
              window.location.reload();
              return;
            }
            window.location.href = fallbackHref;
          });
      });

      links.forEach(function (link) {
        link.addEventListener('click', function (event) {
          event.preventDefault();
          open(link);
        });
      });
    },
  };

})(Drupal, once);
