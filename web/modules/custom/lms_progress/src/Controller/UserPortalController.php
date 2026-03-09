<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_lesson\Entity\Lesson;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User portal pages: dashboard, my courses, lesson view.
 */
final class UserPortalController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
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
    );
  }

  /**
   * Dashboard — overview with stats and recent activity.
   */
  public function dashboard(): array {
    $uid = (int) $this->currentUser->id();

    // Enrolled courses.
    $enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->execute();
    $enrollments = $enrollment_ids
      ? $this->entityTypeManager->getStorage('lms_user_course')->loadMultiple($enrollment_ids)
      : [];

    // Build enrollment data.
    $enrolled_courses = [];
    $in_progress = 0;
    $completed = 0;
    foreach ($enrollments as $enrollment) {
      $course = $enrollment->get('field_course')->entity;
      if (!$course) {
        continue;
      }
      $status = $enrollment->get('field_status')->value;
      if ($status === 'completed') {
        $completed++;
      }
      else {
        $in_progress++;
      }
      $enrolled_courses[] = [
        'course' => $course,
        'progress' => (float) $enrollment->get('field_progress')->value,
        'status' => $status,
        'enrolled_at' => $enrollment->get('field_enrolled_at')->value,
      ];
    }

    // Certificates.
    $cert_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'lms_dashboard',
      '#stats' => [
        'enrolled' => count($enrollments),
        'in_progress' => $in_progress,
        'completed' => $completed,
        'certificates' => $cert_count,
      ],
      '#recent_courses' => array_slice($enrolled_courses, 0, 4),
    ];
  }

  /**
   * My Courses — list of enrolled courses.
   */
  public function myCourses(): array {
    $uid = (int) $this->currentUser->id();

    $enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('field_enrolled_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $enrollments = $enrollment_ids
      ? $this->entityTypeManager->getStorage('lms_user_course')->loadMultiple($enrollment_ids)
      : [];

    $courses = [];
    foreach ($enrollments as $enrollment) {
      $course = $enrollment->get('field_course')->entity;
      if (!$course) {
        continue;
      }
      $courses[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'progress' => (float) $enrollment->get('field_progress')->value,
        'status' => $enrollment->get('field_status')->value,
        'url' => $course->toUrl()->toString(),
        'thumbnail' => $course->get('field_thumbnail')->entity,
      ];
    }

    return [
      '#theme' => 'lms_my_courses',
      '#courses' => $courses,
    ];
  }

  /**
   * Lesson view — learning page with video/content.
   */
  public function lessonView(Course $lms_course, Lesson $lms_lesson): array {
    $uid = (int) $this->currentUser->id();

    // Get all chapters in course for sidebar.
    $chapter_ids = $this->entityTypeManager->getStorage('lms_chapter')
      ->getQuery()
      ->condition('field_course', $lms_course->id())
      ->condition('status', TRUE)
      ->sort('weight')
      ->accessCheck(FALSE)
      ->execute();
    $chapters = $chapter_ids
      ? $this->entityTypeManager->getStorage('lms_chapter')->loadMultiple($chapter_ids)
      : [];

    $curriculum = [];
    foreach ($chapters as $chapter) {
      $lesson_ids = $this->entityTypeManager->getStorage('lms_lesson')
        ->getQuery()
        ->condition('field_chapter', $chapter->id())
        ->condition('status', TRUE)
        ->sort('weight')
        ->accessCheck(FALSE)
        ->execute();
      $lessons = $lesson_ids
        ? $this->entityTypeManager->getStorage('lms_lesson')->loadMultiple($lesson_ids)
        : [];
      $curriculum[] = [
        'chapter' => $chapter,
        'lessons' => $lessons,
      ];
    }

    // Get/create user lesson progress.
    $progress_ids = $this->entityTypeManager->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lms_lesson->id())
      ->accessCheck(FALSE)
      ->execute();

    $video_position = 0;
    if ($progress_ids) {
      $progress = $this->entityTypeManager->getStorage('lms_user_lesson')
        ->load(reset($progress_ids));
      if ($progress) {
        $video_position = (int) $progress->get('field_video_position')->value;
      }
    }

    return [
      '#theme' => 'lms_lesson_view',
      '#course' => $lms_course,
      '#lesson' => $lms_lesson,
      '#curriculum' => $curriculum,
      '#video_position' => $video_position,
    ];
  }

  /**
   * My Certificates.
   */
  public function myCertificates(): array {
    $uid = (int) $this->currentUser->id();

    $cert_ids = $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('field_issued_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $certificates = $cert_ids
      ? $this->entityTypeManager->getStorage('lms_user_certificate')->loadMultiple($cert_ids)
      : [];

    $certs = [];
    foreach ($certificates as $cert) {
      $template = $cert->get('field_template')->entity;
      $course = $cert->get('field_course')->entity;
      $certs[] = [
        'id' => $cert->id(),
        'template_name' => $template?->label() ?? '',
        'course_name' => $course?->label() ?? '',
        'certificate_number' => $cert->get('field_certificate_number')->value,
        'issued_at' => $cert->get('field_issued_at')->value,
        'pdf' => $cert->get('field_pdf_file')->entity,
      ];
    }

    return [
      '#theme' => 'lms_my_certificates',
      '#certificates' => $certs,
    ];
  }

  /**
   * User profile page.
   */
  public function profile(): array {
    $uid = (int) $this->currentUser->id();
    $account = $this->entityTypeManager->getStorage('user')->load($uid);

    $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $cert_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'lms_profile',
      '#user_data' => [
        'uid' => $uid,
        'name' => $account?->getDisplayName() ?? '',
        'email' => $account?->getEmail() ?? '',
        'created' => $account?->getCreatedTime() ?? 0,
      ],
      '#enrollment_count' => $enrollment_count,
      '#certificate_count' => $cert_count,
    ];
  }

}
