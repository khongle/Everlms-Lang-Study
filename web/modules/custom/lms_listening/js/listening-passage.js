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
  // available — the student's own pick (a Google TTS male voice — sounds
  // noticeably less harsh/robotic than Windows' own SAPI voices). Only
  // Chrome/Edge's online "Google …" voices carry this name; other
  // browsers/OSes don't have it, so this silently falls back to "Default"
  // wherever it isn't installed. Same constant/logic as
  // js/listening-passive.js's own copy — see its findPreferredVoiceIndex()
  // for how a language without a gendered Google voice set is handled.
  var PREFERRED_VOICE_NAME = 'Google';

  /**
   * Index into `voices` of the best Google-voice match — a "Google …
   * Male" voice if one exists, else any other "Google" voice (some
   * languages ship as a single ungendered voice), else -1 (falls back to
   * "Default"). Independent copy of js/listening-passive.js's own
   * findPreferredVoiceIndex().
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

  /**
   * Writes `text` to the clipboard — navigator.clipboard.writeText() where
   * available (secure context), falling back to a temporary off-screen
   * textarea + document.execCommand('copy') otherwise (older browsers, or
   * plain http:// in dev). Returns a Promise resolving/rejecting either
   * way, so callers can show a "Copied!"/"Copy failed" status uniformly.
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
   * original text after a short delay — shared feedback pattern for both
   * this file's and js/listening-passive.js's own "Copy transcript"
   * button.
   */
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

  /**
   * Splits a dialogue passage's whole-block translation text into one
   * {speaker, line} row per turn, by locating each occurrence of one of
   * `dialogueTurns`' own speaker names followed by ":" — the translator
   * is expected to keep those same (untranslated) speaker labels in the
   * translation text (e.g. "Mia: ... Ben: ..."). Returns null (render as
   * one plain block, same as before this existed) when there's no
   * dialogue, or the text doesn't actually contain any recognizable
   * "Speaker:" labels — never throws, never guesses at a split that
   * might be wrong. Independent copy of js/listening-passive.js's own
   * splitDialogueTranslation().
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
   * Fills `container` with `text` — one line per speaker turn (matching
   * the English transcript's own layout) when splitDialogueTranslation()
   * can find them, otherwise the plain text as a single block like
   * before this existed. Independent copy of js/listening-passive.js's
   * own renderTranslationBlock().
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

        // Reformat the server-rendered translation block into one line
        // per speaker turn, matching the English transcript's own layout
        // (see splitDialogueTranslation()/renderTranslationBlock() above)
        // — done once up front, independent of TTS availability, since
        // Twig only ever renders it as a single plain-text block
        // (lms-listening-passage-view.html.twig has no per-turn markup
        // for the translation the way it does for the transcript itself).
        if (translationEl) {
          var turnSpeakerEls = content ? content.querySelectorAll('[data-speaker]') : [];
          var dialogueTurnsForTranslation = Array.prototype.map.call(turnSpeakerEls, function (el) {
            return { speaker: el.dataset.speaker };
          });
          renderTranslationBlock(
            translationEl,
            translationEl.textContent,
            dialogueTurnsForTranslation,
            'lms-listening-passage__translation-line',
          );
        }

        // "Copy transcript (no names)" — content-creation aid (see
        // Quick Edit below), not a student-facing control: only shown to
        // whoever can Quick Edit this passage. Extracts each
        // sentence/turn's plain text (no "Speaker: " prefix) from the DOM
        // directly rather than waiting on unitEls (built later, only once
        // TTS is available) — so it still works even without TTS support.
        var copyTranscriptBtn = document.querySelector('[data-listening-copy-transcript]');
        var copyTranscriptLabelEl = document.querySelector('[data-listening-copy-label]');
        if (copyTranscriptBtn) {
          copyTranscriptBtn.hidden = !settings.canQuickEdit;
          copyTranscriptBtn.addEventListener('click', function () {
            if (!content) {
              return;
            }
            var units = content.querySelectorAll('.lms-listening-passage__sentence, .lms-listening-passage__turn');
            var lines = Array.prototype.map.call(units, function (el) {
              var lineEl = el.querySelector('.lms-listening-passage__turn-line');
              return (lineEl || el).textContent.trim();
            }).filter(function (line) { return line !== ''; });
            writeToClipboard(lines.join('\n'))
              .then(function () { flashButtonLabel(copyTranscriptLabelEl, Drupal.t('Copied!')); })
              .catch(function () { flashButtonLabel(copyTranscriptLabelEl, Drupal.t('Copy failed')); });
          });
        }

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

        // --- Quick Edit drawer (narration audio upload) ------------------
        // Wired before the TTS-availability guard below so it still works
        // in a browser without speechSynthesis — uploading/removing the
        // recording doesn't itself need TTS, even though full playback
        // control still does today (see that guard's own comment). Same
        // shape as js/listening-passive.js's own copy, and ultimately
        // lms_flashcard's Quick Edit drawer — kept independent since this
        // one edits exactly one field (field_audio), not a card's whole
        // content, and this page has exactly one passage rather than a
        // looping queue.
        var currentAudioUrl = settings.audioUrl || null;
        // sentenceAudioUrls[i] is the recorded-clip URL to use for
        // unitEls[i], or undefined — best-effort index pairing against
        // upload order (see ListeningPresentationService::
        // buildSentenceAudioUrls()'s docblock). Non-empty takes priority
        // over currentAudioUrl for the Play button's continuous playback
        // — see usesWholeFileAudio() further below, near activeAudioEl.
        var sentenceAudioUrls = settings.sentenceAudioUrls || [];
        var quickEditBtnEl = document.querySelector('[data-listening-quick-edit]');
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
        var quickEditOpen = false;
        var quickEditSaving = false;
        var editAudioRemoveFlag = false;
        var editSentenceAudioRemoveFlag = false;
        var editDrawerCloseTimer = null;

        if (quickEditBtnEl) {
          quickEditBtnEl.hidden = !settings.canQuickEdit;
        }

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

        function populateQuickEditForm(data) {
          editAudioRemoveFlag = false;
          if (editAudioInputEl) {
            editAudioInputEl.value = '';
          }
          if (editAudioCurrentEl) {
            editAudioCurrentEl.hidden = !data.audio_url;
          }
          if (editAudioPreviewEl && data.audio_url) {
            editAudioPreviewEl.src = data.audio_url;
          }

          editSentenceAudioRemoveFlag = false;
          if (editSentenceAudioInputEl) {
            editSentenceAudioInputEl.value = '';
          }
          var sentenceAudio = Array.isArray(data.sentence_audio) ? data.sentence_audio : [];
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

        function openQuickEdit() {
          if (!settings.quickEditGetUrl || quickEditOpen) {
            return;
          }
          quickEditOpen = true;
          setEditStatus('', false);
          setEditSaving(false);
          populateQuickEditForm({
            audio_url: currentAudioUrl,
            sentence_audio: sentenceAudioUrls.map(function (url) { return { filename: url.split('/').pop() }; }),
          });
          showEditDrawer();

          fetch(settings.quickEditGetUrl, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (!quickEditOpen || !data || data.status !== 'ok' || !data.passage) {
                return;
              }
              populateQuickEditForm(data.passage);
            })
            .catch(function () {
              // Keep showing the currentAudioUrl-populated form.
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
          if (quickEditSaving || !settings.quickEditSaveUrl) {
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

          fetch(settings.quickEditSaveUrl, { method: 'POST', body: formData, headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
            .then(function (result) {
              setEditSaving(false);
              if (!result.ok || !result.data || result.data.status !== 'ok' || !result.data.passage) {
                setEditStatus((result.data && result.data.error) || Drupal.t('Something went wrong — please try again.'), true);
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

        /**
         * Whether this passage has any speaker-tagged turn — i.e. whether
         * speakFrom()/speakOneUnit() will alternate per-speaker voices
         * rather than speak everything in one. Drives the Voice dropdown's
         * default label/selection just below, so it doesn't visually claim
         * a single named voice while two are actually alternating.
         */
        function isDialoguePassage() {
          return unitEls.some(function (el) { return !!el.dataset.speaker; });
        }

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

          // Preserve whatever the student already picked across a refresh;
          // otherwise default to PREFERRED_VOICE_NAME — except for a
          // Dialogue passage, where speakFrom()/speakOneUnit() alternate a
          // voice per speaker instead of using any single one, so
          // pre-selecting PREFERRED_VOICE_NAME here would show one named
          // voice while two are actually heard. Label that state "Auto"
          // and leave the dropdown on it instead.
          var dialogue = isDialoguePassage();
          var previousValue = voiceSelect.value;
          voiceSelect.textContent = '';
          var defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = dialogue ? Drupal.t('Auto (alternating voices)') : Drupal.t('Default');
          voiceSelect.appendChild(defaultOption);
          matchingVoices.forEach(function (voice, index) {
            var option = document.createElement('option');
            option.value = index;
            option.textContent = voice.name + (voice.localService ? '' : ' *');
            voiceSelect.appendChild(option);
          });
          var preferredIndex = findPreferredVoiceIndex(matchingVoices);

          if (userPickedVoice && previousValue !== '' && matchingVoices[previousValue]) {
            voiceSelect.value = previousValue;
          }
          else if (dialogue) {
            voiceSelect.value = '';
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

        // Holds a Screen Wake Lock while state === 'playing' so mobile
        // browsers don't turn the screen off mid-playback — once the
        // screen actually turns off, speechSynthesis stops firing its
        // callbacks (utterance.onend never runs) and playback silently
        // stalls until the student manually unlocks the phone again.
        // Unsupported browsers (no navigator.wakeLock) simply keep
        // today's behavior.
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
        // goes to the background/hidden (which also happens right as the
        // screen locks) — re-acquire it as soon as the page is visible
        // again while still playing.
        document.addEventListener('visibilitychange', function () {
          if (document.visibilityState === 'visible' && state === 'playing' && !wakeLock) {
            requestWakeLock();
          }
        });

        // The <audio> element currently playing — either currentAudioUrl
        // (whole-file mode, via playWholeAudio()) or one unit's own
        // recorded clip (per-sentence mode, via speakFrom()/speakOneUnit()
        // below) — or null. Same pause-in-place-for-a-click-interjection
        // design as js/listening-passive.js's own copy.
        var activeAudioEl = null;

        /**
         * Whole-passage audio is only actually used for the Play button's
         * continuous playback when there's no per-sentence audio at all —
         * otherwise speakFrom() (mixing recorded clips and TTS per unit)
         * takes over entirely. See the toggleBtn/playFromUnit/
         * restartCurrentUnit branches below, all of which check this.
         */
        function usesWholeFileAudio() {
          return !!currentAudioUrl && sentenceAudioUrls.length === 0;
        }

        function stopActivePassageAudio() {
          if (activeAudioEl) {
            activeAudioEl.pause();
            activeAudioEl = null;
          }
        }

        function playWholeAudio() {
          var audio = new Audio(currentAudioUrl);
          activeAudioEl = audio;
          function onEnd() {
            if (activeAudioEl === audio) {
              activeAudioEl = null;
            }
            currentIndex = 0;
            setState('idle');
          }
          audio.addEventListener('ended', onEnd);
          audio.addEventListener('error', onEnd);
          audio.play().catch(onEnd);
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
          if (state === 'playing') {
            requestWakeLock();
          }
          else {
            releaseWakeLock();
          }
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

          // A recorded clip for this exact unit takes priority over TTS —
          // see sentenceAudioUrls' own doc comment above.
          if (sentenceAudioUrls[index]) {
            var clip = new Audio(sentenceAudioUrls[index]);
            activeAudioEl = clip;
            var advance = function () {
              if (activeAudioEl === clip) {
                activeAudioEl = null;
              }
              if (!restarting && state === 'playing') {
                speakFrom(index + 1);
              }
            };
            clip.addEventListener('ended', advance);
            clip.addEventListener('error', advance);
            clip.play().catch(advance);
            return;
          }

          var utterance = new SpeechSynthesisUtterance(speechTextFor(el));
          utterance.lang = speechLang;
          utterance.rate = parseFloat(speedSelect.value) || 1;
          // Only an actual, explicit pick from the Voice dropdown overrides
          // per-speaker alternation. `voiceSelect.value` is also non-empty
          // right after populateVoices() auto-preselects PREFERRED_VOICE_NAME
          // as a nicer default than the browser's own "Default" — that's not
          // something the student chose, and letting it win here silently
          // flattens every dialogue turn into one voice.
          if (userPickedVoice && voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
          }
          else if (el.dataset.speaker && speakerVoiceIndex[el.dataset.speaker] !== undefined) {
            utterance.voice = matchingVoices[speakerVoiceIndex[el.dataset.speaker]];
          }
          else if (voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
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
          // A voice/speed change only affects future TTS — either a
          // click-to-hear-one-line interjection, or the per-unit chain
          // below (which itself only falls back to TTS for units with no
          // recorded clip of their own). It never applies to a
          // whole-file-only recording already playing as one file, so
          // there's nothing to restart here for that case.
          if (usesWholeFileAudio() || state === 'idle') {
            return;
          }
          lineStopped = false;
          restarting = true;
          window.speechSynthesis.cancel();
          stopActivePassageAudio();
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

          // A recorded clip for this exact unit takes priority over TTS —
          // per the user's own "click always uses that sentence's own
          // clip when it has one" decision.
          if (sentenceAudioUrls[index]) {
            var clip = new Audio(sentenceAudioUrls[index]);
            activeAudioEl = clip;
            var onClipEnd = function () {
              if (activeAudioEl === clip) {
                activeAudioEl = null;
              }
              if (!restarting) {
                lineStopped = true;
                setState('paused');
              }
            };
            clip.addEventListener('ended', onClipEnd);
            clip.addEventListener('error', onClipEnd);
            clip.play().catch(onClipEnd);
            return;
          }

          var utterance = new SpeechSynthesisUtterance(speechTextFor(el));
          utterance.lang = speechLang;
          utterance.rate = parseFloat(speedSelect.value) || 1;
          // Only an actual, explicit pick from the Voice dropdown overrides
          // per-speaker alternation. `voiceSelect.value` is also non-empty
          // right after populateVoices() auto-preselects PREFERRED_VOICE_NAME
          // as a nicer default than the browser's own "Default" — that's not
          // something the student chose, and letting it win here silently
          // flattens every dialogue turn into one voice.
          if (userPickedVoice && voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
          }
          else if (el.dataset.speaker && speakerVoiceIndex[el.dataset.speaker] !== undefined) {
            utterance.voice = matchingVoices[speakerVoiceIndex[el.dataset.speaker]];
          }
          else if (voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
            utterance.voice = matchingVoices[voiceSelect.value];
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

          // With a whole-file-only recording (no per-sentence audio at
          // all), a clicked sentence/turn always plays via TTS instead
          // (there's no way to seek the recording to a specific sentence)
          // — pausing the recording in place (not stopping it) and
          // resuming it from the same spot once the TTS interjection
          // ends, rather than the TTS-only "stays paused, click Resume to
          // replay this line" behavior below. When per-sentence audio
          // exists instead, this branch is skipped entirely — the
          // fallthrough below calls speakOneUnit(), which plays that
          // unit's own clip when it has one (see its own branch there).
          if (usesWholeFileAudio()) {
            var audioEl = activeAudioEl;
            var resumeAt = audioEl ? audioEl.currentTime : 0;
            var wasPlaying = state === 'playing' && !!audioEl;
            if (audioEl) {
              audioEl.pause();
            }
            window.speechSynthesis.cancel();
            highlightUnit(index);
            setState('playing');

            var interjection = new SpeechSynthesisUtterance(speechTextFor(unitEls[index]));
            interjection.lang = speechLang;
            interjection.rate = parseFloat(speedSelect.value) || 1;
            if (voiceSelect.value !== '' && matchingVoices[voiceSelect.value]) {
              interjection.voice = matchingVoices[voiceSelect.value];
            }
            function resumeAfterInterjection() {
              clearHighlight();
              if (wasPlaying) {
                activeAudioEl = audioEl;
                audioEl.currentTime = resumeAt;
                audioEl.play().catch(function () {});
                setState('playing');
              }
              else {
                setState('idle');
              }
            }
            interjection.onend = resumeAfterInterjection;
            interjection.onerror = resumeAfterInterjection;
            window.speechSynthesis.speak(interjection);
            return;
          }

          restarting = true;
          window.speechSynthesis.cancel();
          stopActivePassageAudio();
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
          if (usesWholeFileAudio()) {
            if (state === 'idle') {
              if (hasPlayedOnce) {
                session.replayCount++;
                saveSession(passageId, session);
                updateReplayCountDisplay();
              }
              hasPlayedOnce = true;
              unlockReveal();
              setState('playing');
              playWholeAudio();
            }
            else if (state === 'playing') {
              if (activeAudioEl) {
                activeAudioEl.pause();
              }
              setState('paused');
            }
            else if (activeAudioEl) {
              activeAudioEl.play().catch(function () {});
              setState('playing');
            }
            else {
              // Shouldn't normally happen (paused implies activeAudioEl
              // exists), but recover cleanly rather than getting stuck.
              setState('playing');
              playWholeAudio();
            }
            return;
          }

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
            // The currently-playing unit might be a recorded clip
            // (activeAudioEl) rather than a TTS utterance when
            // sentenceAudioUrls has a mix of the two — pause whichever
            // one is actually sounding.
            if (activeAudioEl) {
              activeAudioEl.pause();
            }
            else {
              window.speechSynthesis.pause();
            }
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
            stopActivePassageAudio();
            setState('playing');
            window.setTimeout(function () {
              restarting = false;
              speakFrom(currentIndex);
            }, 50);
          }
          else if (activeAudioEl) {
            activeAudioEl.play().catch(function () {});
            setState('playing');
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
            releaseWakeLock();
            window.speechSynthesis.cancel();
            stopActivePassageAudio();
          });
        }
        window.addEventListener('beforeunload', function () {
          releaseWakeLock();
          window.speechSynthesis.cancel();
          stopActivePassageAudio();
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
