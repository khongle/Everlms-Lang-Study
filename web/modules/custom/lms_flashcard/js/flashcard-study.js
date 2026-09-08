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

        var section = stage.closest('.lms-flashcard-study');
        var emptyState = section.querySelector('[data-flashcard-empty]');
        var progressText = section.querySelector('[data-flashcard-progress-text]');
        var progressBar = section.querySelector('[data-flashcard-progress-bar]');
        var progressFill = section.querySelector('[data-flashcard-progress-fill]');
        var cardEl = section.querySelector('[data-flashcard-card]');
        var gradesEl = section.querySelector('[data-flashcard-grades]');

        var termEl = section.querySelector('[data-flashcard-term]');
        var posEl = section.querySelector('[data-flashcard-pos]');
        var pronunciationEl = section.querySelector('[data-flashcard-pronunciation]');
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

        // Optional typed-answer elements — additive to click-to-flip, see
        // submitTypedAnswer() below.
        var answerInputEl = section.querySelector('[data-flashcard-answer-input]');
        var checkBtnEl = section.querySelector('[data-flashcard-check]');
        var yourAnswerEl = section.querySelector('[data-flashcard-your-answer]');

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
         * back face regardless of direction.
         */
        function speakCard() {
          if (!currentCard) {
            return;
          }

          var direction = currentCard.direction || 'forward';
          var termShowing = direction === 'reverse' ? revealed : !revealed;
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

        function showCard(card) {
          currentCard = card;
          var direction = card.direction || 'forward';

          posEl.textContent = card.part_of_speech || '';

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

          // Interval preview under each grade button (again/hard/good/easy);
          // computed server-side as a dry run through the same SM-2 code
          // that grading uses, never saved — see buildGradePreviews().
          var previews = card.previews || {};
          Object.keys(previewEls).forEach(function (key) {
            previewEls[key].textContent = previews[key] || '';
          });

          // Reset the optional typed-answer input for the new card — it's
          // purely a display aid (see submitTypedAnswer()), never sent to
          // the server or compared against the real answer.
          if (answerInputEl) {
            answerInputEl.value = '';
          }
          if (yourAnswerEl) {
            yourAnswerEl.hidden = true;
            yourAnswerEl.textContent = '';
          }

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

            var sentence = document.createElement('div');
            sentence.className = 'lms-flashcard__example';
            sentence.textContent = example.sentence || '';
            pair.appendChild(sentence);

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
          cardEl.classList.remove('is-flipped');
          // Grading is allowed from either side — sometimes a student
          // already knows the word and wants to grade it without flipping
          // — so the buttons just need to be visible once a real card has
          // loaded, not toggled per flip state.
          gradesEl.hidden = false;
          // Best-effort: browsers usually allow this since it follows a user
          // gesture (Study Now click, or a grading button click that led
          // here) — if a stricter autoplay policy blocks it, the visible
          // speaker button still lets the student trigger it manually.
          speakCard();
        }

        function flip() {
          if (loading) {
            return;
          }
          revealed = !revealed;
          cardEl.classList.toggle('is-flipped', revealed);
          // Read the term aloud on every click, not just the first time the
          // card appears — flipping front→back or back→front both count.
          speakCard();
        }

        /**
         * Optional typed-answer flow: additive to plain click-to-flip, never
         * auto-graded. Stashes whatever was typed into the "Your answer" row
         * before flipping, so the student sees their guess next to the real
         * answer — grading itself is still only ever done via the 4 buttons.
         */
        function submitTypedAnswer() {
          if (revealed || loading) {
            return;
          }
          var typed = answerInputEl ? answerInputEl.value.trim() : '';
          if (yourAnswerEl) {
            if (typed) {
              yourAnswerEl.textContent = Drupal.t('You typed: @answer', { '@answer': typed });
              yourAnswerEl.hidden = false;
            }
            else {
              yourAnswerEl.hidden = true;
            }
          }
          flip();
        }

        function loadNext() {
          loading = true;
          cardEl.setAttribute('aria-busy', 'true');
          fetch(nextUrl + '?deck=' + encodeURIComponent(deckId) + '&direction=' + encodeURIComponent(direction) + '&mode=' + encodeURIComponent(mode), { headers: { Accept: 'application/json' } })
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

        [speakFrontEl, speakBackEl].forEach(function (speakEl) {
          if (speakEl) {
            speakEl.addEventListener('click', function (e) {
              e.stopPropagation();
              speakCard();
            });
          }
        });

        // The typed-answer input/button are additive to click-to-flip:
        // typing or clicking Check must not itself flip the card via the
        // parent cardEl's click listener (stopPropagation), and only
        // Enter/Check actually trigger the flip, via submitTypedAnswer().
        if (answerInputEl) {
          answerInputEl.addEventListener('click', function (e) {
            e.stopPropagation();
          });
          answerInputEl.addEventListener('keydown', function (e) {
            e.stopPropagation();
            if (e.key === 'Enter') {
              e.preventDefault();
              submitTypedAnswer();
            }
          });
        }
        if (checkBtnEl) {
          checkBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            submitTypedAnswer();
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
          if (e.key === ' ' || e.key === 'Enter') {
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
