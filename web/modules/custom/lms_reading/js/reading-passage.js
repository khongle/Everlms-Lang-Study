/**
 * @file
 * Read step (word highlighting + click-for-info popup) and Understand step
 * (renders questions/context items from drupalSettings and submits the
 * attempt). Both live in one file since they share the same per-passage
 * session state kept in sessionStorage (clicked words, unknown words,
 * start time) — the Read step accumulates it, the Understand step reads it
 * back when submitting the attempt.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  // Deck "language" code (see Deck::field_language allowed values) mapped to
  // a BCP-47 tag for SpeechSynthesis — same table as lms_flashcard's
  // SPEECH_LANG_MAP in js/flashcard-study.js (kept as an independent copy,
  // not a shared import, so this module never reaches into that one's JS).
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

  function sessionKey(passageId) {
    return 'lmsReadingSession:' + passageId;
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
    return { clickedCards: [], clickedNotes: [], unknownTerms: [], startedAt: Date.now() };
  }

  function saveSession(passageId, session) {
    try {
      window.sessionStorage.setItem(sessionKey(passageId), JSON.stringify(session));
    }
    catch (e) {
      // Ignore — worst case the Review screen under-counts, it never blocks
      // the study flow itself.
    }
  }

  function addUnique(list, value) {
    if (list.indexOf(value) === -1) {
      list.push(value);
    }
  }

  // --- Read step: sentence tokenization + word highlighting + Listen ------

  /**
   * Splits plain text into sentence-ish chunks (each chunk keeps its own
   * trailing punctuation/whitespace, so re-joining every chunk reproduces
   * the original text exactly). Used to give the "Listen to passage"
   * feature a natural per-sentence highlight/pause unit instead of reading
   * the whole passage as one indivisible utterance.
   *
   * Deliberately simple (splits on ./!/? followed by optional closing
   * quote/paren) — it will mis-split on things like "Dr. Smith" or "3.5
   * hours"; acceptable for v1 since a wrong split only affects *where* the
   * read-aloud pauses/highlights, never the passage text itself (nothing
   * here mutates field_body).
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
   * Builds the {pattern, byLower} pair buildHighlightedFragment() needs
   * from drupalSettings.lmsReading.highlightTerms — shared by both the
   * Narrative (sentence-by-sentence) and Dialogue (turn-by-turn) highlight
   * behaviors below, so a term is matched identically regardless of which
   * one it appears in.
   */
  function buildHighlightPattern(terms) {
    var byLower = {};
    terms.forEach(function (entry) {
      byLower[entry.term.toLowerCase()] = entry;
    });

    // Longest-term-first so a multi-word term like "cough medicine" takes
    // priority over a shorter one it contains (e.g. "medicine" alone, if
    // that also happened to be a term).
    var sortedTerms = terms.slice().sort(function (a, b) {
      return b.term.length - a.term.length;
    });
    var escaped = sortedTerms.map(function (entry) {
      return entry.term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    });
    // Unicode-aware word-boundary lookaround (plain \b doesn't handle
    // accented characters well); matches nothing when there are no terms,
    // which is fine — buildHighlightedFragment() just returns the text
    // unchanged in that case.
    var pattern = new RegExp(
      escaped.length ? '(?:^|(?<=[^\\p{L}\\p{N}]))(' + escaped.join('|') + ')(?=$|[^\\p{L}\\p{N}])' : '(?!)',
      'giu',
    );
    return { pattern: pattern, byLower: byLower };
  }

  /**
   * Builds a DocumentFragment for one chunk of text with every matching
   * glossary term wrapped in a clickable `.lms-reading-passage__word` span
   * (dataset term/source/id, consumed by the word-popup behavior below) —
   * shared by every sentence chunk built in the highlight behavior.
   */
  function buildHighlightedFragment(text, pattern, byLower) {
    var fragment = document.createDocumentFragment();
    var lastIndex = 0;
    var match;
    pattern.lastIndex = 0;
    while ((match = pattern.exec(text)) !== null) {
      if (match.index > lastIndex) {
        fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
      }
      var entry = byLower[match[0].toLowerCase()];
      var span = document.createElement('span');
      span.className = 'lms-reading-passage__word';
      span.textContent = match[0];
      if (entry) {
        span.dataset.term = entry.term;
        span.dataset.source = entry.source;
        span.dataset.id = entry.id;
        span.tabIndex = 0;
        span.setAttribute('role', 'button');
      }
      fragment.appendChild(span);
      lastIndex = match.index + match[0].length;
    }
    if (lastIndex < text.length) {
      fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
    }
    return fragment;
  }

  Drupal.behaviors.lmsReadingPassageHighlight = {
    attach: function (context) {
      once('lms-reading-passage-highlight', '[data-reading-body]', context).forEach(function (body) {
        var settings = (drupalSettings.lmsReading) || {};
        var built = buildHighlightPattern(settings.highlightTerms || []);

        var text = body.textContent;
        var sentences = splitSentences(text);
        var bodyFragment = document.createDocumentFragment();
        sentences.forEach(function (sentence, index) {
          var sentenceEl = document.createElement('span');
          sentenceEl.className = 'lms-reading-passage__sentence';
          sentenceEl.dataset.sentenceIndex = index;
          sentenceEl.appendChild(buildHighlightedFragment(sentence, built.pattern, built.byLower));
          bodyFragment.appendChild(sentenceEl);
        });

        body.textContent = '';
        body.appendChild(bodyFragment);
      });
    },
  };

  /**
   * Dialogue passages: highlights glossary terms within each turn's line
   * (`.lms-reading-passage__turn-line`), without sentence-splitting — one
   * whole turn is already the right granularity, both for highlighting and
   * (see the Listen behavior below) for read-aloud pacing.
   */
  Drupal.behaviors.lmsReadingDialogueHighlight = {
    attach: function (context) {
      once('lms-reading-dialogue-highlight', '[data-reading-dialogue]', context).forEach(function (dialogue) {
        var settings = (drupalSettings.lmsReading) || {};
        var built = buildHighlightPattern(settings.highlightTerms || []);

        Array.prototype.slice.call(dialogue.querySelectorAll('[data-reading-line]')).forEach(function (lineEl) {
          var fragment = buildHighlightedFragment(lineEl.textContent, built.pattern, built.byLower);
          lineEl.textContent = '';
          lineEl.appendChild(fragment);
        });
      });
    },
  };

  /**
   * "Listen to passage": reads each speech "unit" — a
   * `.lms-reading-passage__sentence` for a Narrative passage/intro, or a
   * `.lms-reading-passage__turn` for a Dialogue one, whichever exist,
   * collected in document order from `[data-reading-content]` — aloud via
   * SpeechSynthesis (same mechanism as lms_flashcard's speakCard()/
   * speakText() fallback — see js/flashcard-study.js — kept as an
   * independent implementation rather than a shared import), highlighting
   * the unit currently being spoken so a student can follow along while
   * training listening comprehension. For a Dialogue passage with no
   * voice explicitly picked from the dropdown, distinct speakers are
   * automatically alternated across up to 2 installed voices (if the
   * browser/OS has that many for the language) so the two sides of a
   * conversation don't sound identical — see assignSpeakerVoices() below.
   * Hidden entirely on browsers without speechSynthesis rather than
   * showing a control that would silently do nothing.
   */
  /**
   * "Show translation" — one-shot reveal of field_translation's
   * server-rendered (but hidden) content, same pattern as Active
   * Listening's own Show Translation (js/listening-passage.js, an
   * independent copy). Unlike that one, this isn't gated behind a
   * "Listen first" unlock — Reading has no such prerequisite (the
   * passage text is already on the page), so the button is clickable
   * immediately. The template only renders this markup at all when the
   * passage actually has a translation, so this behavior is a no-op
   * otherwise.
   */
  Drupal.behaviors.lmsReadingTranslation = {
    attach: function (context) {
      once('lms-reading-translation', '[data-reading-translation-btn]', context).forEach(function (btn) {
        var translationEl = document.querySelector('[data-reading-translation]');
        if (!translationEl) {
          return;
        }
        btn.addEventListener('click', function () {
          translationEl.hidden = false;
          btn.hidden = true;
        });
      });
    },
  };

  Drupal.behaviors.lmsReadingPassageListen = {
    attach: function (context) {
      once('lms-reading-passage-listen', '[data-reading-listen-bar]', context).forEach(function (bar) {
        if (!window.speechSynthesis) {
          return;
        }

        var content = document.querySelector('[data-reading-content]');
        if (!content) {
          return;
        }

        var settings = (drupalSettings.lmsReading) || {};
        var speechLang = SPEECH_LANG_MAP[settings.language] || 'en-US';

        var toggleBtn = bar.querySelector('[data-reading-listen-toggle]');
        var icon = bar.querySelector('[data-reading-listen-icon]');
        var label = bar.querySelector('[data-reading-listen-label]');
        var speedSelect = bar.querySelector('[data-reading-listen-speed]');
        var voiceSelect = bar.querySelector('[data-reading-listen-voice]');
        var voiceWrapper = bar.querySelector('[data-reading-listen-voice-wrapper]');

        // Document order regardless of the mixed selector — a Dialogue
        // passage's optional intro (.lms-reading-passage__sentence, from
        // its field_body) always sits before its .lms-reading-passage__turn
        // elements in the DOM, so this naturally reads intro-then-turns.
        var unitEls = Array.prototype.slice.call(
          content.querySelectorAll('.lms-reading-passage__sentence, .lms-reading-passage__turn'),
        );
        if (!unitEls.length) {
          return;
        }

        bar.hidden = false;

        // --- Voice picker: populated from the browser/OS's own installed
        // voices, filtered to ones that speak this passage's language.
        // getVoices() can return an empty list on first call in some
        // browsers (Chrome loads them async) — 'voiceschanged' fires once
        // they're ready, so this re-populates then too. matchingVoices is
        // read from speakFrom() below via closure, not passed around.
        var matchingVoices = [];
        // speaker name -> matchingVoices index, built once matchingVoices
        // is known/changes — see assignSpeakerVoices() below.
        var speakerVoiceIndex = {};

        function populateVoices() {
          var all = window.speechSynthesis.getVoices() || [];
          var langPrefix = speechLang.split('-')[0];
          matchingVoices = all.filter(function (voice) {
            return voice.lang && voice.lang.toLowerCase().indexOf(langPrefix) === 0;
          });
          assignSpeakerVoices();
          // Nothing installed for this language (common on a fresh OS
          // install) — no point showing a picker with only "Default".
          if (!matchingVoices.length) {
            voiceWrapper.hidden = true;
            return;
          }

          voiceSelect.textContent = '';
          var defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = Drupal.t('Default');
          voiceSelect.appendChild(defaultOption);
          matchingVoices.forEach(function (voice, index) {
            var option = document.createElement('option');
            option.value = index;
            // "Google US English" / "Microsoft Aria (Natural) - English
            // (United States)" etc. — whatever label the OS/browser gives
            // it; there's no cleaner cross-browser name to show instead.
            option.textContent = voice.name + (voice.localService ? '' : ' *');
            voiceSelect.appendChild(option);
          });
          voiceWrapper.hidden = false;
        }

        /**
         * Maps each distinct speaker name (in the order they first appear)
         * to alternating voices from matchingVoices — voice 0 for the 1st
         * speaker, voice 1 for the 2nd, back to voice 0 for a 3rd, etc.
         * Only meaningful with >= 2 matching voices installed; with 0 or 1,
         * every speaker just falls back to the single/default voice, same
         * as a Narrative passage always has.
         */
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
        // True for the brief cancel()->speak() window opened by
        // restartCurrentUnit() below — the outgoing utterance's onend/
        // onerror (fired by that cancel()) must not also advance to the
        // next unit while the restart is already about to re-speak the
        // current one.
        var restarting = false;

        function setState(next) {
          state = next;
          icon.className = state === 'playing' ? 'fas fa-pause' : 'fas fa-play';
          label.textContent = state === 'playing'
            ? Drupal.t('Pause')
            : (state === 'paused' ? Drupal.t('Resume listening') : Drupal.t('Listen to passage'));
        }

        function clearHighlight() {
          unitEls.forEach(function (el) { el.classList.remove('is-speaking'); });
        }

        function highlightUnit(index) {
          clearHighlight();
          var el = unitEls[index];
          if (el) {
            el.classList.add('is-speaking');
            el.scrollIntoView({ block: 'center', behavior: 'smooth' });
          }
        }

        /**
         * The text actually spoken for a unit: a dialogue turn speaks only
         * its line (`.lms-reading-passage__turn-line`), never the visible
         * speaker-name label next to it; a narrative sentence speaks its
         * own full text.
         */
        function speechTextFor(el) {
          var lineEl = el.querySelector('.lms-reading-passage__turn-line');
          return (lineEl || el).textContent.trim();
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
          // An explicitly picked voice always wins (same accent for every
          // unit, including every speaker in a dialogue). Otherwise, a
          // dialogue turn with a known speaker gets that speaker's
          // auto-assigned voice from assignSpeakerVoices(); anything else
          // (a narrative sentence, or a speaker voice map that's empty
          // because <2 voices are installed) leaves it to the browser/OS
          // based on utterance.lang alone.
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
         * right now. Used instead of speechSynthesis.pause()/resume() when
         * the voice or speed is changed mid-playback: some engines (notably
         * Windows SAPI voices such as "Microsoft Mark") can leave an
         * in-flight utterance permanently stuck once speechSynthesis.pause()
         * has been called on it, so simply switching the <select> and
         * later clicking "Resume listening" can silently produce no audio
         * at all. Cancelling the stuck utterance and starting a fresh one
         * for the same unit sidesteps that rather than relying on
         * resume() to work.
         */
        function restartCurrentUnit() {
          if (state === 'idle') {
            // Nothing is speaking/paused — the new voice/speed simply
            // applies whenever playback is next started.
            return;
          }
          restarting = true;
          window.speechSynthesis.cancel();
          setState('playing');
          window.setTimeout(function () {
            restarting = false;
            speakFrom(currentIndex);
          }, 50);
        }

        toggleBtn.addEventListener('click', function () {
          if (state === 'idle') {
            setState('playing');
            // Same Chrome workaround as lms_flashcard's speakText(): a
            // speak() called immediately after cancel() in the same tick
            // is silently dropped.
            window.speechSynthesis.cancel();
            window.setTimeout(function () { speakFrom(currentIndex); }, 50);
          }
          else if (state === 'playing') {
            window.speechSynthesis.pause();
            setState('paused');
          }
          else {
            window.speechSynthesis.resume();
            setState('playing');
          }
        });

        // Picking a different voice or speed while a unit is
        // speaking/paused restarts that unit immediately (see
        // restartCurrentUnit() above) rather than waiting for the next
        // unit to pick it up — and rather than trusting
        // speechSynthesis.resume() to still work on the outgoing utterance.
        voiceSelect.addEventListener('change', restartCurrentUnit);
        speedSelect.addEventListener('change', restartCurrentUnit);

        // Stop reading (rather than let it keep talking over the next
        // page) when the student moves on to the questions step.
        var continueLink = document.querySelector('[data-reading-continue]');
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

  Drupal.behaviors.lmsReadingPassageWordPopup = {
    attach: function (context) {
      // [data-reading-content] rather than [data-reading-body] — covers a
      // click on a word in either the intro paragraph (Narrative, or a
      // Dialogue passage's optional intro) or inside a dialogue turn line,
      // via the delegated listener below.
      once('lms-reading-passage-word-popup', '[data-reading-content]', context).forEach(function (content) {
        var settings = (drupalSettings.lmsReading) || {};
        var passageId = settings.passageId;
        var popup = document.querySelector('[data-reading-word-popup]');
        if (!popup || !passageId) {
          return;
        }

        var termEl = popup.querySelector('[data-reading-word-popup-term]');
        var posEl = popup.querySelector('[data-reading-word-popup-pos]');
        var pronEl = popup.querySelector('[data-reading-word-popup-pron]');
        var meaningEl = popup.querySelector('[data-reading-word-popup-meaning]');
        var closeBtn = popup.querySelector('[data-reading-word-popup-close]');
        var knownBtn = popup.querySelector('[data-reading-word-known]');
        var unknownBtn = popup.querySelector('[data-reading-word-unknown]');

        var session = loadSession(passageId);
        var currentTerm = null;

        function closePopup() {
          popup.hidden = true;
          currentTerm = null;
        }

        function openPopupFor(span) {
          var term = span.dataset.term;
          var source = span.dataset.source;
          var id = span.dataset.id;
          currentTerm = term;

          if (source === 'card') {
            addUnique(session.clickedCards, parseInt(id, 10));
          }
          else {
            addUnique(session.clickedNotes, parseInt(id, 10));
          }
          saveSession(passageId, session);

          termEl.textContent = term;
          posEl.textContent = '';
          pronEl.textContent = '';
          meaningEl.textContent = Drupal.t('Loading…');
          popup.hidden = false;

          var url = settings.wordInfoUrl + '?passage=' + encodeURIComponent(passageId) + '&term=' + encodeURIComponent(term);
          fetch(url, { credentials: 'same-origin' })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (!data.found) {
                meaningEl.textContent = Drupal.t('No definition available yet.');
                return;
              }
              posEl.textContent = data.part_of_speech || '';
              pronEl.textContent = data.pronunciation || '';
              meaningEl.textContent = data.meaning || '';
            })
            .catch(function () {
              meaningEl.textContent = Drupal.t('Could not load word info.');
            });
        }

        content.addEventListener('click', function (event) {
          var span = event.target.closest('.lms-reading-passage__word');
          if (span && span.dataset.term) {
            openPopupFor(span);
          }
        });

        if (closeBtn) {
          closeBtn.addEventListener('click', closePopup);
        }

        if (knownBtn) {
          knownBtn.addEventListener('click', closePopup);
        }

        if (unknownBtn) {
          unknownBtn.addEventListener('click', function () {
            if (currentTerm) {
              addUnique(session.unknownTerms, currentTerm);
              saveSession(passageId, session);
            }
            closePopup();
          });
        }

        var continueLink = document.querySelector('[data-reading-continue]');
        if (continueLink && settings.questionsUrl) {
          continueLink.setAttribute('href', settings.questionsUrl);
        }
      });
    },
  };

  // --- Understand step: render questions/context items, submit attempt ----

  Drupal.behaviors.lmsReadingQuestions = {
    attach: function (context) {
      once('lms-reading-questions', '[data-reading-questions-form]', context).forEach(function (form) {
        var settings = (drupalSettings.lmsReading) || {};
        var passageId = settings.passageId;
        if (!passageId) {
          return;
        }

        var questionsList = form.querySelector('[data-reading-questions-list]');
        var contextList = form.querySelector('[data-reading-context-list]');

        (settings.questions || []).forEach(function (question, index) {
          var wrapper = document.createElement('div');
          wrapper.className = 'lms-reading-question';
          var heading = document.createElement('p');
          heading.className = 'lms-reading-question__text';
          heading.textContent = (index + 1) + '. ' + question.text;
          wrapper.appendChild(heading);

          if (question.type === 'multiple_choice') {
            (question.choices || []).forEach(function (choice) {
              var label = document.createElement('label');
              label.className = 'lms-reading-question__choice';
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
              label.className = 'lms-reading-question__choice';
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
            input.className = 'lms-reading-question__text-input';
            input.name = 'answer_' + question.id;
            wrapper.appendChild(input);
          }

          questionsList.appendChild(wrapper);
        });

        (settings.contextItems || []).forEach(function (item, index) {
          var wrapper = document.createElement('div');
          wrapper.className = 'lms-reading-question lms-reading-question--context';
          var heading = document.createElement('p');
          heading.className = 'lms-reading-question__text';
          heading.innerHTML = '';
          heading.appendChild(document.createTextNode(Drupal.t('Guess from context') + ' ' + (index + 1) + ': "' + item.context_sentence + '"'));
          wrapper.appendChild(heading);

          var sub = document.createElement('p');
          sub.className = 'lms-reading-question__sub';
          sub.textContent = Drupal.t('What does "@word" probably mean here?', { '@word': item.target_word });
          wrapper.appendChild(sub);

          (item.choices || []).forEach(function (choiceText, choiceIndex) {
            var label = document.createElement('label');
            label.className = 'lms-reading-question__choice';
            var input = document.createElement('input');
            input.type = 'radio';
            input.name = 'context_' + item.id;
            input.value = choiceIndex;
            label.appendChild(input);
            label.appendChild(document.createTextNode(' ' + choiceText));
            wrapper.appendChild(label);
          });

          contextList.appendChild(wrapper);
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

          var contextAnswers = {};
          (settings.contextItems || []).forEach(function (item) {
            var checked = form.querySelector('input[name="context_' + item.id + '"]:checked');
            if (checked) {
              contextAnswers[item.id] = parseInt(checked.value, 10);
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
              answers: answers,
              context_answers: contextAnswers,
              clicked_cards: session.clickedCards,
              clicked_notes: session.clickedNotes,
              unknown_terms: session.unknownTerms,
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
