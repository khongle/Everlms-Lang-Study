<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProgressApiController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  public function markComplete(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Not authenticated'], 401);
    }

    $content = json_decode($request->getContent(), TRUE);
    $course_id = $content['course_id'] ?? NULL;
    $lesson_id = $content['lesson_id'] ?? NULL;

    if (!$course_id || !$lesson_id) {
      return new JsonResponse(['error' => 'Missing parameter'], 400);
    }

    $course = $this->entityTypeManager->getStorage('lms_course')->load($course_id);
    $lesson = $this->entityTypeManager->getStorage('lms_lesson')->load($lesson_id);

    if (!$course || !$lesson) {
      return new JsonResponse(['error' => 'Invalid entities'], 404);
    }

    // 1. Mark lesson as complete
    $progress_storage = $this->entityTypeManager->getStorage('lms_user_lesson');
    $progress_ids = $progress_storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->execute();

    $now = time();
    $lesson_completed = FALSE;

    if ($progress_ids) {
      $progress = $progress_storage->load(reset($progress_ids));
      if ($progress->get('field_status')->value !== 'completed') {
        $progress->set('field_status', 'completed');
        $progress->set('field_completed_at', $now);
        $progress->save();
        $lesson_completed = TRUE;
      }
    } else {
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

    // 2. Determine next lesson URL and calculate total complete
    $next_lesson_url = NULL;
    $chapters = $course->get('field_chapters')->referencedEntities();
    $all_lessons = [];
    foreach ($chapters as $chapter) {
      if ($chapter->get('status')->value && $chapter->hasField('field_lessons')) {
        foreach ($chapter->get('field_lessons')->referencedEntities() as $cl) {
          if ($cl->get('status')->value) {
            $all_lessons[] = $cl->id();
          }
        }
      }
    }

    // Find next lesson to redirect
    foreach ($all_lessons as $idx => $lid) {
      if ($lid == $lesson_id) {
        if (isset($all_lessons[$idx + 1])) {
          $next_lesson_url = \Drupal\Core\Url::fromRoute('lms_progress.lesson_view', [
            'lms_course' => $course_id,
            'lms_lesson' => $all_lessons[$idx + 1]
          ])->toString();
        }
        break;
      }
    }

    // 3. Update Course Progress
    $completed_count = 0;
    foreach ($all_lessons as $lid) {
      // Fetch if this lesson is completed by the user
      $p_ids = $progress_storage->getQuery()
        ->condition('uid', $uid)
        ->condition('field_lesson', $lid)
        ->condition('field_status', 'completed')
        ->accessCheck(FALSE)
        ->execute();
      if (!empty($p_ids)) {
        $completed_count++;
      }
    }

    $total_lessons = count($all_lessons);
    $progress_percentage = $total_lessons > 0 ? floor(($completed_count / $total_lessons) * 100) : 0;

    $enrollment_storage = $this->entityTypeManager->getStorage('lms_user_course');
    $e_ids = $enrollment_storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_course', $course_id)
      ->accessCheck(FALSE)
      ->execute();

    $course_completed = FALSE;

    if ($e_ids) {
      $enrollment = $enrollment_storage->load(reset($e_ids));
      $enrollment->set('field_progress', $progress_percentage);
      
      if ($progress_percentage >= 100 && $enrollment->get('field_status')->value !== 'completed') {
        $enrollment->set('field_status', 'completed');
        $enrollment->set('field_completed_at', $now);
        $course_completed = TRUE;
      }
      $enrollment->save();
    }

    return new JsonResponse([
      'success' => TRUE,
      'lesson_completed' => $lesson_completed,
      'course_completed' => $course_completed,
      'progress' => $progress_percentage,
      'next_lesson_url' => $next_lesson_url,
    ]);
  }
}
