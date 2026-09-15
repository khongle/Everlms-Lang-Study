/**
 * @file
 * Listen step (audio-first playback + progressive hint/transcript reveal —
 * no "Show translation" step, removed at the student's request) and
 * Comprehension step (renders questions from drupalSettings and submits the
 * attempt). Both live in one file since they share the same per-passage
 * session state kept in sessionStorage (replay count, hints used,
 * transcript shown, start time) — the Listen step accumulates it, the
 * Comprehension step reads it back when submitting the attempt. Mirrors
 * lms_reading's reading-passage.js shape exactly, kept as an independent
 * copy per this module's independence from lms_reading's own code.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  // Deck "language" code mapped to a BCP-47 tag for SpeechSynthesis — same
  // table as lms_reading's SPEECH_LANG_MAP (js/reading-passage.js) and
  // lms_flashcard's SPEECH_LANG_MAP (js/flashcard-study.js), kept as an
  // independent copy.
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
  // available (Windows' built-in SAPI voice, offered by Chrome/Edge) — the
  // student's own pick, requested after trying every voice by hand. Only a
  // substring match (not the full "Microsoft David - English (United
  // States)" label) since Windows spells this slightly differently across
  // versions (e.g. "Microsoft David Desktop - English (United States)").
  // Silently falls back to "Default" wherever this voice isn't installed
  // (macOS, Linux, mobile) — same as js/listening-passive.js's own copy of
  // this constant/logic.
  var PREFERRED_VOICE_NAME = 'Microsoft David';

  function sessionKey(passageId) {
    return 'lmsListeningSession:' + passageId;
  }

  function loadSession(passageId) {
    try {
      var raw = window.sessionStorage.getItem(sessionKey(passageId));
      if (raw) {
        return JSON.parse(raw);
      }
    }
    catch (e) {
      // Fall through to a fresh session below (private browsing, storage
      // disabled, or corrupt JSON).
    }
    return {
      replayCount: 0,
      hintsUsed: 0,
      transcriptShown: false,
      translationShown: false,
      startedAt: Date.now(),
    };
  }

  function saveSession(passageId, session) {
    try {
      window.sessionStorage.setItem(sessionKey(passageId), JSON.stringify(session));
    }
    catch (e) {
      // Ignore — worst case the submitted attempt under-counts replay/aid
      // usage, it never blocks the study flow itself.
    }
  }

  /**
   * Splits plain text into sentence-ish chunks — identical approach to
   * lms_reading's splitSentences() (js/reading-passage.js): deliberately
   * simple, a wrong split only affects *where* the read-aloud pauses, never
   * the transcript text itself.
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

  // --- Listen step: TTS playback + progressive reveal --------------------

  Drupal.behaviors.lmsListeningPassageListen = {
    attach: function (context) {
      once('lms-listening-passage-listen', '[data-listening-listen-bar]', context).forEach(function (bar) {
        var settings = (drupalSettings.lmsListening) || {};
        var passageId = settings.passageId;
        var session = loadSession(passageId);

        var content = document.querySelector('[data-listening-content]');
        var revealBar = document.querySelector('[data-listening-reveal-bar]');
        var hintBtn = revealBar ? revealBar.querySelector('[data-listening-hint-btn]') : null;
        var transcriptBtn = revealBar ? revealBar.querySelector('[data-listening-transcript-btn]') : null;
        var translationBtn = revealBar ? revealBar.querySelector('[data-listening-translation-btn]') : null;
        var translationEl = document.querySelector('[data-listening-translation]');
        var hintList = document.querySelector('[data-listening-hint-list]');
        var hints = settings.hints || [];

        // The template ships this as a plain href="#" — set upfront (rather
        // than in the TTS-only section below) so it works even on browsers
        // without speechSynthesis support, matching lms_reading's own
        // continue link.
        var continueLink = document.querySelector('[data-listening-continue]');
        if (continueLink && settings.questionsUrl) {
          continueLink.setAttribute('href', settings.questionsUrl);
        }

        // Tokenize the intro/body into per-sentence spans up front (same as
        // lms_reading's highlight behavior) so TTS can speak/highlight one
        // sentence at a time even while the whole block stays hidden.
        // Dialogue turns are already split into one .lms-listening-passage__turn
        // element per turn server-side (see the Twig template) — nothing to
        // tokenize there.
        var bodyEl = content ? content.querySelector('[data-listening-body]') : null;
        if (bodyEl && bodyEl.textContent.trim() !== '') {
          var sentences = splitSentences(bodyEl.textContent);
          var bodyFragment = document.createDocumentFragment();
          sentences.forEach(function (sentence, index) {
            var sentenceEl = document.createElement('span');
            sentenceEl.className = 'lms-listening-passage__sentence';
            sentenceEl.dataset.sentenceIndex = index;
            sentenceEl.textContent = sentence;
            bodyFragment.appendChild(sentenceEl);
          });
          bodyEl.textContent = '';
          bodyEl.appendChild(bodyFragment);
        }

        // --- Progressive reveal: locked until the student has pressed Play
        // at least once (see unlockReveal() below), and each button
        // permanently used up once clicked (Hint cycles through `hints`
        // until exhausted, then hides itself).
        var hintIndex = 0;

        function unlockReveal() {
          if (hintBtn) {
            hintBtn.disabled = false;
          }
          if (transcriptBtn) {
            transcriptBtn.disabled = false;
          }
          if (translationBtn) {
            translationBtn.disabled = false;
          }
        }

        if (hintBtn) {
          hintBtn.addEventListener('click', function () {
            if (hintIndex >= hints.length) {
              return;
            }
            var li = document.createElement('li');
            li.textContent = hints[hintIndex];
            hintList.hidden = false;
            hintList.appendChild(li);
            hintIndex++;
            session.hintsUsed = hintIndex;
            saveSession(passageId, session);
            if (hintIndex >= hints.length) {
              hintBtn.disabled = true;
              hintBtn.hidden = true;
            }
          });
        }

        if (transcriptBtn) {
          transcriptBtn.addEventListener('click', function () {
            if (content) {
              content.hidden = false;
            }
            session.transcriptShown = true;
            saveSession(passageId, session);
            transcriptBtn.disabled = true;
          });
        }

        // One-shot reveal, same as Transcript above — independent of it
        // (a student can show the translation without the transcript, or
        // vice versa). Only rendered by the template at all when the
        // passage actually has one (field_translation), so translationBtn/
        // translationEl are both simply absent otherwise.
        if (translationBtn && translationEl) {
          translationBtn.addEventListener('click', function () {
            translationEl.hidden = false;
            translationBtn.disabled = true;
            translationBtn.hidden = true;
          });
        }

        // --- TTS playback (Web Speech API) — same mechanism/robustness
        // fixes as lms_reading's "Listen to passage" (js/reading-passage.js):
        // voice/speed pickers populated from speechSynthesis.getVoices(),
        // per-speaker voice alternation for Dialogue, and a
        // restartCurrentUnit() that re-speaks the current unit from
        // scratch on a voice/speed change instead of trusting
        // speechSynthesis.pause()/resume() across it (some engines,
        // notably Windows SAPI voices, can leave a paused utterance
        // permanently stuck otherwise).
        if (!window.speechSynthesis || !content) {
          return;
        }

        var speechLang = SPEECH_LANG_MAP[settings.language] || 'en-US';

        var toggleBtn = bar.querySelector('[data-listening-listen-toggle]');
        var icon = bar.querySelector('[data-listening-listen-icon]');
        var label = bar.querySelector('[data-listening-listen-label]');
        var speedSelect = bar.querySelector('[data-listening-listen-speed]');
        var voiceSelect = bar.querySelector('[data-listening-listen-voice]');
        var voiceWrapper = bar.querySelector('[data-listening-listen-voice-wrapper]');
        var replayCountEl = bar.querySelector('[data-listening-replay-count]');

        var unitEls = Array.prototype.slice.call(
          content.querySelectorAll('.lms-listening-passage__sentence, .lms-listening-passage__turn'),
        );
        if (!unitEls.length) {
          return;
        }

        bar.hidden = false;

        var matchingVoices = [];
        var speakerVoiceIndex = {};
        // Flips true the moment the student touches the dropdown themself
        // (see the voiceSelect 'change' listener below) — after that, a
        // voice-list refresh (onvoiceschanged can fire more than once)
        // preserves their pick instead of snapping back to the preferred
        // default underneath them.
        var userPickedVoice = false;

        function populateVoices() {
          var all = window.speechSynthesis.getVoices() || [];
          var langPrefix = speechLang.split('-')[0];
          matchingVoices = all.filter(function (voice) {
            return voice.lang && voice.lang.toLowerCase().indexOf(langPrefix) === 0;
          });
          assignSpeakerVoices();
          if (!matchingVoices.length) {
            voiceWrapper.hidden = true;
            return;
          }

          var previousValue = voiceSelect.value;
          voiceSelect.textContent = '';
          var defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = Drupal.t('Default');
          voiceSelect.appendChild(defaultOption);
          var preferredIndex = -1;
          matchingVoices.forEach(function (voice, index) {
            var option = document.createElement('option');
            option.value = index;
            option.textContent = voice.name + (voice.localService ? '' : ' *');
            voiceSelect.appendChild(option);
            if (preferredIndex === -1 && voice.name.indexOf(PREFERRED_VOICE_NAME) !== -1) {
              preferredIndex = index;
            }
          });

          if (userPickedVoice && previousValue !== '' && matchingVoices[previousValue]) {
            voiceSelect.value = previousValue;
          }
          else if (preferredIndex !== -1) {
            voiceSelect.value = String(preferredIndex);
          }
          voiceWrapper.hidden = false;
        }

        function assignSpeakerVoices() {
          speakerVoiceIndex = {};
          if (matchingVoices.length < 2) {
            return;
          }
          var seen = [];
          unitEls.forEach(function (el) {
            var speaker = el.dataset.speaker;
            if (speaker && seen.indexOf(speaker) === -1) {
              seen.push(speaker);
              speakerVoiceIndex[speaker] = (seen.length - 1) % matchingVoices.length;
            }
          });
        }

        populateVoices();
        if ('onvoiceschanged' in window.speechSynthesis) {
          window.speechSynthesis.onvoiceschanged = populateVoices;
        }

        var state = 'idle'; // 'idle' | 'playing' | 'paused'
        var currentIndex = 0;
        var hasPlayedOnce = false;
        var restarting = false;
        // True right after a clicked line finishes speaking and playback
        // auto-stops there (see playFromUnit()/speakOneUnit() below) — as
        // opposed to a genuine mid-utterance Pause. Both show the same
        // "Resume listening" button/state, but they resume differently:
        // a real pause hands off to the browser's native
        // speechSynthesis.resume() (continuing mid-sentence), while this
        // one re-speaks that same line from its start, then carries on
        // through the rest as normal — see toggleBtn's 'paused' branch.
        var lineStopped = false;

        function setState(next) {
          state = next;
          icon.className = state === 'playing' ? 'fas fa-pause' : 'fas fa-play';
          label.textContent = state === 'playing'
            ? Drupal.t('Pause')
            : (state === 'paused' ? Drupal.t('Resume listening') : Drupal.t('Listen'));
        }

        function clearHighlight() {
          unitEls.forEach(function (el) { el.classList.remove('is-speaking'); });
        }

        function highlightUnit(index) {
          clearHighlight();
          var el = unitEls[index];
          if (el) {
            el.classList.add('is-speaking');
            // Only meaningful once the transcript is revealed (`content`
            // is visible) — scrollIntoView() on a hidden element is a
            // harmless no-op otherwise.
            el.scrollIntoView({ block: 'center', behavior: 'smooth' });
          }
        }

        function speechTextFor(el) {
          var lineEl = el.querySelector('.lms-listening-passage__turn-line');
          return (lineEl || el).textContent.trim();
        }

        function updateReplayCountDisplay() {
          if (!replayCountEl) {
            return;
          }
          if (session.replayCount > 0) {
            replayCountEl.hidden = false;
            replayCountEl.textContent = Drupal.t('Replayed @count time(s)', { '@count': session.replayCount });
          }
        }

        function speakFrom(index) {
          if (index >= unitEls.length) {
            currentIndex = 0;
            clearHighlight();
            setState('idle');
            return;
          }
          currentIndex = index;
          var el = unitEls[index];
          highlightUnit(index);

          var utterance = new SpeechSynthesisUtterance(speechTextFor(el));
          utterance.lang = speechLang;
          utterance.rate = parseFloat(speedSelect.value) || 1;
          if (voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
          }
          else if (el.dataset.speaker && speakerVoiceIndex[el.dataset.speaker] !== undefined) {
            utterance.voice = matchingVoices[speakerVoiceIndex[el.dataset.speaker]];
          }
          utterance.onend = function () {
            if (!restarting && state === 'playing') {
              speakFrom(index + 1);
            }
          };
          utterance.onerror = function () {
            if (!restarting && state === 'playing') {
              speakFrom(index + 1);
            }
          };
          window.speechSynthesis.speak(utterance);
        }

        /**
         * Re-speaks the current unit with whatever voice/rate is selected
         * right now — see lms_reading's reading-passage.js
         * restartCurrentUnit() for the full rationale (Windows SAPI voices
         * can leave a paused utterance permanently stuck across a plain
         * pause()/resume()).
         */
        function restartCurrentUnit() {
          if (state === 'idle') {
            return;
          }
          lineStopped = false;
          restarting = true;
          window.speechSynthesis.cancel();
          setState('playing');
          window.setTimeout(function () {
            restarting = false;
            speakFrom(currentIndex);
          }, 50);
        }

        /**
         * Speaks exactly one unit and stops there — unlike speakFrom(),
         * its onend/onerror never chains to index + 1. Used only for the
         * "click a line to hear it" flow below: the student asked to hear
         * that one line, not to be swept on into the rest of the passage
         * right after it. Leaves state as 'paused' (not 'idle') once it
         * finishes, so the button reads "Resume listening" — clicking it
         * re-speaks this same line from the top, then continues normally
         * from there (see toggleBtn's 'paused' branch and the
         * `lineStopped` flag above).
         */
        function speakOneUnit(index) {
          currentIndex = index;
          var el = unitEls[index];
          highlightUnit(index);

          var utterance = new SpeechSynthesisUtterance(speechTextFor(el));
          utterance.lang = speechLang;
          utterance.rate = parseFloat(speedSelect.value) || 1;
          if (voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
          }
          else if (el.dataset.speaker && speakerVoiceIndex[el.dataset.speaker] !== undefined) {
            utterance.voice = matchingVoices[speakerVoiceIndex[el.dataset.speaker]];
          }
          utterance.onend = function () {
            if (!restarting) {
              lineStopped = true;
              setState('paused');
            }
          };
          utterance.onerror = utterance.onend;
          window.speechSynthesis.speak(utterance);
        }

        /**
         * Click a sentence/turn to hear just that line — jumps straight
         * there, speaks only it (speakOneUnit(), not the speakFrom()
         * chain the Play button starts), then stops. Counts as a replay
         * the same way pressing Play again from idle already does, so
         * the Review screen's "replayed N times" stays a meaningful
         * signal either way the student asks to hear something again.
         */
        function playFromUnit(index) {
          session.replayCount++;
          saveSession(passageId, session);
          updateReplayCountDisplay();
          hasPlayedOnce = true;
          unlockReveal();

          restarting = true;
          window.speechSynthesis.cancel();
          setState('playing');
          window.setTimeout(function () {
            restarting = false;
            speakOneUnit(index);
          }, 50);
        }

        unitEls.forEach(function (el, index) {
          el.classList.add('lms-listening-passage__unit--clickable');
          el.addEventListener('click', function (event) {
            event.stopPropagation();
            playFromUnit(index);
          });
        });

        toggleBtn.addEventListener('click', function () {
          if (state === 'idle') {
            if (hasPlayedOnce) {
              session.replayCount++;
              saveSession(passageId, session);
              updateReplayCountDisplay();
            }
            hasPlayedOnce = true;
            unlockReveal();

            lineStopped = false;
            setState('playing');
            // Same Chrome workaround as lms_reading's toggle handler: a
            // speak() called immediately after cancel() in the same tick
            // is silently dropped.
            window.speechSynthesis.cancel();
            window.setTimeout(function () { speakFrom(currentIndex); }, 50);
          }
          else if (state === 'playing') {
            window.speechSynthesis.pause();
            setState('paused');
          }
          else if (lineStopped) {
            // Resuming after a clicked line auto-stopped, not after a
            // genuine mid-utterance Pause — nothing is actually paused
            // in the synthesis queue to hand back to (the utterance
            // already ended), so replay that same line from the top and
            // let it carry on into the rest of the passage as normal.
            lineStopped = false;
            restarting = true;
            window.speechSynthesis.cancel();
            setState('playing');
            window.setTimeout(function () {
              restarting = false;
              speakFrom(currentIndex);
            }, 50);
          }
          else {
            window.speechSynthesis.resume();
            setState('playing');
          }
        });

        voiceSelect.addEventListener('change', function () {
          userPickedVoice = true;
          restartCurrentUnit();
        });
        speedSelect.addEventListener('change', restartCurrentUnit);

        if (continueLink) {
          continueLink.addEventListener('click', function () {
            window.speechSynthesis.cancel();
          });
        }
        window.addEventListener('beforeunload', function () {
          window.speechSynthesis.cancel();
        });
      });
    },
  };

  // --- Comprehension step: render questions, submit attempt --------------

  Drupal.behaviors.lmsListeningQuestions = {
    attach: function (context) {
      once('lms-listening-questions', '[data-listening-questions-form]', context).forEach(function (form) {
        var settings = (drupalSettings.lmsListening) || {};
        var passageId = settings.passageId;
        if (!passageId) {
          return;
        }

        var questionsList = form.querySelector('[data-listening-questions-list]');

        (settings.questions || []).forEach(function (question, index) {
          var wrapper = document.createElement('div');
          wrapper.className = 'lms-listening-question';
          var heading = document.createElement('p');
          heading.className = 'lms-listening-question__text';
          heading.textContent = (index + 1) + '. ' + question.text;
          wrapper.appendChild(heading);

          if (question.type === 'multiple_choice') {
            (question.choices || []).forEach(function (choice) {
              var label = document.createElement('label');
              label.className = 'lms-listening-question__choice';
              var input = document.createElement('input');
              input.type = 'radio';
              input.name = 'answer_' + question.id;
              input.value = choice.id;
              label.appendChild(input);
              label.appendChild(document.createTextNode(' ' + choice.text));
              wrapper.appendChild(label);
            });
          }
          else if (question.type === 'true_false') {
            [['true', Drupal.t('True')], ['false', Drupal.t('False')]].forEach(function (pair) {
              var label = document.createElement('label');
              label.className = 'lms-listening-question__choice';
              var input = document.createElement('input');
              input.type = 'radio';
              input.name = 'answer_' + question.id;
              input.value = pair[0];
              label.appendChild(input);
              label.appendChild(document.createTextNode(' ' + pair[1]));
              wrapper.appendChild(label);
            });
          }
          else {
            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'lms-listening-question__text-input';
            input.name = 'answer_' + question.id;
            wrapper.appendChild(input);
          }

          questionsList.appendChild(wrapper);
        });

        form.addEventListener('submit', function (event) {
          event.preventDefault();

          var answers = {};
          (settings.questions || []).forEach(function (question) {
            var checked = form.querySelector('input[name="answer_' + question.id + '"]:checked');
            var textInput = form.querySelector('input[name="answer_' + question.id + '"][type="text"]');
            if (question.type === 'multiple_choice' && checked) {
              answers[question.id] = parseInt(checked.value, 10);
            }
            else if (question.type === 'true_false' && checked) {
              answers[question.id] = checked.value === 'true';
            }
            else if (textInput) {
              answers[question.id] = textInput.value;
            }
          });

          var session = loadSession(passageId);
          var durationSeconds = Math.max(0, Math.round((Date.now() - (session.startedAt || Date.now())) / 1000));

          fetch(settings.submitUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              passage_id: passageId,
              duration_seconds: durationSeconds,
              replay_count: session.replayCount || 0,
              hints_used: session.hintsUsed || 0,
              transcript_shown: !!session.transcriptShown,
              translation_shown: !!session.translationShown,
              answers: answers,
            }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (data.review_url) {
                try {
                  window.sessionStorage.removeItem(sessionKey(passageId));
                }
                catch (e) {
                  // Ignore.
                }
                window.location.href = data.review_url;
              }
            });
        });
      });
    },
  };

})(Drupal, drupalSettings, once);
