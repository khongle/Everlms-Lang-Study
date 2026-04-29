<?php

declare(strict_types=1);

namespace Drupal\lms_mobile_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles mobile course API endpoints.
 *
 * Endpoints:
 *   GET  /api/v1/courses/search
 *   GET  /api/v1/categories
 *   GET  /api/v1/courses/{courseId}
 *   POST /api/v1/courses/{courseId}/enroll-free
 *   GET  /api/v1/dashboard
 *   GET  /api/v1/my-courses
 */
class MobileCourseController extends ControllerBase {

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

  // ---------------------------------------------------------------------------
  // GET /api/v1/courses/search
  // ---------------------------------------------------------------------------

  /**
   * Searches published courses for the mobile app.
   */
  public function searchCourses(Request $request): JsonResponse {
    $search = trim((string) $request->query->get('search', ''));
    $query_all = $request->query->all();

    $category_input = $query_all['category'] ?? $query_all['category[]'] ?? [];
    $categories = is_array($category_input) ? $category_input : [$category_input];
    $categories = array_values(array_filter(array_map('intval', $categories)));

    $level_input = $query_all['level'] ?? $query_all['level[]'] ?? [];
    $levels = is_array($level_input) ? $level_input : [$level_input];
    $levels = array_values(array_filter(array_map('intval', $levels)));

    $price_input = $query_all['price'] ?? $query_all['price[]'] ?? [];
    $prices = is_array($price_input) ? $price_input : [$price_input];
    $prices = array_values(array_filter(array_map('strval', $prices)));

    $sort = (string) $request->query->get('sort', 'newest');
    $page = max(0, (int) $request->query->get('page', 0));
    $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

    $query = $this->entityTypeManager()->getStorage('lms_course')
      ->getQuery()
      ->condition('status', 1)
      ->accessCheck(FALSE);

    if ($search !== '') {
      $query->condition('name', $search, 'CONTAINS');
    }

    if ($categories !== []) {
      $query->condition('field_category', $categories, 'IN');
    }

    if ($levels !== []) {
      $query->condition('field_level', $levels, 'IN');
    }

    if ($prices !== [] && count($prices) < 2) {
      if (in_array('free', $prices, TRUE)) {
        $query->condition('field_price_type', 'free');
      }
      elseif (in_array('paid', $prices, TRUE)) {
        $query->condition('field_price_type', ['paid', 'membership'], 'IN');
      }
    }

    switch ($sort) {
      case 'popular':
        $query->sort('field_bestseller', 'DESC')->sort('changed', 'DESC');
        break;

      case 'rating':
        $query->sort('field_rating', 'DESC')->sort('changed', 'DESC');
        break;

      case 'price_asc':
        $query->sort('field_price', 'ASC')->sort('changed', 'DESC');
        break;

      case 'price_desc':
        $query->sort('field_price', 'DESC')->sort('changed', 'DESC');
        break;

      case 'newest':
      default:
        $query->sort('created', 'DESC');
        break;
    }

    $count_query = clone $query;
    $total = (int) $count_query->count()->execute();

    $ids = $query
      ->range($page * $limit, $limit)
      ->execute();

    $courses = $ids
      ? $this->entityTypeManager()->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($courses as $course) {
      if (!$course->get('status')->value) {
        continue;
      }
      $items[] = $this->buildCourseListItem($course);
    }

    return new JsonResponse([
      'total' => $total,
      'page' => $page,
      'limit' => $limit,
      'items' => array_values($items),
    ]);
  }

  // ---------------------------------------------------------------------------
  // GET /api/v1/categories
  // ---------------------------------------------------------------------------

  /**
   * Lists all published course categories with course counts.
   */
  public function listCategories(Request $request): JsonResponse {
    $term_storage = $this->entityTypeManager()->getStorage('taxonomy_term');
    $course_storage = $this->entityTypeManager()->getStorage('lms_course');

    $tids = $term_storage->getQuery()
      ->condition('vid', 'course_category')
      ->condition('status', 1)
      ->sort('weight')
      ->sort('name')
      ->accessCheck(FALSE)
      ->execute();

    $terms = $tids ? $term_storage->loadMultiple($tids) : [];
    $items = [];

    foreach ($terms as $term) {
      $course_count = (int) $course_storage->getQuery()
        ->condition('field_category', $term->id())
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      $description = '';
      if ($term->hasField('description') && !$term->get('description')->isEmpty()) {
        $description = strip_tags($term->get('description')->value ?? '');
      }

      $items[] = [
        'id' => (int) $term->id(),
        'uuid' => $term->uuid(),
        'name' => $term->label(),
        'description' => $description,
        'course_count' => $course_count,
      ];
    }

    return new JsonResponse(['items' => $items]);
  }

  // ---------------------------------------------------------------------------
  // GET /api/v1/courses/{courseId}
  // ---------------------------------------------------------------------------

  /**
   * Returns full course detail including curriculum and enrollment status.
   */
  public function getCourseDetail(Request $request, string $courseId): JsonResponse {
    $course = $this->entityTypeManager()->getStorage('lms_course')->load((int) $courseId);

    if (!$course || !$course->get('status')->value) {
      return $this->errorResponse('not_found', 'Course not found.', 404);
    }

    // Resolve authenticated user (optional — null if not logged in).
    $uid = NULL;
    $enrollment = NULL;
    try {
      $user = $this->getAuthenticatedUser($request);
      $uid = (int) $user->id();
      $enrollment = $this->getUserEnrollment($uid, (int) $courseId);
    }
    catch (AccessDeniedHttpException) {
      // Public access — enrollment data will be null.
    }

    // Build curriculum: collect all lesson IDs for completion lookup.
    $all_lesson_ids = [];
    $curriculum = [];

    if ($course->hasField('field_chapters') && !$course->get('field_chapters')->isEmpty()) {
      /** @var \Drupal\lms_chapter\Entity\Chapter[] $chapters */
      $chapters = $course->get('field_chapters')->referencedEntities();
      usort($chapters, fn($a, $b) => ($a->get('weight')->value ?? 0) <=> ($b->get('weight')->value ?? 0));

      foreach ($chapters as $chapter) {
        if (!$chapter->get('status')->value) {
          continue;
        }
        $lessons_data = [];
        if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
          foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
            if (!$lesson->get('status')->value) {
              continue;
            }
            $all_lesson_ids[] = (int) $lesson->id();
            $lessons_data[] = $lesson;
          }
        }
        $curriculum[] = [
          'chapter' => $chapter,
          'lessons' => $lessons_data,
        ];
      }
    }

    // Batch-load completed lesson IDs for this user.
    $completed_lesson_ids = [];
    if ($uid && !empty($all_lesson_ids)) {
      $ul_ids = $this->entityTypeManager()->getStorage('lms_user_lesson')
        ->getQuery()
        ->condition('uid', $uid)
        ->condition('field_lesson', $all_lesson_ids, 'IN')
        ->condition('field_status', 'completed')
        ->accessCheck(FALSE)
        ->execute();

      if ($ul_ids) {
        foreach ($this->entityTypeManager()->getStorage('lms_user_lesson')->loadMultiple($ul_ids) as $ul) {
          $completed_lesson_ids[] = (int) $ul->get('field_lesson')->target_id;
        }
      }
    }

    // Build curriculum response.
    $curriculum_response = [];
    foreach ($curriculum as $idx => $section) {
      $chapter = $section['chapter'];
      $lessons_response = [];

      foreach ($section['lessons'] as $lesson) {
        $lesson_id = (int) $lesson->id();
        $drip_days = (int) ($lesson->hasField('field_drip_days') ? ($lesson->get('field_drip_days')->value ?? 0) : 0);
        $is_locked = FALSE;
        if ($drip_days > 0 && $enrollment) {
          $enrolled_ts = (int) $enrollment->get('field_enrolled_at')->value;
          $elapsed_days = (int) floor((time() - $enrolled_ts) / 86400);
          $is_locked = $elapsed_days < $drip_days;
        }

        $lessons_response[] = [
          'id' => $lesson_id,
          'title' => $lesson->label(),
          'type' => $lesson->hasField('field_type') ? ($lesson->get('field_type')->value ?? 'text') : 'text',
          'duration_minutes' => (int) ($lesson->hasField('field_duration') ? ($lesson->get('field_duration')->value ?? 0) : 0),
          'free_preview' => (bool) ($lesson->hasField('field_free_preview') ? $lesson->get('field_free_preview')->value : FALSE),
          'is_completed' => in_array($lesson_id, $completed_lesson_ids),
          'is_locked' => $is_locked,
        ];
      }

      $curriculum_response[] = [
        'id' => (int) $chapter->id(),
        'title' => $chapter->label(),
        'description' => $chapter->hasField('field_description') ? ($chapter->get('field_description')->value ?? '') : '',
        'weight' => (int) ($chapter->get('weight')->value ?? $idx),
        'lessons' => $lessons_response,
      ];
    }

    // Build enrollment block.
    $enrollment_response = NULL;
    if ($enrollment) {
      $last_lesson_id = $this->findLastLessonId($uid, $all_lesson_ids, $completed_lesson_ids);
      $enrollment_response = [
        'enrolled' => TRUE,
        'progress' => $this->calculateProgress($uid, $all_lesson_ids),
        'status' => $enrollment->hasField('field_status') ? ($enrollment->get('field_status')->value ?? 'enrolled') : 'enrolled',
        'last_lesson_id' => $last_lesson_id,
        'last_lesson_url' => $last_lesson_id
          ? '/api/v1/courses/' . $courseId . '/lessons/' . $last_lesson_id
          : NULL,
      ];
    }

    // Is wishlisted?
    $is_wishlisted = FALSE;
    if ($uid) {
      $wl_ids = $this->entityTypeManager()->getStorage('lms_wishlist')
        ->getQuery()
        ->condition('uid', $uid)
        ->condition('field_course', (int) $courseId)
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();
      $is_wishlisted = !empty($wl_ids);
    }

    // Instructors: owner (uid) + field_instructor.
    $instructors = [];
    $instructor_ids_seen = [];
    $owner = $course->get('uid')->entity;
    if ($owner) {
      $instructors[] = $this->buildInstructorData($owner);
      $instructor_ids_seen[] = (int) $owner->id();
    }
    if ($course->hasField('field_instructor') && !$course->get('field_instructor')->isEmpty()) {
      foreach ($course->get('field_instructor')->referencedEntities() as $instructor) {
        if (!in_array((int) $instructor->id(), $instructor_ids_seen)) {
          $instructors[] = $this->buildInstructorData($instructor);
          $instructor_ids_seen[] = (int) $instructor->id();
        }
      }
    }

    // Tags.
    $tags = [];
    if ($course->hasField('field_tags') && !$course->get('field_tags')->isEmpty()) {
      foreach ($course->get('field_tags')->referencedEntities() as $tag) {
        $tags[] = $tag->label();
      }
    }

    // Rating count.
    $rating_count = (int) $this->entityTypeManager()->getStorage('lms_rating')
      ->getQuery()
      ->condition('field_course', (int) $courseId)
      ->condition('field_approved', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return new JsonResponse([
      'id' => (int) $course->id(),
      'uuid' => $course->uuid(),
      'title' => $course->label(),
      'description' => $course->hasField('field_description') ? ($course->get('field_description')->value ?? '') : '',
      'short_description' => $course->hasField('field_short_description') ? ($course->get('field_short_description')->value ?? '') : '',
      'what_learn' => $course->hasField('field_what_learn') ? ($course->get('field_what_learn')->value ?? '') : '',
      'prerequisites' => $course->hasField('field_prerequisites') ? ($course->get('field_prerequisites')->value ?? '') : '',
      'who_for' => $course->hasField('field_who_for') ? ($course->get('field_who_for')->value ?? '') : '',
      'thumbnail_url' => $this->getImageUrl($course, 'field_thumbnail'),
      'banner_url' => $this->getImageUrl($course, 'field_banner'),
      'price' => (float) ($course->hasField('field_price') ? ($course->get('field_price')->value ?? 0) : 0),
      'price_type' => $course->hasField('field_price_type') ? ($course->get('field_price_type')->value ?? 'free') : 'free',
      'level' => $this->getTermData($course, 'field_level'),
      'category' => $this->getTermData($course, 'field_category'),
      'tags' => $tags,
      'instructors' => $instructors,
      'rating' => (float) ($course->hasField('field_rating') ? ($course->get('field_rating')->value ?? 0) : 0),
      'rating_count' => $rating_count,
      'student_count' => $this->getStudentCount((int) $courseId),
      'duration_minutes' => (int) ($course->hasField('field_duration') ? ($course->get('field_duration')->value ?? 0) : 0),
      'bestseller' => (bool) ($course->hasField('field_bestseller') ? $course->get('field_bestseller')->value : FALSE),
      'featured' => (bool) ($course->hasField('field_featured') ? $course->get('field_featured')->value : FALSE),
      'curriculum' => $curriculum_response,
      'enrollment' => $enrollment_response,
      'is_wishlisted' => $is_wishlisted,
      'certificate_template_id' => $course->hasField('field_certificate_template') && !$course->get('field_certificate_template')->isEmpty()
        ? (int) $course->get('field_certificate_template')->target_id
        : NULL,
      'created' => date('c', (int) $course->get('created')->value),
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/courses/{courseId}/enroll-free
  // ---------------------------------------------------------------------------

  /**
   * Enrolls the authenticated user in a free course.
   */
  public function enrollFree(Request $request, string $courseId): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $course = $this->entityTypeManager()->getStorage('lms_course')->load((int) $courseId);
    if (!$course || !$course->get('status')->value) {
      return $this->errorResponse('not_found', 'Course not found.', 404);
    }

    $price_type = $course->hasField('field_price_type') ? ($course->get('field_price_type')->value ?? 'free') : 'free';
    if ($price_type !== 'free') {
      return $this->errorResponse('not_free', 'This course requires payment.', 403);
    }

    // Check existing enrollment.
    $existing = $this->getUserEnrollment($uid, (int) $courseId);
    if ($existing) {
      return new JsonResponse([
        'error' => 'already_enrolled',
        'message' => 'You are already enrolled in this course.',
      ], 400);
    }

    // Determine first lesson.
    $first_lesson_id = NULL;
    if ($course->hasField('field_chapters') && !$course->get('field_chapters')->isEmpty()) {
      $chapters = $course->get('field_chapters')->referencedEntities();
      usort($chapters, fn($a, $b) => ($a->get('weight')->value ?? 0) <=> ($b->get('weight')->value ?? 0));
      foreach ($chapters as $chapter) {
        if (!$chapter->get('status')->value) {
          continue;
        }
        if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
          foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
            if ($lesson->get('status')->value) {
              $first_lesson_id = (int) $lesson->id();
              break 2;
            }
          }
        }
      }
    }

    // Create enrollment.
    $enrollment = $this->entityTypeManager()->getStorage('lms_user_course')->create([
      'uid' => $uid,
      'field_course' => (int) $courseId,
      'field_progress' => 0.0,
      'field_status' => 'enrolled',
    ]);
    $enrollment->save();

    return new JsonResponse([
      'status' => 'enrolled',
      'course_id' => (int) $courseId,
      'enrollment_id' => (int) $enrollment->id(),
      'message' => 'You have been enrolled successfully.',
      'first_lesson_id' => $first_lesson_id,
    ]);
  }

  // ---------------------------------------------------------------------------
  // GET /api/v1/dashboard
  // ---------------------------------------------------------------------------

  /**
   * Returns a personalized dashboard summary for the authenticated user.
   */
  public function getDashboard(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $enrollment_ids = $this->entityTypeManager()->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('field_enrolled_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $enrollments = $enrollment_ids
      ? $this->entityTypeManager()->getStorage('lms_user_course')->loadMultiple($enrollment_ids)
      : [];

    $total_enrolled = count($enrollments);
    $completed_count = 0;
    $in_progress_courses = [];

    foreach ($enrollments as $enrollment) {
      /** @var \Drupal\lms_course\Entity\Course|null $course */
      $course = $enrollment->get('field_course')->entity;
      if (!$course || !$course->get('status')->value) {
        continue;
      }

      $status = $enrollment->hasField('field_status') && !$enrollment->get('field_status')->isEmpty()
        ? $enrollment->get('field_status')->value
        : 'enrolled';

      if ($status === 'completed') {
        $completed_count++;
        continue;
      }

      // Limit in-progress to top 5.
      if (count($in_progress_courses) >= 5) {
        continue;
      }

      // Collect lesson IDs.
      $lesson_ids = [];
      if ($course->hasField('field_chapters') && !$course->get('field_chapters')->isEmpty()) {
        foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {
          if (!$chapter->get('status')->value) {
            continue;
          }
          if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
            foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
              if ($lesson->get('status')->value) {
                $lesson_ids[] = (int) $lesson->id();
              }
            }
          }
        }
      }

      $progress = $this->calculateProgress($uid, $lesson_ids);
      $last_lesson_id = NULL;
      if (!empty($lesson_ids)) {
        $completed_lesson_ids = $this->getCompletedLessonIds($uid, $lesson_ids);
        $last_lesson_id = $this->findLastLessonId($uid, $lesson_ids, $completed_lesson_ids);
      }

      $last_lesson_title = NULL;
      if ($last_lesson_id) {
        $last_lesson = $this->entityTypeManager()->getStorage('lms_lesson')->load($last_lesson_id);
        $last_lesson_title = $last_lesson ? $last_lesson->label() : NULL;
      }

      $in_progress_courses[] = [
        'id' => (int) $course->id(),
        'title' => $course->label(),
        'thumbnail_url' => $this->getImageUrl($course, 'field_thumbnail'),
        'progress' => $progress,
        'last_lesson_id' => $last_lesson_id,
        'last_lesson_title' => $last_lesson_title,
      ];
    }

    $cert_count = (int) $this->entityTypeManager()->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return new JsonResponse([
      'in_progress_courses' => $in_progress_courses,
      'completed_courses_count' => $completed_count,
      'certificates_count' => $cert_count,
      'enrolled_courses_count' => $total_enrolled,
    ]);
  }

  // ---------------------------------------------------------------------------
  // GET /api/v1/my-courses
  // ---------------------------------------------------------------------------

  /**
   * Lists courses the authenticated user is enrolled in.
   */
  public function getMyCourses(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $uid = (int) $user->id();

    $status = (string) $request->query->get('status', 'all');
    $page = max(0, (int) $request->query->get('page', 0));
    $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

    $query = $this->entityTypeManager()->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE);

    if ($status === 'completed') {
      $query->condition('field_status', 'completed');
    }
    elseif ($status === 'active') {
      $query->condition('field_status', ['enrolled', 'in_progress'], 'IN');
    }

    $count_query = clone $query;
    $total = (int) $count_query->count()->execute();

    $ids = $query
      ->sort('field_enrolled_at', 'DESC')
      ->range($page * $limit, $limit)
      ->execute();

    $items = [];
    $enrollments = $ids
      ? $this->entityTypeManager()->getStorage('lms_user_course')->loadMultiple($ids)
      : [];

    foreach ($enrollments as $enrollment) {
      $course = $enrollment->get('field_course')->entity;
      if (!$course || !$course->get('status')->value) {
        continue;
      }

      $lesson_ids = $this->getOrderedCourseLessonIds($course);
      $completed_lesson_ids = $this->getCompletedLessonIds($uid, $lesson_ids);
      $last_lesson_id = $this->findLastLessonId($uid, $lesson_ids, $completed_lesson_ids);
      $last_lesson_title = NULL;
      if ($last_lesson_id) {
        $last_lesson = $this->entityTypeManager()->getStorage('lms_lesson')->load($last_lesson_id);
        $last_lesson_title = $last_lesson?->label();
      }

      $primary_instructor = $this->getPrimaryInstructor($course);

      $items[] = [
        'enrollment_id' => (int) $enrollment->id(),
        'course_id' => (int) $course->id(),
        'course_title' => $course->label(),
        'course_thumbnail_url' => $this->getImageUrl($course, 'field_thumbnail'),
        'instructor_name' => $primary_instructor?->getDisplayName() ?? '',
        'progress' => (float) ($enrollment->hasField('field_progress') ? ($enrollment->get('field_progress')->value ?? 0) : 0),
        'status' => $enrollment->hasField('field_status') ? ($enrollment->get('field_status')->value ?? 'enrolled') : 'enrolled',
        'enrolled_at' => $enrollment->hasField('field_enrolled_at') && !$enrollment->get('field_enrolled_at')->isEmpty()
          ? date('c', (int) $enrollment->get('field_enrolled_at')->value)
          : NULL,
        'completed_at' => $enrollment->hasField('field_completed_at') && !$enrollment->get('field_completed_at')->isEmpty()
          ? date('c', (int) $enrollment->get('field_completed_at')->value)
          : NULL,
        'last_lesson_id' => $last_lesson_id,
        'last_lesson_title' => $last_lesson_title,
        'total_lessons' => count($lesson_ids),
        'completed_lessons' => count($completed_lesson_ids),
      ];
    }

    return new JsonResponse([
      'total' => $total,
      'page' => $page,
      'limit' => $limit,
      'items' => $items,
    ]);
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  /**
   * Resolves the authenticated user from the Bearer token in the request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  protected function getAuthenticatedUser(Request $request): \Drupal\user\UserInterface {
    $auth_header = $request->headers->get('Authorization', '');

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

    /** @var \Drupal\user\UserInterface|null $user */
    $user = $this->entityTypeManager()->getStorage('user')->load($uid);

    if (!$user || !$user->isActive()) {
      throw new AccessDeniedHttpException('User account not found or inactive.');
    }

    return $user;
  }

  /**
   * Returns a JSON error response.
   */
  protected function errorResponse(
    string $error,
    string $message,
    int $status = 400,
  ): JsonResponse {
    return new JsonResponse(['error' => $error, 'message' => $message], $status);
  }

  /**
   * Finds the lms_user_course enrollment for the given uid+courseId.
   */
  private function getUserEnrollment(int $uid, int $courseId): ?object {
    $ids = $this->entityTypeManager()->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_course', $courseId)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    return $ids
      ? $this->entityTypeManager()->getStorage('lms_user_course')->load(reset($ids))
      : NULL;
  }

  /**
   * Returns completed lesson IDs (lms_user_lesson status=completed) for a uid+lessonIds set.
   *
   * @param int[] $lesson_ids
   * @return int[]
   */
  private function getCompletedLessonIds(int $uid, array $lesson_ids): array {
    if (empty($lesson_ids)) {
      return [];
    }
    $ul_ids = $this->entityTypeManager()->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_ids, 'IN')
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->execute();

    $completed = [];
    if ($ul_ids) {
      foreach ($this->entityTypeManager()->getStorage('lms_user_lesson')->loadMultiple($ul_ids) as $ul) {
        $completed[] = (int) $ul->get('field_lesson')->target_id;
      }
    }
    return $completed;
  }

  /**
   * Calculates progress percentage for a user over a set of lesson IDs.
   */
  private function calculateProgress(int $uid, array $lesson_ids): float {
    if (empty($lesson_ids)) {
      return 0.0;
    }
    $completed_count = (int) $this->entityTypeManager()->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_ids, 'IN')
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return (float) floor(($completed_count / count($lesson_ids)) * 100);
  }

  /**
   * Finds the next incomplete lesson ID, or the first lesson if none is in progress.
   *
   * @param int   $uid
   * @param int[] $all_lesson_ids   Ordered lesson IDs for the course.
   * @param int[] $completed_lesson_ids
   */
  private function findLastLessonId(int $uid, array $all_lesson_ids, array $completed_lesson_ids): ?int {
    if (empty($all_lesson_ids)) {
      return NULL;
    }
    // First lesson not yet completed.
    foreach ($all_lesson_ids as $lid) {
      if (!in_array($lid, $completed_lesson_ids)) {
        return $lid;
      }
    }
    // All completed — return last lesson.
    return end($all_lesson_ids) ?: NULL;
  }

  /**
   * Counts enrolled students for a course.
   */
  private function getStudentCount(int $courseId): int {
    return (int) $this->entityTypeManager()->getStorage('lms_user_course')
      ->getQuery()
      ->condition('field_course', $courseId)
      ->accessCheck(FALSE)
      ->count()
      ->execute();
  }

  /**
   * Returns the absolute URL for an image field, or empty string.
   */
  private function getImageUrl(object $entity, string $field_name): string {
    if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return '';
    }
    $file = $entity->get($field_name)->entity;
    if (!$file) {
      return '';
    }
    return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
  }

  /**
   * Returns {id, name} for a single-value taxonomy term field.
   */
  private function getTermData(object $entity, string $field_name): ?array {
    if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return NULL;
    }
    $term = $entity->get($field_name)->entity;
    if (!$term) {
      return NULL;
    }
    return ['id' => (int) $term->id(), 'name' => $term->label()];
  }

  /**
   * Builds a minimal instructor data array from a user entity.
   */
  private function buildInstructorData(\Drupal\user\UserInterface $user): array {
    $avatar_url = '';
    if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
      $file = $user->get('user_picture')->entity;
      if ($file) {
        $avatar_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    $bio = '';
    if ($user->hasField('field_bio') && !$user->get('field_bio')->isEmpty()) {
      $bio = $user->get('field_bio')->value ?? '';
    }

    return [
      'id' => (int) $user->id(),
      'name' => $user->getDisplayName(),
      'avatar_url' => $avatar_url,
      'bio' => $bio,
    ];
  }

  /**
   * Builds a compact course list item for search responses.
   */
  private function buildCourseListItem(object $course): array {
    $primary_instructor = $this->getPrimaryInstructor($course);
    $rating_count = (int) $this->entityTypeManager()->getStorage('lms_rating')
      ->getQuery()
      ->condition('field_course', (int) $course->id())
      ->condition('field_approved', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      'id' => (int) $course->id(),
      'uuid' => $course->uuid(),
      'title' => $course->label(),
      'short_description' => $course->hasField('field_short_description') ? ($course->get('field_short_description')->value ?? '') : '',
      'thumbnail_url' => $this->getImageUrl($course, 'field_thumbnail'),
      'price' => (float) ($course->hasField('field_price') ? ($course->get('field_price')->value ?? 0) : 0),
      'price_type' => $course->hasField('field_price_type') ? ($course->get('field_price_type')->value ?? 'free') : 'free',
      'level' => $this->getTermData($course, 'field_level'),
      'category' => $this->getTermData($course, 'field_category'),
      'instructor' => $primary_instructor ? $this->buildInstructorData($primary_instructor) : NULL,
      'rating' => (float) ($course->hasField('field_rating') ? ($course->get('field_rating')->value ?? 0) : 0),
      'rating_count' => $rating_count,
      'student_count' => $this->getStudentCount((int) $course->id()),
      'duration_minutes' => (int) ($course->hasField('field_duration') ? ($course->get('field_duration')->value ?? 0) : 0),
      'bestseller' => (bool) ($course->hasField('field_bestseller') ? $course->get('field_bestseller')->value : FALSE),
      'featured' => (bool) ($course->hasField('field_featured') ? $course->get('field_featured')->value : FALSE),
      'created' => date('c', (int) $course->get('created')->value),
    ];
  }

  /**
   * Returns ordered lesson IDs for a course.
   *
   * @return int[]
   */
  private function getOrderedCourseLessonIds(object $course): array {
    $lesson_ids = [];
    if (!$course->hasField('field_chapters') || $course->get('field_chapters')->isEmpty()) {
      return $lesson_ids;
    }

    $chapters = $course->get('field_chapters')->referencedEntities();
    usort($chapters, fn($a, $b) => ($a->get('weight')->value ?? 0) <=> ($b->get('weight')->value ?? 0));

    foreach ($chapters as $chapter) {
      if (!$chapter->get('status')->value) {
        continue;
      }
      if (!$chapter->hasField('field_lessons') || $chapter->get('field_lessons')->isEmpty()) {
        continue;
      }
      foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
        if ($lesson->get('status')->value) {
          $lesson_ids[] = (int) $lesson->id();
        }
      }
    }

    return $lesson_ids;
  }

  /**
   * Returns the primary instructor for a course.
   */
  private function getPrimaryInstructor(object $course): ?\Drupal\user\UserInterface {
    if ($course->hasField('field_instructor') && !$course->get('field_instructor')->isEmpty()) {
      $instructors = $course->get('field_instructor')->referencedEntities();
      $instructor = $instructors[0] ?? NULL;
      if ($instructor instanceof \Drupal\user\UserInterface) {
        return $instructor;
      }
    }
    $owner = $course->get('uid')->entity;
    return $owner instanceof \Drupal\user\UserInterface ? $owner : NULL;
  }

}
