<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\lms_flashcard\Entity\CardStat;
use Drupal\lms_flashcard\Entity\Deck;
use Drupal\lms_flashcard\Service\Sm2SchedulerService;
use Drupal\lms_flashcard\Service\StudyQueueBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AJAX endpoints backing the flashcard study session UI.
 *
 * Follows lms_note's "own content" convention: routes are gated only by
 * _user_is_logged_in, and every query here is scoped by the resolved uid
 * rather than relying on entity-level access grants (see
 * NoteController::save() et al. for the pattern this mirrors).
 */
final class FlashcardApiController extends ControllerBase {

  private const TEMPSTORE_COLLECTION = 'lms_flashcard_study';

  /**
   * CardStat.field_spell_level bounds — see buildSpellPuzzle() in
   * flashcard-study.js for what each level means difficulty-wise.
   */
  private const SPELL_LEVEL_MIN = 1;
  private const SPELL_LEVEL_MAX = 5;

  /**
   * CardStat.field_quiz_level bounds — see buildQuizExercise() in
   * flashcard-study.js for what each level means difficulty-wise.
   */
  private const QUIZ_LEVEL_MIN = 1;
  private const QUIZ_LEVEL_MAX = 3;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected StudyQueueBuilder $queueBuilder,
    protected Sm2SchedulerService $scheduler,
    protected PrivateTempStoreFactory $tempStoreFactory,
    protected TimeInterface $time,
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
      $container->get('lms_flashcard.sm2_scheduler'),
      $container->get('tempstore.private'),
      $container->get('datetime.time'),
    );
  }

  /**
   * GET /api/lms/flashcard/next?deck=ID — pop the next card off the queue.
   */
  public function nextCard(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $deckId = (int) $request->query->get('deck');
    if (!$deckId) {
      return new JsonResponse(['error' => 'Missing deck'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $store = $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION);
    $queueKey = "queue:{$uid}:{$deckId}";
    $currentKey = "current:{$uid}:{$deckId}";
    $directionKey = "direction:{$uid}:{$deckId}";
    $practiceKey = "practice:{$uid}:{$deckId}";
    $drillAllKey = "drillall:{$uid}:{$deckId}";

    $direction = $request->query->get('direction', 'forward');
    if (!in_array($direction, ['forward', 'reverse', 'mixed'], TRUE)) {
      $direction = 'forward';
    }
    // $practice controls whether a grade gets persisted to the real SM-2
    // schedule at all (see submitReview()) — "Practice all cards" only.
    $practice = $request->query->get('mode') === 'practice';
    // $drillAll controls only the *shape* of the queue passed to
    // StudyQueueBuilder::buildQueue() — due/new vs. every card in the deck
    // shuffled. Spelling and Quiz Practice (style=spell / style=quiz) also
    // drill every card (so neither is blocked by "nothing due right now"
    // like Study Now would be) but, unlike $practice, both keep writing
    // real SM-2 grades in the background — see submitSpellResult() /
    // submitQuizResult() in flashcard-study.js, which is why this is
    // tracked as its own flag instead of just reusing $practice here.
    $drillAll = $practice || in_array($request->query->get('style'), ['spell', 'quiz'], TRUE);

    $queue = $store->get($queueKey);
    // The front-end always sends direction/mode/style (see
    // flashcard-study.js), even on calls after the first — normally
    // redundant since the session already has its own stored values, but it
    // also means a student clicking a *different* "Or study" link (e.g.
    // "Spelling Practice" while a "Study Now" session for the same deck is
    // still unfinished) is deliberately asking to switch modes. Detect that
    // and discard the stale queue so it rebuilds under the newly requested
    // direction/mode/style instead of silently continuing the old,
    // unfinished one.
    if ($queue !== NULL && (
      $store->get($directionKey) !== $direction
      || (bool) $store->get($practiceKey) !== $practice
      || (bool) $store->get($drillAllKey) !== $drillAll
    )) {
      $queue = NULL;
    }
    if ($queue === NULL) {
      $queue = $this->queueBuilder->buildQueue($uid, [$deckId], $drillAll);
      $store->set($directionKey, $direction);
      $store->set($practiceKey, $practice);
      $store->set($drillAllKey, $drillAll);
    }
    $sessionDirection = $store->get($directionKey) ?? 'forward';
    $sessionPractice = (bool) $store->get($practiceKey);

    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $card = NULL;
    $item = NULL;
    // Skip over any queued cards deleted since the queue was built.
    while ($queue && !$card) {
      $item = array_shift($queue);
      $card = $cardStorage->load($item['card_id']);
    }

    if (!$card) {
      // Delete rather than store an empty array: leaving `[]` behind would
      // make the `$queue === NULL` check above permanently skip rebuilding,
      // so a student returning to /study after finishing a session (e.g.
      // via "Keep Studying" on the summary page) would get bounced straight
      // back to "empty" forever even once more cards become due — instead
      // of a fresh queue picking them up.
      $store->delete($queueKey);
      $store->delete($currentKey);
      $store->delete($directionKey);
      $store->delete($practiceKey);
      $store->delete($drillAllKey);
      return new JsonResponse(['status' => 'empty']);
    }

    $store->set($queueKey, $queue);
    $store->set($currentKey, $item);

    $seenInfo = $this->recordCardSeen((int) $card->id(), $uid);

    $audio = $card->get('field_audio')->entity;
    $image = $card->get('field_image')->entity;
    $cardId = (int) $card->id();
    $direction = $sessionDirection === 'mixed' ? (random_int(0, 1) ? 'reverse' : 'forward') : $sessionDirection;
    $sentences = array_column($card->get('field_example_sentences')->getValue(), 'value');
    $translations = array_column($card->get('field_example_translations')->getValue(), 'value');

    // Preserve cards created before multi-example support until they are
    // edited or migrated by the database update hook.
    if (!$sentences) {
      $legacySentence = trim(strip_tags((string) $card->get('field_example_sentence')->value));
      $legacyTranslation = trim(strip_tags((string) $card->get('field_example_translation')->value));
      if ($legacySentence !== '' || $legacyTranslation !== '') {
        $sentences = [$legacySentence];
        $translations = [$legacyTranslation];
      }
    }

    $examples = [];
    foreach ($sentences as $delta => $sentence) {
      if (trim((string) $sentence) !== '') {
        $examples[] = [
          'sentence' => (string) $sentence,
          'translation' => (string) ($translations[$delta] ?? ''),
        ];
      }
    }

    return new JsonResponse([
      'status' => 'ok',
      'source' => $item['source'],
      'remaining' => count($queue),
      'practice' => $sessionPractice,
      'card' => [
        'id' => $cardId,
        'direction' => $direction,
        'term' => $card->label(),
        'definition' => $card->get('field_definition')->value,
        'part_of_speech' => $card->get('field_part_of_speech')->value,
        'pronunciation' => $card->get('field_pronunciation')->value,
        'examples' => $examples,
        // Retain singular keys for older clients of this endpoint.
        'example_sentence' => $examples[0]['sentence'] ?? '',
        'example_translation' => $examples[0]['translation'] ?? '',
        'audio_url' => $audio ? \Drupal::service('file_url_generator')->generateString($audio->getFileUri()) : NULL,
        'image_url' => $image ? \Drupal::service('file_url_generator')->generateString($image->getFileUri()) : NULL,
        'times_seen' => $seenInfo['times_seen'],
        'spell_level' => $seenInfo['spell_level'],
        'quiz_level' => $seenInfo['quiz_level'],
        // In practice mode grading is a no-op (see submitReview()), so the
        // day-interval previews would be misleading — suppress them rather
        // than showing what *would* happen if this were a real review.
        'previews' => $sessionPractice ? [] : $this->buildGradePreviews($cardId, $uid),
      ],
    ]);
  }

  /**
   * Loads a user's CardStat row for a card, creating an unsaved one if none
   * exists yet — shared by recordCardSeen() and submitReview()'s spelling-
   * level update so both work off the same lazy-create pattern as Review.
   */
  private function loadOrCreateCardStat(int $cardId, int $uid): CardStat {
    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $stat = $statIds ? $statStorage->load(reset($statIds)) : NULL;
    return $stat ?: $statStorage->create(['uid' => $uid, 'field_card' => $cardId]);
  }

  /**
   * Records that a card was shown to a user; returns ['times_seen' => int,
   * 'spell_level' => int, 'quiz_level' => int] for the JSON payload.
   *
   * Deliberately writes to the separate CardStat entity rather than Review
   * — Review's mere existence marks a card as "not new" for
   * StudyQueueBuilder (see its class docblock), so touching it here would
   * corrupt real SRS scheduling on every practice-mode view. CardStat is
   * counted in both practice and SRS sessions since it's purely a "how many
   * times has the student seen this word" display stat, never consulted for
   * scheduling.
   */
  private function recordCardSeen(int $cardId, int $uid): array {
    $stat = $this->loadOrCreateCardStat($cardId, $uid);

    $timesSeen = ((int) ($stat->get('field_times_seen')->value ?? 0)) + 1;
    $stat->set('field_times_seen', $timesSeen);
    $stat->set('field_last_seen_at', $this->time->getRequestTime());
    $stat->save();

    return [
      'times_seen' => $timesSeen,
      'spell_level' => (int) ($stat->get('field_spell_level')->value ?: self::SPELL_LEVEL_MIN),
      'quiz_level' => (int) ($stat->get('field_quiz_level')->value ?: self::QUIZ_LEVEL_MIN),
    ];
  }

  /**
   * Computes what each of the 4 grade buttons would set the next-review
   * interval to, for the card currently being shown — a dry run through
   * Sm2SchedulerService::grade() for every quality, never saved. "Again"
   * is shown as a fixed "<10m" label rather than a real day-interval,
   * since a quality-0 grade resets interval to 0 and gets the card
   * requeued within the same session (see requeueAfterAgain()) instead of
   * literally being scheduled for a fractional day.
   */
  private function buildGradePreviews(int $cardId, int $uid): array {
    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');
    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    $review = $reviewIds ? $reviewStorage->load(reset($reviewIds)) : NULL;
    $currentState = $review ? [
      'ease_factor' => (float) $review->get('field_ease_factor')->value,
      'interval_days' => (int) $review->get('field_interval_days')->value,
      'repetitions' => (int) $review->get('field_repetitions')->value,
      'status' => $review->get('field_status')->value,
    ] : ['ease_factor' => 2.5, 'interval_days' => 0, 'repetitions' => 0, 'status' => 'new'];

    $now = $this->time->getRequestTime();
    $labels = ['again', 'hard', 'good', 'easy'];
    $previews = [];
    foreach ($labels as $quality => $label) {
      $previews[$label] = $quality === 0 ? '<10m' : $this->formatIntervalPreview($this->scheduler->grade($currentState, $quality, $now)['interval_days']);
    }
    return $previews;
  }

  /**
   * Formats a day count for the grade-button preview label.
   */
  private function formatIntervalPreview(int $days): string {
    if ($days < 1) {
      return '<1d';
    }
    if ($days % 30 === 0 && $days >= 30) {
      return ($days / 30) . 'mo';
    }
    if ($days % 7 === 0 && $days >= 14) {
      return ($days / 7) . 'w';
    }
    return $days . 'd';
  }

  /**
   * POST /api/lms/flashcard/review — grade the card currently being shown.
   *
   * Body: {"deck_id": int, "quality": 0-3} (0=Again, 1=Hard, 2=Good, 3=Easy).
   * Also accepts an optional "spell_correct": bool, sent only from Spelling
   * Practice (see checkSpelling() in flashcard-study.js, which auto-derives
   * `quality` from it too since that style hides the 4 grade buttons) —
   * bumps CardStat.field_spell_level up or down a notch, independent of the
   * `quality`-driven SM-2 update above. See FlashcardApiController::
   * loadOrCreateCardStat() and the SPELL_LEVEL_MIN/MAX constants.
   *
   * Also accepts an optional "quiz_correct": bool, sent only from Quiz
   * Practice (submitQuizResult() in flashcard-study.js, same idea as
   * spell_correct) — bumps CardStat.field_quiz_level instead, via the
   * QUIZ_LEVEL_MIN/MAX constants.
   */
  public function submitReview(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $data = json_decode($request->getContent(), TRUE) ?? [];
    $deckId = (int) ($data['deck_id'] ?? 0);
    $quality = $data['quality'] ?? NULL;
    if (!$deckId || $quality === NULL || !is_numeric($quality)) {
      return new JsonResponse(['error' => 'Missing or invalid deck_id/quality'], 400);
    }
    $quality = (int) $quality;
    if ($quality < 0 || $quality > 3) {
      return new JsonResponse(['error' => 'quality must be 0-3'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $store = $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION);
    $currentKey = "current:{$uid}:{$deckId}";
    $practiceKey = "practice:{$uid}:{$deckId}";
    $item = $store->get($currentKey);
    $cardId = $item['card_id'] ?? (int) ($data['card_id'] ?? 0);
    if (!$cardId) {
      return new JsonResponse(['error' => 'No card currently in session for this deck'], 400);
    }
    // A "Practice all cards" session (see StudyQueueBuilder::buildQueue())
    // grades for feedback only — the SM-2 schedule underneath is never
    // touched, so cramming a deck doesn't disturb the student's real due
    // dates.
    $practice = (bool) $store->get($practiceKey);

    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');
    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $review = $reviewIds ? $reviewStorage->load(reset($reviewIds)) : NULL;
    $currentState = $review ? [
      'ease_factor' => (float) $review->get('field_ease_factor')->value,
      'interval_days' => (int) $review->get('field_interval_days')->value,
      'repetitions' => (int) $review->get('field_repetitions')->value,
      'status' => $review->get('field_status')->value,
    ] : ['ease_factor' => 2.5, 'interval_days' => 0, 'repetitions' => 0, 'status' => 'new'];

    $now = $this->time->getRequestTime();
    $result = $this->scheduler->grade($currentState, $quality, $now);

    if (!$practice) {
      if (!$review) {
        $review = $reviewStorage->create(['uid' => $uid, 'field_card' => $cardId]);
      }
      $review->set('field_ease_factor', $result['ease_factor']);
      $review->set('field_interval_days', $result['interval_days']);
      $review->set('field_repetitions', $result['repetitions']);
      $review->set('field_status', $result['status']);
      $review->set('field_next_review_at', $result['next_review_at']);
      $review->set('field_last_reviewed_at', $result['last_reviewed_at']);
      if ($result['lapses_delta']) {
        $review->set('field_lapses', ((int) ($review->get('field_lapses')->value ?? 0)) + $result['lapses_delta']);
      }
      $review->save();
    }

    // Spelling Practice (style=spell): the 4 grade buttons are hidden there
    // in favor of auto-grading the typed answer as $quality above (see
    // checkSpelling() in flashcard-study.js, which is what sends this
    // request at all in that mode) — spell_correct additionally drives a
    // second, independent difficulty-level counter on CardStat. Absent for
    // a manual button click in the plain flip/reverse styles.
    $spellLevel = NULL;
    if (array_key_exists('spell_correct', $data)) {
      $stat = $this->loadOrCreateCardStat($cardId, $uid);
      $spellLevel = (int) ($stat->get('field_spell_level')->value ?: self::SPELL_LEVEL_MIN);
      $spellLevel = !empty($data['spell_correct'])
        ? min(self::SPELL_LEVEL_MAX, $spellLevel + 1)
        : max(self::SPELL_LEVEL_MIN, $spellLevel - 1);
      $stat->set('field_spell_level', $spellLevel);
      $stat->save();
    }

    // Quiz Practice (style=quiz): same idea as spell_correct above but for
    // its own independent level — see FlashcardApiController::
    // buildQuizPool() and buildQuizExercise() in flashcard-study.js.
    $quizLevel = NULL;
    if (array_key_exists('quiz_correct', $data)) {
      $stat = $this->loadOrCreateCardStat($cardId, $uid);
      $quizLevel = (int) ($stat->get('field_quiz_level')->value ?: self::QUIZ_LEVEL_MIN);
      $quizLevel = !empty($data['quiz_correct'])
        ? min(self::QUIZ_LEVEL_MAX, $quizLevel + 1)
        : max(self::QUIZ_LEVEL_MIN, $quizLevel - 1);
      $stat->set('field_quiz_level', $quizLevel);
      $stat->save();
    }

    // "Again": the card isn't done for this session — bring it back a few
    // cards ahead instead of letting it disappear until tomorrow.
    if ($quality === 0 && $item) {
      $queueKey = "queue:{$uid}:{$deckId}";
      $queue = $store->get($queueKey) ?? [];
      $queue = $this->queueBuilder->requeueAfterAgain($queue, $item);
      $store->set($queueKey, $queue);
    }

    return new JsonResponse([
      'status' => 'ok',
      'review' => [
        'ease_factor' => $result['ease_factor'],
        'interval_days' => $result['interval_days'],
        'next_review_at' => $result['next_review_at'],
        'status' => $result['status'],
      ],
      'spell_level' => $spellLevel,
      'quiz_level' => $quizLevel,
    ]);
  }

  /**
   * GET /api/lms/flashcard/session-stats?deck=ID
   */
  public function sessionStats(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $deckId = (int) $request->query->get('deck');
    if (!$deckId) {
      return new JsonResponse(['error' => 'Missing deck'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $counts = $this->queueBuilder->getDueCounts($uid, $deckId);
    return new JsonResponse(['status' => 'ok'] + $counts);
  }

  /**
   * Loads a deck only if it's visible to the given user, else NULL.
   *
   * Guards all three endpoints against a guessed/tampered deck ID being
   * used to read or study someone else's private deck — see
   * StudyQueueBuilder::isDeckVisibleTo() for the visibility rule.
   */
  private function loadVisibleDeck(int $deckId, int $uid): ?Deck {
    $deck = $this->entityTypeManager->getStorage('lms_flashcard_deck')->load($deckId);
    if (!$deck instanceof Deck || !$this->queueBuilder->isDeckVisibleTo($deck, $uid)) {
      return NULL;
    }
    return $deck;
  }

}
