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

      // Delete any attached files too — they have no purpose once the note
      // that references them is gone (same explicit-cascade pattern used
      // elsewhere in this codebase; entity reference fields don't cascade
      // deletes automatically).
      $fileStorage = $this->entityTypeManager->getStorage('file');
      foreach ($notes as $note) {
        $attachmentIds = array_column($note->get('field_attachments')->getValue(), 'target_id');
        if ($attachmentIds) {
          $fileStorage->delete($fileStorage->loadMultiple($attachmentIds));
        }
      }

      $storage->delete($notes);

      // Invalidate cache
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_note:' . $lesson_id . ':' . $uid]);

      return new JsonResponse(['status' => 'deleted']);
    }

    return new JsonResponse(['status' => 'not_found']);
  }

  /**
   * API endpoint to upload a file and attach it to the note for a lesson.
   *
   * Creates the note (with empty text) if one doesn't exist yet for this
   * (user, lesson) pair, so a student can attach a file before ever typing
   * note text.
   */
  public function uploadAttachment(Request $request): JsonResponse {
    $uid = $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $lesson_id = (int) $request->request->get('lesson_id');
    $course_id = (int) $request->request->get('course_id');
    if (!$lesson_id) {
      return new JsonResponse(['error' => 'Missing lesson_id'], 400);
    }

    $uploaded_file = $request->files->get('file');
    if (!$uploaded_file) {
      return new JsonResponse(['error' => 'No file uploaded'], 400);
    }

    $allowed_extensions = ['mp3', 'wav', 'pdf', 'jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower($uploaded_file->getClientOriginalExtension());
    if (!in_array($extension, $allowed_extensions, TRUE)) {
      return new JsonResponse(['error' => 'File type not allowed. Allowed: ' . implode(', ', $allowed_extensions)], 400);
    }

    $max_size = 10 * 1024 * 1024;
    if ($uploaded_file->getSize() > $max_size) {
      return new JsonResponse(['error' => 'File exceeds the 10 MB size limit'], 400);
    }

    $directory = 'public://notes/attachments/' . $uid;
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $fileSystem->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);

    $destination_uri = $directory . '/' . $uploaded_file->getClientOriginalName();

    try {
      /** @var \Drupal\file\FileRepositoryInterface $fileRepo */
      $fileRepo = \Drupal::service('file.repository');
      $file = $fileRepo->writeData(
        file_get_contents($uploaded_file->getPathname()),
        $destination_uri,
        \Drupal\Core\File\FileSystemInterface::EXISTS_RENAME
      );
    }
    catch (\Exception $e) {
      return new JsonResponse(['error' => 'Failed to save file: ' . $e->getMessage()], 500);
    }

    if (!$file) {
      return new JsonResponse(['error' => 'Failed to save file'], 500);
    }

    $file->setPermanent();
    $file->save();

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->execute();

    if ($nids) {
      $note = $storage->load(reset($nids));
    }
    else {
      $note = $storage->create([
        'uid' => $uid,
        'field_lesson' => $lesson_id,
        'field_course' => $course_id,
        'field_body' => ['value' => '', 'format' => 'basic_html'],
      ]);
    }

    $current_attachments = array_column($note->get('field_attachments')->getValue(), 'target_id');
    $current_attachments[] = $file->id();
    $note->set('field_attachments', $current_attachments);
    $note->save();

    \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_note:' . $lesson_id . ':' . $uid]);

    return new JsonResponse([
      'status' => 'uploaded',
      'file_id' => (int) $file->id(),
      'filename' => $file->getFilename(),
      'url' => \Drupal::service('file_url_generator')->generateString($file->getFileUri()),
      'mime' => $file->getMimeType(),
      'size' => (int) $file->getSize(),
    ]);
  }

  /**
   * API endpoint to detach and delete one attachment from a note.
   */
  public function removeAttachment(Request $request): JsonResponse {
    $uid = $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    $lesson_id = (int) ($data['lesson_id'] ?? 0);
    $file_id = (int) ($data['file_id'] ?? 0);
    if (!$lesson_id || !$file_id) {
      return new JsonResponse(['error' => 'Missing lesson_id or file_id'], 400);
    }

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lesson_id)
      ->accessCheck(FALSE)
      ->execute();

    if (!$nids) {
      return new JsonResponse(['error' => 'Note not found'], 404);
    }

    $note = $storage->load(reset($nids));
    // target_id comes back as a numeric string from the field API; cast to
    // int so the strict in_array()/array_diff() comparisons below actually
    // match against $file_id (also an int).
    $current_attachments = array_map('intval', array_column($note->get('field_attachments')->getValue(), 'target_id'));

    // Ownership check: only allow removing a file that is actually attached
    // to this user's own note for this lesson.
    if (!in_array($file_id, $current_attachments, TRUE)) {
      return new JsonResponse(['error' => 'File not attached to this note'], 403);
    }

    $remaining = array_values(array_diff($current_attachments, [$file_id]));
    $note->set('field_attachments', $remaining);
    $note->save();

    $fileStorage = $this->entityTypeManager->getStorage('file');
    $file = $fileStorage->load($file_id);
    if ($file) {
      $file->delete();
    }

    \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_note:' . $lesson_id . ':' . $uid]);

    return new JsonResponse(['status' => 'removed']);
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
      if ($note) {
        $attachments = [];
        foreach ($note->get('field_attachments')->referencedEntities() as $file) {
          $attachments[] = [
            'file_id' => (int) $file->id(),
            'filename' => $file->getFilename(),
            'url' => \Drupal::service('file_url_generator')->generateString($file->getFileUri()),
            'mime' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
          ];
        }

        if (!$note->get('field_body')->isEmpty() || !empty($attachments)) {
          return new JsonResponse([
            'status' => 'found',
            'note_text' => $note->get('field_body')->value,
            'attachments' => $attachments,
          ]);
        }
      }
    }

    return new JsonResponse(['status' => 'not_found', 'note_text' => '', 'attachments' => []]);
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
        'attachment_count' => $note->get('field_attachments')->count(),
      ];
    }

    return [
      '#theme' => 'lms_my_notes',
      '#grouped_notes' => $grouped_notes,
    ];
  }
}
