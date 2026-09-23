/**
 * @file
 * Enhanced Listening Start Modal for Flashcard decks.
 * Allows learners to choose specific listening passages or practice randomly by level
 * in both Active (with questions) and Passive (hands-free) modes.
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
   * Builds the modal overlay and structure once, reused on link clicks.
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

    // 1. Header with Title, Deck badge, and Close (✕) button
    var header = document.createElement('div');
    header.className = 'lms-listening-start-modal__header';

    var headerInfo = document.createElement('div');
    headerInfo.className = 'lms-listening-start-modal__header-info';

    var deckBadge = document.createElement('span');
    deckBadge.className = 'lms-listening-start-modal__deck-badge';
    headerInfo.appendChild(deckBadge);

    var title = document.createElement('h2');
    title.id = 'lms-listening-start-modal-title';
    title.className = 'lms-listening-start-modal__title';
    title.textContent = Drupal.t('Listening Practice');
    headerInfo.appendChild(title);

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'lms-listening-start-modal__close-btn';
    closeBtn.setAttribute('aria-label', Drupal.t('Close'));
    closeBtn.innerHTML = '&times;';

    header.appendChild(headerInfo);
    header.appendChild(closeBtn);
    modal.appendChild(header);

    // 2. Mode Switcher (Active vs Passive pills)
    var modeWrap = document.createElement('div');
    modeWrap.className = 'lms-listening-start-modal__mode-switcher';

    var modeActiveBtn = document.createElement('button');
    modeActiveBtn.type = 'button';
    modeActiveBtn.className = 'lms-listening-start-modal__mode-btn is-active';
    modeActiveBtn.dataset.mode = 'active';
    modeActiveBtn.innerHTML = '<span class="mode-icon"><i class="fas fa-tasks"></i></span>' +
      '<div class="mode-text">' +
      '<strong class="mode-title">' + Drupal.t('Active Practice') + '</strong>' +
      '<span class="mode-sub">' + Drupal.t('Listen & answer questions') + '</span>' +
      '</div>';

    var modePassiveBtn = document.createElement('button');
    modePassiveBtn.type = 'button';
    modePassiveBtn.className = 'lms-listening-start-modal__mode-btn';
    modePassiveBtn.dataset.mode = 'passive';
    modePassiveBtn.innerHTML = '<span class="mode-icon"><i class="fas fa-headphones-alt"></i></span>' +
      '<div class="mode-text">' +
      '<strong class="mode-title">' + Drupal.t('Passive Listening') + '</strong>' +
      '<span class="mode-sub">' + Drupal.t('Continuous hands-free audio') + '</span>' +
      '</div>';

    modeWrap.appendChild(modeActiveBtn);
    modeWrap.appendChild(modePassiveBtn);
    modal.appendChild(modeWrap);

    // Mode description banner
    var modeBanner = document.createElement('div');
    modeBanner.className = 'lms-listening-start-modal__banner';
    modal.appendChild(modeBanner);

    // 3. Selection Method Tabs (Specific Passages vs Quick Random by Level)
    var tabNav = document.createElement('div');
    tabNav.className = 'lms-listening-start-modal__tab-nav';

    var tabBtnSpecific = document.createElement('button');
    tabBtnSpecific.type = 'button';
    tabBtnSpecific.className = 'lms-listening-start-modal__tab-btn is-active';
    tabBtnSpecific.dataset.tab = 'specific';
    tabBtnSpecific.innerHTML = '<i class="fas fa-list-check"></i> ' + Drupal.t('Choose Passages');

    var tabBtnRandom = document.createElement('button');
    tabBtnRandom.type = 'button';
    tabBtnRandom.className = 'lms-listening-start-modal__tab-btn';
    tabBtnRandom.dataset.tab = 'random';
    tabBtnRandom.innerHTML = '<i class="fas fa-shuffle"></i> ' + Drupal.t('Quick Pick by Level');

    tabNav.appendChild(tabBtnSpecific);
    tabNav.appendChild(tabBtnRandom);
    modal.appendChild(tabNav);

    // Tab Content 1: Specific Passages List
    var tabPaneSpecific = document.createElement('div');
    tabPaneSpecific.className = 'lms-listening-start-modal__tab-pane lms-listening-start-modal__tab-pane--specific is-active';

    var specificToolbar = document.createElement('div');
    specificToolbar.className = 'lms-listening-start-modal__specific-toolbar';

    var specificHint = document.createElement('span');
    specificHint.className = 'lms-listening-start-modal__specific-hint';
    specificHint.textContent = Drupal.t('Check the passages you wish to practice:');
    specificToolbar.appendChild(specificHint);

    var specificGlobalActions = document.createElement('div');
    specificGlobalActions.className = 'lms-listening-start-modal__global-actions';
    var selectAllBtn = document.createElement('button');
    selectAllBtn.type = 'button';
    selectAllBtn.className = 'lms-listening-start-modal__text-link';
    selectAllBtn.textContent = Drupal.t('Select All');
    var deselectAllBtn = document.createElement('button');
    deselectAllBtn.type = 'button';
    deselectAllBtn.className = 'lms-listening-start-modal__text-link';
    deselectAllBtn.textContent = Drupal.t('Clear');
    specificGlobalActions.appendChild(selectAllBtn);
    specificGlobalActions.appendChild(document.createTextNode(' · '));
    specificGlobalActions.appendChild(deselectAllBtn);
    specificToolbar.appendChild(specificGlobalActions);
    tabPaneSpecific.appendChild(specificToolbar);

    var specificListEl = document.createElement('div');
    specificListEl.className = 'lms-listening-start-modal__specific-list';
    tabPaneSpecific.appendChild(specificListEl);
    modal.appendChild(tabPaneSpecific);

    // Tab Content 2: Quick Pick by Level
    var tabPaneRandom = document.createElement('div');
    tabPaneRandom.className = 'lms-listening-start-modal__tab-pane lms-listening-start-modal__tab-pane--random';

    var randomHint = document.createElement('p');
    randomHint.className = 'lms-listening-start-modal__random-hint';
    randomHint.textContent = Drupal.t('Select one or more levels. The system will randomly pick passages for your session:');
    tabPaneRandom.appendChild(randomHint);

    var levelsWrap = document.createElement('div');
    levelsWrap.className = 'lms-listening-start-modal__levels';
    tabPaneRandom.appendChild(levelsWrap);

    var perLevelWrap = document.createElement('div');
    perLevelWrap.className = 'lms-listening-start-modal__per-level';
    var perLevelLabel = document.createElement('label');
    perLevelLabel.textContent = Drupal.t('Passages per selected level:');
    var perLevelInput = document.createElement('input');
    perLevelInput.type = 'number';
    perLevelInput.min = '1';
    perLevelInput.max = '20';
    perLevelInput.value = String(DEFAULT_PER_LEVEL);
    perLevelWrap.appendChild(perLevelLabel);
    perLevelWrap.appendChild(perLevelInput);
    tabPaneRandom.appendChild(perLevelWrap);

    modal.appendChild(tabPaneRandom);

    // Error message element
    var error = document.createElement('div');
    error.className = 'lms-listening-start-modal__error';
    error.hidden = true;
    modal.appendChild(error);

    // 4. Modal Actions Footer
    var actions = document.createElement('div');
    actions.className = 'lms-listening-start-modal__actions';

    var selectionSummary = document.createElement('div');
    selectionSummary.className = 'lms-listening-start-modal__summary';
    actions.appendChild(selectionSummary);

    var actionButtons = document.createElement('div');
    actionButtons.className = 'lms-listening-start-modal__action-btns';

    var cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'lms-listening-start-modal__cancel';
    cancelBtn.textContent = Drupal.t('Cancel');

    var startBtn = document.createElement('button');
    startBtn.type = 'button';
    startBtn.className = 'lms-listening-start-modal__start';
    startBtn.textContent = Drupal.t('Start Practicing');

    actionButtons.appendChild(cancelBtn);
    actionButtons.appendChild(startBtn);
    actions.appendChild(actionButtons);
    modal.appendChild(actions);

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    return {
      overlay: overlay,
      modal: modal,
      deckBadge: deckBadge,
      closeBtn: closeBtn,
      modeActiveBtn: modeActiveBtn,
      modePassiveBtn: modePassiveBtn,
      modeBanner: modeBanner,
      tabBtnSpecific: tabBtnSpecific,
      tabBtnRandom: tabBtnRandom,
      tabPaneSpecific: tabPaneSpecific,
      tabPaneRandom: tabPaneRandom,
      selectAllBtn: selectAllBtn,
      deselectAllBtn: deselectAllBtn,
      specificListEl: specificListEl,
      levelsWrap: levelsWrap,
      perLevelInput: perLevelInput,
      error: error,
      selectionSummary: selectionSummary,
      cancelBtn: cancelBtn,
      startBtn: startBtn,
    };
  }

  /**
   * Renders level checkboxes in the "Quick Random" tab.
   */
  function renderLevelCheckboxes(ui, counts, onCountChange) {
    ui.levelsWrap.textContent = '';
    var checkboxes = [];

    LEVELS.forEach(function (pair) {
      var level = pair[0];
      var count = counts[level] || 0;

      var label = document.createElement('label');
      label.className = 'lms-listening-start-modal__level-card';

      var input = document.createElement('input');
      input.type = 'checkbox';
      input.value = level;
      input.disabled = count === 0;

      var titleSpan = document.createElement('span');
      titleSpan.className = 'level-title';
      titleSpan.textContent = pair[1]();

      var badge = document.createElement('span');
      badge.className = 'level-badge';
      badge.textContent = Drupal.t('@count available', { '@count': count });

      label.appendChild(input);
      label.appendChild(titleSpan);
      label.appendChild(badge);

      if (count === 0) {
        label.classList.add('is-disabled');
      }

      input.addEventListener('change', function () {
        if (input.checked) {
          label.classList.add('is-checked');
        } else {
          label.classList.remove('is-checked');
        }
        if (typeof onCountChange === 'function') {
          onCountChange();
        }
      });

      ui.levelsWrap.appendChild(label);
      checkboxes.push(input);
    });

    return checkboxes;
  }

  /**
   * Renders the interactive list of specific passages grouped by level.
   */
  function renderSpecificPassages(ui, passages, onSelectionChange) {
    ui.specificListEl.textContent = '';
    var checkboxes = [];

    LEVELS.forEach(function (pair) {
      var level = pair[0];
      var levelPassages = passages.filter(function (p) { return p.level === level; });
      if (!levelPassages.length) {
        return;
      }

      var groupSection = document.createElement('div');
      groupSection.className = 'lms-listening-start-modal__level-group';

      // Group Header with select all for this level
      var groupHeader = document.createElement('div');
      groupHeader.className = 'lms-listening-start-modal__level-group-header';

      var headingText = document.createElement('span');
      headingText.className = 'lms-listening-start-modal__level-group-title';
      headingText.innerHTML = '<strong>' + pair[1]() + '</strong> <span class="group-count">(' + levelPassages.length + ')</span>';

      var groupAction = document.createElement('button');
      groupAction.type = 'button';
      groupAction.className = 'lms-listening-start-modal__text-link lms-listening-start-modal__group-toggle';
      groupAction.textContent = Drupal.t('Select level');

      groupHeader.appendChild(headingText);
      groupHeader.appendChild(groupAction);
      groupSection.appendChild(groupHeader);

      var levelInputs = [];

      levelPassages.forEach(function (passage) {
        var label = document.createElement('label');
        label.className = 'lms-listening-start-modal__specific-item';

        var input = document.createElement('input');
        input.type = 'checkbox';
        input.value = passage.id;
        input.dataset.passageId = passage.id;
        input.dataset.level = level;

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
        groupSection.appendChild(label);

        input.addEventListener('change', function () {
          if (input.checked) {
            label.classList.add('is-checked');
          } else {
            label.classList.remove('is-checked');
          }
          updateGroupToggleText();
          if (typeof onSelectionChange === 'function') {
            onSelectionChange();
          }
        });

        levelInputs.push(input);
        checkboxes.push(input);
      });

      function updateGroupToggleText() {
        var allChecked = levelInputs.every(function (i) { return i.checked; });
        groupAction.textContent = allChecked ? Drupal.t('Deselect') : Drupal.t('Select level');
      }

      groupAction.addEventListener('click', function () {
        var allChecked = levelInputs.every(function (i) { return i.checked; });
        var targetState = !allChecked;
        levelInputs.forEach(function (i) {
          i.checked = targetState;
          if (targetState) {
            i.closest('.lms-listening-start-modal__specific-item').classList.add('is-checked');
          } else {
            i.closest('.lms-listening-start-modal__specific-item').classList.remove('is-checked');
          }
        });
        updateGroupToggleText();
        if (typeof onSelectionChange === 'function') {
          onSelectionChange();
        }
      });

      ui.specificListEl.appendChild(groupSection);
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
      var levelCheckboxes = [];
      var specificCheckboxes = [];
      var currentMode = 'active';
      var currentTab = 'specific';
      var fallbackHref = null;

      function close() {
        ui.overlay.hidden = true;
        ui.error.hidden = true;
        ui.startBtn.disabled = false;
      }

      function updateSummary() {
        var isPassive = currentMode === 'passive';
        if (currentTab === 'specific') {
          var checkedCount = specificCheckboxes.filter(function (cb) { return cb.checked; }).length;
          if (checkedCount > 0) {
            ui.selectionSummary.innerHTML = '<i class="fas fa-check-circle"></i> ' +
              Drupal.t('<strong>@count</strong> passage(s) selected', { '@count': checkedCount });
            ui.startBtn.disabled = false;
          } else {
            ui.selectionSummary.innerHTML = '<span class="text-muted">' + Drupal.t('No passages selected yet') + '</span>';
          }
        } else {
          var checkedLevels = levelCheckboxes.filter(function (cb) { return cb.checked; });
          var perLevel = parseInt(ui.perLevelInput.value, 10) || DEFAULT_PER_LEVEL;
          if (checkedLevels.length > 0) {
            var approxTotal = checkedLevels.length * perLevel;
            ui.selectionSummary.innerHTML = '<i class="fas fa-sliders"></i> ' +
              Drupal.t('~@count passage(s) across @levels level(s)', { '@count': approxTotal, '@levels': checkedLevels.length });
            ui.startBtn.disabled = false;
          } else {
            ui.selectionSummary.innerHTML = '<span class="text-muted">' + Drupal.t('Pick at least one level') + '</span>';
          }
        }

        ui.startBtn.textContent = isPassive ? Drupal.t('Start Passive Listening') : Drupal.t('Start Practicing');
      }

      function applyMode(mode) {
        currentMode = mode;
        if (mode === 'active') {
          ui.modeActiveBtn.classList.add('is-active');
          ui.modePassiveBtn.classList.remove('is-active');
          ui.modeBanner.className = 'lms-listening-start-modal__banner lms-listening-start-modal__banner--active';
          ui.modeBanner.innerHTML = '<i class="fas fa-info-circle"></i> ' +
            Drupal.t('<strong>Active Practice:</strong> Listen to the passage audio, view transcripts, and complete IELTS comprehension questions.');
        } else {
          ui.modePassiveBtn.classList.add('is-active');
          ui.modeActiveBtn.classList.remove('is-active');
          ui.modeBanner.className = 'lms-listening-start-modal__banner lms-listening-start-modal__banner--passive';
          ui.modeBanner.innerHTML = '<i class="fas fa-headphones"></i> ' +
            Drupal.t('<strong>Passive Listening:</strong> Passages play continuously in sequence (loops automatically). Ideal for commuting or background study.');
        }
        updateSummary();
      }

      function applyTab(tab) {
        currentTab = tab;
        if (tab === 'specific') {
          ui.tabBtnSpecific.classList.add('is-active');
          ui.tabBtnRandom.classList.remove('is-active');
          ui.tabPaneSpecific.classList.add('is-active');
          ui.tabPaneRandom.classList.remove('is-active');
        } else {
          ui.tabBtnRandom.classList.add('is-active');
          ui.tabBtnSpecific.classList.remove('is-active');
          ui.tabPaneRandom.classList.add('is-active');
          ui.tabPaneSpecific.classList.remove('is-active');
        }
        ui.error.hidden = true;
        updateSummary();
      }

      ui.modeActiveBtn.addEventListener('click', function () { applyMode('active'); });
      ui.modePassiveBtn.addEventListener('click', function () { applyMode('passive'); });
      ui.tabBtnSpecific.addEventListener('click', function () { applyTab('specific'); });
      ui.tabBtnRandom.addEventListener('click', function () { applyTab('random'); });

      ui.selectAllBtn.addEventListener('click', function () {
        specificCheckboxes.forEach(function (cb) {
          cb.checked = true;
          cb.closest('.lms-listening-start-modal__specific-item').classList.add('is-checked');
        });
        ui.modal.querySelectorAll('.lms-listening-start-modal__group-toggle').forEach(function (btn) {
          btn.textContent = Drupal.t('Deselect');
        });
        updateSummary();
      });

      ui.deselectAllBtn.addEventListener('click', function () {
        specificCheckboxes.forEach(function (cb) {
          cb.checked = false;
          cb.closest('.lms-listening-start-modal__specific-item').classList.remove('is-checked');
        });
        ui.modal.querySelectorAll('.lms-listening-start-modal__group-toggle').forEach(function (btn) {
          btn.textContent = Drupal.t('Select level');
        });
        updateSummary();
      });

      ui.perLevelInput.addEventListener('input', updateSummary);
      ui.closeBtn.addEventListener('click', close);
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

      function open(link) {
        fallbackHref = link.getAttribute('href');
        var deckName = link.dataset.deckName || Drupal.t('Deck');

        var counts = {};
        try {
          counts = JSON.parse(link.dataset.levelCounts || '{}');
        } catch (e) {}

        var passages = [];
        try {
          passages = JSON.parse(link.dataset.passages || '[]');
        } catch (e) {}

        var totalPassages = Array.isArray(passages) ? passages.length : 0;
        ui.deckBadge.textContent = deckName + (totalPassages ? ' · ' + totalPassages + ' ' + Drupal.t('passages') : '');

        // Render controls
        levelCheckboxes = renderLevelCheckboxes(ui, counts, updateSummary);
        specificCheckboxes = renderSpecificPassages(ui, passages, updateSummary);

        // Pre-check all passages by default so the user can immediately hit Start or deselect what they don't want
        specificCheckboxes.forEach(function (cb) {
          cb.checked = true;
          cb.closest('.lms-listening-start-modal__specific-item').classList.add('is-checked');
        });
        ui.modal.querySelectorAll('.lms-listening-start-modal__group-toggle').forEach(function (btn) {
          btn.textContent = Drupal.t('Deselect');
        });

        ui.perLevelInput.value = String(DEFAULT_PER_LEVEL);

        // Disable passive mode if deck has 0 passages
        var totalAvailable = Object.keys(counts).reduce(function (sum, l) { return sum + (counts[l] || 0); }, 0);
        ui.modePassiveBtn.disabled = totalAvailable === 0;

        // Reset to active mode & specific tab
        applyMode('active');
        applyTab('specific');

        ui.error.hidden = true;
        ui.overlay.hidden = false;
        updateSummary();
      }

      // Start submission handler
      ui.startBtn.addEventListener('click', function () {
        var deckId = new URL(fallbackHref, window.location.origin).searchParams.get('deck');
        var requestBody;

        if (currentTab === 'specific') {
          var chosenIds = specificCheckboxes
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.dataset.passageId; });

          if (!chosenIds.length) {
            ui.error.textContent = Drupal.t('Please select at least one passage from the list.');
            ui.error.hidden = false;
            return;
          }

          requestBody = {
            deck_id: deckId,
            mode: currentMode,
            passage_ids: chosenIds,
          };
        } else {
          var chosenLevels = levelCheckboxes
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.value; });

          if (!chosenLevels.length) {
            ui.error.textContent = Drupal.t('Please select at least one level.');
            ui.error.hidden = false;
            return;
          }

          var perLevel = parseInt(ui.perLevelInput.value, 10);
          if (!perLevel || perLevel < 1) {
            perLevel = DEFAULT_PER_LEVEL;
          }

          requestBody = {
            deck_id: deckId,
            mode: currentMode,
            levels: chosenLevels,
            per_level: perLevel,
          };
        }

        ui.startBtn.disabled = true;
        ui.startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Loading...');

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
            updateSummary();
            ui.error.textContent = data.error || Drupal.t('Something went wrong. Please try again.');
            ui.error.hidden = false;
          })
          .catch(function () {
            if (currentMode === 'passive') {
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
