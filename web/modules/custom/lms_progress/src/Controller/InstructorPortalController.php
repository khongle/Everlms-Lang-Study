<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_progress\Form\InstructorStudentNotificationForm;
use Drupal\lms_progress\Entity\UserCourse;
use Drupal\lms_progress\Service\InstructorWorkspaceContentBuilder;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Instructor workspace pages.
 */
final class InstructorPortalController extends ControllerBase {

  public function __construct(
    private readonly DateFormatterInterface $lmsDateFormatter,
    private readonly EntityTypeManagerInterface $lmsEntityTypeManager,
    private readonly AccountProxyInterface $currentAccount,
    private readonly InstructorWorkspaceContentBuilder $instructorWorkspaceContentBuilder,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('lms_progress.instructor_workspace_content_builder'),
    );
  }

  /**
   * Instructor dashboard.
   */
  public function dashboard(): array {
    $dataset = $this->buildWorkspaceDataset();
    $student_ids = [];
    $active_student_ids = [];
    $completed_enrollments = 0;
    $progress_total = 0.0;

    foreach ($dataset['roster'] as $row) {
      $student_ids[$row['student_id']] = TRUE;
      if ($row['status'] === 'completed') {
        $completed_enrollments++;
      }
      else {
        $active_student_ids[$row['student_id']] = TRUE;
      }
      $progress_total += $row['progress'];
    }

    $enrollment_count = count($dataset['roster']);
    $stats = [
      'courses' => count($dataset['course_summaries']),
      'students' => count($student_ids),
      'active_students' => count($active_student_ids),
      'completion_rate' => $enrollment_count > 0 ? round(($completed_enrollments / $enrollment_count) * 100, 1) : 0,
      'average_progress' => $enrollment_count > 0 ? round($progress_total / $enrollment_count, 1) : 0,
    ];
    $content = $this->instructorWorkspaceContentBuilder->buildDashboardContent($stats, $enrollment_count);

    return [
      '#theme' => 'lms_instructor_dashboard',
      '#stats' => $stats,
      '#courses' => array_slice($dataset['course_summaries'], 0, 6),
      '#hero' => $content['hero'],
      '#spotlight' => $content['spotlight'],
      '#queue' => $content['queue'],
      '#scenario' => $content['scenario'],
      '#has_learner_portal' => in_array('student', $this->currentAccount->getRoles(), TRUE),
      '#courses_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#students_url' => Url::fromRoute('lms_progress.instructor_students')->toString(),
      '#learner_dashboard_url' => Url::fromRoute('lms_progress.dashboard')->toString(),
      '#profile_url' => Url::fromRoute('lms_course.profile')->toString(),
      '#cache' => $this->buildCacheMetadata(),
    ];
  }

  /**
   * Courses taught by the current instructor.
   */
  public function courses(): array {
    $dataset = $this->buildWorkspaceDataset();
    $student_ids = [];
    $published = 0;
    $draft = 0;

    foreach ($dataset['course_summaries'] as $course) {
      if ($course['state'] === 'published') {
        $published++;
      }
      else {
        $draft++;
      }
    }

    foreach ($dataset['roster'] as $row) {
      $student_ids[$row['student_id']] = TRUE;
    }

    return [
      '#theme' => 'lms_instructor_courses',
      '#courses' => $dataset['course_summaries'],
      '#summary' => [
        'total' => count($dataset['course_summaries']),
        'published' => $published,
        'draft' => $draft,
        'students' => count($student_ids),
      ],
      '#dashboard_url' => Url::fromRoute('lms_progress.instructor_dashboard')->toString(),
      '#students_url' => Url::fromRoute('lms_progress.instructor_students')->toString(),
      '#cache' => $this->buildCacheMetadata(),
    ];
  }

  /**
   * Student roster for all taught courses.
   */
  public function students(): array {
    $dataset = $this->buildWorkspaceDataset();
    $student_ids = [];
    $student_suggestions = [];
    $course_suggestions = [];
    $in_progress = 0;
    $completed = 0;

    foreach ($dataset['roster'] as $row) {
      $student_ids[$row['student_id']] = TRUE;
      $student_name = trim((string) ($row['student_name'] ?? ''));
      $student_email = trim((string) ($row['email'] ?? ''));
      $course_title = trim((string) ($row['course_title'] ?? ''));

      if ($student_name !== '') {
        $student_suggestions[$student_name] = $student_name;
      }
      if ($student_email !== '') {
        $student_suggestions[$student_email] = $student_email;
      }
      if ($course_title !== '') {
        $course_suggestions[$course_title] = $course_title;
      }

      if ($row['status'] === 'completed') {
        $completed++;
      }
      else {
        $in_progress++;
      }
    }

    natcasesort($student_suggestions);
    natcasesort($course_suggestions);

    return [
      '#theme' => 'lms_instructor_students',
      '#students' => $dataset['roster'],
      '#student_suggestions' => array_values($student_suggestions),
      '#course_suggestions' => array_values($course_suggestions),
      '#page_size' => 20,
      '#summary' => [
        'students' => count($student_ids),
        'enrollments' => count($dataset['roster']),
        'in_progress' => $in_progress,
        'completed' => $completed,
      ],
      '#dashboard_url' => Url::fromRoute('lms_progress.instructor_dashboard')->toString(),
      '#courses_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#cache' => $this->buildCacheMetadata(),
    ];
  }

  /**
   * Learner detail page scoped to the instructor's assigned courses.
   */
  public function studentDetail(UserInterface $user): array {
    $dataset = $this->buildWorkspaceDataset();
    $courses = $dataset['courses'];
    $course_ids = array_map('intval', array_keys($courses));
    $student_id = (int) $user->id();
    $enrollments = $this->loadStudentEnrollments($student_id, $course_ids);

    if ($enrollments === []) {
      throw new AccessDeniedHttpException();
    }

    $course_rows = $this->buildStudentCourseRows($enrollments, $courses);
    $quiz_course_map = $this->buildCourseQuizMap($courses);
    $quiz_results = $this->loadStudentQuizResults($student_id, $quiz_course_map);
    $certificates = $this->loadStudentCertificates($student_id, $course_ids);
    $notifications = $this->loadStudentNotifications($student_id);

    $completed_courses = 0;
    $progress_total = 0.0;
    foreach ($course_rows as $row) {
      if ($row['status'] === 'completed') {
        $completed_courses++;
      }
      $progress_total += $row['progress'];
    }

    $average_quiz_score = 0.0;
    if ($quiz_results !== []) {
      $average_quiz_score = round(array_sum(array_column($quiz_results, 'score')) / count($quiz_results), 1);
    }

    $joined_at = $user->hasField('created') ? $this->formatTimestamp($user->get('created')->value ?? NULL) : '';
    $last_access = $user->hasField('access') ? $this->formatTimestamp($user->get('access')->value ?? NULL) : '';
    $notification_form = \Drupal::moduleHandler()->moduleExists('lms_notification')
      ? \Drupal::formBuilder()->getForm(InstructorStudentNotificationForm::class, $user)
      : [];

    return [
      '#theme' => 'lms_instructor_student_detail',
      '#student' => [
        'id' => $student_id,
        'name' => $user->getDisplayName(),
        'email' => (string) $user->getEmail(),
        'status' => $user->isBlocked() ? 'blocked' : 'active',
        'joined_at' => $joined_at,
        'last_access' => $last_access,
      ],
      '#summary' => [
        'courses' => count($course_rows),
        'completed_courses' => $completed_courses,
        'average_progress' => $course_rows !== [] ? round($progress_total / count($course_rows), 1) : 0,
        'quiz_attempts' => count($quiz_results),
        'average_quiz_score' => $average_quiz_score,
        'certificates' => count($certificates),
      ],
      '#courses' => $course_rows,
      '#quiz_results' => $quiz_results,
      '#certificates' => $certificates,
      '#notifications' => $notifications,
      '#notification_form' => $notification_form,
      '#dashboard_url' => Url::fromRoute('lms_progress.instructor_dashboard')->toString(),
      '#students_url' => Url::fromRoute('lms_progress.instructor_students')->toString(),
      '#courses_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#mail_url' => $user->getEmail() !== '' ? 'mailto:' . $user->getEmail() : '',
      '#cache' => [
        'contexts' => ['user', 'user.roles'],
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'lms_progress/instructor_student_detail',
        ],
      ],
    ];
  }

  /**
   * Instructor-facing course edit form wrapper.
   */
  public function courseEditForm(Course $lms_course): array {
    $roles = $this->currentAccount->getRoles();
    $uid = (int) $this->currentAccount->id();
    $is_privileged = in_array('administrator', $roles, TRUE) || in_array('manager', $roles, TRUE);
    if (!$is_privileged && !$this->isInstructorScopedCourse($lms_course, $uid)) {
      throw new AccessDeniedHttpException();
    }

    $form_mode = $this->resolveCourseFormMode();
    $form = \Drupal::service('entity.form_builder')->getForm($lms_course, $form_mode);

    return [
      '#theme' => 'lms_instructor_course_form',
      '#title' => $this->t('Edit Course: @title', ['@title' => $lms_course->label()]),
      '#subtitle' => $this->t('You are editing this course inside the instructor workspace so the teaching flow stays in the same persona. Any save action keeps the existing course access rules.'),
      '#form' => $form,
      '#back_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#public_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $lms_course->id()])->toString(),
      '#cache' => [
        'contexts' => ['user', 'user.roles'],
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Learner activity inside one instructor-scoped course.
   */
  public function studentCourseDetail(UserInterface $user, Course $lms_course): array {
    $this->assertInstructorCourseAccess($lms_course);

    $student_id = (int) $user->id();
    $course_id = (int) $lms_course->id();
    $enrollments = $this->loadStudentEnrollments($student_id, [$course_id]);
    $enrollment = reset($enrollments);

    if (!$enrollment instanceof UserCourse) {
      throw new AccessDeniedHttpException();
    }

    $lesson_map = $this->buildCourseLessonMap($lms_course);
    $lesson_activity = $this->buildStudentLessonTimeline($student_id, $lms_course, $lesson_map);
    $quiz_map = $this->buildCourseQuizMapForSingleCourse($lms_course, $lesson_map);
    $comments = $this->loadStudentLessonComments($student_id, $lesson_map);
    $quiz_attempts = $this->loadStudentQuizAttemptDetails($student_id, $quiz_map);

    $feedback_count = 0;
    $pending_reviews = 0;
    foreach ($quiz_attempts as $attempt) {
      $feedback_count += (int) ($attempt['feedback_count'] ?? 0);
      $pending_reviews += (int) ($attempt['pending_count'] ?? 0);
    }

    $joined_at = $user->hasField('created') ? $this->formatTimestamp($user->get('created')->value ?? NULL) : '';
    $last_access = $user->hasField('access') ? $this->formatTimestamp($user->get('access')->value ?? NULL) : '';

    return [
      '#theme' => 'lms_instructor_student_course_detail',
      '#student' => [
        'id' => $student_id,
        'name' => $user->getDisplayName(),
        'email' => (string) $user->getEmail(),
        'status' => $user->isBlocked() ? 'blocked' : 'active',
        'joined_at' => $joined_at,
        'last_access' => $last_access,
      ],
      '#course' => [
        'id' => $course_id,
        'title' => $lms_course->label(),
        'category' => $this->getCourseCategoryName($lms_course),
        'state' => $this->isPublishedCourse($lms_course) ? 'published' : 'draft',
        'thumbnail_url' => $this->getCourseThumbnailUrl($lms_course),
        'public_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString(),
        'status' => $this->normalizeEnrollmentStatus($enrollment),
        'progress' => $this->getEnrollmentProgress($enrollment),
        'enrolled_at' => $this->formatTimestamp($enrollment->get('field_enrolled_at')->value ?? NULL),
        'completed_at' => $this->formatTimestamp($enrollment->get('field_completed_at')->value ?? NULL),
        'current_lesson' => (string) ($lesson_activity['summary']['current_lesson'] ?? ''),
        'last_activity' => (string) ($lesson_activity['summary']['last_activity'] ?? ''),
        'completed_lessons' => (int) ($lesson_activity['summary']['completed_lessons'] ?? 0),
        'total_lessons' => (int) ($lesson_activity['summary']['total_lessons'] ?? 0),
      ],
      '#summary' => [
        [
          'label' => (string) $this->t('Progress'),
          'value' => number_format($this->getEnrollmentProgress($enrollment), 1) . '%',
        ],
        [
          'label' => (string) $this->t('Completed lessons'),
          'value' => (string) (($lesson_activity['summary']['completed_lessons'] ?? 0) . '/' . ($lesson_activity['summary']['total_lessons'] ?? 0)),
        ],
        [
          'label' => (string) $this->t('Comments'),
          'value' => (string) count($comments),
        ],
        [
          'label' => (string) $this->t('Quiz attempts'),
          'value' => (string) count($quiz_attempts),
        ],
        [
          'label' => (string) $this->t('Teacher feedback'),
          'value' => (string) $feedback_count,
        ],
        [
          'label' => (string) $this->t('Pending review'),
          'value' => (string) $pending_reviews,
        ],
      ],
      '#lesson_timeline' => $lesson_activity['rows'],
      '#comments' => $comments,
      '#quiz_attempts' => $quiz_attempts,
      '#students_url' => Url::fromRoute('lms_progress.instructor_students')->toString(),
      '#student_detail_url' => Url::fromRoute('lms_progress.instructor_student_detail', ['user' => $student_id])->toString(),
      '#courses_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#cache' => $this->buildCacheMetadata(),
    ];
  }

  /**
   * Instructor-facing course analytics detail page.
   */
  public function courseStats(Course $lms_course): array {
    $this->assertInstructorCourseAccess($lms_course);
    $payload = $this->buildCourseStatsPayload($lms_course);

    return [
      '#theme' => 'lms_instructor_course_stats',
      '#course' => $payload['course'],
      '#summary' => $payload['summary'],
      '#learners' => $payload['learners'],
      '#quiz_results' => $payload['quiz_results'],
      '#comments' => $payload['comments'],
      '#questions' => $payload['questions'],
      '#dashboard_url' => Url::fromRoute('lms_progress.instructor_dashboard')->toString(),
      '#courses_url' => Url::fromRoute('lms_progress.instructor_courses')->toString(),
      '#cache' => $this->buildCacheMetadata(),
      '#attached' => [
        'library' => [
          'lms_progress/instructor_course_stats',
        ],
      ],
    ];
  }

  /**
   * JSON summary endpoint for instructor hover cards.
   */
  public function courseStatsSummary(Course $lms_course): JsonResponse {
    $this->assertInstructorCourseAccess($lms_course);
    $payload = $this->buildCourseStatsPayload($lms_course);

    return new JsonResponse([
      'course_id' => $payload['course']['id'],
      'title' => $payload['course']['title'],
      'category' => $payload['course']['category'],
      'state' => $payload['course']['state'],
      'stats_url' => $payload['course']['stats_url'],
      'public_url' => $payload['course']['public_url'],
      'counts' => $payload['counts'],
    ]);
  }

  /**
   * Builds the instructor-scoped dataset once per page render.
   *
   * Keeps legacy owner-only courses visible only when no instructor assignment
   * exists yet, so current data does not disappear during the transition.
   */
  private function buildWorkspaceDataset(): array {
    $uid = (int) $this->currentAccount->id();
    $courses = $this->loadTaughtCourses($uid);
    $course_ids = array_map('intval', array_keys($courses));
    $enrollments = $this->loadEnrollments($course_ids);
    $enrollments_by_course = $this->groupEnrollmentsByCourse($enrollments);

    return [
      'courses' => $courses,
      'course_summaries' => $this->buildCourseSummaries($courses, $enrollments_by_course),
      'roster' => $this->buildRoster($enrollments, $courses),
    ];
  }

  /**
   * Ensures the current user can inspect the instructor-scoped course.
   */
  private function assertInstructorCourseAccess(Course $course): void {
    $roles = $this->currentAccount->getRoles();
    $uid = (int) $this->currentAccount->id();
    $is_privileged = in_array('administrator', $roles, TRUE) || in_array('manager', $roles, TRUE);

    if (!$is_privileged && !$this->isInstructorScopedCourse($course, $uid)) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Builds the full analytics payload for one instructor-scoped course.
   */
  private function buildCourseStatsPayload(Course $course): array {
    $course_id = (int) $course->id();
    $enrollments = $this->loadEnrollments([$course_id]);
    $learners = $this->buildCourseLearnerRows($enrollments, $course);
    $lesson_map = $this->buildCourseLessonMap($course);
    $quiz_map = $this->buildCourseQuizMapForSingleCourse($course, $lesson_map);
    $student_ids = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['student_id'], $learners)));
    $quiz_results = $this->loadCourseQuizResults($student_ids, $quiz_map);
    $comments = $this->loadCourseCommentRows($lesson_map);
    $questions = $this->loadCourseQuestionRows($quiz_map);
    $counts = $this->buildCourseStatsCounts($course, $enrollments, $learners, $quiz_results, $comments, $questions);

    return [
      'course' => [
        'id' => $course_id,
        'title' => $course->label(),
        'category' => $this->getCourseCategoryName($course),
        'state' => $this->isPublishedCourse($course) ? 'published' : 'draft',
        'thumbnail_url' => $this->getCourseThumbnailUrl($course),
        'public_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString(),
        'stats_url' => Url::fromRoute('lms_progress.instructor_course_stats', ['lms_course' => $course_id])->toString(),
        'edit_url' => $course->access('update', $this->currentAccount)
          ? Url::fromRoute('lms_progress.instructor_course_edit', ['lms_course' => $course_id], ['query' => ['destination' => '/workspace/instructor/courses']])->toString()
          : '',
      ],
      'summary' => [
        [
          'label' => (string) $this->t('Enrollments'),
          'value' => (string) $counts['enrollments'],
        ],
        [
          'label' => (string) $this->t('Active learners'),
          'value' => (string) $counts['active_students'],
        ],
        [
          'label' => (string) $this->t('Quiz attempts'),
          'value' => (string) $counts['quiz_attempts'],
        ],
        [
          'label' => (string) $this->t('Lesson comments'),
          'value' => (string) $counts['comment_count'],
        ],
        [
          'label' => (string) $this->t('Question bank'),
          'value' => (string) $counts['question_count'],
        ],
        [
          'label' => (string) $this->t('Completion rate'),
          'value' => $counts['completion_rate'] . '%',
        ],
      ],
      'learners' => $learners,
      'quiz_results' => $quiz_results,
      'comments' => $comments,
      'questions' => $questions,
      'counts' => $counts,
    ];
  }

  /**
   * Loads courses assigned to the current instructor.
   *
   * Field assignment is the primary source of truth. Owner fallback is kept
   * only for legacy courses without any instructor references yet.
   *
   * @return array<int, \Drupal\lms_course\Entity\Course>
   *   Courses keyed by course ID.
   */
  private function loadTaughtCourses(int $uid): array {
    $storage = $this->lmsEntityTypeManager->getStorage('lms_course');

    $assigned_ids = $storage->getQuery()
      ->condition('field_instructor', $uid)
      ->sort('id', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $owned_ids = $storage->getQuery()
      ->condition('uid', $uid)
      ->sort('id', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $candidate_ids = array_values(array_unique(array_merge(array_values($assigned_ids), array_values($owned_ids))));
    if ($candidate_ids === []) {
      return [];
    }

    $loaded_courses = $storage->loadMultiple($candidate_ids);
    $courses = [];

    foreach ($candidate_ids as $course_id) {
      $course = $loaded_courses[$course_id] ?? NULL;
      if (!$course instanceof Course) {
        continue;
      }

      if ($this->isInstructorScopedCourse($course, $uid)) {
        $courses[$course_id] = $course;
      }
    }

    return $courses;
  }

  /**
   * Checks whether the course belongs in the instructor workspace.
   */
  private function isInstructorScopedCourse(Course $course, int $uid): bool {
    if ($course->hasField('field_instructor') && !$course->get('field_instructor')->isEmpty()) {
      foreach ($course->get('field_instructor')->getValue() as $item) {
        if ((int) ($item['target_id'] ?? 0) === $uid) {
          return TRUE;
        }
      }

      return FALSE;
    }

    return $course->getOwnerId() === $uid;
  }

  /**
   * Loads enrollments for the given course IDs.
   *
   * @return array<int, \Drupal\lms_progress\Entity\UserCourse>
   *   Enrollment entities keyed by entity ID.
   */
  private function loadEnrollments(array $course_ids): array {
    if ($course_ids === []) {
      return [];
    }

    $storage = $this->lmsEntityTypeManager->getStorage('lms_user_course');
    $enrollment_ids = $storage->getQuery()
      ->condition('field_course', $course_ids, 'IN')
      ->sort('field_enrolled_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    return $enrollment_ids ? $storage->loadMultiple($enrollment_ids) : [];
  }

  /**
   * Groups enrollments by course ID.
   *
   * @param array<int, \Drupal\lms_progress\Entity\UserCourse> $enrollments
   *   Enrollment entities keyed by entity ID.
   *
   * @return array<int, array<int, \Drupal\lms_progress\Entity\UserCourse>>
   *   Enrollments keyed by course ID.
   */
  private function groupEnrollmentsByCourse(array $enrollments): array {
    $grouped = [];

    foreach ($enrollments as $enrollment) {
      if (!$enrollment instanceof UserCourse || $enrollment->get('field_course')->isEmpty()) {
        continue;
      }

      $course_id = (int) $enrollment->get('field_course')->target_id;
      $grouped[$course_id][] = $enrollment;
    }

    return $grouped;
  }

  /**
   * Builds course cards for the instructor workspace.
   *
   * @param array<int, \Drupal\lms_course\Entity\Course> $courses
   *   Courses keyed by course ID.
   * @param array<int, array<int, \Drupal\lms_progress\Entity\UserCourse>> $enrollments_by_course
   *   Enrollments keyed by course ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready course summaries.
   */
  private function buildCourseSummaries(array $courses, array $enrollments_by_course): array {
    $summaries = [];

    foreach ($courses as $course_id => $course) {
      $course_enrollments = $enrollments_by_course[$course_id] ?? [];
      $total_enrollments = count($course_enrollments);
      $completed = 0;
      $active = 0;
      $progress_total = 0.0;

      foreach ($course_enrollments as $enrollment) {
        $status = $this->normalizeEnrollmentStatus($enrollment);
        $progress = $this->getEnrollmentProgress($enrollment);
        $progress_total += $progress;

        if ($status === 'completed') {
          $completed++;
        }
        else {
          $active++;
        }
      }

      $summaries[] = [
        'id' => $course_id,
        'title' => $course->label(),
        'short_description' => $this->getCourseCardDescription($course),
        'category' => $this->getCourseCategoryName($course),
        'thumbnail_url' => $this->getCourseThumbnailUrl($course),
        'state' => $this->isPublishedCourse($course) ? 'published' : 'draft',
        'enrollments' => $total_enrollments,
        'active_students' => $active,
        'completed_students' => $completed,
        'completion_rate' => $total_enrollments > 0 ? round(($completed / $total_enrollments) * 100, 1) : 0,
        'average_progress' => $total_enrollments > 0 ? round($progress_total / $total_enrollments, 1) : 0,
        'public_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString(),
        'stats_url' => Url::fromRoute('lms_progress.instructor_course_stats', ['lms_course' => $course_id])->toString(),
        'stats_summary_url' => Url::fromRoute('lms_progress.instructor_course_stats_summary', ['lms_course' => $course_id])->toString(),
        'edit_url' => $course->access('update', $this->currentAccount)
          ? Url::fromRoute('lms_progress.instructor_course_edit', ['lms_course' => $course_id], ['query' => ['destination' => '/workspace/instructor/courses']])->toString()
          : '',
      ];
    }

    return $summaries;
  }

  /**
   * Builds the student roster across all taught courses.
   *
   * @param array<int, \Drupal\lms_progress\Entity\UserCourse> $enrollments
   *   Enrollment entities keyed by entity ID.
   * @param array<int, \Drupal\lms_course\Entity\Course> $courses
   *   Courses keyed by course ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready roster rows.
   */
  private function buildRoster(array $enrollments, array $courses): array {
    $user_ids = [];
    foreach ($enrollments as $enrollment) {
      if ($enrollment instanceof UserCourse) {
        $user_ids[] = (int) $enrollment->getOwnerId();
      }
    }

    $users = $user_ids === []
      ? []
      : $this->lmsEntityTypeManager->getStorage('user')->loadMultiple(array_values(array_unique($user_ids)));

    $rows = [];
    foreach ($enrollments as $enrollment) {
      if (!$enrollment instanceof UserCourse || $enrollment->get('field_course')->isEmpty()) {
        continue;
      }

      $course_id = (int) $enrollment->get('field_course')->target_id;
      $course = $courses[$course_id] ?? NULL;
      if (!$course instanceof Course) {
        continue;
      }

      $student_id = (int) $enrollment->getOwnerId();
      $user = $users[$student_id] ?? NULL;

      $rows[] = [
        'student_id' => $student_id,
        'student_name' => $user instanceof UserInterface ? $user->getDisplayName() : $this->t('Unknown learner'),
        'email' => $user instanceof UserInterface ? (string) $user->getEmail() : '',
        'detail_url' => Url::fromRoute('lms_progress.instructor_student_detail', ['user' => $student_id])->toString(),
        'course_title' => $course->label(),
        'course_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString(),
        'progress' => $this->getEnrollmentProgress($enrollment),
        'status' => $this->normalizeEnrollmentStatus($enrollment),
        'enrolled_at' => $this->formatTimestamp($enrollment->get('field_enrolled_at')->value ?? NULL),
        'completed_at' => $this->formatTimestamp($enrollment->get('field_completed_at')->value ?? NULL),
      ];
    }

    usort($rows, static function (array $left, array $right): int {
      $course_compare = strcmp($left['course_title'], $right['course_title']);
      if ($course_compare !== 0) {
        return $course_compare;
      }

      return strcmp($left['student_name'], $right['student_name']);
    });

    return $rows;
  }

  /**
   * Builds learner rows for one course stats page.
   *
   * @param array<int, \Drupal\lms_progress\Entity\UserCourse> $enrollments
   *   Enrollment entities keyed by entity ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready learner rows.
   */
  private function buildCourseLearnerRows(array $enrollments, Course $course): array {
    $user_ids = [];
    foreach ($enrollments as $enrollment) {
      if ($enrollment instanceof UserCourse) {
        $user_ids[] = (int) $enrollment->getOwnerId();
      }
    }

    $users = $user_ids === []
      ? []
      : $this->lmsEntityTypeManager->getStorage('user')->loadMultiple(array_values(array_unique($user_ids)));

    $rows = [];
    foreach ($enrollments as $enrollment) {
      if (!$enrollment instanceof UserCourse) {
        continue;
      }

      $student_id = (int) $enrollment->getOwnerId();
      $user = $users[$student_id] ?? NULL;
      $rows[] = [
        'student_id' => $student_id,
        'student_name' => $user instanceof UserInterface ? $user->getDisplayName() : $this->t('Unknown learner'),
        'email' => $user instanceof UserInterface ? (string) $user->getEmail() : '',
        'detail_url' => Url::fromRoute('lms_progress.instructor_student_detail', ['user' => $student_id])->toString(),
        'progress' => $this->getEnrollmentProgress($enrollment),
        'status' => $this->normalizeEnrollmentStatus($enrollment),
        'enrolled_at' => $this->formatTimestamp($enrollment->get('field_enrolled_at')->value ?? NULL),
        'completed_at' => $this->formatTimestamp($enrollment->get('field_completed_at')->value ?? NULL),
        'course_title' => $course->label(),
      ];
    }

    usort($rows, static function (array $left, array $right): int {
      return strcmp($left['student_name'], $right['student_name']);
    });

    return $rows;
  }

  /**
   * Builds the lesson map for one course.
   *
   * @return array<int, array<string, mixed>>
   *   Lesson metadata keyed by lesson ID.
   */
  private function buildCourseLessonMap(Course $course): array {
    if (!$course->hasField('field_chapters') || $course->get('field_chapters')->isEmpty()) {
      return [];
    }

    $chapter_ids = [];
    foreach ($course->get('field_chapters')->getValue() as $item) {
      $chapter_id = (int) ($item['target_id'] ?? 0);
      if ($chapter_id > 0) {
        $chapter_ids[] = $chapter_id;
      }
    }

    if ($chapter_ids === []) {
      return [];
    }

    $chapters = $this->lmsEntityTypeManager->getStorage('lms_chapter')->loadMultiple($chapter_ids);
    $lesson_map = [];

    foreach ($chapter_ids as $chapter_id) {
      $chapter = $chapters[$chapter_id] ?? NULL;
      if (!$chapter || !$chapter->hasField('field_lessons') || $chapter->get('field_lessons')->isEmpty()) {
        continue;
      }

      foreach ($chapter->get('field_lessons')->getValue() as $item) {
        $lesson_id = (int) ($item['target_id'] ?? 0);
        if ($lesson_id <= 0) {
          continue;
        }

        $lesson_map[$lesson_id] = [
          'lesson_id' => $lesson_id,
          'lesson_title' => '',
          'chapter_title' => $chapter->label(),
          'course_title' => $course->label(),
          'course_id' => (int) $course->id(),
          'quiz_id' => 0,
        ];
      }
    }

    if ($lesson_map === []) {
      return [];
    }

    $lessons = $this->lmsEntityTypeManager->getStorage('lms_lesson')->loadMultiple(array_keys($lesson_map));
    foreach ($lessons as $lesson_id => $lesson) {
      if (!isset($lesson_map[(int) $lesson_id])) {
        continue;
      }

      $lesson_map[(int) $lesson_id]['lesson_title'] = $lesson->label();
      if ($lesson->hasField('field_quiz') && !$lesson->get('field_quiz')->isEmpty()) {
        $lesson_map[(int) $lesson_id]['quiz_id'] = (int) $lesson->get('field_quiz')->target_id;
      }
    }

    return $lesson_map;
  }

  /**
   * Builds the quiz map for a single course.
   *
   * @param array<int, array<string, mixed>> $lesson_map
   *   Lesson metadata keyed by lesson ID.
   *
   * @return array<int, array<string, mixed>>
   *   Quiz metadata keyed by quiz ID.
   */
  private function buildCourseQuizMapForSingleCourse(Course $course, array $lesson_map): array {
    $quiz_map = [];

    foreach ($lesson_map as $lesson_meta) {
      $quiz_id = (int) ($lesson_meta['quiz_id'] ?? 0);
      if ($quiz_id <= 0 || isset($quiz_map[$quiz_id])) {
        continue;
      }

      $quiz_map[$quiz_id] = [
        'course_id' => (int) $course->id(),
        'course_title' => $course->label(),
        'lesson_title' => (string) ($lesson_meta['lesson_title'] ?? ''),
      ];
    }

    return $quiz_map;
  }

  /**
   * Loads quiz attempts across the current course.
   *
   * @param array<int> $student_ids
   *   Enrolled learner IDs.
   * @param array<int, array<string, mixed>> $quiz_course_map
   *   Quiz metadata keyed by quiz ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready quiz rows.
   */
  private function loadCourseQuizResults(array $student_ids, array $quiz_course_map): array {
    if ($student_ids === [] || $quiz_course_map === [] || !$this->lmsEntityTypeManager->hasDefinition('lms_quiz_result')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $student_ids, 'IN')
      ->condition('field_quiz', array_keys($quiz_course_map), 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $results = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')->loadMultiple($ids);
    $users = $this->lmsEntityTypeManager->getStorage('user')->loadMultiple($student_ids);
    $items = [];

    foreach ($results as $result) {
      if ($result->get('field_quiz')->isEmpty()) {
        continue;
      }

      $quiz_id = (int) $result->get('field_quiz')->target_id;
      $course_meta = $quiz_course_map[$quiz_id] ?? NULL;
      if ($course_meta === NULL) {
        continue;
      }

      $student_id = (int) ($result->getOwnerId() ?? 0);
      $user = $users[$student_id] ?? NULL;
      $quiz = $result->get('field_quiz')->entity;
      $score = (float) ($result->get('field_score')->value ?? 0);
      $threshold = $quiz && $quiz->hasField('field_threshold') && !$quiz->get('field_threshold')->isEmpty()
        ? (float) $quiz->get('field_threshold')->value
        : 80.0;

      $state = 'retry';
      if ($score >= $threshold) {
        $state = 'passed';
      }
      elseif ($score >= max($threshold - 15, 50)) {
        $state = 'review';
      }

      $items[] = [
        'student_id' => $student_id,
        'student_name' => $user instanceof UserInterface ? $user->getDisplayName() : $this->t('Unknown learner'),
        'student_email' => $user instanceof UserInterface ? (string) $user->getEmail() : '',
        'student_url' => $student_id > 0 ? Url::fromRoute('lms_progress.instructor_student_detail', ['user' => $student_id])->toString() : '',
        'result_url' => Url::fromRoute('lms_quiz.result', ['lms_quiz_result' => $result->id()])->toString(),
        'quiz_name' => $quiz?->label() ?? $this->t('Quiz'),
        'lesson_title' => (string) $course_meta['lesson_title'],
        'score' => round($score, 1),
        'threshold' => round($threshold, 1),
        'state' => $state,
        'attempted_at' => $this->formatTimestamp($result->get('created')->value ?? NULL),
      ];
    }

    return $items;
  }

  /**
   * Loads lesson comments for the current course.
   *
   * @param array<int, array<string, mixed>> $lesson_map
   *   Lesson metadata keyed by lesson ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready comment rows.
   */
  private function loadCourseCommentRows(array $lesson_map): array {
    if ($lesson_map === [] || !$this->lmsEntityTypeManager->hasDefinition('comment')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('comment')
      ->getQuery()
      ->condition('entity_type', 'lms_lesson')
      ->condition('field_name', 'field_comments')
      ->condition('entity_id', array_keys($lesson_map), 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $comments = $this->lmsEntityTypeManager->getStorage('comment')->loadMultiple($ids);
    $items = [];

    foreach ($comments as $comment) {
      $lesson_id = (int) ($comment->get('entity_id')->value ?? 0);
      $lesson_meta = $lesson_map[$lesson_id] ?? NULL;
      if ($lesson_meta === NULL) {
        continue;
      }

      $owner = $comment->getOwner();
      $message = trim(strip_tags((string) ($comment->get('comment_body')->value ?? '')));
      $items[] = [
        'author' => $owner ? $owner->getDisplayName() : ((string) ($comment->get('name')->value ?? $this->t('Anonymous'))),
        'lesson_title' => (string) $lesson_meta['lesson_title'],
        'chapter_title' => (string) $lesson_meta['chapter_title'],
        'message' => $message,
        'created_at' => $this->formatTimestamp($comment->get('created')->value ?? NULL),
        'lesson_url' => Url::fromRoute('lms_progress.lesson_view', [
          'lms_course' => (int) $lesson_meta['course_id'],
          'lms_lesson' => $lesson_id,
        ])->toString(),
      ];
    }

    return $items;
  }

  /**
   * Loads question bank rows across all quizzes in the course.
   *
   * @param array<int, array<string, mixed>> $quiz_course_map
   *   Quiz metadata keyed by quiz ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready question rows.
   */
  private function loadCourseQuestionRows(array $quiz_course_map): array {
    if ($quiz_course_map === []) {
      return [];
    }

    $quizzes = $this->lmsEntityTypeManager->getStorage('lms_quiz')->loadMultiple(array_keys($quiz_course_map));
    $question_ids = [];
    $question_context = [];

    foreach ($quizzes as $quiz_id => $quiz) {
      if (!$quiz->hasField('field_questions') || $quiz->get('field_questions')->isEmpty()) {
        continue;
      }

      foreach ($quiz->get('field_questions')->getValue() as $item) {
        $question_id = (int) ($item['target_id'] ?? 0);
        if ($question_id <= 0) {
          continue;
        }

        $question_ids[$question_id] = $question_id;
        $question_context[] = [
          'question_id' => $question_id,
          'quiz_name' => $quiz->label(),
          'lesson_title' => (string) ($quiz_course_map[(int) $quiz_id]['lesson_title'] ?? ''),
        ];
      }
    }

    if ($question_ids === []) {
      return [];
    }

    $questions = $this->lmsEntityTypeManager->getStorage('lms_question')->loadMultiple(array_values($question_ids));
    $items = [];

    foreach ($question_context as $row) {
      $question = $questions[$row['question_id']] ?? NULL;
      if ($question === NULL) {
        continue;
      }

      $items[] = [
        'quiz_name' => $row['quiz_name'],
        'lesson_title' => $row['lesson_title'],
        'question_name' => $question->label(),
        'type' => ucfirst(str_replace('_', ' ', (string) $question->bundle())),
        'points' => $question->hasField('field_points') ? (float) ($question->get('field_points')->value ?? 0) : 0,
      ];
    }

    return $items;
  }

  /**
   * Builds the top-level counts for popup and detail summary.
   *
   * @param array<int, \Drupal\lms_progress\Entity\UserCourse> $enrollments
   *   Enrollment entities keyed by entity ID.
   * @param array<int, array<string, mixed>> $learners
   *   Learner rows.
   * @param array<int, array<string, mixed>> $quiz_results
   *   Quiz rows.
   * @param array<int, array<string, mixed>> $comments
   *   Comment rows.
   * @param array<int, array<string, mixed>> $questions
   *   Question rows.
   *
   * @return array<string, int|float>
   *   Scalar stats.
   */
  private function buildCourseStatsCounts(Course $course, array $enrollments, array $learners, array $quiz_results, array $comments, array $questions): array {
    $completed = 0;
    $active = 0;
    $progress_total = 0.0;

    foreach ($enrollments as $enrollment) {
      if (!$enrollment instanceof UserCourse) {
        continue;
      }

      $progress_total += $this->getEnrollmentProgress($enrollment);
      if ($this->normalizeEnrollmentStatus($enrollment) === 'completed') {
        $completed++;
      }
      else {
        $active++;
      }
    }

    $quiz_takers = [];
    foreach ($quiz_results as $result) {
      $student_id = (int) ($result['student_id'] ?? 0);
      if ($student_id > 0) {
        $quiz_takers[$student_id] = TRUE;
      }
    }

    $certificate_count = 0;
    if ($this->lmsEntityTypeManager->hasDefinition('lms_user_certificate')) {
      $certificate_count = (int) $this->lmsEntityTypeManager->getStorage('lms_user_certificate')
        ->getQuery()
        ->condition('field_course', (int) $course->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    $enrollment_count = count($enrollments);

    return [
      'enrollments' => $enrollment_count,
      'active_students' => $active,
      'completed_students' => $completed,
      'average_progress' => $enrollment_count > 0 ? round($progress_total / $enrollment_count, 1) : 0,
      'completion_rate' => $enrollment_count > 0 ? round(($completed / $enrollment_count) * 100, 1) : 0,
      'quiz_attempts' => count($quiz_results),
      'quiz_takers' => count($quiz_takers),
      'comment_count' => count($comments),
      'question_count' => count($questions),
      'certificate_count' => $certificate_count,
      'learner_count' => count($learners),
    ];
  }

  /**
   * Loads enrollments for a single learner within the instructor's courses.
   *
   * @return array<int, \Drupal\lms_progress\Entity\UserCourse>
   *   Enrollment entities keyed by entity ID.
   */
  private function loadStudentEnrollments(int $student_id, array $course_ids): array {
    if ($student_id <= 0 || $course_ids === []) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $student_id)
      ->condition('field_course', $course_ids, 'IN')
      ->accessCheck(FALSE)
      ->execute();

    return $ids !== []
      ? $this->lmsEntityTypeManager->getStorage('lms_user_course')->loadMultiple($ids)
      : [];
  }

  /**
   * Builds learner-centric course rows for the detail page.
   *
   * @param array<int, \Drupal\lms_progress\Entity\UserCourse> $enrollments
   *   Enrollment entities keyed by entity ID.
   * @param array<int, \Drupal\lms_course\Entity\Course> $courses
   *   Courses keyed by course ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready learner course rows.
   */
  private function buildStudentCourseRows(array $enrollments, array $courses): array {
    $rows = [];

    foreach ($enrollments as $enrollment) {
      if (!$enrollment instanceof UserCourse || $enrollment->get('field_course')->isEmpty()) {
        continue;
      }

      $course_id = (int) $enrollment->get('field_course')->target_id;
      $course = $courses[$course_id] ?? NULL;
      if (!$course instanceof Course) {
        continue;
      }

      $rows[] = [
        'course_title' => $course->label(),
        'course_url' => Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString(),
        'thumbnail_url' => $this->getCourseThumbnailUrl($course),
        'activity_url' => Url::fromRoute('lms_progress.instructor_student_course_detail', [
          'user' => (int) $enrollment->getOwnerId(),
          'lms_course' => $course_id,
        ])->toString(),
        'progress' => $this->getEnrollmentProgress($enrollment),
        'status' => $this->normalizeEnrollmentStatus($enrollment),
        'enrolled_at' => $this->formatTimestamp($enrollment->get('field_enrolled_at')->value ?? NULL),
        'completed_at' => $this->formatTimestamp($enrollment->get('field_completed_at')->value ?? NULL),
      ];
    }

    usort($rows, static function (array $left, array $right): int {
      return strcmp($left['course_title'], $right['course_title']);
    });

    return $rows;
  }

  /**
   * Builds a quiz-to-course map for the instructor's assigned courses.
   *
   * @param array<int, \Drupal\lms_course\Entity\Course> $courses
   *   Courses keyed by course ID.
   *
   * @return array<int, array<string, mixed>>
   *   Quiz metadata keyed by quiz ID.
   */
  private function buildCourseQuizMap(array $courses): array {
    $chapter_to_course = [];
    foreach ($courses as $course_id => $course) {
      if (!$course instanceof Course || $course->get('field_chapters')->isEmpty()) {
        continue;
      }

      foreach ($course->get('field_chapters')->getValue() as $item) {
        $chapter_id = (int) ($item['target_id'] ?? 0);
        if ($chapter_id <= 0 || isset($chapter_to_course[$chapter_id])) {
          continue;
        }

        $chapter_to_course[$chapter_id] = [
          'course_id' => (int) $course_id,
          'course_title' => $course->label(),
        ];
      }
    }

    if ($chapter_to_course === []) {
      return [];
    }

    $chapters = $this->lmsEntityTypeManager->getStorage('lms_chapter')->loadMultiple(array_keys($chapter_to_course));
    $lesson_to_course = [];
    foreach ($chapters as $chapter_id => $chapter) {
      $course_meta = $chapter_to_course[(int) $chapter_id] ?? NULL;
      if ($course_meta === NULL || $chapter->get('field_lessons')->isEmpty()) {
        continue;
      }

      foreach ($chapter->get('field_lessons')->getValue() as $item) {
        $lesson_id = (int) ($item['target_id'] ?? 0);
        if ($lesson_id > 0 && !isset($lesson_to_course[$lesson_id])) {
          $lesson_to_course[$lesson_id] = $course_meta;
        }
      }
    }

    if ($lesson_to_course === []) {
      return [];
    }

    $lessons = $this->lmsEntityTypeManager->getStorage('lms_lesson')->loadMultiple(array_keys($lesson_to_course));
    $quiz_map = [];
    foreach ($lessons as $lesson_id => $lesson) {
      $course_meta = $lesson_to_course[(int) $lesson_id] ?? NULL;
      if ($course_meta === NULL || $lesson->get('field_quiz')->isEmpty()) {
        continue;
      }

      $quiz_id = (int) $lesson->get('field_quiz')->target_id;
      if ($quiz_id <= 0 || isset($quiz_map[$quiz_id])) {
        continue;
      }

      $quiz_map[$quiz_id] = $course_meta + [
        'lesson_title' => $lesson->label(),
      ];
    }

    return $quiz_map;
  }

  /**
   * Loads quiz attempts for the learner within the instructor's courses.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready quiz result rows.
   */
  private function loadStudentQuizResults(int $student_id, array $quiz_course_map): array {
    if ($student_id <= 0 || $quiz_course_map === [] || !$this->lmsEntityTypeManager->hasDefinition('lms_quiz_result')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $student_id)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $results = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')->loadMultiple($ids);
    $items = [];

    foreach ($results as $result) {
      if ($result->get('field_quiz')->isEmpty()) {
        continue;
      }

      $quiz_id = (int) $result->get('field_quiz')->target_id;
      $course_meta = $quiz_course_map[$quiz_id] ?? NULL;
      if ($course_meta === NULL) {
        continue;
      }

      $quiz = $result->get('field_quiz')->entity;
      $score = (float) ($result->get('field_score')->value ?? 0);
      $threshold = $quiz && $quiz->hasField('field_threshold') && !$quiz->get('field_threshold')->isEmpty()
        ? (float) $quiz->get('field_threshold')->value
        : 80.0;
      $state = 'retry';
      if ($score >= $threshold) {
        $state = 'passed';
      }
      elseif ($score >= max($threshold - 15, 50)) {
        $state = 'review';
      }

      $items[] = [
        'quiz_name' => $quiz?->label() ?? $this->t('Quiz'),
        'course_title' => $course_meta['course_title'],
        'score' => round($score, 1),
        'threshold' => round($threshold, 1),
        'state' => $state,
        'attempted_at' => $this->formatTimestamp($result->get('created')->value ?? NULL),
      ];
    }

    return $items;
  }

  /**
   * Loads certificates for the learner within the instructor's courses.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready certificate rows.
   */
  private function loadStudentCertificates(int $student_id, array $course_ids): array {
    if ($student_id <= 0 || $course_ids === [] || !$this->lmsEntityTypeManager->hasDefinition('lms_user_certificate')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $student_id)
      ->condition('field_course', $course_ids, 'IN')
      ->sort('field_issued_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $certificates = $this->lmsEntityTypeManager->getStorage('lms_user_certificate')->loadMultiple($ids);
    $file_url_generator = \Drupal::service('file_url_generator');
    $items = [];

    foreach ($certificates as $certificate) {
      $course = $certificate->get('field_course')->entity;
      $download_url = '';
      if (!$certificate->get('field_pdf_file')->isEmpty()) {
        $file = $certificate->get('field_pdf_file')->entity;
        if ($file) {
          $download_url = $file_url_generator->generateString($file->getFileUri());
        }
      }

      $items[] = [
        'course_title' => $course?->label() ?? $this->t('Course'),
        'code' => (string) ($certificate->get('field_certificate_number')->value ?? ''),
        'issued_at' => $this->formatTimestamp($certificate->get('field_issued_at')->value ?? NULL),
        'expiry_at' => $this->formatTimestamp($certificate->get('field_expiry_date')->value ?? NULL),
        'revoked' => (bool) ($certificate->get('field_revoked')->value ?? FALSE),
        'download_url' => $download_url,
      ];
    }

    return $items;
  }

  /**
   * Loads recent learner notifications that are relevant to learning activity.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready notification rows.
   */
  private function loadStudentNotifications(int $student_id): array {
    if ($student_id <= 0 || !$this->lmsEntityTypeManager->hasDefinition('lms_notification')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_notification')
      ->getQuery()
      ->condition('uid', $student_id)
      ->condition('field_type', ['announcement', 'reminder', 'system', 'enrollment', 'quiz_result', 'certificate', 'new_lesson', 'forum_reply'], 'IN')
      ->sort('created', 'DESC')
      ->range(0, 8)
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $notifications = $this->lmsEntityTypeManager->getStorage('lms_notification')->loadMultiple($ids);
    $items = [];
    foreach ($notifications as $notification) {
      $type = (string) ($notification->get('field_type')->value ?? 'system');
      $type_label = match ($type) {
        'announcement' => (string) $this->t('Announcement'),
        'reminder' => (string) $this->t('Reminder'),
        'enrollment' => (string) $this->t('Enrollment'),
        'quiz_result' => (string) $this->t('Quiz Result'),
        'certificate' => (string) $this->t('Certificate'),
        'new_lesson' => (string) $this->t('New Lesson'),
        'forum_reply' => (string) $this->t('Forum Reply'),
        default => (string) $this->t('System'),
      };

      $items[] = [
        'title' => (string) ($notification->get('field_title')->value ?? ''),
        'message' => trim((string) ($notification->get('field_message')->value ?? '')),
        'type_label' => $type_label,
        'created_at' => $this->formatTimestamp($notification->get('created')->value ?? NULL),
        'is_read' => (bool) ($notification->get('field_read')->value ?? FALSE),
        'link' => (string) ($notification->get('field_link')->value ?? ''),
      ];
    }

    return $items;
  }

  /**
   * Builds the learner lesson timeline for one course.
   *
   * @param array<int, array<string, mixed>> $lesson_map
   *   Lesson metadata keyed by lesson ID.
   *
   * @return array{rows: array<int, array<string, mixed>>, summary: array<string, mixed>}
   *   Ordered timeline rows and summary values.
   */
  private function buildStudentLessonTimeline(int $student_id, Course $course, array $lesson_map): array {
    if ($student_id <= 0 || $lesson_map === [] || !$this->lmsEntityTypeManager->hasDefinition('lms_user_lesson')) {
      return [
        'rows' => [],
        'summary' => [
          'current_lesson' => '',
          'last_activity' => '',
          'completed_lessons' => 0,
          'total_lessons' => count($lesson_map),
        ],
      ];
    }

    $storage = $this->lmsEntityTypeManager->getStorage('lms_user_lesson');
    $ids = $storage->getQuery()
      ->condition('uid', $student_id)
      ->condition('field_lesson', array_keys($lesson_map), 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $records = $ids === []
      ? []
      : $storage->loadMultiple($ids);

    $progress_by_lesson = [];
    $latest_record_at = 0;
    $current_lesson = '';

    foreach ($ids as $id) {
      $record = $records[$id] ?? NULL;
      if (!$record || $record->get('field_lesson')->isEmpty()) {
        continue;
      }

      $lesson_id = (int) $record->get('field_lesson')->target_id;
      if ($lesson_id <= 0 || isset($progress_by_lesson[$lesson_id])) {
        continue;
      }

      $status = (string) ($record->get('field_status')->value ?? 'not_started');
      $created_at = (int) ($record->get('created')->value ?? 0);
      $progress_by_lesson[$lesson_id] = [
        'status' => in_array($status, ['completed', 'in_progress', 'not_started'], TRUE) ? $status : 'not_started',
        'video_position' => (int) ($record->get('field_video_position')->value ?? 0),
        'completed_at' => $this->formatTimestamp($record->get('field_completed_at')->value ?? NULL),
      ];

      if ($created_at > $latest_record_at) {
        $latest_record_at = $created_at;
      }

      if ($current_lesson === '' && $status === 'in_progress') {
        $current_lesson = (string) ($lesson_map[$lesson_id]['lesson_title'] ?? '');
      }
    }

    $rows = [];
    $completed_lessons = 0;
    $in_progress_lessons = 0;

    foreach ($lesson_map as $lesson_id => $lesson_meta) {
      $progress = $progress_by_lesson[$lesson_id] ?? NULL;
      $status = (string) ($progress['status'] ?? 'not_started');
      if ($status === 'completed') {
        $completed_lessons++;
      }
      elseif ($status === 'in_progress') {
        $in_progress_lessons++;
      }

      $rows[] = [
        'lesson_id' => $lesson_id,
        'lesson_title' => (string) $lesson_meta['lesson_title'],
        'chapter_title' => (string) $lesson_meta['chapter_title'],
        'status' => $status,
        'video_position' => (int) ($progress['video_position'] ?? 0),
        'video_position_label' => $this->formatVideoPosition((int) ($progress['video_position'] ?? 0)),
        'completed_at' => (string) ($progress['completed_at'] ?? ''),
        'lesson_url' => Url::fromRoute('lms_progress.lesson_view', [
          'lms_course' => (int) $course->id(),
          'lms_lesson' => $lesson_id,
        ])->toString(),
        'has_quiz' => (int) ($lesson_meta['quiz_id'] ?? 0) > 0,
      ];
    }

    if ($current_lesson === '' && $in_progress_lessons === 0) {
      foreach ($rows as $row) {
        if ($row['status'] === 'not_started') {
          $current_lesson = $row['lesson_title'];
          break;
        }
      }
    }

    if ($current_lesson === '' && $completed_lessons === count($lesson_map) && $completed_lessons > 0) {
      $current_lesson = (string) $this->t('Course completed');
    }

    return [
      'rows' => $rows,
      'summary' => [
        'current_lesson' => $current_lesson,
        'last_activity' => $this->formatTimestamp($latest_record_at),
        'completed_lessons' => $completed_lessons,
        'total_lessons' => count($lesson_map),
      ],
    ];
  }

  /**
   * Loads learner-authored lesson comments for one course.
   *
   * @param array<int, array<string, mixed>> $lesson_map
   *   Lesson metadata keyed by lesson ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready learner comment rows.
   */
  private function loadStudentLessonComments(int $student_id, array $lesson_map): array {
    if ($student_id <= 0 || $lesson_map === [] || !$this->lmsEntityTypeManager->hasDefinition('comment')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('comment')
      ->getQuery()
      ->condition('entity_type', 'lms_lesson')
      ->condition('field_name', 'field_comments')
      ->condition('entity_id', array_keys($lesson_map), 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $comments = $this->lmsEntityTypeManager->getStorage('comment')->loadMultiple($ids);
    $reply_counts = [];
    foreach ($comments as $comment) {
      $parent_id = (int) ($comment->get('pid')->target_id ?? $comment->get('pid')->value ?? 0);
      if ($parent_id > 0) {
        $reply_counts[$parent_id] = ($reply_counts[$parent_id] ?? 0) + 1;
      }
    }

    $items = [];
    foreach ($comments as $comment) {
      if ((int) $comment->getOwnerId() !== $student_id) {
        continue;
      }

      $lesson_id = (int) ($comment->get('entity_id')->value ?? 0);
      $lesson_meta = $lesson_map[$lesson_id] ?? NULL;
      if ($lesson_meta === NULL) {
        continue;
      }

      $items[] = [
        'lesson_title' => (string) $lesson_meta['lesson_title'],
        'chapter_title' => (string) $lesson_meta['chapter_title'],
        'message' => trim(strip_tags((string) ($comment->get('comment_body')->value ?? ''))),
        'created_at' => $this->formatTimestamp($comment->get('created')->value ?? NULL),
        'lesson_url' => Url::fromRoute('lms_progress.lesson_view', [
          'lms_course' => (int) $lesson_meta['course_id'],
          'lms_lesson' => $lesson_id,
        ])->toString(),
        'reply_count' => (int) ($reply_counts[(int) $comment->id()] ?? 0),
        'is_reply' => (int) ($comment->get('pid')->target_id ?? $comment->get('pid')->value ?? 0) > 0,
      ];
    }

    return $items;
  }

  /**
   * Loads quiz attempts with response-level feedback for one learner.
   *
   * @param array<int, array<string, mixed>> $quiz_course_map
   *   Quiz metadata keyed by quiz ID.
   *
   * @return array<int, array<string, mixed>>
   *   Theme-ready quiz attempt rows.
   */
  private function loadStudentQuizAttemptDetails(int $student_id, array $quiz_course_map): array {
    if ($student_id <= 0 || $quiz_course_map === [] || !$this->lmsEntityTypeManager->hasDefinition('lms_quiz_result')) {
      return [];
    }

    $ids = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $student_id)
      ->condition('field_quiz', array_keys($quiz_course_map), 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if ($ids === []) {
      return [];
    }

    $results = $this->lmsEntityTypeManager->getStorage('lms_quiz_result')->loadMultiple($ids);
    $items = [];

    foreach ($results as $result) {
      if ($result->get('field_quiz')->isEmpty()) {
        continue;
      }

      $quiz_id = (int) $result->get('field_quiz')->target_id;
      $course_meta = $quiz_course_map[$quiz_id] ?? NULL;
      if ($course_meta === NULL) {
        continue;
      }

      $quiz = $result->get('field_quiz')->entity;
      $score = (float) ($result->get('field_score')->value ?? 0);
      $threshold = $quiz && $quiz->hasField('field_threshold') && !$quiz->get('field_threshold')->isEmpty()
        ? (float) $quiz->get('field_threshold')->value
        : 80.0;
      $state = 'retry';
      if ($score >= $threshold) {
        $state = 'passed';
      }
      elseif ($score >= max($threshold - 15, 50)) {
        $state = 'review';
      }

      $responses = [];
      $feedback_count = 0;
      $pending_count = 0;
      foreach ($result->get('field_responses') as $reference) {
        $response = $reference->entity;
        if (!$response) {
          continue;
        }

        $question = $response->get('field_question')->entity;
        $choices = [];
        if ($response->hasField('field_answers') && !$response->get('field_answers')->isEmpty()) {
          foreach ($response->get('field_answers') as $answer_reference) {
            if ($answer_reference->entity) {
              $choices[] = $answer_reference->entity->label();
            }
          }
        }

        $feedback = trim((string) ($response->get('field_teacher_feedback')->value ?? ''));
        $pending = (bool) ($response->get('field_pending_review')->value ?? FALSE);
        if ($feedback !== '') {
          $feedback_count++;
        }
        if ($pending) {
          $pending_count++;
        }

        $responses[] = [
          'question' => $question?->label() ?? $this->t('Question'),
          'choices' => $choices,
          'text_answer' => trim((string) ($response->get('field_text_answer')->value ?? '')),
          'correct' => (bool) ($response->get('field_correct')->value ?? FALSE),
          'score' => round((float) ($response->get('field_score')->value ?? 0), 1),
          'feedback' => $feedback,
          'pending' => $pending,
        ];
      }

      $items[] = [
        'quiz_name' => $quiz?->label() ?? $this->t('Quiz'),
        'course_title' => (string) $course_meta['course_title'],
        'lesson_title' => (string) ($course_meta['lesson_title'] ?? ''),
        'score' => round($score, 1),
        'threshold' => round($threshold, 1),
        'state' => $state,
        'attempted_at' => $this->formatTimestamp($result->get('created')->value ?? NULL),
        'responses' => $responses,
        'feedback_count' => $feedback_count,
        'pending_count' => $pending_count,
      ];
    }

    return $items;
  }

  /**
   * Normalizes the enrollment status for display.
   */
  private function normalizeEnrollmentStatus(UserCourse $enrollment): string {
    $status = $enrollment->hasField('field_status') && !$enrollment->get('field_status')->isEmpty()
      ? (string) $enrollment->get('field_status')->value
      : 'enrolled';

    return match ($status) {
      'completed' => 'completed',
      'in_progress', 'active' => 'in_progress',
      default => $this->getEnrollmentProgress($enrollment) > 0 ? 'in_progress' : 'not_started',
    };
  }

  /**
   * Reads the stored progress percentage from the enrollment entity.
   */
  private function getEnrollmentProgress(UserCourse $enrollment): float {
    if ($enrollment->hasField('field_progress') && !$enrollment->get('field_progress')->isEmpty()) {
      return round((float) $enrollment->get('field_progress')->value, 1);
    }

    return 0.0;
  }

  /**
   * Resolves the category label for a course.
   */
  private function getCourseCategoryName(Course $course): string {
    if ($course->hasField('field_category') && !$course->get('field_category')->isEmpty()) {
      $category = $course->get('field_category')->entity;
      if ($category) {
        return $category->label();
      }
    }

    return '';
  }

  /**
   * Resolves the best short description for course cards.
   */
  private function getCourseCardDescription(Course $course): string {
    $summary = '';

    if ($course->hasField('field_short_description') && !$course->get('field_short_description')->isEmpty()) {
      $summary = trim(strip_tags((string) $course->get('field_short_description')->value));
    }

    if ($summary === '' && $course->hasField('field_description') && !$course->get('field_description')->isEmpty()) {
      $summary = trim(strip_tags((string) $course->get('field_description')->value));
    }

    return $summary !== ''
      ? Unicode::truncate($summary, 150, TRUE, TRUE)
      : '';
  }

  /**
   * Resolves the thumbnail URL for a course.
   */
  private function getCourseThumbnailUrl(Course $course): string {
    if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
      $file = $course->get('field_thumbnail')->entity;
      if ($file) {
        return \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }

    return '';
  }

  /**
   * Checks whether the course is published.
   */
  private function isPublishedCourse(Course $course): bool {
    return $course->hasField('status') ? (bool) $course->get('status')->value : TRUE;
  }

  /**
   * Formats a timestamp for the roster table.
   */
  private function formatTimestamp(mixed $timestamp): string {
    $value = (int) $timestamp;
    if ($value <= 0) {
      return '';
    }

    return $this->lmsDateFormatter->format($value, 'custom', 'Y-m-d');
  }

  /**
   * Formats a saved video position into a short label.
   */
  private function formatVideoPosition(int $seconds): string {
    if ($seconds <= 0) {
      return '';
    }

    $minutes = intdiv($seconds, 60);
    $remaining_seconds = $seconds % 60;

    return sprintf('%02d:%02d', $minutes, $remaining_seconds);
  }

  /**
   * Cache metadata for instructor workspace pages.
   */
  private function buildCacheMetadata(): array {
    return [
      'contexts' => ['user', 'user.roles'],
      'max-age' => 0,
    ];
  }

  /**
   * Selects the safest form mode for the current instructor context.
   */
  private function resolveCourseFormMode(): string {
    $roles = $this->currentAccount->getRoles();

    if (in_array('administrator', $roles, TRUE)) {
      return 'admin_edit';
    }

    if (in_array('manager', $roles, TRUE)) {
      return 'manager_edit';
    }

    return 'default';
  }

}
