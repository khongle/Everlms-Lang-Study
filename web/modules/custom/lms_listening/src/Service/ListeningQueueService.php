<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Builds and persists the ordered passage-id queue for a "Listening"
 * level-picker session (see ListeningStartForm): the student checks one or
 * more levels, this picks a handful of passages per level and remembers the
 * resulting order across the multi-request Listen → Comprehension → Review
 * flow, so ListeningController::findNextPassageId() can chain "Next
 * passage" through exactly the passages the student picked — and only
 * those — instead of the whole deck.
 *
 * Same tempstore-backed queue idea as lms_flashcard's study session (see
 * FlashcardApiController's use of PrivateTempStore + StudyQueueBuilder),
 * kept as an independent, much smaller implementation: this queue is a
 * flat, already-final list of ids (no re-queueing, no due-date logic),
 * built once and walked forward until exhausted.
 */
final class ListeningQueueService {

  private const COLLECTION = 'lms_listening_study';

  /**
   * Canonical order levels are picked in when several are selected at
   * once — easiest first, so a session that runs out of time still covers
   * the gentler material.
   */
  private const LEVEL_ORDER = ['easy', 'normal', 'challenge'];

  public function __construct(
    protected PrivateTempStoreFactory $tempStoreFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ListeningVisibilityService $visibility,
  ) {}

  /**
   * Picks passages to practice from one or more levels of a deck.
   *
   * Within each level, passages the student has never attempted come
   * first (in the same 'created' DESC order the passage grid uses), so a
   * session never repeats material there's fresh material for; once a
   * level's unattempted passages run out, the remainder is filled
   * randomly from ones already attempted, so asking for more than a level
   * actually has still returns as many as exist rather than under-filling.
   *
   * @param int $uid
   *   The studying user.
   * @param int $deckId
   *   The deck to pick from.
   * @param string[] $levels
   *   Any of 'easy', 'normal', 'challenge'. Invalid values are ignored.
   * @param int $perLevel
   *   How many passages to pick per selected level (default 3; the
   *   level-picker UI lets the student change this, so any value is
   *   clamped to at least 1 here rather than trusted as already valid).
   *
   * @return int[]
   *   Passage ids in study order — levels in self::LEVEL_ORDER, ignoring
   *   any not in $levels, and within a level in the order picked above.
   *   A level with no passages simply contributes nothing; the result can
   *   be empty if the deck has no passages in any requested level.
   */
  public function buildFromLevels(int $uid, int $deckId, array $levels, int $perLevel = 3): array {
    $perLevel = max(1, $perLevel);
    $queue = [];
    foreach (self::LEVEL_ORDER as $level) {
      if (!in_array($level, $levels, TRUE)) {
        continue;
      }
      $queue = array_merge($queue, $this->pickForLevel($uid, $deckId, $level, $perLevel));
    }
    return $queue;
  }

  /**
   * Builds a Passive Listening queue: $perLevel passages per level, in
   * self::LEVEL_ORDER, each picked at random (see PASSIVE_LISTENING_PLAN.md)
   * — deliberately not pickForLevel()'s unattempted-first/newest-first
   * logic, since passive mode doesn't track "already completed" the way a
   * scored session does; the student explicitly asked for a random pick
   * each time. A level with fewer passages than $perLevel simply
   * contributes as many as it has (the session ends up shorter than
   * 3 * $perLevel), same graceful-degradation as buildFromLevels() already
   * has for Active mode.
   *
   * @param int $perLevel
   *   How many passages to pick per level (default 1 — the original "fixed
   *   3 total" design; the student later asked to be able to change this,
   *   same "Passages per level" control Active mode already has). Clamped
   *   to at least 1.
   * @param string[] $levels
   *   Which levels to include at all (default: all 3) — the student later
   *   asked to be able to narrow this too, same level checkboxes Active
   *   mode already has. Invalid values are ignored, same as
   *   buildFromLevels().
   *
   * @return int[]
   *   Up to count($levels) * $perLevel passage ids, in self::LEVEL_ORDER.
   */
  public function buildPassiveQueue(int $uid, int $deckId, int $perLevel = 1, array $levels = self::LEVEL_ORDER): array {
    $perLevel = max(1, $perLevel);
    $queue = [];
    foreach (self::LEVEL_ORDER as $level) {
      if (!in_array($level, $levels, TRUE)) {
        continue;
      }
      $queue = array_merge($queue, $this->pickRandomForLevel($uid, $deckId, $level, $perLevel));
    }
    return $queue;
  }

  /**
   * Lists every passage in a deck visible to $uid — {id, title, level}
   * rows, ordered by self::LEVEL_ORDER (then 'created' DESC within a
   * level) — backs the "choose specific passages" option in Passive
   * Listening's start popup (see js/listening-start-modal.js and
   * ListeningStartForm), an alternative to the random per-level pick
   * buildPassiveQueue() does.
   *
   * @return array<int, array{id: int, title: string, level: string}>
   */
  public function listPassagesForDeck(int $uid, int $deckId): array {
    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
    $rows = [];
    foreach (self::LEVEL_ORDER as $level) {
      $ids = $storage->getQuery()
        ->condition('status', TRUE)
        ->condition('field_deck', $deckId)
        ->condition('field_level', $level)
        ->sort('created', 'DESC')
        ->accessCheck(FALSE)
        ->execute();
      if (!$ids) {
        continue;
      }
      foreach ($storage->loadMultiple($ids) as $passage) {
        /** @var \Drupal\lms_listening\Entity\ListeningPassage $passage */
        if ($this->visibility->isPassageVisibleTo($passage, $uid)) {
          $rows[] = [
            'id' => (int) $passage->id(),
            'title' => (string) $passage->label(),
            'level' => $level,
            'format' => (string) $passage->get('field_format')->value,
          ];
        }
      }
    }
    return $rows;
  }

  /**
   * Builds a queue from passage ids the student picked directly (the
   * "choose specific passages" option) rather than a random per-level
   * pick — validated against, and ordered by, listPassagesForDeck() so a
   * tampered/stale id that isn't actually in this deck (or isn't visible
   * to $uid) is silently dropped rather than trusted, and the result still
   * plays Easy before Normal before Challenge even for a hand-picked set.
   *
   * @param int[] $passageIds
   *
   * @return int[]
   */
  public function buildQueueFromIds(int $uid, int $deckId, array $passageIds): array {
    $wanted = array_flip(array_map('intval', $passageIds));
    if (!$wanted) {
      return [];
    }

    $ordered = [];
    foreach ($this->listPassagesForDeck($uid, $deckId) as $row) {
      if (isset($wanted[$row['id']])) {
        $ordered[] = $row['id'];
      }
    }
    return $ordered;
  }

  /**
   * Counts passages available per level for one deck, visible to $uid —
   * used by the level-picker UI (ListeningStartForm) to show "N available"
   * next to each checkbox and disable any level with none. Simpler than
   * buildFromLevels()/pickForLevel(): just "does anything exist here",
   * with no unattempted/attempted split.
   *
   * @return array<string, int>
   *   Level => count, only for levels with at least one passage.
   */
  public function countByLevel(int $uid, int $deckId): array {
    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
    $ids = $storage->getQuery()
      ->condition('status', TRUE)
      ->condition('field_deck', $deckId)
      ->accessCheck(FALSE)
      ->execute();
    if (!$ids) {
      return [];
    }

    $counts = [];
    foreach ($storage->loadMultiple($ids) as $passage) {
      /** @var \Drupal\lms_listening\Entity\ListeningPassage $passage */
      if (!$this->visibility->isPassageVisibleTo($passage, $uid)) {
        continue;
      }
      $level = $passage->get('field_level')->value;
      $counts[$level] = ($counts[$level] ?? 0) + 1;
    }
    return $counts;
  }

  /**
   * Picks up to $count passages from one deck+level for buildFromLevels().
   *
   * @return int[]
   */
  private function pickForLevel(int $uid, int $deckId, string $level, int $count): array {
    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
    $ids = $storage->getQuery()
      ->condition('status', TRUE)
      ->condition('field_deck', $deckId)
      ->condition('field_level', $level)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    if (!$ids) {
      return [];
    }

    $attempt_storage = $this->entityTypeManager->getStorage('lms_listening_attempt');

    $unattempted = [];
    $attempted = [];
    foreach ($storage->loadMultiple($ids) as $passage) {
      /** @var \Drupal\lms_listening\Entity\ListeningPassage $passage */
      if (!$this->visibility->isPassageVisibleTo($passage, $uid)) {
        continue;
      }

      $passage_id = (int) $passage->id();
      $has_attempt = (bool) $attempt_storage->getQuery()
        ->condition('field_passage', $passage_id)
        ->condition('uid', $uid)
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();

      if ($has_attempt) {
        $attempted[] = $passage_id;
      }
      else {
        $unattempted[] = $passage_id;
      }
    }

    $picked = array_slice($unattempted, 0, $count);
    $remaining = $count - count($picked);
    if ($remaining > 0 && $attempted) {
      shuffle($attempted);
      $picked = array_merge($picked, array_slice($attempted, 0, $remaining));
    }

    return $picked;
  }

  /**
   * Picks up to $count passages from one deck+level completely at random —
   * for buildPassiveQueue() only. Unlike pickForLevel(), this never
   * distinguishes attempted from unattempted: passive mode doesn't track
   * "already completed" (no ListeningAttempt is ever written from it), so
   * there is nothing to prefer avoiding.
   *
   * @return int[]
   */
  private function pickRandomForLevel(int $uid, int $deckId, string $level, int $count): array {
    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
    $ids = $storage->getQuery()
      ->condition('status', TRUE)
      ->condition('field_deck', $deckId)
      ->condition('field_level', $level)
      ->accessCheck(FALSE)
      ->execute();
    if (!$ids) {
      return [];
    }

    $visibleIds = [];
    foreach ($storage->loadMultiple($ids) as $passage) {
      /** @var \Drupal\lms_listening\Entity\ListeningPassage $passage */
      if ($this->visibility->isPassageVisibleTo($passage, $uid)) {
        $visibleIds[] = (int) $passage->id();
      }
    }

    shuffle($visibleIds);
    return array_slice($visibleIds, 0, $count);
  }

  /**
   * Starts (or replaces) a practice queue for one deck.
   *
   * @param int $uid
   *   The studying user.
   * @param int $deckId
   *   The deck the queue was built from.
   * @param int[] $passageIds
   *   Passage ids in the order they should be studied.
   * @param string $mode
   *   'active' (default) or 'passive' — namespaces the tempstore key so an
   *   Active-mode queue and a Passive-mode queue for the same (uid, deck)
   *   never overwrite each other; a student can have one of each in
   *   flight at once. Every existing call site omits this and keeps
   *   working against the same 'active' key as before this parameter
   *   existed.
   */
  public function setQueue(int $uid, int $deckId, array $passageIds, string $mode = 'active'): void {
    $this->tempStoreFactory->get(self::COLLECTION)->set(
      $this->queueKey($mode, $uid, $deckId),
      array_values(array_map('intval', $passageIds)),
    );
  }

  /**
   * Finds the id after $currentPassageId in $uid's active queue for
   * $deckId.
   *
   * @param string $mode
   *   'active' (default) or 'passive' — must match whatever setQueue() was
   *   called with for this session, so the two modes' queues never cross.
   *
   * @param bool $loop
   *   Passive Listening only (see PASSIVE_LISTENING_PLAN.md — "repeat the
   *   whole session until the student presses Stop", at their own
   *   request): when TRUE and $currentPassageId was the queue's last item,
   *   wrap back around to the first item instead of ending — the queue is
   *   also NOT deleted in that case, since there's no "session over" to
   *   reach. Active mode never passes this (defaults FALSE), so it keeps
   *   ending normally once its own queue is exhausted.
   *
   * @return array{in_queue: bool, next_id: int|null}
   *   'in_queue' is FALSE when there's no active queue for this deck, or
   *   $currentPassageId simply isn't part of it — the caller should fall
   *   back to plain whole-deck browsing in that case. When 'in_queue' is
   *   TRUE, 'next_id' is either the next passage to show, or NULL meaning
   *   $currentPassageId was the queue's last item (and $loop was FALSE) —
   *   the chosen practice run is over (the queue is also cleared here),
   *   and the caller should stop rather than fall back and silently
   *   resume browsing the rest of the deck the student didn't ask for.
   */
  public function nextAfter(int $uid, int $deckId, int $currentPassageId, string $mode = 'active', bool $loop = FALSE): array {
    $store = $this->tempStoreFactory->get(self::COLLECTION);
    $key = $this->queueKey($mode, $uid, $deckId);
    $ids = $store->get($key);
    if (!is_array($ids) || !$ids) {
      return ['in_queue' => FALSE, 'next_id' => NULL];
    }

    $position = array_search($currentPassageId, $ids, TRUE);
    if ($position === FALSE) {
      return ['in_queue' => FALSE, 'next_id' => NULL];
    }

    if (isset($ids[$position + 1])) {
      return ['in_queue' => TRUE, 'next_id' => (int) $ids[$position + 1]];
    }

    if ($loop) {
      return ['in_queue' => TRUE, 'next_id' => (int) $ids[0]];
    }

    $store->delete($key);
    return ['in_queue' => TRUE, 'next_id' => NULL];
  }

  /**
   * Builds the tempstore key setQueue()/nextAfter() read and write.
   *
   * Deliberately produces the exact same "queue:{uid}:{deckId}" string as
   * before $mode existed whenever $mode is 'active' (anything other than
   * literally 'passive' is treated as 'active') — this is what keeps every
   * pre-existing Active-mode queue, and every call site that doesn't pass
   * $mode at all, working unchanged. Only 'passive' gets its own
   * "queue:passive:{uid}:{deckId}" namespace.
   */
  private function queueKey(string $mode, int $uid, int $deckId): string {
    $prefix = $mode === 'passive' ? 'passive:' : '';
    return "queue:{$prefix}{$uid}:{$deckId}";
  }

}
