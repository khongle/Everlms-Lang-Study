<?php

declare(strict_types=1);

namespace Drupal\lms_mobile_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\lms_quiz\Grading\GradingStrategyFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles mobile lesson, quiz, and progress endpoints.
 */
class MobileLearningController extends ControllerBase {

  public function __construct(
    protected LoggerInterface $logger,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('logger.factory')->get('lms_mobile_api'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * Returns full lesson detail, gating access by enrollment / free preview.
   */
  public function getLessonDetail(Request $request, string $courseId, string $lessonId): JsonResponse {
    $course = $this->entityTypeManager()->getStorage('lms_course')->load((int) $courseId);
    $lesson = $this->entityTypeManager()->getStorage('lms_lesson')->load((int) $lessonId);

    if (!$course || !$course->get('status')->value || !$lesson || !$lesson->get('status')->value) {
      return $this->errorResponse('not_found', 'Course or lesson not found.', 404);
    }

    $ordered_lessons = $this->getOrderedCourseLessons($course);
    $lesson_ids = array_column($ordered_lessons, 'id');
    if (!in_array((int) $lessonId, $lesson_ids, TRUE)) {
      return $this->errorResponse('not_found', 'Lesson not found in this course.', 404);
    }

    $free_preview = (bool) ($lesson->hasField('field_free_preview') ? $lesson->get('field_free_preview')->value : FALSE);
    $user = NULL;
    $uid = NULL;
    $enrollment = NULL;

    try {
      $user = $this->getAuthenticatedUser($request);
      $uid = (int) $user->id();
      $enrollment = $this->getUserEnrollment($uid, (int) $courseId);
    }
    catch (AccessDeniedHttpException) {
      if (!$free_preview) {
        return $this->errorResponse('not_enrolled', 'You must be enrolled in this course to access this lesson.', 403);
      }
    }

    if (!$free_preview && !$enrollment) {
      return $this->errorResponse('not_enrolled', 'You must be enrolled in this course to access this lesson.', 403);
    }

    $drip_days = (int) ($lesson->hasField('field_drip_days') ? ($lesson->get('field_drip_days')->value ?? 0) : 0);
    $unlock_ts = NULL;
    $is_locked = FALSE;
    if ($enrollment && $drip_days > 0) {
      $enrolled_at = (int) ($enrollment->get('field_enrolled_at')->value ?? 0);
      $unlock_ts = $enrolled_at + ($drip_days * 86400);
      $is_locked = $unlock_ts > time();
    }

    if ($is_locked) {
      return new JsonResponse([
        'error' => 'lesson_locked',
        'message' => sprintf('This lesson is unlocked in %d days.', max(1, (int) ceil(($unlock_ts - time()) / 86400))),
        'unlocks_at' => date('c', $unlock_ts),
      ], 423);
    }

    $completed_lesson_ids = $uid ? $this->getCompletedLessonIds($uid, $lesson_ids) : [];
    $is_completed = in_array((int) $lessonId, $completed_lesson_ids, TRUE);

    $navigation = [
      'prev_lesson_id' => NULL,
      'next_lesson_id' => NULL,
      'course_id' => (int) $courseId,
    ];

    foreach ($lesson_ids as $index => $ordered_lesson_id) {
      if ($ordered_lesson_id === (int) $lessonId) {
        $navigation['prev_lesson_id'] = $lesson_ids[$index - 1] ?? NULL;
        $navigation['next_lesson_id'] = $lesson_ids[$index + 1] ?? NULL;
        break;
      }
    }

    $curriculum = [];
    foreach ($this->getCourseCurriculum($course, $uid, $enrollment) as $chapter) {
      $curriculum[] = [
        'id' => $chapter['id'],
        'title' => $chapter['title'],
        'lessons' => array_map(static fn(array $item): array => [
          'id' => $item['id'],
          'title' => $item['title'],
          'type' => $item['type'],
          'is_completed' => $item['is_completed'],
        ], $chapter['lessons']),
      ];
    }

    return new JsonResponse([
      'id' => (int) $lesson->id(),
      'uuid' => $lesson->uuid(),
      'title' => $lesson->label(),
      'type' => $this->getLessonType($lesson),
      'duration_minutes' => (int) ($lesson->hasField('field_duration') ? ($lesson->get('field_duration')->value ?? 0) : 0),
      'free_preview' => $free_preview,
      'is_completed' => $is_completed,
      'is_locked' => $is_locked,
      'drip_days' => $drip_days,
      'content' => $this->buildLessonContent($lesson),
      'attachments' => $this->buildLessonAttachments($lesson),
      'navigation' => $navigation,
      'curriculum' => $curriculum,
    ]);
  }

  /**
   * Returns quiz detail and question payload for the mobile app.
   */
  public function getQuiz(Request $request, string $quizId): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $quiz = $this->entityTypeManager()->getStorage('lms_quiz')->load((int) $quizId);
    if (!$quiz || !$quiz->get('status')->value) {
      return $this->errorResponse('not_found', 'Quiz not found.', 404);
    }

    $attempts_used = $this->countQuizAttempts($uid, (int) $quizId);
    $attempts_allowed = (int) ($quiz->hasField('field_take_quiz_number') ? ($quiz->get('field_take_quiz_number')->value ?? 0) : 0);
    $can_attempt = $attempts_allowed === 0 || $attempts_used < $attempts_allowed;

    $question_entities = $quiz->hasField('field_questions') ? $quiz->get('field_questions')->referencedEntities() : [];

    $questions = [];
    foreach ($question_entities as $question) {
      $questions[] = $this->buildQuizQuestion($question);
    }

    return new JsonResponse([
      'id' => (int) $quiz->id(),
      'uuid' => $quiz->uuid(),
      'title' => $quiz->label(),
      'instructions' => $quiz->hasField('field_body') ? ($quiz->get('field_body')->value ?? '') : '',
      'time_limit_minutes' => (int) ($quiz->hasField('field_time_limit') ? ($quiz->get('field_time_limit')->value ?? 0) : 0),
      'threshold_percent' => (int) ($quiz->hasField('field_threshold') ? ($quiz->get('field_threshold')->value ?? 0) : 0),
      'form_style' => $quiz->hasField('field_form_style') ? ($quiz->get('field_form_style')->value ?? 'one_page') : 'one_page',
      'display_right_answer' => (bool) ($quiz->hasField('field_display_right_answer') ? $quiz->get('field_display_right_answer')->value : FALSE),
      'attempts_allowed' => $attempts_allowed,
      'attempts_used' => $attempts_used,
      'can_attempt' => $can_attempt,
      'questions' => $questions,
    ]);
  }

  /**
   * Submits a quiz attempt and returns the normalized result payload.
   */
  public function submitQuiz(Request $request, string $quizId): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $quiz = $this->entityTypeManager()->getStorage('lms_quiz')->load((int) $quizId);
    if (!$quiz || !$quiz->get('status')->value) {
      return $this->errorResponse('not_found', 'Quiz not found.', 404);
    }

    $attempts_used = $this->countQuizAttempts($uid, (int) $quizId);
    $attempts_allowed = (int) ($quiz->hasField('field_take_quiz_number') ? ($quiz->get('field_take_quiz_number')->value ?? 0) : 0);
    if ($attempts_allowed > 0 && $attempts_used >= $attempts_allowed) {
      return $this->errorResponse('max_attempts_reached', 'You have reached the maximum number of attempts for this quiz.', 400);
    }

    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload)) {
      return $this->errorResponse('invalid_payload', 'Request body must be valid JSON.', 400);
    }

    $submitted_answers = [];
    foreach (($payload['answers'] ?? []) as $answer) {
      if (isset($answer['question_id'])) {
        $submitted_answers[(int) $answer['question_id']] = $answer;
      }
    }

    $total_score = 0.0;
    $max_score = 0.0;
    $response_refs = [];
    $pending_review = FALSE;
    $response_storage = $this->entityTypeManager()->getStorage('lms_question_response');

    $question_entities = $quiz->hasField('field_questions') ? $quiz->get('field_questions')->referencedEntities() : [];
    foreach ($question_entities as $question) {
      $strategy = GradingStrategyFactory::getStrategy($question->bundle());
      if (!$strategy) {
        continue;
      }

      $max_score += $strategy->getMaxScore($question);
      $answer_data = $submitted_answers[(int) $question->id()] ?? [];
      $normalized_submission = $this->normalizeQuizSubmission($question, $answer_data);
      $graded = $strategy->grade($question, $normalized_submission);

      $response_entity = $response_storage->create([
        'field_question' => (int) $question->id(),
        'field_quiz' => (int) $quizId,
        'field_correct' => $graded['is_correct'],
        'field_score' => $graded['score'],
        'field_text_answer' => $graded['text_answer'],
        'field_pending_review' => $graded['pending_review'],
        'field_answers' => $graded['answer_ids'] ?? [],
      ]);
      $response_entity->save();

      $response_refs[] = ['target_id' => (int) $response_entity->id()];
      $total_score += (float) $graded['score'];
      $pending_review = $pending_review || !empty($graded['pending_review']);
    }

    $score_percent = $max_score > 0 ? round(($total_score / $max_score) * 100, 2) : 0.0;
    $threshold_percent = (float) ($quiz->hasField('field_threshold') ? ($quiz->get('field_threshold')->value ?? 0) : 0);

    $status = 'failed';
    $result_term_name = 'Failed';
    if ($pending_review) {
      $status = 'pending';
      $result_term_name = 'Pending';
    }
    elseif ($score_percent >= $threshold_percent) {
      $status = 'passed';
      $result_term_name = 'Passed';
    }

    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'quiz_results',
      'name' => $result_term_name,
    ]);
    $result_term = reset($terms) ?: NULL;

    $now = \Drupal::time()->getRequestTime();
    $quiz_result = $this->entityTypeManager()->getStorage('lms_quiz_result')->create([
      'uid' => $uid,
      'field_quiz' => (int) $quizId,
      'field_score' => $score_percent,
      'field_responses' => $response_refs,
      'field_start_time' => max(0, $now - (int) (($payload['time_taken_seconds'] ?? 0))),
      'field_end_time' => $now,
    ]);

    if ($result_term) {
      $quiz_result->set('field_result', (int) $result_term->id());
    }
    $quiz_result->save();

    if ($result_term && $result_term->label() === 'Passed' && class_exists('\\Drupal\\lms_quiz\\Event\\QuizPassedEvent')) {
      $event = new \Drupal\lms_quiz\Event\QuizPassedEvent($quiz_result, $quiz, $user);
      \Drupal::service('event_dispatcher')->dispatch($event, \Drupal\lms_quiz\Event\QuizPassedEvent::EVENT_NAME);
    }

    return new JsonResponse([
      'result_id' => (int) $quiz_result->id(),
      'score_percent' => $score_percent,
      'passed' => $status === 'passed',
      'status' => $status,
      'threshold_percent' => $threshold_percent,
      'time_taken_seconds' => (int) ($payload['time_taken_seconds'] ?? 0),
      'attempt_number' => $attempts_used + 1,
      'message' => $status === 'passed'
        ? 'Congratulations! You passed.'
        : ($status === 'pending' ? 'Your submission is pending manual review.' : 'You did not reach the passing score.'),
    ]);
  }

  /**
   * Returns a single quiz result.
   */
  public function getQuizResult(Request $request, string $resultId): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $result = $this->entityTypeManager()->getStorage('lms_quiz_result')->load((int) $resultId);
    if (!$result) {
      return $this->errorResponse('not_found', 'Quiz result not found.', 404);
    }
    if ((int) $result->getOwnerId() !== $uid) {
      return $this->errorResponse('forbidden', 'You do not have access to this result.', 403);
    }

    $quiz = $result->get('field_quiz')->entity;
    if (!$quiz) {
      return $this->errorResponse('not_found', 'Quiz not found.', 404);
    }

    $attempts_allowed = (int) ($quiz->hasField('field_take_quiz_number') ? ($quiz->get('field_take_quiz_number')->value ?? 0) : 0);
    $attempts_used = $this->countQuizAttempts($uid, (int) $quiz->id());

    $feedback = [];
    $show_answers = (bool) ($quiz->hasField('field_display_right_answer') ? $quiz->get('field_display_right_answer')->value : FALSE);
    if ($show_answers && $result->hasField('field_responses')) {
      foreach ($result->get('field_responses')->referencedEntities() as $response) {
        $question = $response->get('field_question')->entity;
        if (!$question) {
          continue;
        }
        $feedback[] = $this->buildQuestionFeedback($question, $response);
      }
    }

    return new JsonResponse([
      'id' => (int) $result->id(),
      'quiz_id' => (int) $quiz->id(),
      'quiz_title' => $quiz->label(),
      'score_percent' => (float) ($result->get('field_score')->value ?? 0),
      'passed' => $this->getQuizResultStatus($result) === 'passed',
      'status' => $this->getQuizResultStatus($result),
      'threshold_percent' => (float) ($quiz->hasField('field_threshold') ? ($quiz->get('field_threshold')->value ?? 0) : 0),
      'attempt_number' => $this->getAttemptNumber($uid, (int) $result->id(), (int) $quiz->id()),
      'time_taken_seconds' => $this->getQuizResultDurationSeconds($result),
      'completed_at' => $result->hasField('field_end_time') && !$result->get('field_end_time')->isEmpty()
        ? date('c', (int) $result->get('field_end_time')->value)
        : NULL,
      'question_feedback' => $feedback,
      'can_retry' => $attempts_allowed === 0 || $attempts_used < $attempts_allowed,
    ]);
  }

  /**
   * Lists quiz results for the authenticated user.
   */
  public function listMyQuizResults(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();
    $page = max(0, (int) $request->query->get('page', 0));
    $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

    $query = $this->entityTypeManager()->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE);

    $count_query = clone $query;
    $total = (int) $count_query->count()->execute();
    $ids = $query->range($page * $limit, $limit)->execute();

    $items = [];
    $results = $ids
      ? $this->entityTypeManager()->getStorage('lms_quiz_result')->loadMultiple($ids)
      : [];

    foreach ($results as $result) {
      $quiz = $result->get('field_quiz')->entity;
      if (!$quiz) {
        continue;
      }

      $course = $this->findCourseForQuiz((int) $quiz->id());
      $items[] = [
        'id' => (int) $result->id(),
        'quiz_id' => (int) $quiz->id(),
        'quiz_title' => $quiz->label(),
        'course_id' => $course?->id(),
        'course_title' => $course?->label() ?? '',
        'score_percent' => (float) ($result->get('field_score')->value ?? 0),
        'status' => $this->getQuizResultStatus($result),
        'attempt_number' => $this->getAttemptNumber($uid, (int) $result->id(), (int) $quiz->id()),
        'completed_at' => $result->hasField('field_end_time') && !$result->get('field_end_time')->isEmpty()
          ? date('c', (int) $result->get('field_end_time')->value)
          : NULL,
      ];
    }

    return new JsonResponse([
      'total' => $total,
      'page' => $page,
      'limit' => $limit,
      'items' => $items,
    ]);
  }

  /**
   * Marks a lesson complete and recalculates course progress.
   */
  public function markLessonComplete(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();
    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload)) {
      return $this->errorResponse('invalid_payload', 'Request body must be valid JSON.', 400);
    }

    $course_id = (int) ($payload['course_id'] ?? 0);
    $lesson_id = (int) ($payload['lesson_id'] ?? 0);
    if ($course_id <= 0 || $lesson_id <= 0) {
      return $this->errorResponse('missing_parameter', 'course_id and lesson_id are required.', 400);
    }

    $course = $this->entityTypeManager()->getStorage('lms_course')->load($course_id);
    $lesson = $this->entityTypeManager()->getStorage('lms_lesson')->load($lesson_id);
    if (!$course || !$lesson || !$course->get('status')->value || !$lesson->get('status')->value) {
      return $this->errorResponse('not_found', 'Course or lesson not found.', 404);
    }

    $enrollment = $this->getUserEnrollment($uid, $course_id);
    if (!$enrollment) {
      return $this->errorResponse('not_enrolled', 'You are not enrolled in this course.', 403);
    }

    $lesson_ids = array_column($this->getOrderedCourseLessons($course), 'id');
    if (!in_array($lesson_id, $lesson_ids, TRUE)) {
      return $this->errorResponse('not_found', 'Lesson not found in this course.', 404);
    }

    $progress_storage = $this->entityTypeManager()->getStorage('lms_user_lesson');
    $existing = $progress_storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    $now = time();
    $lesson_completed = FALSE;
    if ($existing) {
      $progress = $progress_storage->load(reset($existing));
      if ($progress && $progress->get('field_status')->value !== 'completed') {
        $progress->set('field_status', 'completed');
        $progress->set('field_completed_at', $now);
        $progress->save();
        $lesson_completed = TRUE;
      }
    }
    else {
      $progress = $progress_storage->create([
        'uid' => $uid,
        'field_lesson' => $lesson_id,
        'field_status' => 'completed',
        'field_video_position' => 0,
        'field_completed_at' => $now,
      ]);
      $progress->save();
      $lesson_completed = TRUE;
    }

    $completed_lesson_ids = $this->getCompletedLessonIds($uid, $lesson_ids);
    $progress_percent = count($lesson_ids) > 0
      ? (float) floor((count($completed_lesson_ids) / count($lesson_ids)) * 100)
      : 0.0;

    $course_completed = FALSE;
    $enrollment->set('field_progress', $progress_percent);
    if ($progress_percent >= 100 && $enrollment->get('field_status')->value !== 'completed') {
      $enrollment->set('field_status', 'completed');
      $enrollment->set('field_completed_at', $now);
      $course_completed = TRUE;
    }
    elseif ($progress_percent > 0 && $enrollment->get('field_status')->value === 'enrolled') {
      $enrollment->set('field_status', 'in_progress');
    }
    $enrollment->save();

    $next_lesson_id = NULL;
    foreach ($lesson_ids as $index => $ordered_lesson_id) {
      if ($ordered_lesson_id === $lesson_id) {
        $next_lesson_id = $lesson_ids[$index + 1] ?? NULL;
        break;
      }
    }

    $response = [
      'success' => TRUE,
      'lesson_completed' => $lesson_completed,
      'course_completed' => $course_completed,
      'progress' => $progress_percent,
      'next_lesson_url' => $next_lesson_id ? '/api/v1/courses/' . $course_id . '/lessons/' . $next_lesson_id : NULL,
      'next_lesson_id' => $next_lesson_id,
    ];

    return new JsonResponse($response);
  }

  /**
   * Resolves the authenticated user from a bearer token.
   */
  protected function getAuthenticatedUser(Request $request): \Drupal\user\UserInterface {
    // The Authorization header carries Basic Auth credentials for the server-
    // level HTTP auth gate. The Bearer token is sent in X-Authorization.
    $x_auth = $request->headers->get('X-Authorization', '');
    $auth_header = preg_match('/Bearer\s+/i', $x_auth) ? $x_auth : $request->headers->get('Authorization', '');

    if (!preg_match('/Bearer\s+(\S+)/i', $auth_header, $matches)) {
      throw new AccessDeniedHttpException('Missing or invalid Authorization header.');
    }

    $token = $matches[1];
    $uid = \Drupal::service('keyvalue.expirable')
      ->get('lms_mobile_api.tokens')
      ->get($token);

    if (!$uid) {
      throw new AccessDeniedHttpException('Access token is invalid or has expired.');
    }

    $user = $this->entityTypeManager()->getStorage('user')->load($uid);
    if (!$user || !$user->isActive()) {
      throw new AccessDeniedHttpException('User account not found or inactive.');
    }

    return $user;
  }

  /**
   * Returns a JSON error response.
   */
  protected function errorResponse(string $error, string $message, int $status = 400): JsonResponse {
    return new JsonResponse(['error' => $error, 'message' => $message], $status);
  }

  /**
   * Returns the user's course enrollment if present.
   */
  private function getUserEnrollment(int $uid, int $course_id): ?object {
    $ids = $this->entityTypeManager()->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_course', $course_id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    return $ids
      ? $this->entityTypeManager()->getStorage('lms_user_course')->load(reset($ids))
      : NULL;
  }

  /**
   * Returns completed lesson IDs for the user.
   *
   * @param int[] $lesson_ids
   *
   * @return int[]
   */
  private function getCompletedLessonIds(int $uid, array $lesson_ids): array {
    if ($lesson_ids === []) {
      return [];
    }

    $ids = $this->entityTypeManager()->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_ids, 'IN')
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->execute();

    $completed = [];
    if ($ids) {
      foreach ($this->entityTypeManager()->getStorage('lms_user_lesson')->loadMultiple($ids) as $progress) {
        $completed[] = (int) $progress->get('field_lesson')->target_id;
      }
    }

    return $completed;
  }

  /**
   * Returns ordered lesson metadata for a course.
   *
   * @return array<int, array<string, mixed>>
   */
  private function getOrderedCourseLessons(object $course): array {
    $items = [];
    if (!$course->hasField('field_chapters') || $course->get('field_chapters')->isEmpty()) {
      return $items;
    }

    $chapters = $course->get('field_chapters')->referencedEntities();
    usort($chapters, fn($a, $b) => ($a->get('weight')->value ?? 0) <=> ($b->get('weight')->value ?? 0));

    foreach ($chapters as $chapter) {
      if (!$chapter->get('status')->value || !$chapter->hasField('field_lessons') || $chapter->get('field_lessons')->isEmpty()) {
        continue;
      }

      foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
        if (!$lesson->get('status')->value) {
          continue;
        }
        $items[] = [
          'id' => (int) $lesson->id(),
          'title' => $lesson->label(),
          'type' => $this->getLessonType($lesson),
          'chapter_id' => (int) $chapter->id(),
          'chapter_title' => $chapter->label(),
          'chapter_weight' => (int) ($chapter->get('weight')->value ?? 0),
          'lesson' => $lesson,
        ];
      }
    }

    return $items;
  }

  /**
   * Builds curriculum structure with completion and lock state.
   *
   * @return array<int, array<string, mixed>>
   */
  private function getCourseCurriculum(object $course, ?int $uid, ?object $enrollment): array {
    $completed_lesson_ids = $uid ? $this->getCompletedLessonIds($uid, array_column($this->getOrderedCourseLessons($course), 'id')) : [];
    $curriculum = [];
    if (!$course->hasField('field_chapters') || $course->get('field_chapters')->isEmpty()) {
      return $curriculum;
    }

    $chapters = $course->get('field_chapters')->referencedEntities();
    usort($chapters, fn($a, $b) => ($a->get('weight')->value ?? 0) <=> ($b->get('weight')->value ?? 0));

    foreach ($chapters as $chapter) {
      if (!$chapter->get('status')->value) {
        continue;
      }
      $lessons = [];
      if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
        foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
          if (!$lesson->get('status')->value) {
            continue;
          }

          $drip_days = (int) ($lesson->hasField('field_drip_days') ? ($lesson->get('field_drip_days')->value ?? 0) : 0);
          $locked = FALSE;
          if ($enrollment && $drip_days > 0) {
            $enrolled_at = (int) ($enrollment->get('field_enrolled_at')->value ?? 0);
            $locked = ($enrolled_at + ($drip_days * 86400)) > time();
          }

          $lessons[] = [
            'id' => (int) $lesson->id(),
            'title' => $lesson->label(),
            'type' => $this->getLessonType($lesson),
            'is_completed' => in_array((int) $lesson->id(), $completed_lesson_ids, TRUE),
            'is_locked' => $locked,
          ];
        }
      }

      $curriculum[] = [
        'id' => (int) $chapter->id(),
        'title' => $chapter->label(),
        'lessons' => $lessons,
      ];
    }

    return $curriculum;
  }

  /**
   * Returns lesson type with a text fallback.
   */
  private function getLessonType(object $lesson): string {
    return $lesson->hasField('field_type') ? ($lesson->get('field_type')->value ?? 'text') : 'text';
  }

  /**
   * Builds the lesson content payload.
   */
  private function buildLessonContent(object $lesson): array {
    $pdf_url = NULL;
    if ($this->getLessonType($lesson) === 'pdf' && $lesson->hasField('field_attachments') && !$lesson->get('field_attachments')->isEmpty()) {
      $file = $lesson->get('field_attachments')->first()?->entity;
      if ($file) {
        $pdf_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    $scorm_url = NULL;
    if ($this->getLessonType($lesson) === 'scorm') {
      $scorm_uri = 'public://scorm_extracted/' . $lesson->id() . '/index.html';
      if (file_exists($scorm_uri)) {
        $scorm_url = $this->fileUrlGenerator->generateAbsoluteString($scorm_uri);
      }
    }

    return [
      'video_url' => $lesson->hasField('field_video_url') ? ($lesson->get('field_video_url')->value ?: NULL) : NULL,
      'video_file_url' => $this->getFileFieldUrl($lesson, 'field_video_file'),
      'text_body' => $lesson->hasField('field_content') ? ($lesson->get('field_content')->value ?: NULL) : NULL,
      'h5p_embed' => $lesson->hasField('field_h5p_embed') ? ($lesson->get('field_h5p_embed')->value ?: NULL) : NULL,
      'scorm_url' => $scorm_url,
      'pdf_url' => $pdf_url,
      'pptx_url' => $this->getFileFieldUrl($lesson, 'field_pptx_attachment'),
      'zoom_meeting_id' => $lesson->hasField('field_zoom_meeting_id') ? ($lesson->get('field_zoom_meeting_id')->value ?: NULL) : NULL,
      'zoom_schedule' => $lesson->hasField('field_zoom_schedule') && !$lesson->get('field_zoom_schedule')->isEmpty()
        ? date('c', (int) $lesson->get('field_zoom_schedule')->value)
        : NULL,
      'quiz_id' => $lesson->hasField('field_quiz') && !$lesson->get('field_quiz')->isEmpty()
        ? (int) $lesson->get('field_quiz')->target_id
        : NULL,
    ];
  }

  /**
   * Returns attachment metadata for a lesson.
   *
   * @return array<int, array<string, mixed>>
   */
  private function buildLessonAttachments(object $lesson): array {
    $attachments = [];
    if (!$lesson->hasField('field_attachments') || $lesson->get('field_attachments')->isEmpty()) {
      return $attachments;
    }

    foreach ($lesson->get('field_attachments')->referencedEntities() as $file) {
      $attachments[] = [
        'name' => $file->getFilename(),
        'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
        'mime_type' => $file->getMimeType(),
        'size_bytes' => (int) $file->getSize(),
      ];
    }
    return $attachments;
  }

  /**
   * Returns a file URL for the first item in a file field.
   */
  private function getFileFieldUrl(object $entity, string $field_name): ?string {
    if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return NULL;
    }
    $file = $entity->get($field_name)->entity;
    return $file ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()) : NULL;
  }

  /**
   * Counts quiz attempts for a user.
   */
  private function countQuizAttempts(int $uid, int $quiz_id): int {
    return (int) $this->entityTypeManager()->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_quiz', $quiz_id)
      ->accessCheck(FALSE)
      ->count()
      ->execute();
  }

  /**
   * Builds the mobile question structure.
   */
  private function buildQuizQuestion(object $question): array {
    $item = [
      'id' => (int) $question->id(),
      'bundle' => $question->bundle(),
      'text' => $question->label(),
      'details' => $question->hasField('field_body') ? ($question->get('field_body')->value ?? '') : '',
      'points' => (int) ($question->hasField('field_points') ? ($question->get('field_points')->value ?? 0) : 0),
      'explanation' => $question->hasField('field_explanation') ? ($question->get('field_explanation')->value ?? '') : '',
    ];

    if (in_array($question->bundle(), ['single_choice', 'multiple_choice'], TRUE)) {
      $item['choices'] = [];
      foreach ($question->get('field_choices')->referencedEntities() as $answer) {
        $item['choices'][] = [
          'id' => (int) $answer->id(),
          'text' => $answer->label(),
        ];
      }
    }
    elseif ($question->bundle() === 'true_false') {
      $item['choices'] = [
        ['id' => 'true', 'text' => 'True'],
        ['id' => 'false', 'text' => 'False'],
      ];
    }
    elseif ($question->bundle() === 'matching') {
      $left_items = [];
      $right_items = [];
      foreach ($question->get('field_matching_pairs')->referencedEntities() as $pair) {
        $left_items[] = [
          'id' => (int) $pair->id(),
          'text' => $pair->get('field_left_item')->value,
        ];
        $right_items[] = [
          'id' => (int) $pair->id(),
          'text' => $pair->get('field_right_item')->value,
        ];
      }
      $item['left_items'] = $left_items;
      $item['right_items'] = $right_items;
    }
    elseif ($question->bundle() === 'ordering') {
      $items = [];
      foreach ($question->get('field_ordered_items') as $ordered_item) {
        $items[] = ['text' => $ordered_item->value];
      }
      shuffle($items);
      $item['items'] = $items;
    }
    elseif (in_array($question->bundle(), ['fill_blank', 'short_answer', 'essay'], TRUE)) {
      $item['placeholder'] = 'Enter your answer';
      if ($question->hasField('field_max_words')) {
        $item['max_words'] = (int) ($question->get('field_max_words')->value ?? 0);
      }
    }

    return $item;
  }

  /**
   * Normalizes quiz submission payload into strategy input.
   */
  private function normalizeQuizSubmission(object $question, array $answer_data): mixed {
    return match ($question->bundle()) {
      'single_choice' => (int) ($answer_data['selected'] ?? 0),
      'multiple_choice' => array_map('intval', is_array($answer_data['selected'] ?? NULL) ? $answer_data['selected'] : []),
      'true_false' => match (strtolower((string) ($answer_data['selected'] ?? ''))) {
        'true', '1' => TRUE,
        'false', '0' => FALSE,
        default => NULL,
      },
      'ordering' => is_array($answer_data['order'] ?? NULL)
        ? array_map(static fn($item): string => is_array($item) ? (string) ($item['text'] ?? '') : (string) $item, $answer_data['order'])
        : [],
      'matching' => $this->normalizeMatchingSubmission($question, $answer_data['pairs'] ?? []),
      'fill_blank', 'short_answer', 'essay' => (string) ($answer_data['text'] ?? ''),
      default => (string) ($answer_data['text'] ?? ''),
    };
  }

  /**
   * Normalizes matching answers into the strategy's expected ordered array.
   */
  private function normalizeMatchingSubmission(object $question, mixed $pairs): array {
    $submitted_pairs = is_array($pairs) ? $pairs : [];
    $normalized = [];
    $pair_entities = $question->get('field_matching_pairs')->referencedEntities();
    foreach ($pair_entities as $index => $pair) {
      $left = (string) $pair->get('field_left_item')->value;
      $normalized[$index] = $submitted_pairs[(string) $pair->id()]
        ?? $submitted_pairs[$left]
        ?? $submitted_pairs[$index]
        ?? '';
    }
    return $normalized;
  }

  /**
   * Returns normalized result status.
   */
  private function getQuizResultStatus(object $result): string {
    $term = $result->get('field_result')->entity;
    return match (strtolower((string) ($term?->label() ?? 'failed'))) {
      'passed' => 'passed',
      'pending' => 'pending',
      default => 'failed',
    };
  }

  /**
   * Returns the attempt number of a specific result.
   */
  private function getAttemptNumber(int $uid, int $result_id, int $quiz_id): int {
    $ids = $this->entityTypeManager()->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_quiz', $quiz_id)
      ->sort('created', 'ASC')
      ->accessCheck(FALSE)
      ->execute();

    $position = 1;
    foreach (array_values($ids) as $index => $id) {
      if ((int) $id === $result_id) {
        $position = $index + 1;
        break;
      }
    }

    return $position;
  }

  /**
   * Returns result duration in seconds.
   */
  private function getQuizResultDurationSeconds(object $result): int {
    $start = (int) ($result->hasField('field_start_time') ? ($result->get('field_start_time')->value ?? 0) : 0);
    $end = (int) ($result->hasField('field_end_time') ? ($result->get('field_end_time')->value ?? 0) : 0);
    return max(0, $end - $start);
  }

  /**
   * Builds per-question feedback for a quiz result.
   */
  private function buildQuestionFeedback(object $question, object $response): array {
    return [
      'question_id' => (int) $question->id(),
      'question_text' => $question->label(),
      'bundle' => $question->bundle(),
      'your_answer' => $this->formatUserAnswer($question, $response),
      'correct_answer' => $this->formatCorrectAnswer($question),
      'is_correct' => (bool) ($response->get('field_correct')->value ?? FALSE),
      'points_earned' => (float) ($response->get('field_score')->value ?? 0),
      'points_possible' => (float) ($question->hasField('field_points') ? ($question->get('field_points')->value ?? 0) : 0),
      'teacher_feedback' => $response->hasField('field_teacher_feedback') ? ($response->get('field_teacher_feedback')->value ?? '') : '',
    ];
  }

  /**
   * Formats the user's answer for feedback.
   */
  private function formatUserAnswer(object $question, object $response): mixed {
    if (in_array($question->bundle(), ['single_choice', 'multiple_choice'], TRUE)) {
      $selected = [];
      foreach ($response->get('field_answers')->referencedEntities() as $answer) {
        $selected[] = $answer->label();
      }
      return $question->bundle() === 'single_choice' ? ($selected[0] ?? '') : $selected;
    }

    return $response->hasField('field_text_answer') ? ($response->get('field_text_answer')->value ?? '') : '';
  }

  /**
   * Formats the correct answer for feedback.
   */
  private function formatCorrectAnswer(object $question): mixed {
    return match ($question->bundle()) {
      'single_choice', 'multiple_choice' => array_values(array_map(
        static fn($answer) => $answer->label(),
        array_filter($question->get('field_choices')->referencedEntities(), static fn($answer): bool => (bool) ($answer->get('field_correct')->value ?? FALSE)),
      )),
      'true_false' => (bool) ($question->get('field_correct_answer')->value ?? FALSE) ? 'True' : 'False',
      'matching' => array_map(static fn($pair): array => [
        'left' => $pair->get('field_left_item')->value,
        'right' => $pair->get('field_right_item')->value,
      ], $question->get('field_matching_pairs')->referencedEntities()),
      'ordering' => array_map(static fn($item): string => $item->value, iterator_to_array($question->get('field_ordered_items'))),
      'fill_blank', 'short_answer' => array_map(static fn($item): string => $item->value, iterator_to_array($question->get('field_correct_answers'))),
      default => '',
    };
  }

  /**
   * Finds the course that contains a given quiz through its lesson relation.
   */
  private function findCourseForQuiz(int $quiz_id): ?object {
    $lesson_ids = $this->entityTypeManager()->getStorage('lms_lesson')
      ->getQuery()
      ->condition('field_quiz', $quiz_id)
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    if (!$lesson_ids) {
      return NULL;
    }

    $lesson_id = (int) reset($lesson_ids);
    $chapter_ids = $this->entityTypeManager()->getStorage('lms_chapter')
      ->getQuery()
      ->condition('field_lessons', $lesson_id)
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    if (!$chapter_ids) {
      return NULL;
    }

    $chapter_id = (int) reset($chapter_ids);
    $course_ids = $this->entityTypeManager()->getStorage('lms_course')
      ->getQuery()
      ->condition('field_chapters', $chapter_id)
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    return $course_ids
      ? $this->entityTypeManager()->getStorage('lms_course')->load((int) reset($course_ids))
      : NULL;
  }

}