/**
 * @file
 * Passive Listening session (see PASSIVE_LISTENING_PLAN.md): plays 3
 * passages back to back — transcript, then vocabulary terms, then
 * auto-advance — with no interaction required beyond one initial "Start"
 * click. Everything after that first click runs inside one continuous
 * Promise chain rooted in that same user gesture (never a page navigation),
 * which is what keeps speechSynthesis.speak() reliably allowed to keep
 * firing across all 3 passages — browsers can be inconsistent about
 * allowing it outside of a user-gesture-derived call stack.
 *
 * The transcript is also click-to-jump: each rendered sentence/turn is
 * highlighted while it's being spoken, and clicking one cancels whatever's
 * currently playing (transcript or vocabulary) and starts speaking from
 * there instead — see the activeToken/startNewRun()/jumpToUnit() trio
 * below for how an interrupted run is superseded cleanly instead of
 * racing the one that replaces it.
 *
 * Same TTS mechanism as js/listening-passage.js (Active mode) and
 * lms_flashcard's flashcard-study.js: plain SpeechSynthesisUtterance, no
 * uploaded audio file involved anywhere in this module.
 */

(function (Drupal, once) {
  'use strict';

  var SPEECH_LANG_MAP = {
    en: 'en-US',
    vi: 'vi-VN',
    ja: 'ja-JP',
    ko: 'ko-KR',
    zh: 'zh-CN',
    fr: 'fr-FR',
    de: 'de-DE',
    es: 'es-ES',
  };

  // Preselected in the Voice dropdown instead of "Default" whenever it's
  // available — same constant/reasoning as js/listening-passage.js's own
  // copy (Active mode): the student's own pick (a Google TTS male voice —
  // sounds noticeably less harsh/robotic than Windows' own SAPI voices).
  // Only Chrome/Edge's online "Google …" voices carry this name; other
  // browsers/OSes don't have it, so this silently falls back to "Default"
  // wherever it isn't installed. Not every language's Google voice set is
  // split by gender (e.g. "Google US English" is a single voice with no
  // "Male"/"Female" variant) — see findPreferredVoiceIndex() below for how
  // that's handled.
  var PREFERRED_VOICE_NAME = 'Google';

  /**
   * Index into `voices` of the best Google-voice match — a "Google …
   * Male" voice if one exists (e.g. "Google UK English Male"), else any
   * other "Google" voice (some languages, like "Google US English", ship
   * as a single ungendered voice), else -1 (falls back to "Default").
   * Shared by both populateVoiceSelect() (here) and js/listening-passage.js's
   * own populateVoices(), kept as an independent copy.
   */
  function findPreferredVoiceIndex(voices) {
    var fallbackIndex = -1;
    for (var i = 0; i < voices.length; i++) {
      var name = voices[i].name || '';
      if (name.indexOf(PREFERRED_VOICE_NAME) !== -1) {
        if (name.toLowerCase().indexOf('male') !== -1 && name.toLowerCase().indexOf('female') === -1) {
          return i;
        }
        if (fallbackIndex === -1) {
          fallbackIndex = i;
        }
      }
    }
    return fallbackIndex;
  }

  // Pauses between phases — tunable; see PASSIVE_LISTENING_PLAN.md's "Open
  // Decisions" for why these particular values are just a starting guess.
  var PAUSE_BEFORE_VOCAB_MS = 1000;
  var PAUSE_BETWEEN_VOCAB_TERMS_MS = 500;
  var PAUSE_BEFORE_NEXT_PASSAGE_MS = 1200;

  /**
   * Speaks one utterance, resolving once it ends (or immediately if
   * speechSynthesis isn't available/text is empty) — never rejects, so a
   * broken TTS call can't stall the whole passage/vocab chain. `voice`
   * (optional) is a SpeechSynthesisVoice — see assignSpeakerVoices() below
   * for how a Dialogue passage's turns each get one. `rate` (optional,
   * default 1) is the Voice/Speed bar's speed control.
   */
  function speak(text, lang, voice, rate) {
    return new Promise(function (resolve) {
      if (!window.speechSynthesis || !text) {
        resolve();
        return;
      }
      var utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = lang;
      utterance.rate = rate || 1;
      if (voice) {
        utterance.voice = voice;
      }
      utterance.onend = resolve;
      utterance.onerror = resolve;
      window.speechSynthesis.speak(utterance);
    });
  }

  function wait(ms) {
    return new Promise(function (resolve) {
      window.setTimeout(resolve, ms);
    });
  }

  /**
   * Splits plain text into sentence-ish chunks — identical approach to
   * js/listening-passage.js's own splitSentences() (Active mode):
   * deliberately simple, a wrong split only affects *where* playback
   * pauses/highlights, never the transcript text itself.
   */
  function splitSentences(text) {
    var pattern = /[^.!?]+[.!?]+(?:["'”’)\]]*)\s*/g;
    var chunks = text.match(pattern) || [];
    var consumedLength = chunks.join('').length;
    if (consumedLength < text.length) {
      chunks.push(text.slice(consumedLength));
    }
    return chunks.filter(function (chunk) { return chunk.trim() !== ''; });
  }

  /**
   * Writes `text` to the clipboard — identical approach to
   * js/listening-passage.js's own writeToClipboard(), kept as an
   * independent copy. navigator.clipboard.writeText() where available
   * (secure context), falling back to a temporary off-screen textarea +
   * document.execCommand('copy') otherwise.
   */
  function writeToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      var textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      try {
        var ok = document.execCommand('copy');
        document.body.removeChild(textarea);
        if (ok) {
          resolve();
        }
        else {
          reject(new Error('execCommand copy failed'));
        }
      }
      catch (e) {
        document.body.removeChild(textarea);
        reject(e);
      }
    });
  }

  /**
   * Briefly swaps a copy button's label to `message`, then restores its
   * original text — independent copy of js/listening-passage.js's own
   * flashButtonLabel().
   */
  /**
   * Splits a dialogue passage's whole-block translation text into one
   * {speaker, line} row per turn, by locating each occurrence of one of
   * `dialogueTurns`' own speaker names followed by ":" — the translator
   * is expected to keep those same (untranslated) speaker labels in the
   * translation text, same as the screenshot that prompted this ("Mia:
   * ... Ben: ..." inline). Returns null (render as one plain block, same
   * as before this existed) when there's no dialogue, or the text
   * doesn't actually contain any recognizable "Speaker:" labels — never
   * throws, never guesses at a split that might be wrong.
   */
  function splitDialogueTranslation(text, dialogueTurns) {
    if (!text || !Array.isArray(dialogueTurns) || !dialogueTurns.length) {
      return null;
    }
    var uniqueNames = [];
    dialogueTurns.forEach(function (turn) {
      if (turn.speaker && uniqueNames.indexOf(turn.speaker) === -1) {
        uniqueNames.push(turn.speaker);
      }
    });
    if (!uniqueNames.length) {
      return null;
    }
    var escaped = uniqueNames.map(function (name) {
      return name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    });
    var pattern = new RegExp('(?:^|\\s)(' + escaped.join('|') + ')\\s*:\\s*', 'g');
    var matches = [];
    var match;
    while ((match = pattern.exec(text)) !== null) {
      matches.push({ speaker: match[1], start: match.index, contentStart: pattern.lastIndex });
    }
    if (!matches.length) {
      return null;
    }
    var rows = [];
    matches.forEach(function (m, i) {
      var end = (i + 1 < matches.length) ? matches[i + 1].start : text.length;
      var line = text.slice(m.contentStart, end).trim();
      if (line) {
        rows.push({ speaker: m.speaker, line: line });
      }
    });
    return rows.length ? rows : null;
  }

  /**
   * Fills `container` with the passage's whole-block translation — one
   * line per speaker turn (matching the English transcript's own layout)
   * when splitDialogueTranslation() can find them, otherwise the plain
   * text as a single block like before this existed.
   */
  function renderTranslationBlock(container, text, dialogueTurns, lineClass) {
    if (!container) {
      return;
    }
    container.replaceChildren();
    var rows = splitDialogueTranslation(text, dialogueTurns);
    if (!rows) {
      container.textContent = text || '';
      return;
    }
    rows.forEach(function (row) {
      var p = document.createElement('p');
      if (lineClass) {
        p.className = lineClass;
      }
      var strong = document.createElement('strong');
      strong.textContent = row.speaker + ': ';
      p.appendChild(strong);
      p.appendChild(document.createTextNode(row.line));
      container.appendChild(p);
    });
  }

  function flashButtonLabel(labelEl, message) {
    if (!labelEl) {
      return;
    }
    var original = labelEl.dataset.originalText || labelEl.textContent;
    labelEl.dataset.originalText = original;
    labelEl.textContent = message;
    window.setTimeout(function () {
      labelEl.textContent = original;
    }, 1800);
  }

  Drupal.behaviors.lmsListeningPassive = {
    attach: function (context) {
      once('lms-listening-passive', '[data-listening-passive-start]', context).forEach(function (startBtn) {
        var settings = (drupalSettings.lmsListeningPassive || {});
        var section = startBtn.closest('.lms-listening-passive');
        if (!section || !settings.passage) {
          return;
        }

        var deckId = settings.deckId;
        var nextUrl = settings.nextUrl;
        var speechLang = SPEECH_LANG_MAP[settings.language] || 'en-US';

        var titleEl = section.querySelector('[data-listening-passive-title]');
        var levelEl = section.querySelector('[data-listening-passive-level]');
        var statusEl = section.querySelector('[data-listening-passive-status]');
        var progressEl = section.querySelector('[data-listening-passive-progress]');
        var transcriptEl = section.querySelector('[data-listening-passive-transcript]');
        var vocabWrapEl = section.querySelector('[data-listening-passive-vocab]');
        var vocabListEl = section.querySelector('[data-listening-passive-vocab-list]');
        var translationBtn = section.querySelector('[data-listening-passive-translation-btn]');
        var translationEl = section.querySelector('[data-listening-passive-translation]');
        var stopBtn = section.querySelector('[data-listening-passive-stop]');
        var startLabelEl = section.querySelector('[data-listening-passive-start-label]');
        var doneEl = section.querySelector('[data-listening-passive-done]');
        var voiceBarEl = section.querySelector('[data-listening-passive-voice-bar]');
        var voiceSelectEl = section.querySelector('[data-listening-passive-voice]');
        var speedSelectEl = section.querySelector('[data-listening-passive-speed]');
        var noteWidgetEl = section.querySelector('[data-lms-note-widget]');
        // "Copy transcript (no names)" — content-creation aid (see Quick
        // Edit below), not a student-facing control: only shown to
        // whoever can Quick Edit this passage.
        var copyTranscriptBtn = section.querySelector('[data-listening-passive-copy-transcript]');
        var copyTranscriptLabelEl = section.querySelector('[data-listening-passive-copy-label]');

        // Quick Edit (narration audio upload) — see
        // ListeningApiController::quickEditFormData()/quickEditSave() and
        // the data-listening-edit-* markup in this template.
        var quickEditBtnEl = section.querySelector('[data-listening-passive-quick-edit]');
        var editOverlayEl = document.querySelector('[data-listening-edit-overlay]');
        var editDrawerEl = document.querySelector('[data-listening-edit-drawer]');
        var editCloseBtn = document.querySelector('[data-listening-edit-close]');
        var editCancelBtn = document.querySelector('[data-listening-edit-cancel]');
        var editSaveBtn = document.querySelector('[data-listening-edit-save]');
        var editStatusEl = document.querySelector('[data-listening-edit-status]');
        var editAudioCurrentEl = document.querySelector('[data-listening-edit-audio-current]');
        var editAudioPreviewEl = document.querySelector('[data-listening-edit-audio-preview]');
        var editAudioRemoveBtn = document.querySelector('[data-listening-edit-audio-remove]');
        var editAudioInputEl = document.querySelector('[data-listening-edit-audio-input]');
        var editSentenceAudioCurrentEl = document.querySelector('[data-listening-edit-sentence-audio-current]');
        var editSentenceAudioRemoveBtn = document.querySelector('[data-listening-edit-sentence-audio-remove]');
        var editSentenceAudioInputEl = document.querySelector('[data-listening-edit-sentence-audio-input]');

        // Background Music (see BackgroundMusicTrack's docblock) — a
        // second, independent <audio> element, created once here and kept
        // alive for the whole (possibly multi-passage, looping) session,
        // unlike activeAudioEl above which is recreated per narration
        // clip. Deliberately separate from the TTS/narration volume:
        // there's no "master volume" concept in this page, just this one
        // slider for the music bed underneath.
        var bgTrackSelectEl = section.querySelector('[data-listening-passive-bg-track]');
        var bgVolumeEl = section.querySelector('[data-listening-passive-bg-volume]');
        var bgTracks = settings.bgTracks || [];
        var BG_TRACK_STORAGE_KEY = 'lmsListeningPassiveBgTrackId';
        var BG_VOLUME_STORAGE_KEY = 'lmsListeningPassiveBgVolume';
        var bgAudioEl = new Audio();
        bgAudioEl.loop = true;
        bgAudioEl.preload = 'none';

        function readStoredPref(key) {
          try {
            return window.localStorage.getItem(key);
          }
          catch (e) {
            return null;
          }
        }

        function writeStoredPref(key, value) {
          try {
            window.localStorage.setItem(key, value);
          }
          catch (e) {
            // Private browsing / storage disabled — the picker still works
            // for this page load, it just won't remember next time.
          }
        }

        function applyBgTrack(trackId) {
          var track = null;
          for (var i = 0; i < bgTracks.length; i++) {
            if (String(bgTracks[i].id) === String(trackId)) {
              track = bgTracks[i];
              break;
            }
          }
          if (track) {
            bgAudioEl.src = track.url;
          }
          else {
            bgAudioEl.pause();
            bgAudioEl.removeAttribute('src');
          }
          // Greyed out while "None" is picked — the slider has nothing to
          // control yet, and a silent slider that visibly does nothing
          // reads as broken rather than as "no track selected".
          if (bgVolumeEl) {
            bgVolumeEl.disabled = !track;
          }
        }

        function playBgMusicIfSelected() {
          if (bgTrackSelectEl && bgTrackSelectEl.value && bgAudioEl.src) {
            // play() returns a Promise that rejects if the browser blocks
            // it — always inside a click handler here (startBtn), so
            // autoplay restrictions don't apply, but a rejection is
            // swallowed defensively rather than throwing regardless.
            bgAudioEl.play().catch(function () {});
          }
        }

        if (bgTrackSelectEl) {
          bgTracks.forEach(function (track) {
            var option = document.createElement('option');
            option.value = String(track.id);
            option.textContent = track.name;
            bgTrackSelectEl.appendChild(option);
          });

          var storedTrackId = readStoredPref(BG_TRACK_STORAGE_KEY);
          if (storedTrackId && bgTrackSelectEl.querySelector('option[value="' + storedTrackId + '"]')) {
            bgTrackSelectEl.value = storedTrackId;
          }
          applyBgTrack(bgTrackSelectEl.value);

          bgTrackSelectEl.addEventListener('change', function () {
            writeStoredPref(BG_TRACK_STORAGE_KEY, bgTrackSelectEl.value);
            applyBgTrack(bgTrackSelectEl.value);
            if (running) {
              playBgMusicIfSelected();
            }
          });
        }

        if (bgVolumeEl) {
          var storedVolume = readStoredPref(BG_VOLUME_STORAGE_KEY);
          if (storedVolume !== null && !isNaN(parseInt(storedVolume, 10))) {
            bgVolumeEl.value = storedVolume;
          }
          bgAudioEl.volume = (parseInt(bgVolumeEl.value, 10) || 0) / 100;

          bgVolumeEl.addEventListener('input', function () {
            bgAudioEl.volume = (parseInt(bgVolumeEl.value, 10) || 0) / 100;
            writeStoredPref(BG_VOLUME_STORAGE_KEY, bgVolumeEl.value);
          });
        }

        var currentPassage = settings.passage;
        var firstPassageId = settings.passage.id;
        var passageNumber = 0;
        var lapCount = 1;
        var stopped = false;
        var running = false;
        var ttsAvailable = !!window.speechSynthesis;

        // The current passage's uploaded whole-passage narration audio
        // (field_audio) — when set AND sentenceAudioUrls is empty,
        // runPlayback() plays this <audio> file front-to-back instead of
        // speaking transcriptUnits one at a time with TTS. See
        // usesWholeFileAudio() below for why sentenceAudioUrls always
        // takes priority when it has anything in it.
        var currentAudioUrl = null;
        // sentenceAudioUrls[i] is the recorded-clip URL to use for
        // transcriptUnits[i], or undefined — best-effort index pairing
        // against upload order, same convention as translationUnits (see
        // its own doc comment above). When non-empty, speakTranscript()'s
        // speakUnit() plays each unit's own clip where one exists, falling
        // back to TTS only for the units that don't have one — see that
        // function below.
        var sentenceAudioUrls = [];
        // The <audio> element currently playing (currentAudioUrl in
        // whole-file mode, or one sentence's own clip in per-sentence
        // mode), or null — jumpToUnit() pauses/resumes this in place
        // around a TTS interjection in whole-file mode rather than
        // tearing it down and restarting.
        var activeAudioEl = null;

        /**
         * Whole-passage audio is only actually used for continuous
         * playback when there's no per-sentence audio at all — otherwise
         * the per-unit chain (speakTranscript(), mixing recorded clips
         * and TTS per unit) takes over entirely. See runPlayback() and
         * jumpToUnit() below, both of which branch on this.
         */
        function usesWholeFileAudio() {
          return !!currentAudioUrl && sentenceAudioUrls.length === 0;
        }

        var quickEditGetUrl = null;
        var quickEditSaveUrl = null;
        var quickEditOpen = false;
        var quickEditSaving = false;

        // One entry per rendered transcript sentence/turn — {el, text,
        // speaker} — rebuilt every renderPassage() call so it always
        // matches what's currently on screen. Clicking one jumps playback
        // there (see wireTranscriptClicks()/jumpToUnit() below); kept in
        // sync with whichever unit speakTranscript() is currently
        // speaking via highlightTranscriptUnit().
        var transcriptUnits = [];

        // translationUnits[i] is the translated sentence to show in a
        // tooltip for transcriptUnits[i] — best-effort only: there's no
        // per-sentence translation stored server-side (field_translation
        // is one plain-text block for the whole passage), so this pairs
        // the i-th English sentence with the i-th sentence of the
        // translation text by splitSentences()'ing both and matching by
        // index. Holds where the translation doesn't split into at least
        // as many sentences as the transcript (e.g. no translation at
        // all, or the translator merged/reordered sentences) — those
        // indexes are simply left undefined and no tooltip shows.
        var translationUnits = [];

        // Bumped every time a fresh "run" of playback starts — the
        // initial Start click, or clicking a transcript sentence/turn to
        // jump there. Every async step further down captures its own
        // `token` and checks it against this before doing anything, so
        // an interrupted run's still-pending callbacks (notably a
        // cancelled utterance's onend) quietly do nothing instead of
        // racing whichever run superseded them.
        var activeToken = 0;

        // Holds a Screen Wake Lock while playing so mobile browsers don't
        // turn the screen off mid-session — once the screen actually
        // turns off, speechSynthesis stops firing its callbacks and the
        // whole Promise chain in runPlayback() silently stalls
        // until the student manually unlocks the phone again. Unsupported
        // browsers (no navigator.wakeLock) simply keep today's behavior:
        // playback still works, it just risks the screen turning off.
        var wakeLock = null;

        function requestWakeLock() {
          if (!('wakeLock' in navigator)) {
            return;
          }
          navigator.wakeLock.request('screen').then(function (lock) {
            wakeLock = lock;
            wakeLock.addEventListener('release', function () {
              wakeLock = null;
            });
          }).catch(function () {
            // Ignore — e.g. battery saver mode, or the document isn't
            // visible right now. Best-effort only.
          });
        }

        function releaseWakeLock() {
          if (wakeLock) {
            wakeLock.release().catch(function () {});
            wakeLock = null;
          }
        }

        // The lock is auto-released by the browser the instant the tab
        // itself goes to the background/hidden (which also happens right
        // as the screen locks) — re-acquire it as soon as the page is
        // visible again while a session is still running, instead of
        // only ever acquiring it once at Start.
        document.addEventListener('visibilitychange', function () {
          if (document.visibilityState === 'visible' && running && !wakeLock) {
            requestWakeLock();
          }
        });

        // Per-speaker voice alternation for Dialogue passages — same
        // mechanism js/listening-passage.js's assignSpeakerVoices() uses
        // for Active mode, so a 2-person conversation doesn't play in one
        // flat voice for both sides. Re-assigned per passage (see
        // speakTranscript() below) since each passage can have different
        // speaker names.
        var matchingVoices = [];
        // Flips true once the student touches the Voice dropdown
        // themself (see its 'change' listener below) — after that, a
        // voice-list refresh preserves their pick instead of snapping
        // back to the preferred default underneath them.
        var userPickedVoice = false;

        if (voiceSelectEl) {
          voiceSelectEl.addEventListener('change', function () {
            userPickedVoice = true;
          });
        }

        function refreshVoices() {
          var all = window.speechSynthesis ? (window.speechSynthesis.getVoices() || []) : [];
          var langPrefix = speechLang.split('-')[0];
          matchingVoices = all.filter(function (voice) {
            return voice.lang && voice.lang.toLowerCase().indexOf(langPrefix) === 0;
          });
          populateVoiceSelect();
        }

        /**
         * Whether `currentPassage` is a Dialogue with turns — i.e. whether
         * speakTranscript() below will alternate per-speaker voices rather
         * than speak in one. Drives the Voice dropdown's default label/
         * selection just below, so it doesn't visually claim a single named
         * voice while two are actually alternating.
         */
        function isDialoguePassage() {
          var t = (currentPassage && currentPassage.transcript) || {};
          return t.format === 'dialogue' && Array.isArray(t.dialogueTurns) && t.dialogueTurns.length > 0;
        }

        /**
         * Fills the Voice <select> from matchingVoices — same shape as
         * js/listening-passage.js's own populateVoices() (Active mode):
         * a "Default"/"Auto" option (value "") plus one per matching voice,
         * index-valued. Only shown when there's more than one to choose
         * from; hidden (but Speed stays available either way) otherwise —
         * getVoices() can return just one, or resolve asynchronously after
         * onvoiceschanged, depending on the browser.
         */
        function populateVoiceSelect() {
          if (!voiceSelectEl) {
            return;
          }
          if (matchingVoices.length < 2) {
            voiceSelectEl.hidden = true;
            return;
          }
          // Preserve whatever the student already picked across a refresh
          // (onvoiceschanged can fire more than once as a browser loads
          // its voice list in stages); otherwise default to
          // PREFERRED_VOICE_NAME when it's available — except for a
          // Dialogue passage, where speakTranscript() alternates a voice
          // per speaker instead of using any single one, so pre-selecting
          // PREFERRED_VOICE_NAME here would show one named voice while two
          // are actually heard. Label that state "Auto" and leave the
          // dropdown on it instead.
          var dialogue = isDialoguePassage();
          var previousValue = voiceSelectEl.value;
          voiceSelectEl.textContent = '';
          var defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = dialogue ? Drupal.t('Auto (alternating voices)') : Drupal.t('Default');
          voiceSelectEl.appendChild(defaultOption);
          matchingVoices.forEach(function (voice, index) {
            var option = document.createElement('option');
            option.value = index;
            option.textContent = voice.name + (voice.localService ? '' : ' *');
            voiceSelectEl.appendChild(option);
          });
          var preferredIndex = findPreferredVoiceIndex(matchingVoices);
          if (userPickedVoice && previousValue !== '' && matchingVoices[previousValue]) {
            voiceSelectEl.value = previousValue;
          }
          else if (dialogue) {
            voiceSelectEl.value = '';
          }
          else if (preferredIndex !== -1) {
            voiceSelectEl.value = String(preferredIndex);
          }
          voiceSelectEl.hidden = false;
        }

        /**
         * The voice the student explicitly picked from the dropdown, or
         * undefined to fall back to whatever the caller would otherwise
         * use (per-speaker alternation for Dialogue, default voice
         * otherwise) — mirrors js/listening-passage.js's own
         * voiceSelect.value handling in Active mode's speakFrom().
         */
        function manualVoice() {
          if (voiceSelectEl && voiceSelectEl.value !== '' && matchingVoices[voiceSelectEl.value]) {
            return matchingVoices[voiceSelectEl.value];
          }
          return undefined;
        }

        function currentRate() {
          return speedSelectEl ? (parseFloat(speedSelectEl.value) || 1) : 1;
        }

        /**
         * Maps each unique speaker name in `dialogueTurns` to a distinct
         * voice (round-robin through matchingVoices) — {} (every turn
         * falls back to the default voice) if fewer than 2 matching voices
         * are available, same as Active mode's own fallback.
         */
        function assignSpeakerVoices(dialogueTurns) {
          var speakerVoiceIndex = {};
          if (matchingVoices.length < 2) {
            return speakerVoiceIndex;
          }
          var seen = [];
          dialogueTurns.forEach(function (turn) {
            if (turn.speaker && seen.indexOf(turn.speaker) === -1) {
              seen.push(turn.speaker);
              speakerVoiceIndex[turn.speaker] = (seen.length - 1) % matchingVoices.length;
            }
          });
          return speakerVoiceIndex;
        }

        /**
         * Renders one passage's title/level/transcript/vocabulary into the
         * page — used both for the initial (already-loaded) passage and
         * every one fetched afterward, so the two never drift into two
         * different-looking renders.
         */
        function renderPassage(passage) {
          // The session loops indefinitely (see PASSIVE_LISTENING_PLAN.md
          // — only Stop ends it, at the student's request) rather than
          // stopping after one pass through the queue — ListeningApiController
          // ::passiveNext() wraps back to the first passage instead of
          // ever returning {status:'done'} in normal use. Detecting that
          // wrap here (this passage's id matches the very first one again,
          // and it isn't literally the first render) is what counts laps
          // and resets the "Passage N" counter back to 1 each time,
          // instead of just counting up forever.
          if (passage.id === firstPassageId && passageNumber > 0) {
            lapCount++;
            passageNumber = 0;
          }
          currentPassage = passage;
          passageNumber++;

          currentAudioUrl = passage.audioUrl || null;
          sentenceAudioUrls = Array.isArray(passage.sentenceAudioUrls) ? passage.sentenceAudioUrls : [];
          quickEditGetUrl = passage.quickEditGetUrl || null;
          quickEditSaveUrl = passage.quickEditSaveUrl || null;
          if (quickEditBtnEl) {
            quickEditBtnEl.hidden = !passage.canQuickEdit;
          }
          if (copyTranscriptBtn) {
            copyTranscriptBtn.hidden = !passage.canQuickEdit;
          }

          // Point the note widget at this passage — but only once it has
          // actually changed (its own dataset stays in sync with whatever
          // it's currently loaded, seeded from the template for the very
          // first passage). Guards two things: not re-fetching on the
          // very first renderPassage() call the template's own initial
          // data-note-context-id already covers, and not erroring if
          // js/note-widget.js's own behavior hasn't attached yet (its
          // 'lmsNoteWidget' API only appears on the element once that has
          // run — attach order between two separate libraries' behaviors
          // isn't guaranteed).
          if (noteWidgetEl && noteWidgetEl.lmsNoteWidget && String(passage.id) !== noteWidgetEl.dataset.noteContextId) {
            noteWidgetEl.lmsNoteWidget.setContext('listening_passage', passage.id);
          }

          if (titleEl) {
            titleEl.textContent = passage.title || '';
          }
          if (levelEl) {
            var level = passage.level || '';
            levelEl.textContent = level.charAt(0).toUpperCase() + level.slice(1);
          }
          if (progressEl) {
            progressEl.textContent = lapCount > 1
              ? Drupal.t('Passage @n · Lap @lap', { '@n': passageNumber, '@lap': lapCount })
              : Drupal.t('Passage @n', { '@n': passageNumber });
          }

          if (transcriptEl) {
            transcriptEl.hidden = false;
            transcriptEl.replaceChildren();
            transcriptUnits = [];
            var t = passage.transcript || {};
            if (t.format === 'dialogue' && Array.isArray(t.dialogueTurns)) {
              t.dialogueTurns.forEach(function (turn) {
                var line = document.createElement('p');
                line.className = 'lms-listening-passive__line';
                var speaker = document.createElement('strong');
                speaker.textContent = turn.speaker + ': ';
                line.appendChild(speaker);
                line.appendChild(document.createTextNode(turn.line));
                transcriptEl.appendChild(line);
                transcriptUnits.push({ el: line, text: turn.line, speaker: turn.speaker });
              });
            }
            else if (t.body) {
              var body = document.createElement('p');
              splitSentences(t.body).forEach(function (sentence) {
                var span = document.createElement('span');
                span.className = 'lms-listening-passive__sentence';
                span.textContent = sentence;
                body.appendChild(span);
                transcriptUnits.push({ el: span, text: sentence });
              });
              transcriptEl.appendChild(body);
            }

            // Best-effort pairing with the transcript's own units — for
            // dialogue, splitDialogueTranslation() gives an exact 1:1
            // per-turn match (already stripped of the "Speaker: " label)
            // whenever the translator kept those labels; falls back to
            // no tooltip at all for dialogue rather than a naive sentence
            // split that would leave "Mia: " stuck onto the wrong turn.
            // For a plain body, sentence-splitting both texts and pairing
            // by index is still the only option (see translationUnits'
            // own doc comment above for the same caveat there).
            translationUnits = [];
            if (passage.translation) {
              if (t.format === 'dialogue') {
                var dialogueTranslationRows = splitDialogueTranslation(passage.translation, t.dialogueTurns);
                if (dialogueTranslationRows) {
                  transcriptUnits.forEach(function (unit, i) {
                    translationUnits[i] = dialogueTranslationRows[i] ? dialogueTranslationRows[i].line : undefined;
                  });
                }
              }
              else {
                var translatedSentences = splitSentences(passage.translation);
                transcriptUnits.forEach(function (unit, i) {
                  translationUnits[i] = translatedSentences[i];
                });
              }
            }

            wireTranscriptClicks();
          }

          var terms = Array.isArray(passage.vocabTerms) ? passage.vocabTerms : [];
          if (vocabListEl) {
            vocabListEl.replaceChildren();
            terms.forEach(function (term) {
              var item = document.createElement('li');
              item.textContent = term;
              vocabListEl.appendChild(item);
            });
          }
          if (vocabWrapEl) {
            vocabWrapEl.hidden = terms.length === 0;
          }

          // Re-hidden (button re-shown) on every new passage — a
          // translation revealed for the previous passage must not
          // silently carry over and look like it belongs to this one.
          // The button itself only ever shows when this passage actually
          // has a translation.
          if (translationEl) {
            translationEl.hidden = true;
            renderTranslationBlock(
              translationEl,
              passage.translation,
              (passage.transcript || {}).dialogueTurns,
              'lms-listening-passive__line',
            );
          }
          if (translationBtn) {
            translationBtn.hidden = !passage.translation;
          }

          // Keep the Voice dropdown's "Default"/"Auto (alternating voices)"
          // label in sync with *this* passage right away — otherwise it'd
          // still show whatever the previous passage's format implied
          // until speakTranscript() gets around to it (which never
          // happens at all for a non-Dialogue passage).
          populateVoiceSelect();
        }

        function clearTranscriptHighlight() {
          transcriptUnits.forEach(function (unit) { unit.el.classList.remove('is-speaking'); });
        }

        function highlightTranscriptUnit(index) {
          clearTranscriptHighlight();
          var unit = transcriptUnits[index];
          if (unit) {
            unit.el.classList.add('is-speaking');
            unit.el.scrollIntoView({ block: 'center', behavior: 'smooth' });
          }
        }

        /**
         * Speaks/plays transcriptUnits[startIndex..] one at a time,
         * highlighting each as it plays — resolves once the last one
         * ends, or as soon as `token` stops matching activeToken (a
         * jumpToUnit() call superseded this run; see the module-level
         * activeToken doc comment above). Never rejects, same as speak()
         * below. Each unit picks its own source independently
         * (speakUnit() below): its own recorded clip
         * (sentenceAudioUrls[index]) when it has one, TTS otherwise — so
         * this same function naturally covers a pure-TTS passage
         * (sentenceAudioUrls entirely empty), a fully-recorded one, and
         * any mix of the two.
         */
        function speakTranscript(startIndex, token) {
          return new Promise(function (resolve) {
            if (!transcriptUnits.length) {
              resolve();
              return;
            }
            var rate = currentRate();
            var dialogue = isDialoguePassage();
            // getVoices() can still be empty on the very first call in some
            // browsers (they load the voice list asynchronously) — refresh
            // right before assigning, not just once at attach time.
            if (dialogue) {
              refreshVoices();
            }
            var speakerVoiceIndex = dialogue
              ? assignSpeakerVoices((currentPassage.transcript || {}).dialogueTurns || [])
              : {};
            // Only an actual, explicit pick from the Voice dropdown
            // overrides per-speaker alternation — manualVoice() also
            // returns a value right after populateVoiceSelect() auto-
            // preselects PREFERRED_VOICE_NAME as a nicer default than
            // "Default", which isn't something the student chose, and
            // letting it win here silently flattens every dialogue turn
            // into one voice (the bug this guard exists to prevent).
            var picked = userPickedVoice ? manualVoice() : undefined;

            function speakUnit(index) {
              if (token !== activeToken) {
                return;
              }
              if (index >= transcriptUnits.length) {
                clearTranscriptHighlight();
                resolve();
                return;
              }
              var unit = transcriptUnits[index];
              highlightTranscriptUnit(index);

              // A recorded clip for this exact unit takes priority over
              // TTS — see sentenceAudioUrls' own doc comment above.
              if (sentenceAudioUrls[index]) {
                var clip = new Audio(sentenceAudioUrls[index]);
                activeAudioEl = clip;
                var advance = function () {
                  if (activeAudioEl === clip) {
                    activeAudioEl = null;
                  }
                  speakUnit(index + 1);
                };
                clip.addEventListener('ended', advance);
                clip.addEventListener('error', advance);
                clip.play().catch(advance);
                return;
              }

              var voiceIndex = unit.speaker ? speakerVoiceIndex[unit.speaker] : undefined;
              var voice = picked || (voiceIndex !== undefined ? matchingVoices[voiceIndex] : undefined) || manualVoice();
              var utterance = new SpeechSynthesisUtterance(unit.text);
              utterance.lang = speechLang;
              utterance.rate = rate;
              if (voice) {
                utterance.voice = voice;
              }
              utterance.onend = function () { speakUnit(index + 1); };
              utterance.onerror = utterance.onend;
              window.speechSynthesis.speak(utterance);
            }

            speakUnit(startIndex || 0);
          });
        }

        function speakVocab(token) {
          var terms = Array.isArray(currentPassage.vocabTerms) ? currentPassage.vocabTerms : [];
          if (!terms.length) {
            return Promise.resolve();
          }
          if (statusEl) {
            statusEl.textContent = Drupal.t('Vocabulary…');
          }
          var picked = manualVoice();
          var rate = currentRate();
          return terms.reduce(function (chain, term) {
            return chain.then(function () {
              // A click on a transcript unit mid-vocab cancels whatever's
              // speaking and starts a new run — without this check, the
              // cancelled utterance's onend would still let this reduce
              // chain march through the remaining terms underneath it.
              if (token !== activeToken) {
                return Promise.resolve();
              }
              return speak(term, speechLang, picked, rate).then(function () {
                return wait(PAUSE_BETWEEN_VOCAB_TERMS_MS);
              });
            });
          }, Promise.resolve());
        }

        /**
         * Stops/tears down activeAudioEl (the uploaded-narration <audio>
         * element), if one is currently playing — used whenever a run is
         * superseded or the session stops outright. Does NOT touch
         * speechSynthesis; callers that need both call this alongside
         * window.speechSynthesis.cancel().
         */
        function stopActivePassageAudio() {
          if (activeAudioEl) {
            activeAudioEl.pause();
            activeAudioEl = null;
          }
        }

        /**
         * Plays currentAudioUrl (the passage's uploaded narration
         * recording) front-to-back, resolving once it ends — or as soon
         * as `token` stops matching activeToken, same superseded-run
         * contract as speakTranscript(). No per-sentence highlighting
         * here (an uploaded recording has no per-sentence timestamps to
         * sync to); highlighting only happens for the brief TTS
         * interjection jumpToUnit() plays over top when the student
         * clicks a sentence mid-playback.
         */
        function playPassageAudio(token) {
          return new Promise(function (resolve) {
            if (!currentAudioUrl) {
              resolve();
              return;
            }
            var audio = new Audio(currentAudioUrl);
            activeAudioEl = audio;

            function finish() {
              audio.removeEventListener('ended', finish);
              audio.removeEventListener('error', finish);
              if (activeAudioEl === audio) {
                activeAudioEl = null;
              }
              resolve();
            }

            // Only ever reached once this exact run's audio actually ends
            // (or errors) — a jumpToUnit()/startNewRun() interruption
            // pauses/replaces `audio` without ever firing 'ended', so
            // this doesn't need its own `token` re-check the way
            // speakTranscript()'s per-sentence onend does.
            audio.addEventListener('ended', finish);
            audio.addEventListener('error', finish);
            audio.play().catch(finish);
          });
        }

        function fetchNextPassage() {
          if (statusEl) {
            statusEl.textContent = Drupal.t('Loading next passage…');
          }
          return fetch(nextUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ deck_id: deckId, current_passage_id: currentPassage.id }),
          }).then(function (response) { return response.json(); });
        }

        /**
         * Rare defensive fallback only — not the normal end of a session
         * any more. The session loops indefinitely (ListeningApiController
         * ::passiveNext() wraps back to the first passage instead of ever
         * returning {status:'done'} while a queue exists), so this only
         * fires if the server-side queue itself is gone (e.g. the
         * tempstore entry expired from sitting idle a very long time).
         */
        function showDone() {
          if (statusEl) {
            statusEl.textContent = '';
          }
          if (stopBtn) {
            stopBtn.hidden = true;
          }
          if (doneEl) {
            doneEl.hidden = false;
          }
        }

        /**
         * Plays the current passage from transcript unit `startIndex`
         * onward, then vocabulary, fetches the next passage, and
         * recurses — same overall shape as before, except now resumable
         * mid-transcript and guarded by `token` (see activeToken's doc
         * comment above) instead of only `stopped`, so a jumpToUnit()
         * call can cleanly supersede whichever run was in flight.
         */
        function runPlayback(startIndex, token) {
          if (stopped || token !== activeToken) {
            return;
          }
          if (statusEl) {
            statusEl.textContent = Drupal.t('Playing…');
          }

          var transcriptStep = usesWholeFileAudio() ? playPassageAudio(token) : speakTranscript(startIndex, token);
          transcriptStep
            .then(function () {
              if (stopped || token !== activeToken) {
                return Promise.resolve();
              }
              return wait(PAUSE_BEFORE_VOCAB_MS).then(function () { return speakVocab(token); });
            })
            .then(function () {
              if (stopped || token !== activeToken) {
                return;
              }
              return wait(PAUSE_BEFORE_NEXT_PASSAGE_MS).then(fetchNextPassage);
            })
            .then(function (data) {
              if (stopped || token !== activeToken || !data) {
                return;
              }
              if (data.status !== 'ok' || !data.passage) {
                showDone();
                return;
              }
              renderPassage(data.passage);
              runPlayback(0, token);
            })
            .catch(function () {
              if (token !== activeToken) {
                return;
              }
              if (statusEl) {
                statusEl.textContent = Drupal.t('Something went wrong — please try starting again.');
              }
            });
        }

        /**
         * Starts a brand-new run from transcript unit `startIndex` in the
         * current passage — used both by the Start button (startIndex 0)
         * and by jumpToUnit() (whatever unit was clicked). Bumping
         * activeToken *before* cancel() matters: cancel() can fire the
         * in-flight utterance's onend synchronously in some browsers, and
         * that handler's own stale `token` must already read as stale by
         * the time it runs, or it would wrongly advance the run being
         * superseded.
         */
        function startNewRun(startIndex) {
          activeToken++;
          var token = activeToken;
          if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
          }
          stopActivePassageAudio();
          runPlayback(startIndex || 0, token);
        }

        /**
         * Click-to-jump: hear a specific transcript sentence/turn right
         * away — always via the browser's TTS voice, even when this
         * passage has an uploaded narration recording (there's no way to
         * seek that recording to a specific sentence). Three cases:
         *
         * 1. Session not running yet: doubles as "Resume/Start from
         *    here" — begins a fresh run, from the clicked sentence for a
         *    TTS-only passage or a per-sentence-audio one (each unit
         *    picks its own source — see speakUnit() above), or from the
         *    very start of the recording for a whole-file-only passage
         *    (no seeking into it).
         * 2. Whole-file recording currently playing (usesWholeFileAudio()
         *    — i.e. no per-sentence audio at all): paused in place (not
         *    stopped — no token bump, this isn't a new "run"), the
         *    clicked sentence is spoken via TTS, then the SAME recording
         *    resumes from where it paused.
         * 3. Otherwise (TTS-only or per-sentence-audio passage already
         *    running): cancels the current per-unit run and starts a
         *    fresh one at the clicked index — speakUnit() there picks
         *    that unit's own clip if it has one, TTS otherwise.
         */
        function jumpToUnit(index) {
          var unit = transcriptUnits[index];
          if (!unit) {
            return;
          }

          if (!running) {
            running = true;
            stopped = false;
            startBtn.hidden = true;
            if (stopBtn) {
              stopBtn.hidden = false;
            }
            if (startLabelEl) {
              startLabelEl.textContent = Drupal.t('Resume passive listening');
            }
            requestWakeLock();
            startNewRun(usesWholeFileAudio() ? 0 : index);
            return;
          }

          if (usesWholeFileAudio() && activeAudioEl) {
            var audioEl = activeAudioEl;
            var resumeAt = audioEl.currentTime;
            audioEl.pause();
            if (window.speechSynthesis) {
              window.speechSynthesis.cancel();
            }
            highlightTranscriptUnit(index);
            speak(unit.text, speechLang, manualVoice(), currentRate()).then(function () {
              clearTranscriptHighlight();
              if (!stopped && audioEl === activeAudioEl) {
                audioEl.currentTime = resumeAt;
                audioEl.play().catch(function () {});
              }
            });
            return;
          }

          startNewRun(index);
        }

        // Single floating tooltip element reused for every sentence/turn —
        // appended to <body> (not `section`) so its `position: fixed`
        // isn't affected by any ancestor's own transform/overflow, and
        // repositioned per call rather than recreated.
        var tooltipEl = null;
        var tooltipHideTimer = null;

        function ensureTooltipEl() {
          if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.className = 'lms-listening-passive__sentence-tooltip';
            tooltipEl.setAttribute('role', 'tooltip');
            document.body.appendChild(tooltipEl);
          }
          return tooltipEl;
        }

        function hideTranslationTooltip() {
          if (tooltipHideTimer) {
            window.clearTimeout(tooltipHideTimer);
            tooltipHideTimer = null;
          }
          if (tooltipEl) {
            tooltipEl.classList.remove('is-visible');
          }
        }

        function showTranslationTooltip(unitEl, text) {
          if (tooltipHideTimer) {
            window.clearTimeout(tooltipHideTimer);
            tooltipHideTimer = null;
          }
          var el = ensureTooltipEl();
          el.textContent = text;
          el.classList.add('is-visible');
          var rect = unitEl.getBoundingClientRect();
          var top = rect.top - el.offsetHeight - 8;
          if (top < 8) {
            top = rect.bottom + 8;
          }
          var left = Math.min(Math.max(rect.left, 8), window.innerWidth - el.offsetWidth - 8);
          el.style.top = Math.max(8, top) + 'px';
          el.style.left = Math.max(8, left) + 'px';
        }

        // Auto-hide only for a click/tap-triggered tooltip (there's no
        // 'mouseleave' on a touch device to hide it otherwise) — hover
        // already hides itself via 'mouseleave' below.
        function scheduleTooltipAutoHide() {
          if (tooltipHideTimer) {
            window.clearTimeout(tooltipHideTimer);
          }
          tooltipHideTimer = window.setTimeout(hideTranslationTooltip, 4000);
        }

        // Tap/click anywhere outside the tooltip and outside a transcript
        // unit dismisses it — the mobile equivalent of a hover tooltip's
        // 'mouseleave'.
        document.addEventListener('click', function (event) {
          if (!tooltipEl || !tooltipEl.classList.contains('is-visible')) {
            return;
          }
          if (tooltipEl.contains(event.target)) {
            return;
          }
          if (event.target.closest && event.target.closest('.lms-listening-passive__unit--clickable')) {
            return;
          }
          hideTranslationTooltip();
        });
        window.addEventListener('scroll', hideTranslationTooltip, true);

        function wireTranscriptClicks() {
          if (!ttsAvailable) {
            return;
          }
          transcriptUnits.forEach(function (unit, index) {
            unit.el.classList.add('lms-listening-passive__unit--clickable');
            unit.el.addEventListener('mouseenter', function () {
              if (translationUnits[index]) {
                showTranslationTooltip(unit.el, translationUnits[index]);
              }
            });
            unit.el.addEventListener('mouseleave', hideTranslationTooltip);
            unit.el.addEventListener('click', function () {
              jumpToUnit(index);
              if (translationUnits[index]) {
                showTranslationTooltip(unit.el, translationUnits[index]);
                scheduleTooltipAutoHide();
              }
            });
          });
        }

        // --- Quick Edit drawer (narration audio upload) -----------------
        // Same shape as lms_flashcard's own Quick Edit drawer
        // (flashcard-study.js openQuickEdit()/saveQuickEdit() etc.), kept
        // as an independent, much-smaller copy: this drawer edits exactly
        // one field (field_audio), not a card's whole content.

        var editAudioRemoveFlag = false;
        var editSentenceAudioRemoveFlag = false;
        var editDrawerCloseTimer = null;

        function showEditDrawer() {
          if (editDrawerCloseTimer) {
            window.clearTimeout(editDrawerCloseTimer);
            editDrawerCloseTimer = null;
          }
          if (editOverlayEl) {
            editOverlayEl.hidden = false;
          }
          if (editDrawerEl) {
            editDrawerEl.hidden = false;
          }
          window.requestAnimationFrame(function () {
            if (editOverlayEl) {
              editOverlayEl.classList.add('is-open');
            }
            if (editDrawerEl) {
              editDrawerEl.classList.add('is-open');
              editDrawerEl.setAttribute('aria-hidden', 'false');
            }
          });
        }

        function hideEditDrawer() {
          if (editOverlayEl) {
            editOverlayEl.classList.remove('is-open');
          }
          if (editDrawerEl) {
            editDrawerEl.classList.remove('is-open');
            editDrawerEl.setAttribute('aria-hidden', 'true');
          }
          // Actually hide (display:none) only once the closing transition
          // has had time to finish — matches the 0.3s transform transition
          // on .lms-listening-edit-drawer in listening-passive.css.
          editDrawerCloseTimer = window.setTimeout(function () {
            if (editOverlayEl) {
              editOverlayEl.hidden = true;
            }
            if (editDrawerEl) {
              editDrawerEl.hidden = true;
            }
            editDrawerCloseTimer = null;
          }, 300);
        }

        function setEditStatus(message, isError) {
          if (!editStatusEl) {
            return;
          }
          editStatusEl.hidden = !message;
          editStatusEl.textContent = message || '';
          editStatusEl.classList.toggle('is-error', !!isError);
          editStatusEl.classList.toggle('is-success', !isError && !!message);
        }

        function setEditSaving(saving) {
          quickEditSaving = saving;
          if (editSaveBtn) {
            editSaveBtn.disabled = saving;
          }
          if (editCancelBtn) {
            editCancelBtn.disabled = saving;
          }
        }

        function populateQuickEditForm(passage) {
          editAudioRemoveFlag = false;
          if (editAudioInputEl) {
            editAudioInputEl.value = '';
          }
          if (editAudioCurrentEl) {
            editAudioCurrentEl.hidden = !passage.audio_url;
          }
          if (editAudioPreviewEl && passage.audio_url) {
            editAudioPreviewEl.src = passage.audio_url;
          }

          editSentenceAudioRemoveFlag = false;
          if (editSentenceAudioInputEl) {
            editSentenceAudioInputEl.value = '';
          }
          var sentenceAudio = Array.isArray(passage.sentence_audio) ? passage.sentence_audio : [];
          if (editSentenceAudioCurrentEl) {
            editSentenceAudioCurrentEl.hidden = sentenceAudio.length === 0;
            editSentenceAudioCurrentEl.replaceChildren();
            sentenceAudio.forEach(function (item, index) {
              var li = document.createElement('li');
              var label = document.createElement('span');
              label.textContent = (index + 1) + '. ' + item.filename;
              li.appendChild(label);
              editSentenceAudioCurrentEl.appendChild(li);
            });
          }
          if (editSentenceAudioRemoveBtn) {
            editSentenceAudioRemoveBtn.hidden = sentenceAudio.length === 0;
          }
        }

        /**
         * Opens the drawer for the currently displayed passage. Populates
         * immediately from currentAudioUrl (already in memory) so the
         * drawer never looks empty, then quietly refetches in the
         * background and re-populates once that lands — guards against
         * the on-screen copy being stale.
         */
        function openQuickEdit() {
          if (!quickEditGetUrl || quickEditOpen) {
            return;
          }
          var openedForPassageId = currentPassage.id;
          quickEditOpen = true;
          setEditStatus('', false);
          setEditSaving(false);
          populateQuickEditForm({
            audio_url: currentAudioUrl,
            sentence_audio: sentenceAudioUrls.map(function (url) { return { filename: url.split('/').pop() }; }),
          });
          showEditDrawer();

          fetch(quickEditGetUrl, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (!quickEditOpen || currentPassage.id !== openedForPassageId || !data || data.status !== 'ok' || !data.passage) {
                return;
              }
              populateQuickEditForm(data.passage);
            })
            .catch(function () {
              // Keep showing the currentAudioUrl-populated form — nothing
              // to recover from here, a stale-but-present form beats none.
            });
        }

        function closeQuickEdit() {
          quickEditOpen = false;
          hideEditDrawer();
          if (quickEditBtnEl && !quickEditBtnEl.hidden) {
            quickEditBtnEl.focus();
          }
        }

        function saveQuickEdit() {
          if (quickEditSaving || !quickEditSaveUrl) {
            return;
          }

          setEditSaving(true);
          setEditStatus('', false);

          var formData = new FormData();
          if (editAudioInputEl && editAudioInputEl.files && editAudioInputEl.files[0]) {
            formData.append('audio', editAudioInputEl.files[0]);
          }
          else if (editAudioRemoveFlag) {
            formData.append('remove_audio', 'true');
          }

          if (editSentenceAudioInputEl && editSentenceAudioInputEl.files && editSentenceAudioInputEl.files.length) {
            Array.prototype.forEach.call(editSentenceAudioInputEl.files, function (file) {
              formData.append('sentence_audio[]', file);
            });
          }
          else if (editSentenceAudioRemoveFlag) {
            formData.append('remove_sentence_audio', 'true');
          }

          var savingForPassageId = currentPassage.id;
          var saveUrl = quickEditSaveUrl;
          fetch(saveUrl, { method: 'POST', body: formData, headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
            .then(function (result) {
              setEditSaving(false);
              if (!result.ok || !result.data || result.data.status !== 'ok' || !result.data.passage) {
                setEditStatus((result.data && result.data.error) || Drupal.t('Something went wrong — please try again.'), true);
                return;
              }
              // Guards against the passive queue having advanced to a
              // different passage while this save was in flight.
              if (currentPassage.id !== savingForPassageId) {
                return;
              }
              currentAudioUrl = result.data.passage.audio_url || null;
              sentenceAudioUrls = (result.data.passage.sentence_audio || []).map(function (item) { return item.url; });
              setEditStatus(Drupal.t('Saved.'), false);
              window.setTimeout(function () {
                if (quickEditOpen) {
                  closeQuickEdit();
                }
              }, 900);
            })
            .catch(function () {
              setEditSaving(false);
              setEditStatus(Drupal.t('Something went wrong — please try again.'), true);
            });
        }

        if (quickEditBtnEl) {
          quickEditBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            openQuickEdit();
          });
        }
        if (editCloseBtn) {
          editCloseBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeQuickEdit();
          });
        }
        if (editCancelBtn) {
          editCancelBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeQuickEdit();
          });
        }
        if (editOverlayEl) {
          editOverlayEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!quickEditSaving) {
              closeQuickEdit();
            }
          });
        }
        if (editSaveBtn) {
          editSaveBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            saveQuickEdit();
          });
        }
        if (editDrawerEl) {
          editDrawerEl.addEventListener('click', function (e) {
            e.stopPropagation();
          });
          editDrawerEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
            if (e.key === 'Escape' && !quickEditSaving) {
              closeQuickEdit();
            }
          });
        }
        if (editAudioRemoveBtn) {
          editAudioRemoveBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            editAudioRemoveFlag = true;
            if (editAudioInputEl) {
              editAudioInputEl.value = '';
            }
            if (editAudioCurrentEl) {
              editAudioCurrentEl.hidden = true;
            }
          });
        }
        if (editAudioInputEl) {
          editAudioInputEl.addEventListener('change', function () {
            if (editAudioInputEl.files && editAudioInputEl.files[0]) {
              editAudioRemoveFlag = false;
              if (editAudioCurrentEl) {
                editAudioCurrentEl.hidden = true;
              }
            }
          });
        }
        if (editSentenceAudioRemoveBtn) {
          editSentenceAudioRemoveBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            editSentenceAudioRemoveFlag = true;
            if (editSentenceAudioInputEl) {
              editSentenceAudioInputEl.value = '';
            }
            if (editSentenceAudioCurrentEl) {
              editSentenceAudioCurrentEl.hidden = true;
              editSentenceAudioCurrentEl.replaceChildren();
            }
            editSentenceAudioRemoveBtn.hidden = true;
          });
        }
        if (editSentenceAudioInputEl) {
          editSentenceAudioInputEl.addEventListener('change', function () {
            if (editSentenceAudioInputEl.files && editSentenceAudioInputEl.files.length) {
              editSentenceAudioRemoveFlag = false;
              if (editSentenceAudioCurrentEl) {
                editSentenceAudioCurrentEl.hidden = true;
                editSentenceAudioCurrentEl.replaceChildren();
              }
              if (editSentenceAudioRemoveBtn) {
                editSentenceAudioRemoveBtn.hidden = true;
              }
            }
          });
        }

        if (translationBtn) {
          translationBtn.addEventListener('click', function () {
            if (translationEl) {
              translationEl.hidden = false;
            }
            translationBtn.hidden = true;
          });
        }

        if (copyTranscriptBtn) {
          copyTranscriptBtn.addEventListener('click', function () {
            var lines = transcriptUnits.map(function (unit) { return unit.text; });
            writeToClipboard(lines.join('\n'))
              .then(function () { flashButtonLabel(copyTranscriptLabelEl, Drupal.t('Copied!')); })
              .catch(function () { flashButtonLabel(copyTranscriptLabelEl, Drupal.t('Copy failed')); });
          });
        }

        startBtn.addEventListener('click', function () {
          if (running || startBtn.disabled) {
            return;
          }
          running = true;
          stopped = false;
          startBtn.hidden = true;
          if (stopBtn) {
            stopBtn.hidden = false;
          }
          requestWakeLock();
          playBgMusicIfSelected();
          startNewRun(0);
        });

        if (stopBtn) {
          stopBtn.addEventListener('click', function () {
            // Pauses right here — does NOT navigate away (the "My
            // Flashcards" link already in the page header still does
            // that if the student actually wants to leave). Pressing
            // Start again (now labeled "Resume") continues the loop from
            // this same passage: currentPassage already tracks it, so
            // startNewRun() just re-enters playback from there instead of
            // restarting from the first passage — same as clicking a
            // transcript sentence directly (jumpToUnit()).
            stopped = true;
            running = false;
            activeToken++;
            releaseWakeLock();
            if (window.speechSynthesis) {
              window.speechSynthesis.cancel();
            }
            stopActivePassageAudio();
            bgAudioEl.pause();
            clearTranscriptHighlight();
            stopBtn.hidden = true;
            startBtn.hidden = false;
            if (startLabelEl) {
              startLabelEl.textContent = Drupal.t('Resume passive listening');
            }
            if (statusEl) {
              statusEl.textContent = Drupal.t('Paused — press Resume to continue.');
            }
          });
        }

        // Best-effort: stop the browser talking after the page is gone if
        // the student navigates away mid-sentence.
        window.addEventListener('pagehide', function () {
          releaseWakeLock();
          if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
          }
          stopActivePassageAudio();
          bgAudioEl.pause();
        });

        if (!window.speechSynthesis) {
          startBtn.disabled = true;
          if (statusEl) {
            statusEl.textContent = Drupal.t('Your browser doesn\'t support text-to-speech, so Passive Listening can\'t play here.');
          }
          if (voiceBarEl) {
            voiceBarEl.hidden = true;
          }
        }
        else {
          if (voiceBarEl) {
            voiceBarEl.hidden = false;
          }
          refreshVoices();
          if ('onvoiceschanged' in window.speechSynthesis) {
            window.speechSynthesis.onvoiceschanged = refreshVoices;
          }
        }

        // Show the first passage's transcript/vocabulary right away (see
        // PASSIVE_LISTENING_PLAN.md's "Open Decisions": shown plainly, not
        // gated behind Start) — only the audio itself waits for the click.
        renderPassage(currentPassage);
      });
    },
  };
})(Drupal, once);
