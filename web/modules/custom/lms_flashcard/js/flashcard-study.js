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
        var summaryUrl = settings.summaryUrl;
        // Only actually consulted server-side on the call that (re)builds
        // the queue (see FlashcardApiController::nextCard()) — sent on
        // every call anyway since that's harmless and simpler than tracking
        // "is this the first call" here too.
        var direction = settings.direction || 'forward';
        var mode = settings.practice ? 'practice' : 'srs';
        // 'spell' = Spelling Practice (fill in the missing letters of the
        // term); 'quiz' = Quiz Practice (3 example-sentence exercises —
        // see buildQuizExercise()). Mainly a front-end rendering choice,
        // but also sent to nextCard() (see loadNext() below) so it can
        // queue every card in the deck instead of just what's due — see
        // FlashcardApiController::nextCard()'s $drillAll. It never affects
        // whether a grade gets persisted to the real SM-2 schedule, though
        // — that's $practice (mode=practice) alone, still driven only by
        // settings.practice.
        var style = ['spell', 'quiz'].indexOf(settings.style) !== -1 ? settings.style : 'flip';
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
        var gradesEl = section.querySelector('[data-flashcard-grades]');

        // Spelling/Quiz mode's hint differs from the shared default in two
        // ways: clicking/tapping the card no longer flips it early (see
        // flip()'s guard below — the exercise must be answered first), and
        // Space replays the pronunciation instead of flipping. Set once
        // here since `style` never changes mid-session.
        var hintEl = section.querySelector('[data-flashcard-hint]');
        if (hintEl && style === 'spell') {
          hintEl.textContent = Drupal.t('Fill in the blanks, then check · Press space to hear pronunciation');
        }
        else if (hintEl && style === 'quiz') {
          hintEl.textContent = Drupal.t('Choose or type an answer · Press space to hear pronunciation');
        }

        var termEl = section.querySelector('[data-flashcard-term]');
        var posEl = section.querySelector('[data-flashcard-pos]');
        var pronunciationEl = section.querySelector('[data-flashcard-pronunciation]');
        var seenCountEl = section.querySelector('[data-flashcard-seen-count]');
        var definitionEl = section.querySelector('[data-flashcard-definition]');
        var examplesEl = section.querySelector('[data-flashcard-examples]');

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

        // Interval-preview <span>s inside each grade button, keyed by
        // again/hard/good/easy — populated from card.previews in showCard().
        var previewEls = {};
        section.querySelectorAll('[data-flashcard-preview]').forEach(function (el) {
          previewEls[el.getAttribute('data-flashcard-preview')] = el;
        });

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
         * Shared tail for every Quiz Practice answer path (MCQ click or
         * the level-3 Check button): reveal the back face for full
         * confirmation (term, pronunciation, examples) and submit the
         * result.
         */
        function finishQuizAnswer(correct) {
          cardEl.focus();
          if (!revealed) {
            flip();
          }
          submitQuizResult(correct);
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

        function showCard(card) {
          currentCard = card;
          var direction = card.direction || 'forward';

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

          quizEl.hidden = style !== 'quiz';
          if (style === 'quiz') {
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

          // Interval preview under each grade button (again/hard/good/easy);
          // computed server-side as a dry run through the same SM-2 code
          // that grading uses, never saved — see buildGradePreviews().
          var previews = card.previews || {};
          Object.keys(previewEls).forEach(function (key) {
            previewEls[key].textContent = previews[key] || '';
          });

          examplesEl.replaceChildren();
          var examples = Array.isArray(card.examples) ? card.examples : [];
          if (!examples.length && card.example_sentence) {
            examples = [{
              sentence: card.example_sentence,
              translation: card.example_translation || '',
            }];
          }
          examples.forEach(function (example) {
            var pair = document.createElement('div');
            pair.className = 'lms-flashcard__example-pair';

            var sentenceRow = document.createElement('div');
            sentenceRow.className = 'lms-flashcard__example-row';

            var speakExample = document.createElement('button');
            speakExample.type = 'button';
            speakExample.className = 'lms-flashcard__example-speak';
            speakExample.setAttribute('aria-label', Drupal.t('Play example sentence'));
            speakExample.title = Drupal.t('Play example sentence');
            speakExample.innerHTML = '<i class="fas fa-volume-up" aria-hidden="true"></i>';
            speakExample.addEventListener('click', function (e) {
              e.stopPropagation();
              speakText(example.sentence || '');
            });
            sentenceRow.appendChild(speakExample);

            var sentence = document.createElement('div');
            sentence.className = 'lms-flashcard__example';
            sentence.textContent = example.sentence || '';
            sentenceRow.appendChild(sentence);
            pair.appendChild(sentenceRow);

            if (example.translation) {
              var translation = document.createElement('button');
              translation.type = 'button';
              translation.className = 'lms-flashcard__example-translation';
              translation.textContent = example.translation;
              translation.title = Drupal.t('Click to reveal the translation');
              pair.appendChild(translation);
            }
            examplesEl.appendChild(pair);
          });
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
          // Grading is allowed from either side — sometimes a student
          // already knows the word and wants to grade it without flipping
          // — so the buttons just need to be visible once a real card has
          // loaded, not toggled per flip state. Spelling Practice has no
          // manual grade buttons at all (see checkSpelling()/
          // submitSpellResult()/submitQuizResult()), so they stay hidden
          // for either.
          gradesEl.hidden = style === 'spell' || style === 'quiz';
          // Best-effort: browsers usually allow this since it follows a user
          // gesture (Study Now click, or a grading button click that led
          // here) — if a stricter autoplay policy blocks it, the visible
          // speaker button still lets the student trigger it manually.
          // Quiz levels 2-3 are explicitly a listening exercise, so the
          // term is read immediately there too; level 1 (a reading/cloze
          // task) leaves it to the speaker button instead.
          if (style === 'quiz') {
            if (currentQuizLevel >= 2) {
              speakTerm();
            }
          }
          else {
            speakCard();
          }
        }

        function flip() {
          if (loading) {
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
          if (style === 'quiz' && !revealed && !quizAnswered) {
            return;
          }
          revealed = !revealed;
          cardEl.classList.toggle('is-flipped', revealed);
          // Read the term aloud on every click, not just the first time the
          // card appears — flipping front→back or back→front both count.
          speakCard();
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

        // The translation is blurred by default (CSS :hover un-blurs it);
        // a click/tap toggles it too so it's reachable on touch devices,
        // which have no hover, without also flipping the card.
        examplesEl.addEventListener('click', function (e) {
          var translation = e.target.closest('.lms-flashcard__example-translation');
          if (!translation) {
            return;
          }
          e.stopPropagation();
          translation.classList.toggle('is-revealed');
        });

        cardEl.addEventListener('click', flip);
        cardEl.addEventListener('keydown', function (e) {
          if (e.key === ' ') {
            e.preventDefault();
            // Stop it here so it doesn't also reach the document-level
            // fallback below and call speakCard() a second time.
            e.stopPropagation();
            if (style === 'spell') {
              speakCard();
            }
            else if (style === 'quiz') {
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

        document.addEventListener('keydown', function (e) {
          if (loading) {
            return;
          }
          // Spelling/Quiz Practice have no manual grading — 1-4 do nothing
          // there, and → (only once the Next button has appeared, i.e.
          // after an answer has been submitted and saved) advances instead.
          // Space replays the pronunciation — normally handled by cardEl's
          // or an input's own keydown listener, but neither fires when
          // nothing on the card has focus at all, hence the fallback.
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
          if (style === 'quiz') {
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
          var map = { '1': 0, '2': 1, '3': 2, '4': 3 };
          if (map.hasOwnProperty(e.key)) {
            submitGrade(map[e.key]);
          }
        });

        loadNext();
      });
    },
  };

})(Drupal, once, drupalSettings);
