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
  // copy (Active mode): the student's own pick, a substring match since
  // Windows spells this voice's name slightly differently across
  // versions. Silently falls back to "Default" wherever it isn't
  // installed.
  var PREFERRED_VOICE_NAME = 'Microsoft David';

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

        var currentPassage = settings.passage;
        var firstPassageId = settings.passage.id;
        var passageNumber = 0;
        var lapCount = 1;
        var stopped = false;
        var running = false;

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
         * Fills the Voice <select> from matchingVoices — same shape as
         * js/listening-passage.js's own populateVoices() (Active mode):
         * a "Default" option (value "") plus one per matching voice,
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
          // PREFERRED_VOICE_NAME when it's available.
          var previousValue = voiceSelectEl.value;
          voiceSelectEl.textContent = '';
          var defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = Drupal.t('Default');
          voiceSelectEl.appendChild(defaultOption);
          var preferredIndex = -1;
          matchingVoices.forEach(function (voice, index) {
            var option = document.createElement('option');
            option.value = index;
            option.textContent = voice.name + (voice.localService ? '' : ' *');
            voiceSelectEl.appendChild(option);
            if (preferredIndex === -1 && voice.name.indexOf(PREFERRED_VOICE_NAME) !== -1) {
              preferredIndex = index;
            }
          });
          if (userPickedVoice && previousValue !== '' && matchingVoices[previousValue]) {
            voiceSelectEl.value = previousValue;
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
              });
            }
            else if (t.body) {
              var body = document.createElement('p');
              body.textContent = t.body;
              transcriptEl.appendChild(body);
            }
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
            translationEl.textContent = passage.translation || '';
          }
          if (translationBtn) {
            translationBtn.hidden = !passage.translation;
          }
        }

        function speakTranscript(passage) {
          var t = passage.transcript || {};
          var picked = manualVoice();
          var rate = currentRate();
          if (t.format === 'dialogue' && Array.isArray(t.dialogueTurns) && t.dialogueTurns.length) {
            // getVoices() can still be empty on the very first call in some
            // browsers (they load the voice list asynchronously) — refresh
            // right before assigning, not just once at attach time.
            refreshVoices();
            var speakerVoiceIndex = assignSpeakerVoices(t.dialogueTurns);
            return t.dialogueTurns.reduce(function (chain, turn) {
              return chain.then(function () {
                // An explicit pick from the Voice dropdown wins outright
                // (same "Default" vs. a named voice) — otherwise this
                // turn's speaker gets its own alternating voice, same as
                // js/listening-passage.js's Active mode.
                var voiceIndex = turn.speaker ? speakerVoiceIndex[turn.speaker] : undefined;
                var voice = picked || (voiceIndex !== undefined ? matchingVoices[voiceIndex] : undefined);
                return speak(turn.line, speechLang, voice, rate);
              });
            }, Promise.resolve());
          }
          return speak(t.body, speechLang, picked, rate);
        }

        function speakVocab(passage) {
          var terms = Array.isArray(passage.vocabTerms) ? passage.vocabTerms : [];
          if (!terms.length) {
            return Promise.resolve();
          }
          if (statusEl) {
            statusEl.textContent = Drupal.t('Vocabulary…');
          }
          var picked = manualVoice();
          var rate = currentRate();
          return terms.reduce(function (chain, term) {
            return chain
              .then(function () { return speak(term, speechLang, picked, rate); })
              .then(function () { return wait(PAUSE_BETWEEN_VOCAB_TERMS_MS); });
          }, Promise.resolve());
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
         * Plays the current passage (transcript, then vocabulary), fetches
         * the next one, and recurses — the whole 3-passage sequence is one
         * Promise chain rooted in the original Start click, never a fresh
         * page load. Stops cleanly (mid-chain) the moment `stopped` flips,
         * via each step's own guard rather than throwing/rejecting.
         */
        function playCurrentThenAdvance() {
          if (stopped) {
            return;
          }
          if (statusEl) {
            statusEl.textContent = Drupal.t('Playing…');
          }

          speakTranscript(currentPassage)
            .then(function () {
              if (stopped) {
                return Promise.resolve();
              }
              return wait(PAUSE_BEFORE_VOCAB_MS).then(function () { return speakVocab(currentPassage); });
            })
            .then(function () {
              if (stopped) {
                return;
              }
              return wait(PAUSE_BEFORE_NEXT_PASSAGE_MS).then(fetchNextPassage);
            })
            .then(function (data) {
              if (stopped || !data) {
                return;
              }
              if (data.status !== 'ok' || !data.passage) {
                showDone();
                return;
              }
              renderPassage(data.passage);
              playCurrentThenAdvance();
            })
            .catch(function () {
              if (statusEl) {
                statusEl.textContent = Drupal.t('Something went wrong — please try starting again.');
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
          playCurrentThenAdvance();
        });

        if (stopBtn) {
          stopBtn.addEventListener('click', function () {
            // Pauses right here — does NOT navigate away (the "My
            // Flashcards" link already in the page header still does
            // that if the student actually wants to leave). Pressing
            // Start again (now labeled "Resume") continues the loop from
            // this same passage: currentPassage already tracks it, so
            // playCurrentThenAdvance() just re-enters its own Promise
            // chain from there instead of restarting from the first
            // passage.
            stopped = true;
            running = false;
            if (window.speechSynthesis) {
              window.speechSynthesis.cancel();
            }
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
          if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
          }
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
