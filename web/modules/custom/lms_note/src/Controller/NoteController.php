<?php

declare(strict_types=1);

namespace Drupal\lms_note\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for lms_note API and UI routes.
 */
final class NoteController extends ControllerBase {

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
      $container->get('current_user')
    );
  }

  /**
   * API endpoint to save or update a note for a lesson.
   */
  public function save(Request $request): JsonResponse {
    $uid = $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    if (empty($data['lesson_id']) || empty($data['course_id'])) {
      return new JsonResponse(['error' => 'Missing required fields: lesson_id, course_id'], 400);
    }

    $lesson_id = (int) $data['lesson_id'];
    $course_id = (int) $data['course_id'];
    $note_text = $data['note_text'] ?? '';
    $video_timestamp = (int) ($data['video_timestamp'] ?? 0);

    // Invalidate cache for the lesson view so the new note shows up.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_note:' . $lesson_id . ':' . $uid]);

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $query = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE);
    $nids = $query->execute();

    if ($nids) {
      $note = $storage->load(reset($nids));
      $note->set('field_body', ['value' => $note_text, 'format' => 'basic_html']);
      $note->set('field_course', $course_id);
      $note->set('field_video_timestamp', $video_timestamp);
      $note->save();
      return new JsonResponse(['status' => 'updated', 'id' => $note->id()]);
    }
    else {
      $note = $storage->create([
        'uid' => $uid,
        'field_lesson' => $lesson_id,
        'field_course' => $course_id,
        'field_body' => ['value' => $note_text, 'format' => 'basic_html'],
        'field_video_timestamp' => $video_timestamp,
      ]);
      $note->save();
      return new JsonResponse(['status' => 'created', 'id' => $note->id()]);
    }
  }

  /**
   * API endpoint to delete a note for a lesson.
   */
  public function delete(Request $request): JsonResponse {
    $uid = $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    if (empty($data['lesson_id'])) {
      return new JsonResponse(['error' => 'Missing required field: lesson_id'], 400);
    }

    $lesson_id = (int) $data['lesson_id'];

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->execute();

    if ($nids) {
      $notes = $storage->loadMultiple($nids);
      $storage->delete($notes);
      
      // Invalidate cache
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_note:' . $lesson_id . ':' . $uid]);
      
      return new JsonResponse(['status' => 'deleted']);
    }

    return new JsonResponse(['status' => 'not_found']);
  }

  /**
   * API endpoint to get a note for a lesson.
   */
  public function getNote(Request $request): JsonResponse {
    $uid = $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $lesson_id = (int) $request->query->get('lesson_id');
    if (!$lesson_id) {
      return new JsonResponse(['error' => 'Missing lesson_id'], 400);
    }

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->execute();

    if ($nids) {
      $note = $storage->load(reset($nids));
      if ($note && !$note->get('field_body')->isEmpty()) {
        return new JsonResponse([
          'status' => 'found',
          'note_text' => $note->get('field_body')->value
        ]);
      }
    }

    return new JsonResponse(['status' => 'not_found', 'note_text' => '']);
  }

  /**
   * User Portal Page: My Notes.
   */
  public function myNotes(): array {
    $uid = $this->currentUser->id();
    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->sort('changed', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $notes = $nids ? $storage->loadMultiple($nids) : [];
    
    $grouped_notes = [];
    foreach ($notes as $note) {
      $course = $note->get('field_course')->entity;
      $lesson = $note->get('field_lesson')->entity;
      
      if (!$course || !$lesson) {
        continue;
      }
      
      $cid = $course->id();
      if (!isset($grouped_notes[$cid])) {
        $grouped_notes[$cid] = [
          'course' => $course,
          'notes' => [],
        ];
      }
      
      $grouped_notes[$cid]['notes'][] = [
        'entity' => $note,
        'lesson' => $lesson,
        'text' => $note->get('field_body')->value,
        'timestamp' => $note->get('field_video_timestamp')->value,
        'last_updated' => $note->get('changed')->value,
      ];
    }

    return [
      '#theme' => 'lms_my_notes',
      '#grouped_notes' => $grouped_notes,
    ];
  }
}
