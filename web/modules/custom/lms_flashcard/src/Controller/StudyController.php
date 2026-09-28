<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_flashcard\Entity\Deck;
use Drupal\lms_flashcard\Service\StudyQueueBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Student-facing pages: deck list and the flashcard study session.
 */
final class StudyController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected StudyQueueBuilder $queueBuilder,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('lms_flashcard.study_queue_builder'),
    );
  }

  /**
   * Page: /learn/flashcards — every deck visible to the current user, with
   * due/new counts.
   *
   * A deck is visible if it's published and either (a) the current user
   * owns it, or (b) its owner is staff (has course-management permission)
   * — i.e. an "official" deck meant for everyone. A deck a student
   * self-imports via /manage/flashcard-decks/import is otherwise private to
   * them, matching lms_note's per-user "own content" model.
   */
  public function myDecks(): array {
    $uid = (int) $this->currentUser->id();

    $storage = $this->entityTypeManager->getStorage('lms_flashcard_deck');
    $deckIds = $storage->getQuery()
      ->condition('status', TRUE)
      ->sort('name')
      ->accessCheck(FALSE)
      ->execute();

    $decks = [];
    $cacheTags = [];
    foreach ($storage->loadMultiple($deckIds) as $deck) {
      /** @var \Drupal\lms_flashcard\Entity\Deck $deck */
      if (!$this->queueBuilder->isDeckVisibleTo($deck, $uid)) {
        continue;
      }

      // Card publish/unpublish toggles invalidate the deck's cache tags (see
      // FlashcardApiController::toggleCardStatus) — depend on them here too,
      // so this due/new/card-count listing recomputes instead of staying
      // stale after a toggle.
      $cacheTags = Cache::mergeTags($cacheTags, $deck->getCacheTags());

      $counts = $this->queueBuilder->getDueCounts($uid, (int) $deck->id());

      // Only cards this user hasn't skipped count here (plus the deck
      // owner's own admin-published check) — this badge should reflect
      // what's actually left to study, and shrink as this user skips
      // cards. Skipping is per-user (CardStat::field_skipped), so two
      // students looking at the same shared deck can see different
      // counts here — see that field's docblock for why this is
      // deliberately not Card.status.
      $studyableCardIds = [];
      foreach ($deck->get('field_cards')->referencedEntities() as $referencedCard) {
        /** @var \Drupal\lms_flashcard\Entity\Card $referencedCard */
        if ($referencedCard->isPublished()) {
          $studyableCardIds[] = (int) $referencedCard->id();
        }
      }
      $publishedCardCount = count($studyableCardIds) - count($this->queueBuilder->getSkippedCardIds($uid, $studyableCardIds));

      $courseEntity = $deck->get('field_course')->entity;
      $lessonEntity = $deck->get('field_lesson')->entity;

      $decks[] = [
        'entity' => $deck,
        'name' => $deck->label(),
        'description' => $deck->get('field_description')->value,
        'language' => $deck->get('field_language')->value,
        'card_count' => $publishedCardCount,
        'due_count' => $counts['due'],
        'new_count' => $counts['new'],
        'is_mine' => (int) $deck->getOwnerId() === $uid,
        'course' => $courseEntity ? [
          'id' => (int) $courseEntity->id(),
          'name' => $courseEntity->label(),
        ] : NULL,
        'lesson' => $lessonEntity ? [
          'id' => (int) $lessonEntity->id(),
          'name' => $lessonEntity->label(),
        ] : NULL,
        // Progress-bar figure only — resettable via "Reset Mastery". Does
        // NOT gate Spelling/Pronunciation/Reading/Listening any more; see
        // practice_unlocked below for that.
        'mastery_percent' => $this->queueBuilder->getMasteryPercent($uid, (int) $deck->id()),
        // Gates Spelling/Pronunciation Practice (and, via the same
        // template, Reading/Listening) behind having reached 100% mastery
        // at least once — unlike mastery_percent above, "Reset Mastery"
        // never re-locks this. See StudyQueueBuilder::isPracticeUnlocked().
        'practice_unlocked' => $this->queueBuilder->isPracticeUnlocked($uid, (int) $deck->id()),
      ];
    }

    return [
      '#theme' => 'lms_flashcard_my_decks',
      '#decks' => $decks,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => Cache::mergeTags($cacheTags, $storage->getEntityType()->getListCacheTags()),
      ],
    ];
  }

  /**
   * Title callback for deck detail page.
   */
  public function deckDetailTitle(Deck $lms_flashcard_deck): string {
    return (string) $lms_flashcard_deck->label();
  }

  /**
   * Page: /learn/flashcards/{lms_flashcard_deck} — deck detail view.
   *
   * Displays all cards in the deck with their properties and provides an
   * interactive toggle to publish/unpublish individual cards.
   */
  public function deckDetail(Deck $lms_flashcard_deck): array {
    $uid = (int) $this->currentUser->id();

    if (!$this->queueBuilder->isDeckVisibleTo($lms_flashcard_deck, $uid)) {
      throw new AccessDeniedHttpException();
    }

    $deckId = (int) $lms_flashcard_deck->id();
    $canEdit = $lms_flashcard_deck->access('update', $this->currentUser);

    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->sort('id', 'ASC')
      ->accessCheck(FALSE)
      ->execute();

    // "Skip" is this user's own preference (CardStat::field_skipped, not
    // Card.status — see that field's docblock), so a shared/course deck's
    // cards read as skipped/studying differently per student rather than
    // one student's skip hiding a card from everyone else studying the
    // same deck.
    $skippedCardIds = [];
    if ($cardIds) {
      $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
      $skippedStatIds = $statStorage->getQuery()
        ->condition('uid', $uid)
        ->condition('field_card', array_values(array_map('intval', $cardIds)), 'IN')
        ->condition('field_skipped', TRUE)
        ->accessCheck(FALSE)
        ->execute();
      foreach ($statStorage->loadMultiple($skippedStatIds) as $stat) {
        /** @var \Drupal\lms_flashcard\Entity\CardStat $stat */
        $skippedCardIds[(int) $stat->get('field_card')->target_id] = TRUE;
      }
    }

    $cards = [];
    $studyingCount = 0;
    $skippedCount = 0;

    if ($cardIds) {
      $cardEntities = $cardStorage->loadMultiple($cardIds);
      foreach ($cardEntities as $card) {
        /** @var \Drupal\lms_flashcard\Entity\Card $card */
        $isSkipped = isset($skippedCardIds[(int) $card->id()]);
        if ($isSkipped) {
          $skippedCount++;
        }
        else {
          $studyingCount++;
        }

        // Example sentences + translations pairing.
        $sentences = array_column($card->get('field_example_sentences')->getValue(), 'value');
        $translations = array_column($card->get('field_example_translations')->getValue(), 'value');
        $examples = [];
        foreach ($sentences as $idx => $sentence) {
          $examples[] = [
            'sentence' => $sentence,
            'translation' => $translations[$idx] ?? '',
          ];
        }

        // Collocations + translations pairing.
        $collocPhrases = array_column($card->get('field_collocations')->getValue(), 'value');
        $collocTrans = array_column($card->get('field_collocation_translations')->getValue(), 'value');
        $collocations = [];
        foreach ($collocPhrases as $idx => $phrase) {
          $collocations[] = [
            'phrase' => $phrase,
            'translation' => $collocTrans[$idx] ?? '',
          ];
        }

        $cards[] = [
          'id' => (int) $card->id(),
          'term' => $card->label(),
          'definition' => trim(strip_tags((string) ($card->get('field_definition')->value ?? ''))),
          'translation' => $card->get('field_translation')->value ?? '',
          'part_of_speech' => $card->get('field_part_of_speech')->value ?? '',
          'pronunciation' => $card->get('field_pronunciation')->value ?? '',
          'difficulty' => $card->get('field_difficulty')->value ?? 'medium',
          'examples' => $examples,
          'collocations' => $collocations,
          'is_skipped' => $isSkipped,
          // Skipping is a personal study preference, not a content edit —
          // any user who can see this deck may skip cards in it, unlike
          // can_edit (Quick Edit) which stays owner/staff-only below.
          'can_skip' => TRUE,
        ];
      }
    }

    $counts = $this->queueBuilder->getDueCounts($uid, $deckId);

    $topicTerm = $lms_flashcard_deck->get('field_topic')->entity;

    $stats = [
      'total_cards' => count($cards),
      'published_cards' => $studyingCount,
      'unpublished_cards' => $skippedCount,
      'due_count' => $counts['due'],
      'new_count' => $counts['new'],
      'mastery_percent' => $this->queueBuilder->getMasteryPercent($uid, $deckId),
      'practice_unlocked' => $this->queueBuilder->isPracticeUnlocked($uid, $deckId),
    ];

      $courseEntity = $lms_flashcard_deck->get('field_course')->entity;
      $lessonEntity = $lms_flashcard_deck->get('field_lesson')->entity;

      return [
        '#theme' => 'lms_flashcard_deck_detail',
        '#deck' => [
          'id' => $deckId,
          'name' => $lms_flashcard_deck->label(),
          'description' => $lms_flashcard_deck->get('field_description')->value,
          'language' => $lms_flashcard_deck->get('field_language')->value,
          'native_language' => $lms_flashcard_deck->get('field_native_language')->value,
          'topic' => $topicTerm?->label() ?? '',
          'is_mine' => (int) $lms_flashcard_deck->getOwnerId() === $uid,
          'course' => $courseEntity ? [
            'id' => (int) $courseEntity->id(),
            'name' => $courseEntity->label(),
          ] : NULL,
          'lesson' => $lessonEntity ? [
            'id' => (int) $lessonEntity->id(),
            'name' => $lessonEntity->label(),
          ] : NULL,
        ],
      '#cards' => $cards,
      '#stats' => $stats,
      '#can_edit' => $canEdit,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => $lms_flashcard_deck->getCacheTags(),
      ],
    ];
  }

  /**
   * Page: /learn/flashcards/{deck}/study — the flip-card study session.
   *
   * Accepts an optional ?direction=forward|reverse|mixed (no UI link
   * currently sets this directly — only ?style=spell does, by forcing it to
   * "reverse" below) — passed through to the front end, which sends it back
   * on the *first* /api/lms/flashcard/next call of the session;
   * FlashcardApiController::nextCard() is what actually persists it for the
   * rest of the session.
   *
   * Also accepts an optional ?mode=practice, from the "Practice all cards"
   * link on the same page — drills every card in the deck regardless of SRS
   * due dates, without touching the student's real review schedule (see
   * StudyQueueBuilder::buildQueue() and FlashcardApiController::submitReview()).
   *
   * Also accepts an optional ?style=spell, from the "Spelling Practice" link
   * — shows the definition first (like direction=reverse, which this forces
   * regardless of any ?direction also present) with the term read aloud
   * immediately, and adds a fill-in-the-missing-letters puzzle for the term
   * below it (see buildSpellPuzzle() in flashcard-study.js). Mostly a
   * front-end rendering choice, but flashcard-study.js also forwards it to
   * nextCard() so that endpoint's $drillAll can queue every card in the
   * deck like ?mode=practice does — unlike that flag, style=spell still
   * writes real SM-2 grades in the background (see submitReview()'s
   * spell_correct handling), so it's tracked as its own thing there rather
   * than folded into $practice.
   *
   * Also accepts an optional ?style=quiz — no longer linked from a "Quiz
   * Practice" entry point of its own (folded into style=flip's level 3
   * instead, at the student's own request — see the style=flip paragraph
   * below), but the route/page/style itself is left working exactly as
   * before rather than removed, since style=flip's own level 3 depends on
   * reusing this exact code path (buildQuizExercise() et al. in
   * flashcard-study.js) unchanged. Forces direction=reverse (the term/
   * pronunciation stay hidden until answered) and, like style=spell, is
   * forwarded to nextCard() for $drillAll and still writes real SM-2
   * grades in the background when reached this way. Its 3 exercises (see
   * buildQuizExercise() in flashcard-study.js) are built entirely from the
   * deck's own example sentences, so this also attaches a lightweight pool
   * of every OTHER card's term/definition/examples in the deck (see
   * buildQuizPool() below) — used client-side as the wrong-answer pool for
   * multiple-choice, so nextCard() doesn't need a round trip per card just
   * to fetch distractors.
   *
   * Also accepts an optional ?style=pronounce, from the "Pronunciation
   * Practice" link — unlike spell/quiz, this does NOT force
   * direction=reverse: the goal here is producing a word already known, not
   * recalling it, so term + IPA + definition all stay visible up front (the
   * default direction=forward request param already does this, so no
   * override is needed). Same as spell/quiz otherwise: forwarded to
   * nextCard() for $drillAll, and still writes real SM-2 grades in the
   * background (see submitPronunciationResult() in flashcard-study.js and
   * FlashcardApiController::submitReview()'s pronunciation_correct
   * handling). Grading itself happens entirely client-side via the Web
   * Speech API (with a manual self-grade fallback for browsers without it)
   * — see buildPronunciationExercise() in flashcard-study.js.
   *
   * style=flip (the default, i.e. plain "Study Now") is a three-level
   * mastery flow rather than a single flip-then-manually-grade step, at
   * the student's own request: level 1 is the familiar flip (term →
   * definition, respecting ?direction as always); once viewed, the SAME
   * card comes back a few cards later in the SAME session at level 2 —
   * its definition shown, pick the matching term among 4 choices (see
   * buildMasteryExercise() in flashcard-study.js). Passing that moves it
   * on to level 3 (a few cards later again) — style=quiz's own 3
   * exercises, reused exactly as that style renders/grades them
   * client-side (see finishQuizAnswer() branching in flashcard-study.js),
   * folded in here rather than kept as a separate "Quiz Practice" entry
   * point. Passing level 3 removes the card from the session for good;
   * failing level 2 OR level 3 sends it all the way back to level 1, and
   * the whole cycle repeats until level 3 is passed or the session ends.
   * None of this touches the real SM-2 schedule in either direction at
   * any level — see FlashcardApiController::submitMastery()'s docblock for
   * why — so the 4 manual grade buttons (Again/Hard/Good/Easy) are hidden
   * for this style exactly like they already are for spell/quiz/pronounce.
   * Level 3 does still bump CardStat.field_quiz_level same as a real
   * style=quiz session would (see submitMastery()), so it's this card's
   * one shared exercise-difficulty progression whichever way level 3 is
   * ever reached. Needs the same distractor-term pool style=quiz does (see
   * buildQuizPool() below) for both level 2's and level 3's choices.
   */
  public function study(Deck $lms_flashcard_deck, Request $request): array {
    $this->assertVisible($lms_flashcard_deck);

    $style = $request->query->get('style');
    $style = in_array($style, ['spell', 'quiz', 'pronounce'], TRUE) ? $style : 'flip';

    $direction = in_array($style, ['spell', 'quiz'], TRUE) ? 'reverse' : $request->query->get('direction', 'forward');
    if (!in_array($direction, ['forward', 'reverse', 'mixed'], TRUE)) {
      $direction = 'forward';
    }
    $practice = $request->query->get('mode') === 'practice';

    return [
      '#theme' => 'lms_flashcard_study_session',
      '#deck' => $lms_flashcard_deck,
      '#practice' => $practice,
      '#attached' => [
        'library' => ['lms_flashcard/flashcard_study'],
        'drupalSettings' => [
          'lmsFlashcard' => [
            'deckId' => (int) $lms_flashcard_deck->id(),
            // Used to pick a voice/accent for the text-to-speech fallback
            // when a card has no uploaded pronunciation MP3 (see
            // js/flashcard-study.js speakCard()).
            'language' => $lms_flashcard_deck->get('field_language')->value ?: 'en',
            'direction' => $direction,
            'practice' => $practice,
            'style' => $style,
            // Also built for style=flip now (not just quiz) — its level-2
            // recall check draws wrong-answer term choices from the same
            // pool, same reasoning as style=quiz: no round trip per card
            // just to fetch distractors.
            'quizPool' => in_array($style, ['flip', 'quiz'], TRUE) ? $this->buildQuizPool($lms_flashcard_deck) : [],
            'nextUrl' => '/api/lms/flashcard/next',
            'reviewUrl' => '/api/lms/flashcard/review',
            'masteryUrl' => '/api/lms/flashcard/mastery',
            'summaryUrl' => '/learn/flashcards/' . $lms_flashcard_deck->id() . '/summary' . ($practice ? '?mode=practice' : ''),
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Builds the deck-wide pool of {id, term, definition, examples[]} that
   * Quiz Practice draws wrong-answer choices from — see buildQuizExercise()
   * in flashcard-study.js. `examples` is just the plain English sentences
   * (no translations — only needed for the *current* card's own example,
   * which nextCard() already provides); `definition` and multi-word terms
   * are stripped of HTML/whitespace the same way FlashcardApiController
   * does for the legacy single-example fields. `translation` is included so
   * the definition-fallback exercises (no usable example sentence) can
   * prefer the student's native-language translation over the raw English
   * definition, both for the prompt and for other cards' distractor choices
   * — see pickDistractorDefinitions() in flashcard-study.js.
   */
  private function buildQuizPool(Deck $deck): array {
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $storage->getQuery()
      ->condition('field_deck', $deck->id())
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return [];
    }

    $pool = [];
    foreach ($storage->loadMultiple($cardIds) as $card) {
      $sentences = array_column($card->get('field_example_sentences')->getValue(), 'value');
      $sentences = array_values(array_filter(array_map(
        static fn ($sentence) => trim(strip_tags((string) $sentence)),
        $sentences,
      ), static fn ($sentence) => $sentence !== ''));

      $pool[] = [
        'id' => (int) $card->id(),
        'term' => $card->label(),
        'definition' => trim(strip_tags((string) $card->get('field_definition')->value)),
        'translation' => trim((string) $card->get('field_translation')->value),
        'examples' => $sentences,
      ];
    }
    return $pool;
  }

  /**
   * Page: /learn/flashcards/{deck}/summary — end-of-session recap.
   *
   * The optional ?mode=practice (carried over from the study page's
   * drupalSettings.lmsFlashcard.summaryUrl, see study() above) just
   * controls the recap copy/CTA below — it never affects #remaining_due /
   * #remaining_new, which always reflect the real SRS schedule regardless
   * of how the session that just ended was studied.
   */
  public function summary(Deck $lms_flashcard_deck, Request $request): array {
    $this->assertVisible($lms_flashcard_deck);

    $uid = (int) $this->currentUser->id();
    $counts = $this->queueBuilder->getDueCounts($uid, (int) $lms_flashcard_deck->id());

    return [
      '#theme' => 'lms_flashcard_session_summary',
      '#deck' => $lms_flashcard_deck,
      '#remaining_due' => $counts['due'],
      '#remaining_new' => $counts['new'],
      '#practice' => $request->query->get('mode') === 'practice',
      '#attached' => ['library' => ['lms_flashcard/flashcard_pages']],
    ];
  }

  /**
   * Denies access to a deck the current user isn't allowed to study.
   *
   * Guards direct/guessed URLs since these routes are only gated by
   * _user_is_logged_in, not per-deck entity access.
   */
  private function assertVisible(Deck $deck): void {
    if (!$this->queueBuilder->isDeckVisibleTo($deck, (int) $this->currentUser->id())) {
      throw new AccessDeniedHttpException();
    }
  }

}
