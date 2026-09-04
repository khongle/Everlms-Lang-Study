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
  private const AGAIN_BUFFER = 3;

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
   * Builds the initial ordered queue for a study session.
   *
   * @param int $uid
   *   The studying user.
   * @param int[] $deckIds
   *   One or more deck IDs in scope for this session.
   *
   * @return array
   *   List of ['card_id' => int, 'source' => string], in study order.
   */
  public function buildQueue(int $uid, array $deckIds): array {
    if (!$deckIds) {
      return [];
    }

    $now = $this->time->getRequestTime();
    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');

    $cardIds = $cardStorage->getQuery()
      ->condition('field_deck', $deckIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return [];
    }
    $cardIds = array_values(array_map('intval', $cardIds));

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
      ->accessCheck(FALSE)
      ->execute();
    if (!$cardIds) {
      return ['due' => 0, 'new' => 0];
    }
    $cardIds = array_values(array_map('intval', $cardIds));

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

    $new = count(array_diff($cardIds, array_keys($seen)));

    return ['due' => $due, 'new' => min($new, max(0, $this->getRemainingNewCardBudget($uid)))];
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
