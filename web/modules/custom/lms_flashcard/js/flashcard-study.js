/**
 * @file
 * Flashcard study session: flip the card, grade with 4 buttons or 1-4/space.
 */

(function (Drupal, once, drupalSettings) {
  'use strict';

  // Deck "language" code (see Deck::field_language allowed values) mapped to
  // a BCP-47 tag for the SpeechSynthesis fallback below.
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

  // Spelling Practice difficulty ladder — must mirror
  // FlashcardApiController::SPELL_LEVEL_MIN/MAX (server owns the actual
  // level number, stored on CardStat.field_spell_level; this only maps a
  // level to how much of the word buildSpellPuzzle() masks). 1 = easiest
  // (most letters given as hints), 5 = hardest: the whole word, blanked out
  // inside one of the card's own example sentences instead of shown in
  // isolation — see findTermInExamples().
  var SPELL_LEVEL_MAX = 5;
  var SPELL_LEVEL_HIDE_RATIO = { 1: 0.3, 2: 0.5, 3: 0.7, 4: 1, 5: 1 };

  // Quiz Practice difficulty ladder — must mirror FlashcardApiController::
  // QUIZ_LEVEL_MIN/MAX. All 3 levels are built from the card's own example
  // sentences (see buildQuizExercise()): 1 = cloze multiple-choice, 2 =
  // listen-and-match the sentence, 3 = listen-and-type it from its
  // translation, graded by character-similarity rather than exact match.
  var QUIZ_LEVEL_MAX = 3;
  var QUIZ_PASS_SIMILARITY = 0.8;

  // Pronunciation Practice — must mirror FlashcardApiController::
  // PRONUNCIATION_LEVEL_MIN/MAX. Unlike SPELL/QUIZ_LEVEL_MAX this never
  // changes what the exercise looks like (see buildPronunciationExercise()),
  // it's shown purely as a "Level X of 5" badge like Spelling Practice's.
  var PRONOUNCE_LEVEL_MAX = 5;
  // A recording is graded by how closely its transcript matches the term
  // (see textSimilarity()) rather than requiring an exact match, since
  // Web Speech API transcription itself is imperfect even for a correctly
  // pronounced word. Below CLOSE it's a clear miss; between CLOSE and
  // CORRECT it's "close, try again" rather than an outright fail — see
  // handlePronounceResult(). Thresholds are a starting point, per the plan
  // doc — expect to tune after trying this with real users/accents.
  var PRONOUNCE_CORRECT_SIMILARITY = 0.75;
  var PRONOUNCE_CLOSE_SIMILARITY = 0.5;
  // How many recording attempts a student gets on one card before it's
  // force-graded as incorrect and moved past — mirrors the bounded-retry
  // feel of Quiz Practice's 3 exercises rather than letting a card block
  // the session forever on a transcript that just won't match.
  var PRONOUNCE_MAX_ATTEMPTS = 3;

  /**
   * Levenshtein edit distance between two strings — the number of single-
   * character insertions/deletions/substitutions to turn `a` into `b`.
   * Classic DP with a single rolling row (no need for the full matrix).
   */
  function levenshteinDistance(a, b) {
    var m = a.length;
    var n = b.length;
    var row = [];
    for (var j = 0; j <= n; j++) {
      row[j] = j;
    }
    for (var i = 1; i <= m; i++) {
      var prev = row[0];
      row[0] = i;
      for (var k = 1; k <= n; k++) {
        var temp = row[k];
        row[k] = a.charAt(i - 1) === b.charAt(k - 1)
          ? prev
          : 1 + Math.min(prev, row[k], row[k - 1]);
        prev = temp;
      }
    }
    return row[n];
  }

  /**
   * Normalized character-similarity ratio (0-1) between two strings —
   * 1 minus the Levenshtein distance relative to the longer string's
   * length, after trimming and collapsing whitespace. Used by Quiz
   * Practice's level-3 dictation check: "≥80% characters match" rather
   * than requiring an exact match, so a stray typo doesn't fail an
   * otherwise-correct answer.
   */
  function textSimilarity(a, b) {
    a = String(a || '').trim().toLowerCase().replace(/\s+/g, ' ');
    b = String(b || '').trim().toLowerCase().replace(/\s+/g, ' ');
    var maxLen = Math.max(a.length, b.length);
    if (maxLen === 0) {
      return 1;
    }
    return 1 - levenshteinDistance(a, b) / maxLen;
  }

  /**
   * Strips punctuation and collapses whitespace/case for comparing a Web
   * Speech API transcript against a card's term — "don't" vs "dont",
   * trailing periods the recognizer sometimes appends, etc. shouldn't count
   * against the student. textSimilarity() does its own lowercase/trim/
   * whitespace-collapse too, so this only adds the punctuation strip on top
   * before handing both strings to it — see handlePronounceResult().
   */
  function normalizeForCompare(text) {
    return String(text || '')
      .toLowerCase()
      .replace(/[^\p{L}\p{N}\s]/gu, '')
      .trim()
      .replace(/\s+/g, ' ');
  }

  /**
   * Renders a list of {[textKey]: string, translation: string} pairs (an
   * example sentence, or a collocation phrase) into `containerEl` as
   * speaker-button + text + blurred-translation rows — shared by the back
   * face's Examples and Collocations sections in showCard(), which only
   * differ in which property holds the text, what the speaker button's
   * label says, and how `speak` reads the text aloud (each study session
   * instance has its own speakText() closed over its deck's speechLang, so
   * that's passed in rather than assumed global).
   */
  function renderPairList(containerEl, items, textKey, speakLabel, speak) {
    containerEl.replaceChildren();
    items.forEach(function (item) {
      var text = item[textKey] || '';
      var pair = document.createElement('div');
      pair.className = 'lms-flashcard__example-pair';

      var textRow = document.createElement('div');
      textRow.className = 'lms-flashcard__example-row';

      var speakBtn = document.createElement('button');
      speakBtn.type = 'button';
      speakBtn.className = 'lms-flashcard__example-speak';
      speakBtn.setAttribute('aria-label', speakLabel);
      speakBtn.title = speakLabel;
      speakBtn.innerHTML = '<i class="fas fa-volume-up" aria-hidden="true"></i>';
      speakBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        speak(text);
      });
      textRow.appendChild(speakBtn);

      var textEl = document.createElement('div');
      textEl.className = 'lms-flashcard__example';
      textEl.textContent = text;
      textRow.appendChild(textEl);
      pair.appendChild(textRow);

      if (item.translation) {
        var translation = document.createElement('button');
        translation.type = 'button';
        translation.className = 'lms-flashcard__example-translation';
        translation.textContent = item.translation;
        translation.title = Drupal.t('Click to reveal the translation');
        pair.appendChild(translation);
      }
      containerEl.appendChild(pair);
    });
  }

  /**
   * Fisher-Yates shuffle, in place — shared by the spelling-puzzle letter
   * order and Quiz Practice's answer-choice order.
   */
  function shuffleInPlace(arr) {
    for (var i = arr.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = arr[i];
      arr[i] = arr[j];
      arr[j] = tmp;
    }
    return arr;
  }

  Drupal.behaviors.lmsFlashcardStudy = {
    attach: function (context) {
      once('lms-flashcard-study', '[data-flashcard-stage]', context).forEach(function (stage) {
        var settings = (drupalSettings.lmsFlashcard || {});
        var deckId = settings.deckId;
        var speechLang = SPEECH_LANG_MAP[settings.language] || 'en-US';
        var nextUrl = settings.nextUrl || '/api/lms/flashcard/next';
        var reviewUrl = settings.reviewUrl || '/api/lms/flashcard/review';
        var masteryUrl = settings.masteryUrl || '/api/lms/flashcard/mastery';
        var summaryUrl = settings.summaryUrl;
        // Only actually consulted server-side on the call that (re)builds
        // the queue (see FlashcardApiController::nextCard()) — sent on
        // every call anyway since that's harmless and simpler than tracking
        // "is this the first call" here too.
        var direction = settings.direction || 'forward';
        var mode = settings.practice ? 'practice' : 'srs';
        // 'spell' = Spelling Practice (fill in the missing letters of the
        // term); 'quiz' = Quiz Practice (3 example-sentence exercises —
        // see buildQuizExercise()); 'pronounce' = Pronunciation Practice
        // (record yourself saying the term, graded via the Web Speech API
        // — see buildPronunciationExercise()). Mainly a front-end rendering
        // choice, but also sent to nextCard() (see loadNext() below) so it
        // can queue every card in the deck instead of just what's due —
        // see FlashcardApiController::nextCard()'s $drillAll. It never
        // affects whether a grade gets persisted to the real SM-2 schedule,
        // though — that's $practice (mode=practice) alone, still driven
        // only by settings.practice.
        var style = ['spell', 'quiz', 'pronounce'].indexOf(settings.style) !== -1 ? settings.style : 'flip';
        // Deck-wide {id, term, definition, examples[]} pool Quiz Practice
        // draws wrong-answer choices from — see StudyController::
        // buildQuizPool(). Empty/unused for the other styles.
        var quizPool = Array.isArray(settings.quizPool) ? settings.quizPool : [];

        var section = stage.closest('.lms-flashcard-study');
        var emptyState = section.querySelector('[data-flashcard-empty]');
        var progressText = section.querySelector('[data-flashcard-progress-text]');
        var progressBar = section.querySelector('[data-flashcard-progress-bar]');
        var progressFill = section.querySelector('[data-flashcard-progress-fill]');
        var cardEl = section.querySelector('[data-flashcard-card]');
        // Spelling/Quiz mode pack in more than a plain flip card, so they
        // get a taller fixed card height — see the .lms-flashcard--spell /
        // --quiz rules in flashcard-study.css. `style` is a session-level
        // constant (never changes card to card), so this only needs
        // setting once here.
        cardEl.classList.toggle('lms-flashcard--spell', style === 'spell');
        cardEl.classList.toggle('lms-flashcard--quiz', style === 'quiz');
        cardEl.classList.toggle('lms-flashcard--pronounce', style === 'pronounce');
        var gradesEl = section.querySelector('[data-flashcard-grades]');

        // Spelling/Quiz/Pronunciation mode's hint differs from the shared
        // default: Space replays the pronunciation instead of flipping (and
        // for spell/quiz, clicking/tapping the card no longer flips it
        // early — see flip()'s guard below, the exercise must be answered
        // first; Pronunciation doesn't need that guard since nothing about
        // the card is hidden to begin with). Set once here since `style`
        // never changes mid-session.
        var hintEl = section.querySelector('[data-flashcard-hint]');
        if (hintEl && style === 'spell') {
          hintEl.textContent = Drupal.t('Fill in the blanks, then check · Press space to hear pronunciation');
        }
        else if (hintEl && style === 'quiz') {
          hintEl.textContent = Drupal.t('Choose or type an answer · Press space to hear pronunciation');
        }
        else if (hintEl && style === 'pronounce') {
          hintEl.textContent = Drupal.t('Press the mic and say the word · Press space to hear pronunciation');
        }

        var termEl = section.querySelector('[data-flashcard-term]');
        var posEl = section.querySelector('[data-flashcard-pos]');
        var pronunciationEl = section.querySelector('[data-flashcard-pronunciation]');
        var seenCountEl = section.querySelector('[data-flashcard-seen-count]');
        var definitionEl = section.querySelector('[data-flashcard-definition]');
        var examplesEl = section.querySelector('[data-flashcard-examples]');
        var examplesSectionEl = section.querySelector('[data-flashcard-examples-section]');
        // Collocations (e.g. "start a family") sit in their own labeled
        // section below the examples, on the back face — see showCard() and
        // renderPairList() below. The section itself is hidden whenever a
        // card has none, rather than showing an empty "Collocations"
        // heading.
        var collocationsSectionEl = section.querySelector('[data-flashcard-collocations-section]');
        var collocationsEl = section.querySelector('[data-flashcard-collocations]');

        // Reverse-mode ("meaning → word") elements: the prompt shown on the
        // front face instead of the term, and the term/pronunciation/speaker
        // row shown on the back face instead of the front — see showCard().
        var frontTermRowEl = section.querySelector('[data-flashcard-front-term-row]');
        var speakFrontEl = section.querySelector('[data-flashcard-speak-front]');
        var promptEl = section.querySelector('[data-flashcard-prompt]');
        var backTermRowEl = section.querySelector('[data-flashcard-back-term-row]');
        var backTermEl = section.querySelector('[data-flashcard-back-term]');
        var backPronunciationEl = section.querySelector('[data-flashcard-back-pronunciation]');
        var speakBackEl = section.querySelector('[data-flashcard-speak-back]');
        // Spelling mode hides frontTermRowEl (it forces the "reverse"
        // layout — see showCard()), so it has no speaker button of its
        // own otherwise; without this, replaying the pronunciation while
        // still working on the puzzle would require finishing the check
        // first just to reach the back face's speaker button.
        var speakSpellEl = section.querySelector('[data-flashcard-speak-spell]');

        // Spelling-mode elements (style === 'spell') — see buildSpellPuzzle()
        // and checkSpelling() below.
        var spellEl = section.querySelector('[data-flashcard-spell]');
        var spellBoxesEl = section.querySelector('[data-flashcard-spell-boxes]');
        var spellCheckEl = section.querySelector('[data-flashcard-spell-check]');
        var spellNextEl = section.querySelector('[data-flashcard-spell-next]');
        var spellLevelEl = section.querySelector('[data-flashcard-spell-level]');
        var spellInputs = [];
        var currentSpellLevel = 1;

        // Quiz-mode elements (style === 'quiz') — see buildQuizExercise()
        // and submitQuizResult() below.
        var quizEl = section.querySelector('[data-flashcard-quiz]');
        var speakQuizEl = section.querySelector('[data-flashcard-speak-quiz]');
        var quizSentenceEl = section.querySelector('[data-flashcard-quiz-sentence]');
        var quizChoicesEl = section.querySelector('[data-flashcard-quiz-choices]');
        var quizWriteEl = section.querySelector('[data-flashcard-quiz-write]');
        var quizWriteInputEl = section.querySelector('[data-flashcard-quiz-write-input]');
        var quizCheckEl = section.querySelector('[data-flashcard-quiz-check]');
        var quizNextEl = section.querySelector('[data-flashcard-quiz-next]');
        var quizLevelEl = section.querySelector('[data-flashcard-quiz-level]');
        var currentQuizLevel = 1;
        var quizAnswered = false;
        var quizExpectedText = '';
        var quizChoiceEntries = [];

        // Pronunciation-mode elements (style === 'pronounce') — see
        // buildPronunciationExercise() and submitPronunciationResult()
        // below.
        var pronounceEl = section.querySelector('[data-flashcard-pronounce]');
        var pronounceDefinitionEl = section.querySelector('[data-flashcard-pronounce-definition]');
        var micBtnEl = section.querySelector('[data-flashcard-mic-btn]');
        var pronounceStatusEl = section.querySelector('[data-flashcard-pronounce-status]');
        var pronounceFallbackEl = section.querySelector('[data-flashcard-pronounce-fallback]');
        var pronounceFallbackNoteEl = section.querySelector('[data-flashcard-pronounce-fallback-note]');
        var pronounceFallbackCorrectEl = section.querySelector('[data-flashcard-pronounce-fallback-correct]');
        var pronounceFallbackIncorrectEl = section.querySelector('[data-flashcard-pronounce-fallback-incorrect]');
        var pronounceNextEl = section.querySelector('[data-flashcard-pronounce-next]');
        var pronounceLevelEl = section.querySelector('[data-flashcard-pronounce-level]');
        var currentPronounceLevel = 1;
        var pronounceAttempts = 0;
        // True once this card's attempt has been graded and sent to the
        // server (a correct recording, the last of PRONOUNCE_MAX_ATTEMPTS,
        // or a fallback button click) — blocks further recordings/fallback
        // clicks until buildPronunciationExercise() resets it for the next
        // card.
        var pronounceResolved = false;
        var pronounceListening = false;
        // Web Speech API support is checked once for the whole session
        // (not per card) — see the SpeechRecognition constructor lookup
        // below. Also treated as unsupported outside a secure context
        // (plain http://), since Chrome/Edge refuse to start recognition
        // there anyway — see risk notes on secure-context requirements.
        // Tracked separately from the constructor check so the fallback
        // note can tell a student *why* the mic is missing (open via
        // https:// vs. "this browser doesn't support it") instead of a
        // single generic message either way.
        var SpeechRecognitionCtor = window.SpeechRecognition || window.webkitSpeechRecognition;
        var pronounceInsecureContext = !!SpeechRecognitionCtor && window.isSecureContext === false;
        var pronounceSpeechSupported = !!SpeechRecognitionCtor && !pronounceInsecureContext;

        // "Study Now" (style === 'flip') mastery-mode elements — an
        // isolated set of variables/DOM (never quizChoicesEl/quizAnswered/
        // etc. above), by the student's own request not to touch Quiz
        // Practice's own code at all. See buildMasteryExercise() and
        // handleMasteryContinue() below, and FlashcardApiController::
        // submitMastery()'s docblock for the server side of this flow.
        var masteryEl = section.querySelector('[data-flashcard-mastery]');
        var masteryDefinitionEl = section.querySelector('[data-flashcard-mastery-definition]');
        // Bonus "guess it from the picture" round — see
        // startMasteryImageRound() below.
        var masteryImageEl = section.querySelector('[data-flashcard-mastery-image]');
        var masteryImageImgEl = section.querySelector('[data-flashcard-mastery-image-img]');
        var masteryChoicesEl = section.querySelector('[data-flashcard-mastery-choices]');
        var masteryContinueEl = section.querySelector('[data-flashcard-mastery-continue]');
        var currentMasteryLevel = 1;
        var masteryAnswered = false;
        var masteryChoiceEntries = [];
        // Whether the current card has an image at all (decided once per
        // card, in buildMasteryExercise()) and whether the bonus image
        // round is the one currently showing (flips true partway through a
        // single level-2 visit — see renderMasteryChoices()'s click
        // handler and startMasteryImageRound()).
        var masteryHasImageRound = false;
        var masteryImageRoundActive = false;
        // True only during the brief window.setTimeout() gap between
        // answering the meaning round correctly and startMasteryImageRound()
        // actually swapping the exercise to the image round — masteryAnswered
        // is already true by then (needed so a second click on the meaning
        // round's own buttons is ignored), which would otherwise satisfy
        // flip()'s normal "already answered" guard and let a stray click/
        // Space/Enter flip the card early, right as the front face is about
        // to change under it. See flip()'s own guard below.
        var masteryTransitionPending = false;

        // Interval-preview <span>s inside each grade button, keyed by
        // again/hard/good/easy — populated from card.previews in showCard().
        var previewEls = {};
        section.querySelectorAll('[data-flashcard-preview]').forEach(function (el) {
          previewEls[el.getAttribute('data-flashcard-preview')] = el;
        });

        // Quick Edit sidebar elements & state (see
        // QUICK_EDIT_STUDY_CARD_PLAN.md) — deliberately outside
        // [data-flashcard-card] in the template, so clicks inside the
        // drawer/toolbar button can never bubble into flip()/cardEl's own
        // handlers to begin with; see openQuickEdit()/saveQuickEdit() etc.
        // below for the rest.
        var quickEditBtnEl = section.querySelector('[data-flashcard-quick-edit]');
        var editOverlayEl = section.querySelector('[data-flashcard-edit-overlay]');
        var editDrawerEl = section.querySelector('[data-flashcard-edit-drawer]');
        var editTitleEl = section.querySelector('[data-flashcard-edit-title]');
        var editStatusEl = section.querySelector('[data-flashcard-edit-status]');
        var editTermEl = section.querySelector('[data-flashcard-edit-term]');
        var editDefinitionEl = section.querySelector('[data-flashcard-edit-definition]');
        var editTranslationEl = section.querySelector('[data-flashcard-edit-translation]');
        var editPosEl = section.querySelector('[data-flashcard-edit-pos]');
        var editPronunciationEl = section.querySelector('[data-flashcard-edit-pronunciation]');
        var editDifficultyEl = section.querySelector('[data-flashcard-edit-difficulty]');
        var editAudioCurrentEl = section.querySelector('[data-flashcard-edit-audio-current]');
        var editAudioPreviewEl = section.querySelector('[data-flashcard-edit-audio-preview]');
        var editAudioRemoveBtnEl = section.querySelector('[data-flashcard-edit-audio-remove]');
        var editAudioInputEl = section.querySelector('[data-flashcard-edit-audio-input]');
        var editImageCurrentEl = section.querySelector('[data-flashcard-edit-image-current]');
        var editImagePreviewEl = section.querySelector('[data-flashcard-edit-image-preview]');
        var editImageRemoveBtnEl = section.querySelector('[data-flashcard-edit-image-remove]');
        var editImageInputEl = section.querySelector('[data-flashcard-edit-image-input]');
        var editExamplesRowsEl = section.querySelector('[data-flashcard-edit-examples-rows]');
        var editExamplesAddBtnEl = section.querySelector('[data-flashcard-edit-examples-add]');
        var editCollocationsRowsEl = section.querySelector('[data-flashcard-edit-collocations-rows]');
        var editCollocationsAddBtnEl = section.querySelector('[data-flashcard-edit-collocations-add]');
        var editCancelBtnEl = section.querySelector('[data-flashcard-edit-cancel]');
        var editSaveBtnEl = section.querySelector('[data-flashcard-edit-save]');
        var editCloseBtnEl = section.querySelector('[data-flashcard-edit-close]');

        // Card image (see "Image Display" in the plan) — shown only for the
        // plain flip view; see showCard()'s showImage computation.
        var imageFrontEl = section.querySelector('[data-flashcard-image-front]');
        var imageFrontImgEl = section.querySelector('[data-flashcard-image-front-img]');
        var imageBackEl = section.querySelector('[data-flashcard-image-back]');
        var imageBackImgEl = section.querySelector('[data-flashcard-image-back-img]');

        var quickEditOpen = false;
        var quickEditSaving = false;
        // Pending hideEditDrawer() timeout — lets the closing slide
        // transition finish playing before the drawer is actually hidden
        // (display:none) and removed from the tab order; cleared if the
        // drawer is reopened before that fires.
        var editDrawerCloseTimer = null;
        // Captured when the drawer opens and checked against every response
        // that comes back (GET prefetch, POST save) before applying it —
        // guards against the drawer somehow still being open for a card
        // that isn't the one currently on screen any more.
        var quickEditCardId = null;
        var quickEditGetUrl = null;
        var quickEditSaveUrl = null;
        var editAudioRemoveFlag = false;
        var editImageRemoveFlag = false;

        var audioEl = new Audio();
        var currentCard = null;
        var revealed = false;
        var loading = false;
        // Session-total is captured once from the first card's "remaining"
        // count (remaining + the one just shown = how many were queued up
        // at the start of this session); studiedCount then just counts
        // cards shown since. A card graded "Again" gets requeued a few
        // cards later (see StudyQueueBuilder::requeueAfterAgain()), so it
        // can be shown — and counted here — more than once; the bar is
        // clamped at 100% for that case rather than trying to be exact.
        var sessionTotal = null;
        var studiedCount = 0;

        /**
         * Reads the current card aloud: the term (prefers a manually
         * uploaded pronunciation MP3, card.audio_url, falling back to the
         * browser's speech synthesis when no file was uploaded), or the
         * example sentence — there's no recorded audio for a full sentence,
         * so that side always uses speech synthesis.
         *
         * Which face currently shows the term depends on direction: in
         * "forward" mode the term is on the front (revealed === false); in
         * "reverse" mode ("meaning → word") the prompt on the front is the
         * definition and the term only appears once flipped
         * (revealed === true) — see showCard(). Examples always live on the
         * back face regardless of direction. In spelling mode the term is
         * never visibly "shown" on the front (it's masked into the spelling
         * puzzle instead), but the student should still hear it right away
         * to practice against, so it's always treated as showing there.
         */
        function speakCard() {
          if (!currentCard) {
            return;
          }

          var direction = currentCard.direction || 'forward';
          var termShowing = style === 'spell' || (direction === 'reverse' ? revealed : !revealed);
          var firstExample = currentCard.examples && currentCard.examples[0];
          var exampleSentence = firstExample ? firstExample.sentence : currentCard.example_sentence;

          if (termShowing) {
            if (currentCard.audio_url) {
              audioEl.src = currentCard.audio_url;
              audioEl.play().catch(function () {});
              return;
            }
            speakText(currentCard.term);
            return;
          }

          if (revealed && exampleSentence) {
            speakText(exampleSentence);
          }
          // Otherwise (reverse mode, prompt face showing): nothing to speak
          // — the term hasn't been revealed yet, so there's no speaker
          // button visible on that face either.
        }

        function speakText(text) {
          if (!window.speechSynthesis) {
            return;
          }
          window.speechSynthesis.cancel();
          // Chrome silently drops a speak() called immediately after
          // cancel() in the same tick; a zero-delay timeout is the
          // standard workaround so back-to-back clicks (flip, flip again,
          // speaker button…) reliably produce sound every time.
          window.setTimeout(function () {
            var utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = speechLang;
            window.speechSynthesis.speak(utterance);
          }, 50);
        }

        /**
         * Reads just the current card's term aloud — Quiz Practice's
         * speaker button/Space shortcut, unlike speakCard(), always means
         * "play the word" regardless of flip/answered state (there's no
         * front/back-face term-showing concept to gate on here).
         */
        function speakTerm() {
          if (!currentCard) {
            return;
          }
          if (currentCard.audio_url) {
            audioEl.src = currentCard.audio_url;
            audioEl.play().catch(function () {});
            return;
          }
          speakText(currentCard.term);
        }

        function updateProgress(remaining) {
          if (sessionTotal === null) {
            sessionTotal = remaining + 1;
          }
          studiedCount++;

          progressText.textContent = Drupal.t('Studied @done of @total', {
            '@done': Math.min(studiedCount, sessionTotal),
            '@total': sessionTotal,
          });

          progressBar.hidden = false;
          var pct = sessionTotal > 0 ? Math.min(100, Math.round((studiedCount / sessionTotal) * 100)) : 0;
          progressFill.style.width = pct + '%';
        }

        /**
         * Finds `term` inside one of the card's example sentences (first
         * case-insensitive match wins) — used to build the level-5 puzzle,
         * where the word is blanked out in context instead of shown alone.
         * Returns null if none of the examples actually contain it (some
         * don't, e.g. a conjugated/pluralized form in the sentence), in
         * which case buildSpellPuzzle() falls back to the level-4 look.
         */
        function findTermInExamples(term, examples) {
          if (!term || !Array.isArray(examples)) {
            return null;
          }
          var lowerTerm = term.toLowerCase();
          for (var i = 0; i < examples.length; i++) {
            var sentence = examples[i].sentence || '';
            var idx = sentence.toLowerCase().indexOf(lowerTerm);
            if (idx !== -1) {
              return {
                before: sentence.slice(0, idx),
                word: sentence.slice(idx, idx + term.length),
                after: sentence.slice(idx + term.length),
              };
            }
          }
          return null;
        }

        /**
         * Appends the boxes for one word (or multi-word phrase) to
         * spellBoxesEl: `hideRatio` of its letters (rounded, at least 1 and
         * — unless hideRatio is 1, i.e. levels 4-5 — at most length-1)
         * become a 1-character <input>, the rest plain hint letters.
         * Hyphens/apostrophes are never masked, just shown as a hint
         * "letter" like any other; a literal space gets its own thin
         * spacer element instead (see the `ch === ' '` branch below) so
         * multi-word phrases read as separate words without an extra
         * same-size box that looks like another blank input. Appends to
         * spellInputs.
         */
        function renderSpellWordBoxes(word, hideRatio) {
          var chars = String(word || '').split('');
          var letterPositions = [];
          chars.forEach(function (ch, i) {
            if (!/^[\s\-']$/.test(ch)) {
              letterPositions.push(i);
            }
          });

          var hideCount = 0;
          if (letterPositions.length > 0) {
            hideCount = hideRatio >= 1
              ? letterPositions.length
              : (letterPositions.length > 1
                ? Math.min(Math.max(1, Math.round(letterPositions.length * hideRatio)), letterPositions.length - 1)
                : 0);
          }
          var shuffled = letterPositions.slice();
          for (var i = shuffled.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = shuffled[i];
            shuffled[i] = shuffled[j];
            shuffled[j] = tmp;
          }
          var hidden = {};
          shuffled.slice(0, hideCount).forEach(function (pos) {
            hidden[pos] = true;
          });

          chars.forEach(function (ch, i) {
            // A literal space is never a masking candidate (see above), but
            // it still needs its own element so multi-word terms ("pay by
            // card") read as separate words — rendering it as a *plain*
            // same-size box like every letter (as this used to do) made it
            // look just like an unfocused blank input sitting right next to
            // the real one, and typing into it silently did nothing.
            if (ch === ' ') {
              var space = document.createElement('span');
              space.className = 'lms-flashcard__spell-space';
              spellBoxesEl.appendChild(space);
              return;
            }
            if (!hidden[i]) {
              var box = document.createElement('span');
              box.className = 'lms-flashcard__spell-box lms-flashcard__spell-box--filled';
              box.textContent = ch;
              spellBoxesEl.appendChild(box);
              return;
            }
            var input = document.createElement('input');
            input.type = 'text';
            input.maxLength = 1;
            input.autocomplete = 'off';
            input.autocapitalize = 'off';
            input.spellcheck = false;
            input.className = 'lms-flashcard__spell-box lms-flashcard__spell-box--blank';
            input.setAttribute('data-answer', ch);
            input.setAttribute('aria-label', Drupal.t('Letter @position', { '@position': spellInputs.length + 1 }));
            spellBoxesEl.appendChild(input);
            spellInputs.push(input);
          });
        }

        /**
         * Builds the fill-in-the-missing-letters puzzle for `term` inside
         * spellBoxesEl, at a difficulty driven by `level` (1 easiest, 5
         * hardest — see SPELL_LEVEL_HIDE_RATIO). At level 5 the word is
         * blanked out inside a sentence from `examples` instead of shown in
         * isolation, when one is found to contain it.
         */
        function buildSpellPuzzle(term, level, examples) {
          spellBoxesEl.replaceChildren();
          spellInputs = [];
          if (spellCheckEl) {
            spellCheckEl.disabled = false;
          }
          if (spellNextEl) {
            spellNextEl.hidden = true;
          }

          var hideRatio = SPELL_LEVEL_HIDE_RATIO[level] || 0.5;
          var word = term;
          var context = level >= SPELL_LEVEL_MAX ? findTermInExamples(term, examples) : null;

          if (context && context.before) {
            var beforeEl = document.createElement('span');
            beforeEl.className = 'lms-flashcard__spell-context';
            beforeEl.textContent = context.before;
            spellBoxesEl.appendChild(beforeEl);
          }

          renderSpellWordBoxes(context ? context.word : word, hideRatio);

          if (context && context.after) {
            var afterEl = document.createElement('span');
            afterEl.className = 'lms-flashcard__spell-context';
            afterEl.textContent = context.after;
            spellBoxesEl.appendChild(afterEl);
          }

          spellInputs.forEach(function (input, idx) {
            // Keep every interaction inside the puzzle from bubbling up to
            // cardEl's click/keydown listeners, which would otherwise flip
            // the card (click) or replay audio a second time (Space — the
            // keydown handler below already does that itself).
            input.addEventListener('click', function (e) {
              e.stopPropagation();
            });
            input.addEventListener('input', function () {
              input.value = input.value.slice(-1);
              if (input.value && spellInputs[idx + 1]) {
                spellInputs[idx + 1].focus();
              }
            });
            input.addEventListener('keydown', function (e) {
              e.stopPropagation();
              // A masked position is never a literal space (see the
              // ch === ' ' branch above — spaces are never maskable), so
              // there's never a real reason to type one here; free to
              // repurpose for replaying the pronunciation instead.
              if (e.key === ' ') {
                e.preventDefault();
                speakCard();
              }
              else if (e.key === 'Backspace' && !input.value && spellInputs[idx - 1]) {
                spellInputs[idx - 1].focus();
              }
              else if (e.key === 'Enter') {
                e.preventDefault();
                checkSpelling();
              }
            });
          });

          if (spellInputs[0]) {
            spellInputs[0].focus();
          }
        }

        function updateSpellLevelBadge() {
          if (!spellLevelEl) {
            return;
          }
          spellLevelEl.hidden = false;
          spellLevelEl.textContent = Drupal.t('Level @level of @max', {
            '@level': currentSpellLevel,
            '@max': SPELL_LEVEL_MAX,
          });
        }

        /**
         * Sends the spelling result to the same endpoint the 4 grade
         * buttons use — correct/incorrect is auto-mapped to a Good/Again
         * quality (see the docblock on FlashcardApiController::
         * submitReview()) so Spelling Practice still feeds the real SM-2
         * schedule, while `spell_correct` additionally bumps
         * currentSpellLevel up or down a notch server-side. Reveals the
         * Next button only once this resolves, matching submitGrade()'s
         * existing "wait for the save before moving on" sequencing — an
         * incorrect answer also requeues the card a few positions ahead
         * (see requeueAfterAgain()), so a premature loadNext() here could
         * race that.
         */
        function submitSpellResult(correct) {
          loading = true;
          fetch(reviewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, quality: correct ? 2 : 0, spell_correct: correct }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              loading = false;
              if (data && typeof data.spell_level === 'number') {
                currentSpellLevel = data.spell_level;
                updateSpellLevelBadge();
              }
              // Deliberately not auto-focusing the button: cardEl still has
              // focus from checkSpelling(), and a student's habitual "press
              // Enter to confirm" would then land on this now-focused Next
              // button and instantly activate it — skipping past the
              // check result before they've actually looked at it. → or a
              // real click stays the only way to advance.
              if (spellNextEl) {
                spellNextEl.hidden = false;
              }
            })
            .catch(function () {
              loading = false;
              if (spellNextEl) {
                spellNextEl.hidden = false;
              }
            });
        }

        /**
         * Grades each blank against the real letter, reveals it either way,
         * and locks the puzzle. Unlike the plain flip/reverse styles,
         * Spelling Practice has no manual grade buttons — correctness here
         * *is* the grade, auto-submitted via submitSpellResult().
         */
        function checkSpelling() {
          if (spellCheckEl && spellCheckEl.disabled) {
            return;
          }
          var allCorrect = true;
          spellInputs.forEach(function (input) {
            var answer = input.getAttribute('data-answer') || '';
            var correct = (input.value || '').trim().toLowerCase() === answer.toLowerCase();
            input.classList.remove('is-correct', 'is-incorrect');
            input.classList.add(correct ? 'is-correct' : 'is-incorrect');
            if (!correct) {
              input.value = answer;
              allCorrect = false;
            }
            input.readOnly = true;
            input.tabIndex = -1;
          });
          if (spellCheckEl) {
            spellCheckEl.disabled = true;
          }
          // Focus was still sitting inside whichever blank the student last
          // typed into (now readOnly), and that input's own keydown handler
          // stops propagation on every key — so without moving focus back to
          // the card, Enter (flip back) would never fire again.
          cardEl.focus();
          if (!revealed) {
            flip();
          }
          submitSpellResult(allCorrect);
        }

        function updateQuizLevelBadge() {
          if (!quizLevelEl) {
            return;
          }
          quizLevelEl.hidden = false;
          quizLevelEl.textContent = Drupal.t('Level @level of @max', {
            '@level': currentQuizLevel,
            '@max': QUIZ_LEVEL_MAX,
          });
        }

        /**
         * Picks a random example on `card` whose sentence literally contains
         * `term` (case-insensitive) — optionally only among examples that
         * also carry a non-empty translation, needed for levels 2-3.
         * Returns null when no example qualifies (e.g. the card has none at
         * all, or the term only appears in a conjugated/pluralized form the
         * plain indexOf() below can't match) — buildQuizExercise() then
         * falls back to a definition-only exercise for this card.
         */
        function findQuizExample(term, examples, requireTranslation) {
          if (!term || !Array.isArray(examples)) {
            return null;
          }
          var lowerTerm = term.toLowerCase();
          var candidates = [];
          examples.forEach(function (example) {
            var sentence = example.sentence || '';
            if (requireTranslation && !example.translation) {
              return;
            }
            if (sentence.toLowerCase().indexOf(lowerTerm) !== -1) {
              candidates.push(example);
            }
          });
          if (!candidates.length) {
            return null;
          }
          var picked = candidates[Math.floor(Math.random() * candidates.length)];
          var idx = picked.sentence.toLowerCase().indexOf(lowerTerm);
          return {
            sentence: picked.sentence,
            translation: picked.translation || '',
            before: picked.sentence.slice(0, idx),
            word: picked.sentence.slice(idx, idx + term.length),
            after: picked.sentence.slice(idx + term.length),
          };
        }

        /** Up to `n` random, non-repeating items from `arr` (fewer if it's smaller). */
        function sampleArray(arr, n) {
          return shuffleInPlace(arr.slice()).slice(0, Math.min(n, arr.length));
        }

        function pickDistractorTerms(excludeTerm, n) {
          var lowerExclude = (excludeTerm || '').toLowerCase();
          var candidates = [];
          quizPool.forEach(function (entry) {
            if (entry.term && entry.term.toLowerCase() !== lowerExclude) {
              candidates.push(entry.term);
            }
          });
          return sampleArray(candidates, n);
        }

        function pickDistractorSentences(excludeCardId, n) {
          var candidates = [];
          quizPool.forEach(function (entry) {
            if (entry.id === excludeCardId) {
              return;
            }
            (entry.examples || []).forEach(function (sentence) {
              candidates.push(sentence);
            });
          });
          return sampleArray(candidates, n);
        }

        function pickDistractorDefinitions(excludeCardId, n) {
          var candidates = [];
          quizPool.forEach(function (entry) {
            if (entry.id !== excludeCardId && entry.definition) {
              candidates.push(entry.definition);
            }
          });
          return sampleArray(candidates, n);
        }

        /**
         * Renders `choices` ({text, correct}[]) as a shuffled column of
         * buttons in quizChoicesEl; clicking one locks the whole set,
         * colors the picked button and always reveals the correct one too
         * (green), then hands the result to `onAnswer`.
         */
        function renderQuizChoices(choices, onAnswer) {
          quizChoicesEl.replaceChildren();
          quizChoiceEntries = shuffleInPlace(choices.slice()).map(function (choice) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lms-flashcard__quiz-choice';
            btn.textContent = choice.text;
            // Answer-choice text can be a full sentence — never let a click
            // inside it bubble up to cardEl and flip the card mid-answer.
            btn.addEventListener('click', function (e) {
              e.stopPropagation();
              if (quizAnswered) {
                return;
              }
              quizAnswered = true;
              quizChoiceEntries.forEach(function (entry) {
                entry.btn.disabled = true;
                if (entry.choice.correct) {
                  entry.btn.classList.add('is-correct');
                }
              });
              if (!choice.correct) {
                btn.classList.add('is-incorrect');
              }
              onAnswer(choice.correct);
            });
            quizChoicesEl.appendChild(btn);
            return { btn: btn, choice: choice };
          });
        }

        function chooseQuizChoice(index) {
          if (quizAnswered || quizChoicesEl.hidden || quizWriteEl.hidden === false) {
            return;
          }
          var entry = quizChoiceEntries[index];
          if (!entry || entry.btn.disabled) {
            return;
          }
          entry.btn.click();
        }

        /**
         * Builds the level-1 exercise: an English example sentence with
         * `term` blanked out, multiple-choice word options. Falls back to
         * the definition as the prompt (no blank) when the card has no
         * usable example — `found` is whatever findQuizExample() returned.
         */
        function buildQuizLevel1(card, found) {
          quizSentenceEl.replaceChildren();
          if (found) {
            quizSentenceEl.appendChild(document.createTextNode(found.before));
            var blank = document.createElement('span');
            blank.className = 'lms-flashcard__quiz-blank';
            blank.textContent = '_____';
            quizSentenceEl.appendChild(blank);
            quizSentenceEl.appendChild(document.createTextNode(found.after));
          }
          else {
            quizSentenceEl.textContent = card.definition || '';
          }
          var choices = pickDistractorTerms(card.term, 3).map(function (text) {
            return { text: text, correct: false };
          });
          choices.push({ text: card.term, correct: true });
          renderQuizChoices(choices, finishQuizAnswer);
        }

        /**
         * Builds the level-2 exercise: the Vietnamese translation of an
         * example (term's pronunciation played separately, see showCard()),
         * multiple-choice among English sentences — pick the one that
         * matches. Falls back to matching the English *definition* among
         * other definitions when there's no usable example/translation.
         */
        function buildQuizLevel2(card, found) {
          if (found) {
            quizSentenceEl.textContent = found.translation;
            var sentenceChoices = pickDistractorSentences(card.id, 3).map(function (text) {
              return { text: text, correct: false };
            });
            sentenceChoices.push({ text: found.sentence, correct: true });
            renderQuizChoices(sentenceChoices, finishQuizAnswer);
          }
          else {
            quizSentenceEl.textContent = card.definition || '';
            var defChoices = pickDistractorDefinitions(card.id, 3).map(function (text) {
              return { text: text, correct: false };
            });
            defChoices.push({ text: card.definition || '', correct: true });
            renderQuizChoices(defChoices, finishQuizAnswer);
          }
        }

        /**
         * Builds the level-3 exercise: same prompt as level 2, but instead
         * of choosing, the student types the full sentence (or, in the
         * fallback case, just the word) from memory — graded by
         * textSimilarity() against quizExpectedText, not an exact match.
         */
        function buildQuizLevel3(card, found) {
          quizChoicesEl.replaceChildren();
          quizChoiceEntries = [];
          quizWriteEl.hidden = false;
          if (found) {
            quizSentenceEl.textContent = found.translation;
            quizExpectedText = found.sentence;
          }
          else {
            quizSentenceEl.textContent = card.definition || '';
            quizExpectedText = card.term;
          }
          quizWriteInputEl.focus();
        }

        /**
         * Builds whichever of the 3 exercises `level` calls for, resetting
         * all the shared quiz UI state first (choices, write box, Next
         * button, answered flag) so nothing leaks over from the previous
         * card.
         */
        function buildQuizExercise(card, level) {
          quizChoicesEl.replaceChildren();
          quizChoiceEntries = [];
          quizSentenceEl.replaceChildren();
          quizWriteEl.hidden = true;
          quizWriteEl.querySelectorAll('.lms-flashcard__quiz-answer').forEach(function (answerEl) {
            answerEl.remove();
          });
          quizWriteInputEl.value = '';
          quizWriteInputEl.readOnly = false;
          quizWriteInputEl.classList.remove('is-correct', 'is-incorrect');
          if (quizCheckEl) {
            quizCheckEl.disabled = false;
          }
          if (quizNextEl) {
            quizNextEl.hidden = true;
          }
          quizAnswered = false;
          quizExpectedText = '';

          var examples = Array.isArray(card.examples) ? card.examples : [];
          var found = findQuizExample(card.term, examples, level >= 2);

          if (level === 1) {
            buildQuizLevel1(card, found);
          }
          else if (level === 2) {
            buildQuizLevel2(card, found);
          }
          else {
            buildQuizLevel3(card, found);
          }
        }

        /**
         * Grades the level-3 dictation box against quizExpectedText (≥80%
         * character similarity to pass — see textSimilarity()), shows the
         * expected answer for reference either way, and locks the input.
         */
        function checkQuizWrite() {
          if (quizAnswered) {
            return;
          }
          quizAnswered = true;
          var typed = quizWriteInputEl.value || '';
          var correct = textSimilarity(typed, quizExpectedText) >= QUIZ_PASS_SIMILARITY;
          quizWriteInputEl.readOnly = true;
          quizWriteInputEl.classList.add(correct ? 'is-correct' : 'is-incorrect');
          if (quizCheckEl) {
            quizCheckEl.disabled = true;
          }
          quizWriteEl.querySelectorAll('.lms-flashcard__quiz-answer').forEach(function (oldAnswerEl) {
            oldAnswerEl.remove();
          });
          var answerEl = document.createElement('p');
          answerEl.className = 'lms-flashcard__quiz-answer';
          answerEl.textContent = Drupal.t('Answer: @text', { '@text': quizExpectedText });
          quizWriteEl.appendChild(answerEl);
          finishQuizAnswer(correct);
        }

        /**
         * Shared tail for every Quiz Practice exercise's answer path (MCQ
         * click or the level-3 Check button) — reveal the back face for
         * full confirmation (term, pronunciation, examples) and submit the
         * result. Also the answer path for "Study Now" (style=flip)
         * mastery level 3, which reuses this exact exercise (see the quiz
         * block in showCard()) — routed to finishMasteryLevel3() instead
         * of submitQuizResult() there so it never touches the real SM-2
         * schedule, matching level 1/2's own no-SM-2-impact promise (see
         * FlashcardApiController::submitMastery()'s docblock).
         */
        function finishQuizAnswer(correct) {
          cardEl.focus();
          if (!revealed) {
            flip();
          }
          if (style === 'flip' && currentMasteryLevel === 3) {
            finishMasteryLevel3(correct);
          }
          else {
            submitQuizResult(correct);
          }
        }

        /**
         * Sends the quiz result to the same endpoint the 4 grade buttons
         * use — mirrors submitSpellResult() exactly (see its docblock),
         * just with `quiz_correct` driving CardStat.field_quiz_level
         * instead of field_spell_level.
         */
        function submitQuizResult(correct) {
          loading = true;
          fetch(reviewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, quality: correct ? 2 : 0, quiz_correct: correct }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              loading = false;
              if (data && typeof data.quiz_level === 'number') {
                currentQuizLevel = data.quiz_level;
                updateQuizLevelBadge();
              }
              if (quizNextEl) {
                quizNextEl.hidden = false;
              }
            })
            .catch(function () {
              loading = false;
              if (quizNextEl) {
                quizNextEl.hidden = false;
              }
            });
        }

        function updatePronounceLevelBadge() {
          if (!pronounceLevelEl) {
            return;
          }
          pronounceLevelEl.hidden = false;
          pronounceLevelEl.textContent = Drupal.t('Level @level of @max', {
            '@level': currentPronounceLevel,
            '@max': PRONOUNCE_LEVEL_MAX,
          });
        }

        /** Sets the feedback line under the mic button; `tone` is '', 'is-correct', 'is-close' or 'is-incorrect'. */
        function setPronounceStatus(text, tone) {
          if (!pronounceStatusEl) {
            return;
          }
          pronounceStatusEl.hidden = !text;
          pronounceStatusEl.textContent = text || '';
          pronounceStatusEl.className = 'lms-flashcard__pronounce-status' + (tone ? ' ' + tone : '');
        }

        /**
         * Switches from the mic button to the manual self-grade buttons for
         * the rest of the session — used both when the browser never had
         * Web Speech API support (checked once above) and when the student
         * denies the mic permission prompt (checked per attempt, since a
         * browser only reports that once start() is actually called).
         */
        function switchToPronounceFallback(message) {
          pronounceSpeechSupported = false;
          if (micBtnEl) {
            micBtnEl.hidden = true;
          }
          if (pronounceFallbackEl) {
            pronounceFallbackEl.hidden = false;
          }
          if (message) {
            setPronounceStatus(message, 'is-incorrect');
          }
        }

        /**
         * Sends the pronunciation result to the same endpoint the 4 grade
         * buttons use — mirrors submitSpellResult()/submitQuizResult()
         * exactly (see their docblocks), just with `pronunciation_correct`
         * driving CardStat.field_pronunciation_level instead.
         */
        function submitPronunciationResult(correct) {
          loading = true;
          fetch(reviewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, quality: correct ? 2 : 0, pronunciation_correct: correct }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              loading = false;
              if (data && typeof data.pronunciation_level === 'number') {
                currentPronounceLevel = data.pronunciation_level;
                updatePronounceLevelBadge();
              }
              if (pronounceNextEl) {
                pronounceNextEl.hidden = false;
              }
            })
            .catch(function () {
              loading = false;
              if (pronounceNextEl) {
                pronounceNextEl.hidden = false;
              }
            });
        }

        /**
         * Grades one recognized transcript against the current card's term
         * (see normalizeForCompare()/textSimilarity()) into 3 tiers. A
         * "correct" match — or running out of retries — ends the attempt
         * and submits the result; a "close" or outright miss with retries
         * left just gives feedback and leaves the mic ready to go again.
         */
        function handlePronounceResult(transcript) {
          if (pronounceResolved) {
            return;
          }
          pronounceAttempts++;
          var similarity = textSimilarity(normalizeForCompare(transcript), normalizeForCompare(currentCard.term));
          var attemptsLeft = PRONOUNCE_MAX_ATTEMPTS - pronounceAttempts;
          var heard = transcript || Drupal.t('(nothing)');

          if (similarity >= PRONOUNCE_CORRECT_SIMILARITY) {
            pronounceResolved = true;
            if (micBtnEl) {
              micBtnEl.disabled = true;
            }
            setPronounceStatus(Drupal.t('Nice pronunciation! Heard: "@text"', { '@text': heard }), 'is-correct');
            submitPronunciationResult(true);
          }
          else if (attemptsLeft <= 0) {
            pronounceResolved = true;
            if (micBtnEl) {
              micBtnEl.disabled = true;
            }
            setPronounceStatus(Drupal.t('Not quite — heard: "@text". The word is "@term".', { '@text': heard, '@term': currentCard.term }), 'is-incorrect');
            submitPronunciationResult(false);
          }
          else if (similarity >= PRONOUNCE_CLOSE_SIMILARITY) {
            setPronounceStatus(Drupal.t('Close — try again (@n left). Heard: "@text"', { '@n': attemptsLeft, '@text': heard }), 'is-close');
          }
          else {
            setPronounceStatus(Drupal.t('Not quite — try again (@n left). Heard: "@text"', { '@n': attemptsLeft, '@text': heard }), 'is-incorrect');
          }
        }

        /**
         * Starts one Web Speech API recording attempt. A fresh recognizer
         * is created per attempt rather than reused — simpler than tracking
         * whether a previous instance is still mid-request, and this is
         * cheap to construct.
         */
        function startPronounceRecording() {
          if (!pronounceSpeechSupported || pronounceListening || pronounceResolved) {
            return;
          }
          var recognition = new SpeechRecognitionCtor();
          recognition.lang = speechLang;
          recognition.continuous = false;
          recognition.interimResults = false;
          recognition.maxAlternatives = 1;

          pronounceListening = true;
          if (micBtnEl) {
            micBtnEl.classList.add('is-recording');
          }
          setPronounceStatus(Drupal.t('Listening…'), '');

          recognition.addEventListener('result', function (e) {
            var transcript = e.results && e.results[0] && e.results[0][0] ? e.results[0][0].transcript : '';
            handlePronounceResult(transcript);
          });
          recognition.addEventListener('error', function (e) {
            pronounceListening = false;
            if (micBtnEl) {
              micBtnEl.classList.remove('is-recording');
            }
            if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
              switchToPronounceFallback(Drupal.t('Microphone access was denied — grade yourself instead:'));
              return;
            }
            if (!pronounceResolved) {
              setPronounceStatus(Drupal.t("Didn't catch that — try again."), 'is-incorrect');
            }
          });
          recognition.addEventListener('end', function () {
            pronounceListening = false;
            if (micBtnEl) {
              micBtnEl.classList.remove('is-recording');
            }
          });

          try {
            recognition.start();
          }
          catch (err) {
            pronounceListening = false;
            if (micBtnEl) {
              micBtnEl.classList.remove('is-recording');
            }
          }
        }

        /**
         * Resets all Pronunciation Practice state for a new card — mirrors
         * buildSpellPuzzle()/buildQuizExercise()'s role for their styles.
         * `level` is purely for the informational badge (see
         * PRONOUNCE_LEVEL_MAX above); it never changes the exercise itself.
         */
        function buildPronunciationExercise(card, level) {
          pronounceAttempts = 0;
          pronounceResolved = false;
          pronounceListening = false;
          currentPronounceLevel = level || 1;
          updatePronounceLevelBadge();

          if (pronounceDefinitionEl) {
            pronounceDefinitionEl.textContent = card.definition || '';
          }
          setPronounceStatus('', '');
          if (pronounceNextEl) {
            pronounceNextEl.hidden = true;
          }
          if (micBtnEl) {
            micBtnEl.hidden = !pronounceSpeechSupported;
            micBtnEl.disabled = false;
            micBtnEl.classList.remove('is-recording');
          }
          if (pronounceFallbackEl) {
            pronounceFallbackEl.hidden = pronounceSpeechSupported;
          }
          // Tell the student *why* they're seeing the fallback instead of a
          // mic, when it's known ahead of time (unsupported browser or an
          // insecure http:// origin) — a permission denial overwrites this
          // with its own message instead, via switchToPronounceFallback().
          if (pronounceFallbackNoteEl && !pronounceSpeechSupported) {
            pronounceFallbackNoteEl.textContent = pronounceInsecureContext
              ? Drupal.t('Microphone grading needs a secure connection — reopen this page with https:// to use it. For now, grade yourself:')
              : (SpeechRecognitionCtor
                ? Drupal.t('Say the word aloud yourself, then grade it:')
                : Drupal.t("This browser doesn't support speech recognition (try Chrome or Edge). For now, say the word aloud yourself, then grade it:"));
          }
        }

        /**
         * Picks up to n other cards' terms from quizPool as wrong answers
         * for the mastery level-2 recall check — same shape/purpose as
         * pickDistractorTerms() above, kept as an independent copy (own
         * name, own closure state) rather than calling that one directly,
         * so this mode never shares code paths with Quiz Practice.
         */
        function pickMasteryDistractorTerms(excludeTerm, n) {
          var pool = [];
          quizPool.forEach(function (entry) {
            if (entry.term && entry.term !== excludeTerm) {
              pool.push(entry.term);
            }
          });
          return sampleArray(pool, n);
        }

        /**
         * Renders the level-2 "which word matches this meaning?" choices —
         * definition shown, 4 term buttons. Reuses the same
         * .lms-flashcard__quiz-choice CSS class as Quiz Practice purely for
         * visual consistency (a shared style, not shared code/state); every
         * DOM element and JS variable here is this mode's own.
         */
        function renderMasteryChoices(choices) {
          masteryChoicesEl.replaceChildren();
          masteryChoiceEntries = shuffleInPlace(choices.slice()).map(function (choice) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lms-flashcard__quiz-choice';
            btn.textContent = choice.text;
            btn.addEventListener('click', function (e) {
              e.stopPropagation();
              if (masteryAnswered) {
                return;
              }
              masteryAnswered = true;
              masteryChoiceEntries.forEach(function (entry) {
                entry.btn.disabled = true;
                if (entry.choice.correct) {
                  entry.btn.classList.add('is-correct');
                }
              });
              if (!choice.correct) {
                btn.classList.add('is-incorrect');
              }

              // A correct answer on the meaning round advances into the
              // bonus image round instead of finishing right away, for any
              // card with an uploaded image (see startMasteryImageRound())
              // — a brief pause first so the "correct" highlight above is
              // actually seen before the exercise swaps under it. A wrong
              // answer on either round, or a correct answer on the image
              // round itself (masteryImageRoundActive already true by
              // then), falls through to finish the check right away same
              // as before this bonus round existed.
              if (choice.correct && !masteryImageRoundActive && masteryHasImageRound) {
                masteryTransitionPending = true;
                window.setTimeout(startMasteryImageRound, 550);
                return;
              }

              // Same shape as Quiz Practice's own finishQuizAnswer(): submit
              // right away (own endpoint/state, see finishMasteryLevel2())
              // and flip to the back face for full confirmation — the
              // "Next word →" button afterward just loads the next card,
              // nothing further to send.
              cardEl.focus();
              if (!revealed) {
                flip();
              }
              finishMasteryLevel2(choice.correct);
              masteryContinueEl.textContent = Drupal.t('Next word →');
              masteryContinueEl.hidden = false;
              masteryContinueEl.focus();
            });
            masteryChoicesEl.appendChild(btn);
            return { btn: btn, choice: choice };
          });
        }

        /**
         * The bonus "guess the term from its picture" round — only reached
         * after a correct answer on the translation/definition round, and
         * only for a card with an uploaded image (card.image_url). Not a
         * new SM-2 mastery level of its own: finishMasteryLevel2() (see
         * renderMasteryChoices() above) still fires exactly once per card,
         * either right after this round or, for a card with no image,
         * right after the first round — same server call either way, so
         * FlashcardApiController::submitMastery() needed no changes for
         * this.
         */
        function startMasteryImageRound() {
          masteryTransitionPending = false;
          masteryImageRoundActive = true;
          masteryAnswered = false;
          masteryDefinitionEl.hidden = true;
          if (masteryImageEl && masteryImageImgEl && currentCard && currentCard.image_url) {
            masteryImageImgEl.src = currentCard.image_url;
            masteryImageImgEl.alt = currentCard.term || '';
            masteryImageEl.hidden = false;
          }
          var choices = pickMasteryDistractorTerms(currentCard.term, 3).map(function (text) {
            return { text: text, correct: false };
          });
          choices.push({ text: currentCard.term, correct: true });
          renderMasteryChoices(choices);
        }

        /**
         * Keyboard shortcut (1-4) for the level-2 recall check — mirrors
         * chooseQuizChoice() above, own state.
         */
        function chooseMasteryChoice(index) {
          if (masteryAnswered) {
            return;
          }
          var entry = masteryChoiceEntries[index];
          if (!entry || entry.btn.disabled) {
            return;
          }
          entry.btn.click();
        }

        /**
         * Builds the level-2 exercise for `card` — always meaning→term,
         * regardless of the session's own ?direction (level 1 still
         * respects it as always; the recall check is a fixed shape).
         *
         * Prefers the native-language card.translation (e.g. "đậu" for
         * "beans" — see Card::field_translation / the Quick Edit drawer's
         * "Translation" field) over the English card.definition, since
         * recognizing the term from its native-language meaning is the
         * more useful recall check for a learner — falls back to the
         * definition for any card that hasn't had a translation filled in
         * yet, so this never shows a blank prompt.
         */
        function buildMasteryExercise(card) {
          masteryAnswered = false;
          masteryImageRoundActive = false;
          masteryTransitionPending = false;
          masteryHasImageRound = !!card.image_url;
          if (masteryImageEl) {
            masteryImageEl.hidden = true;
          }
          masteryDefinitionEl.hidden = false;
          masteryDefinitionEl.textContent = card.translation || card.definition || '';
          var choices = pickMasteryDistractorTerms(card.term, 3).map(function (text) {
            return { text: text, correct: false };
          });
          choices.push({ text: card.term, correct: true });
          renderMasteryChoices(choices);
        }

        /**
         * Shows/hides the toolbar's Quick Edit button for whichever card is
         * currently on screen, and remembers that card's own GET/POST URLs
         * (each card gets its own endpoint) — called from showCard().
         */
        function toggleQuickEditButton(card) {
          quickEditGetUrl = card.quick_edit_get_url || null;
          quickEditSaveUrl = card.quick_edit_save_url || null;
          if (quickEditBtnEl) {
            quickEditBtnEl.hidden = !card.can_quick_edit;
          }
        }

        // Drawer slide-in/out uses the same "clear hidden, force a layout,
        // then add the class that actually animates" trick showCard() uses
        // for the flip-reset transition — the CSS transitions on
        // transform/opacity live on .is-open, so removing `hidden`
        // instantly (display:none -> flex, still off-screen) before adding
        // it a frame later is what makes the transition play instead of
        // jumping straight to its open state.
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
          // on .lms-flashcard-edit-drawer in flashcard-study.css.
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
          if (editSaveBtnEl) {
            editSaveBtnEl.disabled = saving;
          }
          if (editCancelBtnEl) {
            editCancelBtnEl.disabled = saving;
          }
        }

        /**
         * Builds one example/collocation editable row (two text inputs +
         * remove button) and appends it to containerEl — shared by
         * populateQuickEditForm() (existing rows) and the "+ Add" buttons
         * (one new blank row).
         */
        function addEditableRow(containerEl, textValue, translationValue, textPlaceholder, translationPlaceholder) {
          if (!containerEl) {
            return;
          }
          var row = document.createElement('div');
          row.className = 'lms-flashcard-edit-drawer__pair-row';

          var textInput = document.createElement('input');
          textInput.type = 'text';
          textInput.className = 'lms-flashcard-edit-drawer__pair-input';
          textInput.value = textValue || '';
          textInput.placeholder = textPlaceholder || '';
          textInput.setAttribute('data-pair-text', '1');

          var translationInput = document.createElement('input');
          translationInput.type = 'text';
          translationInput.className = 'lms-flashcard-edit-drawer__pair-input';
          translationInput.value = translationValue || '';
          translationInput.placeholder = translationPlaceholder || '';
          translationInput.setAttribute('data-pair-translation', '1');

          var removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.className = 'lms-flashcard-edit-drawer__pair-remove';
          removeBtn.setAttribute('aria-label', Drupal.t('Remove row'));
          removeBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
          removeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            row.remove();
          });

          row.appendChild(textInput);
          row.appendChild(translationInput);
          row.appendChild(removeBtn);
          containerEl.appendChild(row);
        }

        function buildEditableRows(containerEl, items, textKey, textPlaceholder, translationPlaceholder) {
          if (!containerEl) {
            return;
          }
          containerEl.replaceChildren();
          var rows = (Array.isArray(items) && items.length) ? items : [{}];
          rows.forEach(function (item) {
            addEditableRow(containerEl, item && item[textKey], item && item.translation, textPlaceholder, translationPlaceholder);
          });
        }

        /**
         * Reads every non-empty row back out of an examples/collocations
         * container — a row where the student left both sides blank (e.g.
         * the one default empty row) is simply skipped, matching
         * FlashcardApiController::normalizePairsJson()'s own "skip if both
         * blank" rule. `textKey` becomes the property name the server
         * expects (`sentence` for examples, `phrase` for collocations).
         */
        function collectPairsPayload(containerEl, textKey) {
          var pairs = [];
          if (!containerEl) {
            return pairs;
          }
          containerEl.querySelectorAll('.lms-flashcard-edit-drawer__pair-row').forEach(function (row) {
            var textInput = row.querySelector('[data-pair-text]');
            var translationInput = row.querySelector('[data-pair-translation]');
            var text = textInput ? textInput.value.trim() : '';
            var translation = translationInput ? translationInput.value.trim() : '';
            if (text === '' && translation === '') {
              return;
            }
            var pair = { translation: translation };
            pair[textKey] = text;
            pairs.push(pair);
          });
          return pairs;
        }

        /**
         * Fills the drawer's fields from a {term, definition, ...} payload
         * — either quickEditFormData()'s response or, while that request is
         * still in flight, whatever's already in currentCard so the drawer
         * isn't blank for a moment.
         */
        function populateQuickEditForm(card) {
          if (editTitleEl) {
            editTitleEl.textContent = card.term || Drupal.t('Quick edit');
          }
          if (editTermEl) {
            editTermEl.value = card.term || '';
          }
          if (editDefinitionEl) {
            editDefinitionEl.value = card.definition || '';
          }
          if (editTranslationEl) {
            editTranslationEl.value = card.translation || '';
          }
          if (editPosEl) {
            editPosEl.value = card.part_of_speech || '';
          }
          if (editPronunciationEl) {
            editPronunciationEl.value = card.pronunciation || '';
          }
          if (editDifficultyEl) {
            editDifficultyEl.value = card.difficulty || 'medium';
          }

          editAudioRemoveFlag = false;
          editImageRemoveFlag = false;
          if (editAudioInputEl) {
            editAudioInputEl.value = '';
          }
          if (editImageInputEl) {
            editImageInputEl.value = '';
          }
          if (editAudioCurrentEl) {
            editAudioCurrentEl.hidden = !card.audio_url;
          }
          if (editAudioPreviewEl && card.audio_url) {
            editAudioPreviewEl.src = card.audio_url;
          }
          if (editImageCurrentEl) {
            editImageCurrentEl.hidden = !card.image_url;
          }
          if (editImagePreviewEl && card.image_url) {
            editImagePreviewEl.src = card.image_url;
          }

          buildEditableRows(editExamplesRowsEl, card.examples, 'sentence', Drupal.t('Example sentence'), Drupal.t('Translation'));
          buildEditableRows(editCollocationsRowsEl, card.collocations, 'phrase', Drupal.t('Collocation'), Drupal.t('Translation'));
        }

        /**
         * Opens the drawer for the card currently on screen. Populates
         * immediately from currentCard (already in memory) so the drawer
         * never looks empty, then quietly refetches via GET in the
         * background and re-populates once that lands — guards against the
         * on-screen copy being stale (e.g. edited from another tab).
         */
        function openQuickEdit() {
          if (!currentCard || !quickEditGetUrl || quickEditOpen) {
            return;
          }
          quickEditCardId = currentCard.id;
          quickEditOpen = true;
          setEditStatus('', false);
          setEditSaving(false);
          populateQuickEditForm(currentCard);
          showEditDrawer();
          window.setTimeout(function () {
            if (editTermEl) {
              editTermEl.focus();
            }
          }, 0);

          var openedForCardId = quickEditCardId;
          fetch(quickEditGetUrl, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (!quickEditOpen || quickEditCardId !== openedForCardId || !data || data.status !== 'ok' || !data.card || data.card.id !== openedForCardId) {
                return;
              }
              populateQuickEditForm(data.card);
            })
            .catch(function () {
              // Keep showing the currentCard-populated form — nothing to
              // recover from here, a stale-but-present form beats none.
            });
        }

        function closeQuickEdit() {
          quickEditOpen = false;
          quickEditCardId = null;
          hideEditDrawer();
          if (quickEditBtnEl && !quickEditBtnEl.hidden) {
            quickEditBtnEl.focus();
          }
        }

        /**
         * Patches the currently-shown card in place with a Quick Edit save
         * response — deliberately NOT a call to showCard(card) again: that
         * resets flip state, re-speaks the term, and rebuilds whichever
         * Spelling/Quiz/Pronunciation/Study-Now exercise is in progress,
         * any of which would be disruptive mid-session (see the plan's note
         * on showCard()'s side effects). This only touches the handful of
         * elements that are always on screen regardless of style/exercise
         * step — term/definition/pronunciation/part-of-speech/examples/
         * collocations/image — leaving an in-progress spell/quiz/mastery/
         * pronounce exercise's own markup untouched.
         */
        function applyQuickEditToDisplay(updated) {
          if (!currentCard) {
            return;
          }
          currentCard.term = updated.term;
          currentCard.definition = updated.definition;
          currentCard.translation = updated.translation;
          currentCard.part_of_speech = updated.part_of_speech;
          currentCard.pronunciation = updated.pronunciation;
          currentCard.difficulty = updated.difficulty;
          currentCard.examples = updated.examples || [];
          currentCard.example_sentence = currentCard.examples[0] ? currentCard.examples[0].sentence : '';
          currentCard.example_translation = currentCard.examples[0] ? currentCard.examples[0].translation : '';
          currentCard.collocations = updated.collocations || [];
          currentCard.audio_url = updated.audio_url;
          currentCard.image_url = updated.image_url;

          posEl.textContent = currentCard.part_of_speech || '';
          var direction = currentCard.direction || 'forward';
          if (direction === 'reverse') {
            promptEl.textContent = currentCard.definition;
            backTermEl.textContent = currentCard.term;
            backPronunciationEl.textContent = currentCard.pronunciation || '';
          }
          else {
            termEl.textContent = currentCard.term;
            pronunciationEl.textContent = currentCard.pronunciation || '';
            definitionEl.textContent = currentCard.definition;
          }
          // Spelling Practice shows the pronunciation hint up front despite
          // forcing direction=reverse (see showCard()) — the branch above
          // wrote it onto backPronunciationEl instead in that case, so also
          // refresh the front-facing copy spell mode actually displays.
          if (style === 'spell') {
            pronunciationEl.textContent = currentCard.pronunciation || '';
          }

          if (examplesSectionEl) {
            examplesSectionEl.hidden = currentCard.examples.length === 0;
          }
          renderPairList(examplesEl, currentCard.examples, 'sentence', Drupal.t('Play example sentence'), speakText);
          if (collocationsSectionEl) {
            collocationsSectionEl.hidden = currentCard.collocations.length === 0;
          }
          renderPairList(collocationsEl, currentCard.collocations, 'phrase', Drupal.t('Play collocation'), speakText);

          var showImage = !!currentCard.image_url && style === 'flip' && currentMasteryLevel === 1;
          if (imageFrontEl) {
            imageFrontEl.hidden = !(showImage && direction !== 'reverse');
          }
          if (imageBackEl) {
            imageBackEl.hidden = !(showImage && direction === 'reverse');
          }
          if (showImage && imageFrontImgEl) {
            imageFrontImgEl.src = currentCard.image_url;
            imageFrontImgEl.alt = currentCard.term || '';
          }
          if (showImage && imageBackImgEl) {
            imageBackImgEl.src = currentCard.image_url;
            imageBackImgEl.alt = currentCard.term || '';
          }
        }

        function saveQuickEdit() {
          if (quickEditSaving || !quickEditCardId || !quickEditSaveUrl) {
            return;
          }
          var term = editTermEl ? editTermEl.value.trim() : '';
          var definition = editDefinitionEl ? editDefinitionEl.value.trim() : '';
          if (!term || !definition) {
            setEditStatus(Drupal.t('Term and definition are required.'), true);
            return;
          }

          setEditSaving(true);
          setEditStatus('', false);

          var formData = new FormData();
          formData.append('deck_id', deckId);
          formData.append('term', term);
          formData.append('definition', definition);
          formData.append('translation', editTranslationEl ? editTranslationEl.value.trim() : '');
          formData.append('part_of_speech', editPosEl ? editPosEl.value : '');
          formData.append('pronunciation', editPronunciationEl ? editPronunciationEl.value.trim() : '');
          formData.append('difficulty', editDifficultyEl ? editDifficultyEl.value : 'medium');
          formData.append('examples_json', JSON.stringify(collectPairsPayload(editExamplesRowsEl, 'sentence')));
          formData.append('collocations_json', JSON.stringify(collectPairsPayload(editCollocationsRowsEl, 'phrase')));
          if (editAudioInputEl && editAudioInputEl.files && editAudioInputEl.files[0]) {
            formData.append('audio', editAudioInputEl.files[0]);
          }
          else if (editAudioRemoveFlag) {
            formData.append('remove_audio', '1');
          }
          if (editImageInputEl && editImageInputEl.files && editImageInputEl.files[0]) {
            formData.append('image', editImageInputEl.files[0]);
          }
          else if (editImageRemoveFlag) {
            formData.append('remove_image', '1');
          }

          var savingForCardId = quickEditCardId;
          var saveUrl = quickEditSaveUrl;
          fetch(saveUrl, { method: 'POST', body: formData, headers: { Accept: 'application/json' } })
            .then(function (response) {
              return response.json().then(function (data) { return { ok: response.ok, data: data }; });
            })
            .then(function (result) {
              setEditSaving(false);
              if (!result.ok || !result.data || result.data.status !== 'ok') {
                setEditStatus((result.data && result.data.error) || Drupal.t('Could not save. Please try again.'), true);
                return;
              }
              // The study queue never advances on its own while the drawer
              // is open, but guard anyway against a closed-then-reopened
              // drawer landing this (slow) response on the wrong card.
              if (!currentCard || currentCard.id !== savingForCardId || !result.data.card || result.data.card.id !== savingForCardId) {
                return;
              }
              applyQuickEditToDisplay(result.data.card);
              setEditStatus(Drupal.t('Saved.'), false);
              window.setTimeout(function () {
                if (quickEditOpen && quickEditCardId === savingForCardId) {
                  closeQuickEdit();
                }
              }, 900);
            })
            .catch(function () {
              setEditSaving(false);
              setEditStatus(Drupal.t('Network error. Please try again.'), true);
            });
        }

        function showCard(card) {
          currentCard = card;
          var direction = card.direction || 'forward';

          toggleQuickEditButton(card);

          posEl.textContent = card.part_of_speech || '';

          // "Seen N times" — a plain view counter kept separate from SRS
          // state (see FlashcardApiController::recordCardSeen()), so it
          // counts practice-mode views too.
          if (seenCountEl) {
            var timesSeen = card.times_seen || 1;
            seenCountEl.hidden = false;
            seenCountEl.textContent = Drupal.formatPlural(timesSeen, 'Seen 1 time', 'Seen @count times');
          }

          if (direction === 'reverse') {
            // Prompt with the meaning first; the term/pronunciation/speaker
            // only appear on the back face, once flipped.
            frontTermRowEl.hidden = true;
            pronunciationEl.hidden = true;
            promptEl.hidden = false;
            promptEl.textContent = card.definition;

            backTermRowEl.hidden = false;
            backTermEl.textContent = card.term;
            backPronunciationEl.hidden = false;
            backPronunciationEl.textContent = card.pronunciation || '';
            definitionEl.hidden = true;
          }
          else {
            termEl.textContent = card.term;
            frontTermRowEl.hidden = false;
            pronunciationEl.hidden = false;
            pronunciationEl.textContent = card.pronunciation || '';
            promptEl.hidden = true;

            backTermRowEl.hidden = true;
            definitionEl.hidden = false;
            definitionEl.textContent = card.definition;
          }

          spellEl.hidden = style !== 'spell';
          if (style === 'spell') {
            // Spelling mode forces direction='reverse' (see
            // StudyController::study()), which hides pronunciationEl above
            // until the card is flipped — but the whole point here is
            // recalling the spelling *before* that, so show it up front as
            // another hint alongside the audio button, without waiting for
            // a flip.
            pronunciationEl.hidden = false;
            pronunciationEl.textContent = card.pronunciation || '';

            currentSpellLevel = card.spell_level || 1;
            updateSpellLevelBadge();
            buildSpellPuzzle(card.term, currentSpellLevel, card.examples);
          }
          else if (spellLevelEl) {
            spellLevelEl.hidden = true;
          }

          // "Study Now" (style === 'flip') level 3 is Quiz Practice's own
          // 3 exercises (cloze / listen-match / listen-type), reused as-is
          // — same quizEl markup, same buildQuizExercise()/quiz_level
          // progression a direct Quiz Practice session would show for this
          // card. Only where the *answer* goes differs: finishQuizAnswer()
          // below branches to finishMasteryLevel3() instead of
          // submitQuizResult() here, so this never touches the real SM-2
          // schedule — matching level 1/2's own no-SM-2-impact promise.
          currentMasteryLevel = style === 'flip' ? (card.mastery_level || 1) : 1;
          var showQuiz = style === 'quiz' || (style === 'flip' && currentMasteryLevel === 3);
          quizEl.hidden = !showQuiz;
          if (showQuiz) {
            // Quiz mode also forces direction='reverse' above, which shows
            // promptEl (the definition) on the front by default — but Quiz
            // builds its own prompt text inside quizSentenceEl instead (a
            // cloze sentence, a translation, or the definition as a
            // fallback — see buildQuizExercise()), so suppress the generic
            // one to avoid showing the definition twice.
            promptEl.hidden = true;

            currentQuizLevel = card.quiz_level || 1;
            updateQuizLevelBadge();
            buildQuizExercise(card, currentQuizLevel);
          }
          else if (quizLevelEl) {
            quizLevelEl.hidden = true;
          }

          pronounceEl.hidden = style !== 'pronounce';
          if (style === 'pronounce') {
            currentPronounceLevel = card.pronunciation_level || 1;
            buildPronunciationExercise(card, currentPronounceLevel);
          }
          else if (pronounceLevelEl) {
            pronounceLevelEl.hidden = true;
          }

          // "Study Now" (style === 'flip') level-2 recall check — replaces
          // the normal front face (term hidden, prompt suppressed) with
          // masteryEl's own definition + 4 term choices; level 1 is just
          // the ordinary flip, level 3 is the Quiz-reuse block above —
          // both untouched here. See buildMasteryExercise() and showCard()'s
          // caller-side handling of masteryContinueEl for the rest of this
          // flow. currentMasteryLevel itself is computed earlier, above the
          // quiz block, since that needs it too.
          masteryEl.hidden = !(style === 'flip' && currentMasteryLevel === 2);
          masteryContinueEl.hidden = true;
          // Reset from whatever the previous card (possibly a level-2
          // recall check) left this reading as — see the "Next word →"
          // override in renderMasteryChoices()'s click handler.
          masteryContinueEl.textContent = Drupal.t('Continue →');
          if (style === 'flip' && currentMasteryLevel === 2) {
            frontTermRowEl.hidden = true;
            pronunciationEl.hidden = true;
            promptEl.hidden = true;
            buildMasteryExercise(card);
          }
          if (style === 'flip' && currentMasteryLevel === 3) {
            // The quiz block above already builds the exercise and hides
            // promptEl; still need to hide the front term/pronunciation
            // ourselves the way Quiz's own forced direction='reverse'
            // otherwise would — style=flip keeps whatever direction the
            // session started with, so nothing else hides these here.
            frontTermRowEl.hidden = true;
            pronunciationEl.hidden = true;
          }
          if (hintEl && style === 'flip') {
            // Level 1 is still the ordinary flip, same hint as ever; levels
            // 2-3 have nothing to "reveal" — the exercise is right there on
            // the front — so the hint would just be confusing/wrong.
            hintEl.hidden = currentMasteryLevel !== 1;
          }

          // Interval preview under each grade button (again/hard/good/easy);
          // computed server-side as a dry run through the same SM-2 code
          // that grading uses, never saved — see buildGradePreviews().
          var previews = card.previews || {};
          Object.keys(previewEls).forEach(function (key) {
            previewEls[key].textContent = previews[key] || '';
          });

          var examples = Array.isArray(card.examples) ? card.examples : [];
          if (!examples.length && card.example_sentence) {
            examples = [{
              sentence: card.example_sentence,
              translation: card.example_translation || '',
            }];
          }
          if (examplesSectionEl) {
            examplesSectionEl.hidden = examples.length === 0;
          }
          renderPairList(examplesEl, examples, 'sentence', Drupal.t('Play example sentence'), speakText);

          var collocations = Array.isArray(card.collocations) ? card.collocations : [];
          if (collocationsSectionEl) {
            collocationsSectionEl.hidden = collocations.length === 0;
          }
          renderPairList(collocationsEl, collocations, 'phrase', Drupal.t('Play collocation'), speakText);

          // Card image (see "Image Display" in the plan): only for the
          // plain flip view (both directions), same reasoning as
          // applyQuickEditToDisplay()'s identical computation — Spelling/
          // Quiz/Pronunciation Practice and Study Now's level 2/3 exercises
          // already fill this space with their own markup.
          var showImage = !!card.image_url && style === 'flip' && currentMasteryLevel === 1;
          if (imageFrontEl) {
            imageFrontEl.hidden = !(showImage && direction !== 'reverse');
          }
          if (imageBackEl) {
            imageBackEl.hidden = !(showImage && direction === 'reverse');
          }
          if (showImage && imageFrontImgEl) {
            imageFrontImgEl.src = card.image_url;
            imageFrontImgEl.alt = card.term || '';
          }
          if (showImage && imageBackImgEl) {
            imageBackImgEl.src = card.image_url;
            imageBackImgEl.alt = card.term || '';
          }

          revealed = false;
          // Suppress the flip transition for this specific reset. If the
          // previous card was left flipped (revealed=true — routine in
          // Spelling Practice, which auto-flips after Check), removing
          // 'is-flipped' below would otherwise trigger the normal 0.5s
          // rotate-back CSS transition — but the content above has *already*
          // been swapped to this new card, so mid-rotation the viewer
          // briefly sees the back face with the new card's term/definition
          // already revealed before it settles on the front. Re-enabled one
          // frame later so a genuine user-triggered flip() still animates.
          cardEl.classList.add('lms-flashcard--no-transition');
          cardEl.classList.remove('is-flipped');
          void cardEl.offsetWidth; // Force layout so the line above applies before re-enabling.
          window.requestAnimationFrame(function () {
            cardEl.classList.remove('lms-flashcard--no-transition');
          });
          // The 4 manual grade buttons are never shown any more — Spelling/
          // Quiz/Pronunciation Practice never used them to begin with (see
          // checkSpelling()/submitSpellResult()/submitQuizResult()/
          // submitPronunciationResult(), which auto-grade instead), and
          // Study Now (style=flip) no longer does either, replaced by
          // masteryContinueEl's own flow — see buildMasteryExercise()/
          // handleMasteryContinue() and FlashcardApiController::
          // submitMastery()'s docblock for why. The markup/submitGrade()
          // stay in place, unused, rather than ripping out the (still
          // functionally correct) SM-2 grading plumbing entirely.
          gradesEl.hidden = true;
          // Best-effort: browsers usually allow this since it follows a user
          // gesture (Study Now click, or a grading button click that led
          // here) — if a stricter autoplay policy blocks it, the visible
          // speaker button still lets the student trigger it manually.
          // Quiz levels 2-3 are explicitly a listening exercise, so the
          // term is read immediately there too; level 1 (a reading/cloze
          // task) leaves it to the speaker button instead — same for
          // mastery level 3, which reuses this exact exercise. Mastery
          // level 2 must stay silent here — speakCard() would read the
          // term straight out, giving away the very answer it's testing.
          if (showQuiz) {
            if (currentQuizLevel >= 2) {
              speakTerm();
            }
          }
          else if (!(style === 'flip' && currentMasteryLevel === 2)) {
            speakCard();
          }
        }

        function flip() {
          if (loading) {
            return;
          }
          // Blocks a stray click/Space/Enter from flipping the card mid-way
          // through the pause between answering the mastery meaning round
          // and startMasteryImageRound() actually swapping in the image
          // round — see masteryTransitionPending's own docblock above.
          if (masteryTransitionPending) {
            return;
          }
          // In Spelling/Quiz Practice, a plain click/tap/Enter on the card
          // must not be able to jump straight to the back and reveal the
          // term before the puzzle/exercise has been answered — that would
          // make answering afterwards pointless. checkSpelling() and
          // finishQuizAnswer() are the *only* legitimate ways to reach the
          // back face from here: both flip AFTER marking the attempt
          // answered (spellCheckEl.disabled / quizAnswered), so this guard
          // lets those specific calls through while still blocking a raw
          // click beforehand.
          if (style === 'spell' && !revealed && spellCheckEl && !spellCheckEl.disabled) {
            return;
          }
          if ((style === 'quiz' || (style === 'flip' && currentMasteryLevel === 3)) && !revealed && !quizAnswered) {
            return;
          }
          // Mastery level 2 (the recall check): same reasoning as Quiz
          // above, own state — a raw click can't jump to the back (which
          // would reveal the term/definition, the very answer) before the
          // check is answered.
          if (style === 'flip' && currentMasteryLevel === 2 && !revealed && !masteryAnswered) {
            return;
          }
          revealed = !revealed;
          cardEl.classList.toggle('is-flipped', revealed);
          // Read the term aloud on every click, not just the first time the
          // card appears — flipping front→back or back→front both count.
          speakCard();

          // Mastery level 1: once flipped (the definition has been seen),
          // show the button that sends this card on to its level-2 recall
          // check — see advanceFromLevel1(). Flipping back and forth
          // doesn't re-hide it; only a new card (showCard()) does.
          if (style === 'flip' && currentMasteryLevel === 1 && revealed) {
            masteryContinueEl.hidden = false;
          }
        }

        function loadNext() {
          loading = true;
          cardEl.setAttribute('aria-busy', 'true');
          fetch(nextUrl + '?deck=' + encodeURIComponent(deckId) + '&direction=' + encodeURIComponent(direction) + '&mode=' + encodeURIComponent(mode) + '&style=' + encodeURIComponent(style), { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              loading = false;
              cardEl.removeAttribute('aria-busy');
              if (!data || data.status !== 'ok') {
                stage.hidden = true;
                emptyState.hidden = false;
                if (summaryUrl) {
                  window.location.href = summaryUrl;
                }
                return;
              }
              updateProgress(data.remaining || 0);
              showCard(data.card);
            })
            .catch(function () {
              loading = false;
              cardEl.removeAttribute('aria-busy');
            });
        }

        function submitGrade(quality) {
          if (loading) {
            return;
          }
          loading = true;
          fetch(reviewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, quality: quality }),
          })
            .then(function () {
              loadNext();
            })
            .catch(function () {
              loading = false;
            });
        }

        /**
         * Level 1 (the flip) is done — queue this card again a few cards
         * ahead, now at level 2, then load the next card. The only "Study
         * Now" mastery step with nothing to review first, so submit and
         * advance happen together (unlike finishMasteryLevel2() below).
         */
        function advanceFromLevel1() {
          if (loading) {
            return;
          }
          loading = true;
          fetch(masteryUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, action: 'level1_done' }),
          })
            .then(function () {
              loadNext();
            })
            .catch(function () {
              loading = false;
            });
        }

        /**
         * Level 2 (the recall check) was just answered — sent immediately
         * from the choice click (see renderMasteryChoices()), same moment
         * Quiz Practice's own finishQuizAnswer()/submitQuizResult() send
         * theirs, rather than waiting for the "Next word →" click that
         * follows: that button only needs to call loadNext() once this
         * has already landed, so the student can review the flipped-back
         * card first without holding up the actual write. Pass moves the
         * card on to level 3 (Quiz Practice's own exercises, reused — see
         * the quiz block in showCard()); fail sends it back to level 1.
         */
        function finishMasteryLevel2(correct) {
          fetch(masteryUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, action: correct ? 'level2_pass' : 'level2_fail' }),
          }).catch(function () {
            // Best-effort — worst case this card's mastery step is retried
            // (still shown again later, same as if it had failed) rather
            // than the student getting stuck on a network hiccup.
          });
        }

        /**
         * Level 3 (Quiz Practice's own exercises, reused) was just
         * answered — sent from finishQuizAnswer() the same moment
         * submitQuizResult() would send for a real Quiz Practice session,
         * for the same reason finishMasteryLevel2() explains. Pass removes
         * the card from the session for good — the highest level, nothing
         * further to clear; fail sends it all the way back to level 1.
         * Also bumps CardStat.field_quiz_level up/down a notch exactly
         * like submitQuizResult() does (via FlashcardApiController::
         * submitMastery()'s own quiz_correct handling), so the next time
         * this card reaches level 3 — here or in a real Quiz Practice
         * session — buildQuizExercise() picks from the same progression
         * either way.
         */
        function finishMasteryLevel3(correct) {
          fetch(masteryUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ deck_id: deckId, action: correct ? 'level3_pass' : 'level3_fail', quiz_correct: correct }),
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (data && typeof data.quiz_level === 'number') {
                currentQuizLevel = data.quiz_level;
                updateQuizLevelBadge();
              }
              if (quizNextEl) {
                quizNextEl.hidden = false;
              }
            })
            .catch(function () {
              if (quizNextEl) {
                quizNextEl.hidden = false;
              }
            });
        }

        /**
         * masteryContinueEl's click handler — what it does depends on
         * which step is currently showing (see showCard()'s
         * currentMasteryLevel). Level 2's own write already happened in
         * renderMasteryChoices() the moment it was answered, so this is
         * just "go to the next card" there. (Level 3 doesn't use
         * masteryContinueEl at all — its own quizNextEl plays that role,
         * already wired to loadNext() same as a real Quiz Practice
         * session's Next button.)
         */
        function handleMasteryContinue() {
          if (currentMasteryLevel === 2) {
            loadNext();
          }
          else {
            advanceFromLevel1();
          }
        }

        [speakFrontEl, speakBackEl, speakSpellEl].forEach(function (speakEl) {
          if (speakEl) {
            speakEl.addEventListener('click', function (e) {
              e.stopPropagation();
              speakCard();
            });
          }
        });

        // Quiz's own speaker button always means "play the word" — unlike
        // the group above, speakCard()'s reveal-state gating would make it
        // silently do nothing while still unanswered (termShowing would be
        // false), which is exactly when the student most needs to replay
        // it.
        if (speakQuizEl) {
          speakQuizEl.addEventListener('click', function (e) {
            e.stopPropagation();
            speakTerm();
          });
        }

        // Both buttons live inside cardEl, whose own keydown listener flips
        // the card on Enter/Space — without stopping propagation here too,
        // tabbing to either button and pressing Enter to activate it would
        // also bubble up and trigger an unwanted flip().
        if (spellCheckEl) {
          spellCheckEl.addEventListener('click', function (e) {
            e.stopPropagation();
            checkSpelling();
          });
          spellCheckEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
          });
        }

        if (spellNextEl) {
          spellNextEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!loading) {
              loadNext();
            }
          });
          spellNextEl.addEventListener('keydown', function (e) {
            // Stopped from bubbling to cardEl either way (see above); handle
            // → here too rather than relying on the document-level listener
            // below, since that one would never see it once stopped here —
            // submitSpellResult() moves focus straight to this button.
            e.stopPropagation();
            if (e.key === 'ArrowRight' && !loading) {
              e.preventDefault();
              loadNext();
            }
          });
        }

        if (quizCheckEl) {
          quizCheckEl.addEventListener('click', function (e) {
            e.stopPropagation();
            checkQuizWrite();
          });
          quizCheckEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
          });
        }

        if (quizWriteInputEl) {
          quizWriteInputEl.addEventListener('click', function (e) {
            e.stopPropagation();
          });
          quizWriteInputEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
            if (e.key === ' ') {
              // A dictated sentence legitimately needs spaces — unlike the
              // spelling puzzle's single-letter boxes, don't hijack it for
              // replaying audio here.
              return;
            }
            if (e.key === 'Enter') {
              e.preventDefault();
              checkQuizWrite();
            }
          });
        }

        if (quizNextEl) {
          quizNextEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!loading) {
              loadNext();
            }
          });
          quizNextEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
            if (e.key === 'ArrowRight' && !loading) {
              e.preventDefault();
              loadNext();
            }
          });
        }

        // Card click/keydown listeners flip the card — stop propagation on
        // every control inside .lms-flashcard__pronounce for the same
        // reason as the spell/quiz controls above.
        if (micBtnEl) {
          micBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            startPronounceRecording();
          });
          micBtnEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
          });
        }

        if (pronounceFallbackCorrectEl) {
          pronounceFallbackCorrectEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (pronounceResolved) {
              return;
            }
            pronounceResolved = true;
            submitPronunciationResult(true);
          });
          pronounceFallbackCorrectEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
          });
        }

        if (pronounceFallbackIncorrectEl) {
          pronounceFallbackIncorrectEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (pronounceResolved) {
              return;
            }
            pronounceResolved = true;
            submitPronunciationResult(false);
          });
          pronounceFallbackIncorrectEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
          });
        }

        if (pronounceNextEl) {
          pronounceNextEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!loading) {
              loadNext();
            }
          });
          pronounceNextEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
            if (e.key === 'ArrowRight' && !loading) {
              e.preventDefault();
              loadNext();
            }
          });
        }

        // The translation is blurred by default (CSS :hover un-blurs it);
        // a click/tap toggles it too so it's reachable on touch devices,
        // which have no hover, without also flipping the card.
        [examplesEl, collocationsEl].forEach(function (listEl) {
          listEl.addEventListener('click', function (e) {
            var translation = e.target.closest('.lms-flashcard__example-translation');
            if (!translation) {
              return;
            }
            e.stopPropagation();
            translation.classList.toggle('is-revealed');
          });
        });

        cardEl.addEventListener('click', flip);
        cardEl.addEventListener('keydown', function (e) {
          if (e.key === ' ') {
            e.preventDefault();
            // Stop it here so it doesn't also reach the document-level
            // fallback below and call speakCard() a second time.
            e.stopPropagation();
            if (style === 'spell' || style === 'pronounce') {
              speakCard();
            }
            else if (style === 'quiz' || (style === 'flip' && currentMasteryLevel === 3)) {
              speakTerm();
            }
            else {
              flip();
            }
            return;
          }
          if (e.key === 'Enter') {
            e.preventDefault();
            flip();
          }
        });

        gradesEl.querySelectorAll('[data-quality]').forEach(function (button) {
          button.addEventListener('click', function (e) {
            e.stopPropagation();
            submitGrade(parseInt(button.getAttribute('data-quality'), 10));
          });
        });

        masteryContinueEl.addEventListener('click', function (e) {
          e.stopPropagation();
          handleMasteryContinue();
        });

        document.addEventListener('keydown', function (e) {
          // Belt-and-suspenders alongside editDrawerEl's own
          // stopPropagation() below: if the drawer is open, none of the
          // study session's keyboard shortcuts (space/1-4/arrows) should
          // fire, no matter what currently has focus.
          if (loading || quickEditOpen) {
            return;
          }
          // Spelling/Quiz/Pronunciation Practice have no manual grading —
          // 1-4 do nothing there, and → (only once the Next button has
          // appeared, i.e. after an answer has been submitted and saved)
          // advances instead. Space replays the pronunciation — normally
          // handled by cardEl's or an input's own keydown listener, but
          // neither fires when nothing on the card has focus at all, hence
          // the fallback.
          if (style === 'spell') {
            if (e.key === 'ArrowRight' && spellNextEl && !spellNextEl.hidden) {
              e.preventDefault();
              loadNext();
            }
            else if (e.key === ' ') {
              e.preventDefault();
              speakCard();
            }
            return;
          }
          if (style === 'quiz' || (style === 'flip' && currentMasteryLevel === 3)) {
            var quizChoiceMap = { '1': 0, '2': 1, '3': 2, '4': 3 };
            if (quizChoiceMap.hasOwnProperty(e.key) && quizNextEl && quizNextEl.hidden) {
              e.preventDefault();
              chooseQuizChoice(quizChoiceMap[e.key]);
            }
            else if (e.key === 'ArrowRight' && quizNextEl && !quizNextEl.hidden) {
              e.preventDefault();
              loadNext();
            }
            else if (e.key === ' ') {
              e.preventDefault();
              speakTerm();
            }
            return;
          }
          if (style === 'pronounce') {
            if (e.key === 'ArrowRight' && pronounceNextEl && !pronounceNextEl.hidden) {
              e.preventDefault();
              loadNext();
            }
            else if (e.key === ' ') {
              e.preventDefault();
              speakCard();
            }
            return;
          }
          if (style === 'flip') {
            // Level 1: Enter/click flips as normal (handled by cardEl's own
            // keydown listener above), then ArrowRight advances once
            // masteryContinueEl has appeared. Level 2: 1-4 picks a recall
            // choice while unanswered, then ArrowRight (relabeled "Next
            // word →" by then) moves on — no manual grading shortcuts (1-4
            // as Again/Hard/Good/Easy) any more for this style.
            var masteryChoiceMap = { '1': 0, '2': 1, '3': 2, '4': 3 };
            if (currentMasteryLevel === 2 && masteryChoiceMap.hasOwnProperty(e.key) && !masteryAnswered) {
              e.preventDefault();
              chooseMasteryChoice(masteryChoiceMap[e.key]);
            }
            else if (e.key === 'ArrowRight' && !masteryContinueEl.hidden) {
              e.preventDefault();
              handleMasteryContinue();
            }
            else if (e.key === ' ' && currentMasteryLevel === 1) {
              e.preventDefault();
              speakCard();
            }
          }
        });

        // Quick Edit wiring (see QUICK_EDIT_STUDY_CARD_PLAN.md). The drawer/
        // overlay listeners' stopPropagation() (click + keydown) is what
        // actually keeps typing/clicking inside the sidebar from reaching
        // cardEl's or document's own shortcut handlers — the toolbar
        // button and drawer being DOM siblings of the card, not ancestors/
        // descendants of it, is what makes that a plain stopPropagation()
        // rather than needing anything cleverer.
        if (quickEditBtnEl) {
          quickEditBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            openQuickEdit();
          });
        }
        if (editCloseBtnEl) {
          editCloseBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            closeQuickEdit();
          });
        }
        if (editCancelBtnEl) {
          editCancelBtnEl.addEventListener('click', function (e) {
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
        if (editSaveBtnEl) {
          editSaveBtnEl.addEventListener('click', function (e) {
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
        if (editAudioRemoveBtnEl) {
          editAudioRemoveBtnEl.addEventListener('click', function (e) {
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
        if (editImageRemoveBtnEl) {
          editImageRemoveBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            editImageRemoveFlag = true;
            if (editImageInputEl) {
              editImageInputEl.value = '';
            }
            if (editImageCurrentEl) {
              editImageCurrentEl.hidden = true;
            }
          });
        }
        if (editAudioInputEl) {
          editAudioInputEl.addEventListener('change', function () {
            if (editAudioInputEl.files && editAudioInputEl.files[0]) {
              editAudioRemoveFlag = false;
            }
          });
        }
        if (editImageInputEl) {
          editImageInputEl.addEventListener('change', function () {
            if (editImageInputEl.files && editImageInputEl.files[0]) {
              editImageRemoveFlag = false;
            }
          });
        }
        if (editExamplesAddBtnEl) {
          editExamplesAddBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            addEditableRow(editExamplesRowsEl, '', '', Drupal.t('Example sentence'), Drupal.t('Translation'));
          });
        }
        if (editCollocationsAddBtnEl) {
          editCollocationsAddBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            addEditableRow(editCollocationsRowsEl, '', '', Drupal.t('Collocation'), Drupal.t('Translation'));
          });
        }

        loadNext();
      });
    },
  };

})(Drupal, once, drupalSettings);
