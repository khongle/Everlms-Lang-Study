<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_flashcard\Entity\Deck;

/**
 * Builds a study-session queue: due reviews + relearning + capped new cards.
 *
 * The queue itself is a plain ordered array of ['card_id' => int, 'source'
 * => 'review'|'relearning'|'new'] built once per session start; the caller
 * (FlashcardApiController) is responsible for persisting it (per uid+deck)
 * across the "get next card" / "submit review" request pair — e.g. in
 * PrivateTempStore — and for re-inserting an "Again"-graded card a few
 * positions ahead via requeueAfterAgain(), since that's a stateful,
 * per-in-progress-session concern rather than something to recompute from
 * the database on every request.
 */
final class StudyQueueBuilder {

  /**
   * How many other cards to show before a just-failed card comes back.
   */
  private const AGAIN_BUFFER = 5;

  /**
   * Review cards shown per 1 new card, once new cards start interleaving.
   */
  private const REVIEW_TO_NEW_RATIO = 3;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ConfigFactoryInterface $configFactory,
    protected TimeInterface $time,
  ) {}

  /**
   * Card IDs from $cardIds this user has personally marked "Skip" (see
   * CardStat::field_skipped's docblock) — a per-user preference, distinct
   * from Card.status which is the deck owner's own content publish switch
   * shared by every student. Every method below that filters cards by
   * `status = TRUE` also subtracts this, so a skip only ever affects the
   * student who set it, never other students studying the same shared
   * deck.
   *
   * @param int[] $cardIds
   *   Card IDs already known to belong to the deck(s) in scope, so this
   *   only needs to narrow down which of those this user skipped.
   *
   * @return int[]
   */
  public function getSkippedCardIds(int $uid, array $cardIds): array {
    if (!$cardIds) {
      return [];
    }
    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->condition('field_skipped', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$statIds) {
      return [];
    }
    $skipped = [];
    foreach ($statStorage->loadMultiple($statIds) as $stat) {
      /** @var \Drupal\lms_flashcard\Entity\CardStat $stat */
      $skipped[] = (int) $stat->get('field_card')->target_id;
    }
    return $skipped;
  }

  /**
   * Builds the initial ordered queue for a study session.
   *
   * @param int $uid
   *   The studying user.
   * @param int[] $deckIds
   *   One or more deck IDs in scope for this session.
   * @param bool $practice
   *   TRUE for a free "practice" session that drills every card in the deck
   *   regardless of SRS due dates or the daily new-card cap — used by the
   *   "Practice all cards" link so a student can re-study a deck they've
   *   already cleared for the day without disturbing their real review
   *   schedule (see FlashcardApiController::submitReview(), which skips
   *   persisting grades for a 'practice'-sourced card).
   *
   * @return array
   *   List of ['card_id' => int, 'source' => string], in study order.
   */
  public function buildQueue(int $uid, array $deckIds, bool $practice = FALSE): array {
    if (!$deckIds) {
      return [];
    }

    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');

    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckIds, 'IN')
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return [];
    }
    $cardIds = array_values(array_map('intval', $cardIds));
    $cardIds = array_values(array_diff($cardIds, $this->getSkippedCardIds($uid, $cardIds)));
    if (!$cardIds) {
      return [];
    }

    if ($practice) {
      shuffle($cardIds);
      return array_map(static fn (int $cardId) => ['card_id' => $cardId, 'source' => 'practice'], $cardIds);
    }

    $now = $this->time->getRequestTime();
    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');

    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    $reviews = $reviewIds ? $reviewStorage->loadMultiple($reviewIds) : [];

    $due = [];
    $seenCardIds = [];
    foreach ($reviews as $review) {
      $cardId = (int) $review->get('field_card')->target_id;
      $seenCardIds[$cardId] = TRUE;
      $status = $review->get('field_status')->value;
      $nextReviewAt = (int) ($review->get('field_next_review_at')->value ?? 0);
      if (in_array($status, ['review', 'learning', 'relearning'], TRUE) && $nextReviewAt <= $now) {
        $due[] = [
          'card_id' => $cardId,
          'source' => $status === 'review' ? 'review' : 'relearning',
          'next_review_at' => $nextReviewAt,
        ];
      }
    }
    usort($due, static fn (array $a, array $b) => $a['next_review_at'] <=> $b['next_review_at']);

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    if ($statIds) {
      foreach ($statStorage->loadMultiple($statIds) as $stat) {
        if ($stat->get('field_mastered')->value || (int) ($stat->get('field_times_seen')->value ?? 0) > 0) {
          $seenCardIds[(int) $stat->get('field_card')->target_id] = TRUE;
        }
      }
    }

    $newCardIds = array_values(array_diff($cardIds, array_keys($seenCardIds)));
    $newCap = max(0, $this->getRemainingNewCardBudget($uid));
    $newCardIds = array_slice($newCardIds, 0, $newCap);

    $queue = [];
    $dueIndex = 0;
    $newIndex = 0;
    while ($dueIndex < count($due) || $newIndex < count($newCardIds)) {
      for ($i = 0; $i < self::REVIEW_TO_NEW_RATIO && $dueIndex < count($due); $i++, $dueIndex++) {
        $queue[] = ['card_id' => $due[$dueIndex]['card_id'], 'source' => $due[$dueIndex]['source']];
      }
      if ($newIndex < count($newCardIds)) {
        $queue[] = ['card_id' => $newCardIds[$newIndex], 'source' => 'new'];
        $newIndex++;
      }
      elseif ($dueIndex >= count($due)) {
        break;
      }
    }

    return $queue;
  }

  /**
   * Re-inserts a card graded "Again" a few positions ahead in the queue.
   *
   * @param array $remainingQueue
   *   The queue entries not yet shown this session.
   * @param array $item
   *   The ['card_id' => int, 'source' => string] entry just graded.
   *
   * @return array
   *   The updated remaining queue.
   */
  public function requeueAfterAgain(array $remainingQueue, array $item): array {
    $position = min(self::AGAIN_BUFFER, count($remainingQueue));
    array_splice($remainingQueue, $position, 0, [[
      'card_id' => $item['card_id'],
      'source' => 'relearning',
    ]]);
    return $remainingQueue;
  }

  /**
   * Re-inserts a card a few positions ahead, tagged with the "Study Now"
   * mastery level (see FlashcardApiController::submitMastery()) it should
   * come back at — style=flip's own two-step "flip, then a later recall
   * check" flow, entirely separate from the real SM-2 grade Again/Hard/
   * Good/Easy still track for every other style (quiz/spell/pronounce, and
   * flip's own initial pass isn't graded manually any more either — see
   * that controller's class docblock). Same buffer/positioning as
   * requeueAfterAgain(), just a different tag and never touching a real
   * schedule.
   *
   * @param array $remainingQueue
   *   The queue entries not yet shown this session.
   * @param array $item
   *   The ['card_id' => int, ...] entry just shown.
   * @param int $level
   *   1 (needs the plain flip again — a level-2 recall check was failed) or
   *   2 (ready for the recall check — the flip was just completed).
   *
   * @return array
   *   The updated remaining queue.
   */
  public function requeueForMastery(array $remainingQueue, array $item, int $level): array {
    $position = min(self::AGAIN_BUFFER, count($remainingQueue));
    array_splice($remainingQueue, $position, 0, [[
      'card_id' => $item['card_id'],
      'source' => 'mastery',
      'level' => $level,
    ]]);
    return $remainingQueue;
  }

  /**
   * Builds the "Study Now" (style=flip) queue — deliberately NOT
   * buildQueue()'s due/new-gated queue, at the student's own report that
   * a deck sitting at, say, 48% mastery only ever offered its 1-2 cards
   * due *today* to work through, leaving the rest unreachable for days or
   * weeks: this flow never touches the real SM-2 schedule at all (see
   * FlashcardApiController::submitMastery()'s docblock), so there's
   * nothing to protect by pacing it the way real spaced repetition needs
   * to be paced — every card not yet mastered is fair game right now.
   *
   * @param int $uid
   *   The studying user.
   * @param int $deckId
   *   The deck to pick from.
   * @param bool $includeMastered
   *   TRUE for "Practice all cards" (mode=practice) — every card in the
   *   deck, including ones already mastered, for general extra review
   *   rather than the "get to 100%" grind. FALSE (default): only cards
   *   CardStat.field_mastered doesn't yet mark TRUE for this user.
   *
   * @return array
   *   List of ['card_id' => int, 'source' => 'mastery'], shuffled — no
   *   due-first/new-last ordering to preserve here, unlike buildQueue().
   */
  public function buildMasteryQueue(int $uid, int $deckId, bool $includeMastered = FALSE): array {
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return [];
    }
    $cardIds = array_values(array_map('intval', $cardIds));
    $cardIds = array_values(array_diff($cardIds, $this->getSkippedCardIds($uid, $cardIds)));
    if (!$cardIds) {
      return [];
    }

    if (!$includeMastered) {
      $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
      $statIds = $statStorage->getQuery()
        ->condition('uid', $uid)
        ->condition('field_card', $cardIds, 'IN')
        ->condition('field_mastered', TRUE)
        ->accessCheck(FALSE)
        ->execute();
      $masteredCardIds = [];
      foreach ($statStorage->loadMultiple($statIds) as $stat) {
        /** @var \Drupal\lms_flashcard\Entity\CardStat $stat */
        $masteredCardIds[] = (int) $stat->get('field_card')->target_id;
      }
      $unmastered = array_values(array_diff($cardIds, $masteredCardIds));
      // When all cards in the deck are already mastered (100% mastery),
      // fall back to drilling all cards so the student can still review
      // the deck via "Study Now" rather than getting an empty queue and
      // being immediately bounced to the session summary.
      $cardIds = $unmastered ?: $cardIds;
    }

    shuffle($cardIds);
    return array_map(static fn (int $cardId): array => ['card_id' => $cardId, 'source' => 'mastery'], $cardIds);
  }

  /**
   * How many new cards this user may still start today.
   */
  public function getRemainingNewCardBudget(int $uid): int {
    $dailyCap = (int) ($this->configFactory->get('lms_flashcard.settings')->get('new_cards_per_day') ?? 20);
    $todayStart = strtotime('today', $this->time->getRequestTime());

    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');
    $studiedToday = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('created', $todayStart, '>=')
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return max(0, $dailyCap - (int) $studiedToday);
  }

  /**
   * Counts cards due right now for a set of decks (for "My Decks" badges).
   *
   * @return array
   *   ['due' => int, 'new' => int] for the given deck.
   */
  public function getDueCounts(int $uid, int $deckId): array {
    $now = $this->time->getRequestTime();
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');

    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return ['due' => 0, 'new' => 0];
    }
    $cardIds = array_values(array_map('intval', $cardIds));
    $cardIds = array_values(array_diff($cardIds, $this->getSkippedCardIds($uid, $cardIds)));
    if (!$cardIds) {
      return ['due' => 0, 'new' => 0];
    }

    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    $reviews = $reviewIds ? $reviewStorage->loadMultiple($reviewIds) : [];

    $due = 0;
    $seen = [];
    foreach ($reviews as $review) {
      $seen[(int) $review->get('field_card')->target_id] = TRUE;
      $status = $review->get('field_status')->value;
      $nextReviewAt = (int) ($review->get('field_next_review_at')->value ?? 0);
      if (in_array($status, ['review', 'learning', 'relearning'], TRUE) && $nextReviewAt <= $now) {
        $due++;
      }
    }

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    if ($statIds) {
      foreach ($statStorage->loadMultiple($statIds) as $stat) {
        if ($stat->get('field_mastered')->value || (int) ($stat->get('field_times_seen')->value ?? 0) > 0) {
          $seen[(int) $stat->get('field_card')->target_id] = TRUE;
        }
      }
    }

    $new = count(array_diff($cardIds, array_keys($seen)));

    return ['due' => $due, 'new' => min($new, max(0, $this->getRemainingNewCardBudget($uid)))];
  }

  /**
   * What fraction of a deck this user has mastered via "Study Now" (style=
   * flip's 3-level mastery flow — see FlashcardApiController::
   * submitMastery()), 0-100. Shown on the deck card and used to gate
   * Spelling/Pronunciation Practice (and, from outside this module,
   * Reading/Listening) behind reaching 100%, at the student's own request.
   *
   * Purely CardStat.field_mastered — the single source of truth (set when
   * a card passes level 3; cleared by resetMastery() below) rather than
   * also live-checking the real SM-2 Review for old pre-mastery-flow
   * progress here: that backfill happened once, as a data migration
   * (lms_flashcard_update_10010 in lms_flashcard.install), specifically so
   * resetMastery() has one flag to clear and this then actually drops back
   * out of 100% — checking the live Review data here too would have made
   * a reset on an originally-backfilled deck impossible, since it would
   * just recompute back to "mastered" from that same untouched SM-2 data
   * every time.
   *
   * An empty deck (0 cards) returns 100 — nothing to master, so nothing
   * should end up permanently locked because of it.
   */
  public function getMasteryPercent(int $uid, int $deckId): int {
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return 100;
    }
    $cardIds = array_values(array_map('intval', $cardIds));
    $cardIds = array_values(array_diff($cardIds, $this->getSkippedCardIds($uid, $cardIds)));
    if (!$cardIds) {
      return 100;
    }

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $masteredCount = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->condition('field_mastered', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return (int) round($masteredCount / count($cardIds) * 100);
  }

  /**
   * Whether Spelling/Pronunciation Practice (and, from outside this
   * module, Reading/Listening) should be unlocked for this user on this
   * deck — TRUE once every card has ever passed "Study Now" mastery level
   * 3 at least once (CardStat.field_ever_mastered), regardless of the
   * current, resettable getMasteryPercent(). Deliberately a separate check
   * from that one: getMasteryPercent()'s progress bar is meant to go back
   * down when a student uses "Reset Mastery" so they can re-earn it, but
   * content they've already proven they know shouldn't re-lock just
   * because they wanted another pass through Study Now — see
   * field_ever_mastered's own docblock on CardStat and resetMastery()
   * below, which never touches it.
   *
   * An empty deck (0 cards) returns TRUE, same reasoning as
   * getMasteryPercent()'s own empty-deck case.
   */
  public function isPracticeUnlocked(int $uid, int $deckId): bool {
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return TRUE;
    }
    $cardIds = array_values(array_map('intval', $cardIds));
    $cardIds = array_values(array_diff($cardIds, $this->getSkippedCardIds($uid, $cardIds)));
    if (!$cardIds) {
      return TRUE;
    }

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $everMasteredCount = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->condition('field_ever_mastered', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return $everMasteredCount >= count($cardIds);
  }

  /**
   * Clears CardStat.field_mastered for every card in a deck this user had
   * mastered — "Reset Mastery" on the deck card, for a student who wants
   * to go back through "Study Now" and re-earn the progress bar
   * getMasteryPercent() shows, rather than it staying at 100% forever.
   * Deliberately narrow in two ways: never touches field_ever_mastered
   * (so isPracticeUnlocked() — the actual gate on Spelling/Pronunciation/
   * Reading/Listening — stays unlocked for content already earned, see
   * that field's own docblock on CardStat), and never touches the real
   * SM-2 schedule (Sm2SchedulerService, the Review entity) — a student who
   * wants a true do-over of spaced repetition itself already has
   * "Practice all cards" for that; this only resets the progress-bar
   * flag, so due dates earned through real study are left alone.
   *
   * @return int
   *   How many cards were reset (0 if the deck/user had nothing to reset).
   */
  public function resetMastery(int $uid, int $deckId): int {
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckId)
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return 0;
    }

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', array_values(array_map('intval', $cardIds)), 'IN')
      ->condition('field_mastered', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    if (!$statIds) {
      return 0;
    }

    foreach ($statStorage->loadMultiple($statIds) as $stat) {
      /** @var \Drupal\lms_flashcard\Entity\CardStat $stat */
      $stat->set('field_mastered', FALSE);
      $stat->save();
    }

    return count($statIds);
  }

  /**
   * Whether a deck should be visible/studyable for a given user.
   *
   * A deck is visible to its own owner unconditionally, and to everyone
   * else only if it's published and its owner is staff (has course
   * management permission) — i.e. "official" content meant for everyone.
   * A deck a student self-imports via the JSON import form is otherwise
   * private to them, matching lms_note's per-user "own content" model.
   * Shared by StudyController (the "My Flashcards" list and direct page
   * access) and FlashcardApiController (the study-session AJAX endpoints),
   * so a guessed/tampered deck ID can't be used to read someone else's
   * private vocabulary.
   */
  public function isDeckVisibleTo(Deck $deck, int $uid): bool {
    if ((int) $deck->getOwnerId() === $uid) {
      return TRUE;
    }
    if (!$deck->get('status')->value) {
      return FALSE;
    }
    $owner = $deck->getOwner();
    if (!$owner) {
      // Owner account deleted — fail open rather than orphan the deck.
      return TRUE;
    }
    return $owner->hasPermission('view lms_course') || $owner->hasPermission('administer lms_course');
  }

}
