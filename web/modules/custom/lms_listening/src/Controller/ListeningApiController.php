<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\lms_listening\Entity\ListeningPassage;
use Drupal\lms_listening\Service\ListeningPresentationService;
use Drupal\lms_listening\Service\ListeningQueueService;
use Drupal\lms_listening\Service\ListeningVisibilityService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AJAX endpoint backing the listening passage comprehension check.
 *
 * Follows lms_reading's ReadingApiController convention: gated only by
 * _user_is_logged_in, every query scoped by resolved uid/passage
 * visibility rather than relying on entity-level access grants.
 */
final class ListeningApiController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected ListeningVisibilityService $visibility,
    protected ListeningQueueService $queue,
    protected ListeningPresentationService $presentation,
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
      $container->get('lms_listening.visibility'),
      $container->get('lms_listening.queue'),
      $container->get('lms_listening.presentation'),
      $container->get('datetime.time'),
    );
  }

  /**
   * POST /api/lms/listening/start
   *
   * AJAX counterpart to ListeningStartForm — same level-picker logic
   * (buildFromLevels() + setQueue()), reached from the in-page popup that
   * progressively enhances the "Listening" link on a Flashcard deck card
   * (see js/listening-start-modal.js) instead of a full-page navigation.
   * ListeningStartForm's own page keeps working unchanged as the no-JS
   * fallback for that same link.
   *
   * Body: { deck_id: int, levels: string[], per_level?: int }. `per_level`
   * (default 3, clamped to at least 1 — see
   * ListeningQueueService::buildFromLevels()) applies to every selected
   * level, matching the single "Passages per level" control the popup and
   * ListeningStartForm both show rather than a separate count per level.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   `{redirect_url}` on success (including the "no passages matched"
   *   case, which redirects to the plain passage grid same as the form
   *   does), or `{error}` with a 4xx status for a malformed request the
   *   popup should show inline rather than navigate away from.
   */
  public function start(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $body = json_decode((string) $request->getContent(), TRUE);
    $deck_id = is_array($body) ? (int) ($body['deck_id'] ?? 0) : 0;
    if (!$deck_id) {
      return new JsonResponse(['error' => 'Missing deck_id.'], 400);
    }

    // Passive Listening (see PASSIVE_LISTENING_PLAN.md) branches off into
    // its own method entirely, before any of the levels/per_level
    // validation below — that validation only makes sense for Active
    // mode's own request shape. Anything other than the literal string
    // 'passive' falls through to the existing Active-mode code completely
    // unchanged, so an old client that never sends `mode` at all (or a
    // typo'd value) behaves exactly as it did before this branch existed.
    if (is_array($body) && ($body['mode'] ?? NULL) === 'passive') {
      // Specific passages (see PASSIVE_LISTENING_PLAN.md's "choose exact
      // passages" addition) take priority over the level/per_level random
      // pick when the student checked any — see startPassive().
      $passive_passage_ids = is_array($body['passage_ids'] ?? NULL) ? array_map('intval', $body['passage_ids']) : [];
      $passive_levels = is_array($body['levels'] ?? NULL)
        ? array_values(array_intersect($body['levels'], ['easy', 'normal', 'challenge']))
        : ['easy', 'normal', 'challenge'];
      if (!$passive_passage_ids && !$passive_levels) {
        return new JsonResponse(['error' => 'Pick at least one level, or specific passages.'], 400);
      }
      $passive_per_level = max(1, (int) ($body['per_level'] ?? 1));
      return $this->startPassive($uid, $deck_id, $passive_per_level, $passive_levels, $passive_passage_ids);
    }

    $requested_levels = is_array($body['levels'] ?? NULL) ? $body['levels'] : [];
    $levels = array_values(array_intersect($requested_levels, ['easy', 'normal', 'challenge']));
    if (!$levels) {
      return new JsonResponse(['error' => 'Pick at least one level.'], 400);
    }

    $per_level = max(1, (int) ($body['per_level'] ?? 3));

    $passage_ids = $this->queue->buildFromLevels($uid, $deck_id, $levels, $per_level);
    if (!$passage_ids) {
      return new JsonResponse([
        'redirect_url' => Url::fromRoute('lms_listening.my_passages', [], ['query' => ['deck' => $deck_id]])->toString(),
      ]);
    }

    $this->queue->setQueue($uid, $deck_id, $passage_ids);
    return new JsonResponse([
      'redirect_url' => Url::fromRoute('lms_listening.listen', ['lms_listening_passage' => $passage_ids[0]])->toString(),
    ]);
  }

  /**
   * mode=passive branch of start() — see PASSIVE_LISTENING_PLAN.md.
   * $passage_ids (the "choose specific passages" option), when non-empty,
   * takes over entirely — the student picked exact passages, so
   * $per_level/$levels are ignored (ListeningQueueService::
   * buildQueueFromIds()). Otherwise falls back to the random per-level
   * pick within $levels (buildPassiveQueue()), sharing that same
   * level-checkbox + "Passages per level" control Active mode has.
   */
  private function startPassive(int $uid, int $deck_id, int $per_level, array $levels, array $passage_ids): JsonResponse {
    $passage_ids = $passage_ids
      ? $this->queue->buildQueueFromIds($uid, $deck_id, $passage_ids)
      : $this->queue->buildPassiveQueue($uid, $deck_id, $per_level, $levels);
    if (!$passage_ids) {
      return new JsonResponse([
        'redirect_url' => Url::fromRoute('lms_listening.my_passages', [], ['query' => ['deck' => $deck_id]])->toString(),
      ]);
    }

    $this->queue->setQueue($uid, $deck_id, $passage_ids, 'passive');
    return new JsonResponse([
      'redirect_url' => Url::fromRoute('lms_listening.listen_passive', ['lms_listening_passage' => $passage_ids[0]])->toString(),
    ]);
  }

  /**
   * POST /api/lms/listening/passive-next
   *
   * Body: {deck_id, current_passage_id}. Walks the same 'passive'-mode
   * queue startPassive() set up (ListeningQueueService::nextAfter(...,
   * mode: 'passive', loop: TRUE) — the loop flag is what makes the session
   * repeat indefinitely (wrap back to the first passage) instead of ever
   * naturally ending; per PASSIVE_LISTENING_PLAN.md, only pressing Stop in
   * the browser ends a Passive Listening session, at the student's own
   * request) and returns the next passage's transcript/vocabulary
   * as plain JSON — js/listening-passive.js uses this to advance the
   * Passive Listening session in place, without a page navigation (see
   * ListeningController::listenPassive()'s docblock for why that matters).
   * Also bumps ListeningPassageStat via recordPassageSeen()-equivalent
   * logic... actually see below: reuses the same private helper as
   * ListeningController by calling the passage's own stat-recording
   * inline here, since API controllers and page controllers don't share
   * private methods — kept as a short, obviously-mirrored duplicate of
   * ListeningController::recordPassageSeen() rather than a forced shared
   * abstraction for four lines of code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   `{status: 'done'}` once the queue is exhausted, or `{status: 'ok',
   *   passage: {id, title, level, transcript, vocabTerms}}` for the next
   *   one — same "passage" shape ListeningController::listenPassive()
   *   seeds drupalSettings with for the first passage, so the front end
   *   can use one render function for both.
   */
  public function passiveNext(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $body = json_decode((string) $request->getContent(), TRUE);
    $deck_id = is_array($body) ? (int) ($body['deck_id'] ?? 0) : 0;
    $current_passage_id = is_array($body) ? (int) ($body['current_passage_id'] ?? 0) : 0;
    if (!$deck_id || !$current_passage_id) {
      return new JsonResponse(['error' => 'Missing deck_id or current_passage_id.'], 400);
    }

    $next = $this->queue->nextAfter($uid, $deck_id, $current_passage_id, 'passive', TRUE);
    if (!$next['in_queue'] || $next['next_id'] === NULL) {
      // Only reachable if the tempstore queue itself is gone (expired, or
      // never started) — with loop:TRUE this never happens from simply
      // reaching the last passage. A defensive fallback, not the normal
      // end-of-session path any more (see PASSIVE_LISTENING_PLAN.md).
      return new JsonResponse(['status' => 'done']);
    }

    $passage = $this->loadVisiblePassage($next['next_id'], $uid);
    if (!$passage) {
      // Deleted/unpublished mid-session — treat as the session ending
      // rather than erroring the student out.
      return new JsonResponse(['status' => 'done']);
    }

    $this->recordPassageSeen($passage, $uid);

    return new JsonResponse([
      'status' => 'ok',
      'passage' => [
        'id' => (int) $passage->id(),
        'title' => $passage->label(),
        'level' => $passage->get('field_level')->value,
        'transcript' => $this->presentation->buildPassiveTranscript($passage),
        'vocabTerms' => $this->presentation->buildVocabTerms($passage),
        'translation' => $this->presentation->buildTranslation($passage),
      ],
    ]);
  }

  /**
   * Mirrors ListeningController::recordPassageSeen() exactly (bumps
   * ListeningPassageStat.field_times_seen) — kept as its own copy here
   * rather than a cross-controller call; see passiveNext()'s docblock.
   */
  private function recordPassageSeen(ListeningPassage $passage, int $uid): void {
    $stat_storage = $this->entityTypeManager->getStorage('lms_listening_passage_stat');
    $stat_ids = $stat_storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_passage', $passage->id())
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $stat = $stat_ids ? $stat_storage->load(reset($stat_ids)) : NULL;
    $stat ??= $stat_storage->create(['uid' => $uid, 'field_passage' => $passage->id()]);

    $stat->set('field_times_seen', ((int) ($stat->get('field_times_seen')->value ?? 0)) + 1);
    $stat->set('field_last_seen_at', $this->time->getRequestTime());
    $stat->save();
  }

  /**
   * POST /api/lms/listening/attempt
   *
   * Body: {
   *   passage_id, duration_seconds, replay_count, hints_used,
   *   transcript_shown, translation_shown,
   *   answers: {questionId: answerValue, ...}
   * }
   *
   * `answerValue` shape depends on the question's type (read off the
   * loaded lms_question entity, never trusted from the client):
   * multiple_choice -> selected lms_answer id (int); true_false -> bool;
   * fill_blank/short_answer -> string.
   *
   * Grading happens entirely here, from the entities themselves — never
   * trusting a "correct"/"score" value from the client. Identical grading
   * logic to ReadingApiController::gradeQuestions(), kept as an
   * independent copy per this module's independence from lms_reading's
   * own code.
   */
  public function submitAttempt(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $body = json_decode((string) $request->getContent(), TRUE);
    if (!is_array($body)) {
      return new JsonResponse(['error' => 'Invalid JSON body'], 400);
    }

    $passage = $this->loadVisiblePassage((int) ($body['passage_id'] ?? 0), $uid);
    if (!$passage) {
      return new JsonResponse(['error' => 'Passage not found'], 404);
    }

    $answers = is_array($body['answers'] ?? NULL) ? $body['answers'] : [];
    [$correct, $total] = $this->gradeQuestions($passage, $answers);
    $score = $total > 0 ? (int) round($correct / $total * 100) : 0;

    $attempt_storage = $this->entityTypeManager->getStorage('lms_listening_attempt');
    $attempt = $attempt_storage->create([
      'uid' => $uid,
      'field_passage' => $passage->id(),
      'field_score' => $score,
      'field_correct_count' => $correct,
      'field_question_count' => $total,
      'field_duration_seconds' => max(0, (int) ($body['duration_seconds'] ?? 0)),
      'field_replay_count' => max(0, (int) ($body['replay_count'] ?? 0)),
      'field_hints_used' => max(0, (int) ($body['hints_used'] ?? 0)),
      'field_transcript_shown' => !empty($body['transcript_shown']),
      'field_translation_shown' => !empty($body['translation_shown']),
    ]);
    $attempt->save();

    return new JsonResponse([
      'attempt_id' => (int) $attempt->id(),
      'score' => $score,
      'correct_count' => $correct,
      'question_count' => $total,
      'review_url' => '/learn/listening/' . $passage->id() . '/review/' . $attempt->id(),
    ]);
  }

  /**
   * Grades the comprehension questions.
   *
   * @return array{0: int, 1: int}
   *   [correct_count, total_count].
   */
  private function gradeQuestions(ListeningPassage $passage, array $answers): array {
    $correct = 0;
    $total = 0;
    foreach ($passage->get('field_questions')->referencedEntities() as $question) {
      $total++;
      $given = $answers[$question->id()] ?? NULL;
      if ($given === NULL) {
        continue;
      }

      $is_correct = match ($question->bundle()) {
        'multiple_choice' => $this->isMultipleChoiceCorrect($question, $given),
        'true_false' => is_bool($given) && $given === (bool) $question->get('field_correct_answer')->value,
        'fill_blank', 'short_answer' => $this->isTextAnswerCorrect($question, $given),
        default => FALSE,
      };
      if ($is_correct) {
        $correct++;
      }
    }
    return [$correct, $total];
  }

  private function isMultipleChoiceCorrect($question, mixed $given): bool {
    $selected_id = (int) $given;
    foreach ($question->get('field_choices')->referencedEntities() as $answer) {
      if ((int) $answer->id() === $selected_id) {
        return (bool) $answer->get('field_correct')->value;
      }
    }
    return FALSE;
  }

  private function isTextAnswerCorrect($question, mixed $given): bool {
    $given = mb_strtolower(trim((string) $given));
    if ($given === '') {
      return FALSE;
    }
    foreach (array_column($question->get('field_correct_answers')->getValue(), 'value') as $accepted) {
      if (mb_strtolower(trim((string) $accepted)) === $given) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Loads a passage only if it exists AND is visible to $uid.
   */
  private function loadVisiblePassage(int $passage_id, int $uid): ?ListeningPassage {
    if (!$passage_id) {
      return NULL;
    }
    $passage = $this->entityTypeManager->getStorage('lms_listening_passage')->load($passage_id);
    if (!$passage instanceof ListeningPassage || !$this->visibility->isPassageVisibleTo($passage, $uid)) {
      return NULL;
    }
    return $passage;
  }

}
