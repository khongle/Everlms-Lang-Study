<?php

declare(strict_types=1);

namespace Drupal\lms_note\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for lms_note API and UI routes.
 *
 * Originally built for course-video lesson notes only (keyed by
 * uid + field_lesson). Generalized (see NOTE_EXPANSION_PLAN.md) to cover
 * any "context" a student can jot a note about — a course lesson, a
 * Listening passage, a Reading passage — via the generic
 * field_context_type/field_context_id pair, while every existing
 * lesson_id/course_id caller (theme's js/main.js) keeps working completely
 * unchanged: resolveContext() below treats `lesson_id` as sugar for
 * `context_type: 'lesson'`.
 */
final class NoteController extends ControllerBase {

  /**
   * Context type => the entity type it points at. Used to validate an
   * incoming context_id actually resolves to a real entity of that kind
   * before a note gets attached to it.
   */
  private const CONTEXT_ENTITY_TYPES = [
    'lesson' => 'lms_lesson',
    'listening_passage' => 'lms_listening_passage',
    'reading_passage' => 'lms_reading_passage',
  ];

  /**
   * What js/note-widget.js's rich-text toolbar (Bold/Italic/Underline/
   * bullet+numbered lists) can actually produce — the note body is stored
   * as-is (no server-side text format/filter is applied when a note is
   * later rendered with the `|raw` Twig filter on /my-notes, same as this
   * entity's original lesson-note behavior), so this allow-list is the
   * only thing standing between a raw POST to this endpoint (this isn't
   * only reachable through the widget's own toolbar) and stored XSS.
   * Xss::filter() strips every tag not in this list (unwrapping, not
   * deleting, their contents) and strips all attributes regardless
   * (style=, onerror=, javascript: URIs, …) even off tags that ARE
   * allowed — there is nothing here worth an attribute for.
   */
  private const ALLOWED_BODY_TAGS = ['b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'p', 'div', 'br'];

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
   * Resolves the (context_type, context_id) pair a request is about, from
   * either input shape:
   *  - new: {context_type, context_id}
   *  - legacy: {lesson_id} (+ optionally course_id) — same as before this
   *    generalization, so the existing lesson-note widget's requests need
   *    no changes at all.
   *
   * Returns NULL if neither shape is present, or the type isn't one this
   * module knows about.
   */
  private function resolveContext(array $data): ?array {
    if (!empty($data['context_type']) && isset($data['context_id'])) {
      $type = (string) $data['context_type'];
      $id = (int) $data['context_id'];
      if (!isset(self::CONTEXT_ENTITY_TYPES[$type]) || $id <= 0) {
        return NULL;
      }
      return ['type' => $type, 'id' => $id];
    }

    if (!empty($data['lesson_id'])) {
      return ['type' => 'lesson', 'id' => (int) $data['lesson_id']];
    }

    return NULL;
  }

  /**
   * Whether $context_id resolves to a real, existing entity of the type
   * $context_type points at — the only access/validity check a note's
   * context gets (matches the existing lesson-note posture: any
   * authenticated user who reached that page can note it; the page's own
   * controller already gated visibility before the note widget rendered).
   */
  private function contextEntityExists(string $context_type, int $context_id): bool {
    $entity_type_id = self::CONTEXT_ENTITY_TYPES[$context_type] ?? NULL;
    if (!$entity_type_id) {
      return FALSE;
    }
    return (bool) $this->entityTypeManager->getStorage($entity_type_id)->load($context_id);
  }

  /**
   * Loads this user's existing note for a context, if any — the same
   * "one note per (uid, context)" lookup every endpoint below needs.
   */
  private function loadExistingNote(int $uid, string $context_type, int $context_id) {
    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_context_type', $context_type)
      ->condition('field_context_id', $context_id)
      ->accessCheck(FALSE)
      ->execute();

    return $nids ? $storage->load(reset($nids)) : NULL;
  }

  /**
   * Invalidates the cache tag(s) a saved/deleted note's context cares
   * about. The 'lesson' shape is kept byte-for-byte identical to the
   * original ('lms_note:<lesson_id>:<uid>') since
   * UserPortalController::lessonView() already depends on exactly that
   * string; every context type also gets a same-shaped
   * 'lms_note:<type>:<id>:<uid>' tag for any page that wants to declare a
   * cache dependency on it later.
   */
  private function invalidateNoteCacheTags(string $context_type, int $context_id, int $uid): void {
    $tags = ['lms_note:' . $context_type . ':' . $context_id . ':' . $uid];
    if ($context_type === 'lesson') {
      $tags[] = 'lms_note:' . $context_id . ':' . $uid;
    }
    \Drupal::service('cache_tags.invalidator')->invalidateTags($tags);
  }

  /**
   * API endpoint to save or update a note for a lesson.
   */
  public function save(Request $request): JsonResponse {
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    $context = $this->resolveContext($data);
    if ($context === NULL) {
      return new JsonResponse(['error' => 'Missing required fields: provide either context_type + context_id, or lesson_id.'], 400);
    }
    [$context_type, $context_id] = [$context['type'], $context['id']];

    if ($context_type === 'lesson' && empty($data['course_id'])) {
      return new JsonResponse(['error' => 'Missing required fields: lesson_id, course_id'], 400);
    }
    if (!$this->contextEntityExists($context_type, $context_id)) {
      return new JsonResponse(['error' => 'The item this note is about no longer exists.'], 400);
    }

    // Xss::filter(), not a plain string — see ALLOWED_BODY_TAGS's docblock:
    // this is a raw JSON POST endpoint, not a Drupal form, so nothing
    // upstream of this has already sanitized whatever HTML the client
    // sent (the widget's own toolbar only ever produces tags from that
    // list, but that's not something this endpoint can trust on its own).
    $note_text = Xss::filter((string) ($data['note_text'] ?? ''), self::ALLOWED_BODY_TAGS);
    $video_timestamp = (int) ($data['video_timestamp'] ?? 0);

    $this->invalidateNoteCacheTags($context_type, $context_id, $uid);

    $storage = $this->entityTypeManager->getStorage('lms_note');
    $note = $this->loadExistingNote($uid, $context_type, $context_id);

    if ($note) {
      $note->set('field_body', ['value' => $note_text, 'format' => 'basic_html']);
      if ($context_type === 'lesson') {
        $note->set('field_course', (int) $data['course_id']);
        $note->set('field_video_timestamp', $video_timestamp);
      }
      $note->save();
      return new JsonResponse(['status' => 'updated', 'id' => $note->id()]);
    }
    else {
      $values = [
        'uid' => $uid,
        'field_context_type' => $context_type,
        'field_context_id' => $context_id,
        'field_body' => ['value' => $note_text, 'format' => 'basic_html'],
      ];
      if ($context_type === 'lesson') {
        $values['field_lesson'] = $context_id;
        $values['field_course'] = (int) $data['course_id'];
        $values['field_video_timestamp'] = $video_timestamp;
      }
      $note = $storage->create($values);
      $note->save();
      return new JsonResponse(['status' => 'created', 'id' => $note->id()]);
    }
  }

  /**
   * API endpoint to delete a note for a lesson.
   */
  public function delete(Request $request): JsonResponse {
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    $context = $this->resolveContext($data);
    if ($context === NULL) {
      return new JsonResponse(['error' => 'Missing required fields: provide either context_type + context_id, or lesson_id.'], 400);
    }
    [$context_type, $context_id] = [$context['type'], $context['id']];

    $note = $this->loadExistingNote($uid, $context_type, $context_id);

    if ($note) {
      // Delete any attached files too — they have no purpose once the note
      // that references them is gone (same explicit-cascade pattern used
      // elsewhere in this codebase; entity reference fields don't cascade
      // deletes automatically).
      $fileStorage = $this->entityTypeManager->getStorage('file');
      $attachmentIds = array_column($note->get('field_attachments')->getValue(), 'target_id');
      if ($attachmentIds) {
        $fileStorage->delete($fileStorage->loadMultiple($attachmentIds));
      }

      $note->delete();

      $this->invalidateNoteCacheTags($context_type, $context_id, $uid);

      return new JsonResponse(['status' => 'deleted']);
    }

    return new JsonResponse(['status' => 'not_found']);
  }

  /**
   * API endpoint to get a note for a lesson.
   */
  public function getNote(Request $request): JsonResponse {
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $data = $request->query->all();
    $context = $this->resolveContext($data);
    if ($context === NULL) {
      return new JsonResponse(['error' => 'Missing required fields: provide either context_type + context_id, or lesson_id.'], 400);
    }
    [$context_type, $context_id] = [$context['type'], $context['id']];

    $note = $this->loadExistingNote($uid, $context_type, $context_id);

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

    return new JsonResponse(['status' => 'not_found', 'note_text' => '', 'attachments' => []]);
  }

  /**
   * API endpoint to upload a file and attach it to the note for a lesson.
   *
   * Creates the note (with empty text) if one doesn't exist yet for this
   * (user, lesson) pair, so a student can attach a file before ever typing
   * note text. Attachments stay a lesson-only capability for now (see
   * NOTE_EXPANSION_PLAN.md) — this endpoint keeps the original
   * lesson_id/course_id shape rather than gaining a context_type param.
   */
  public function uploadAttachment(Request $request): JsonResponse {
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
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

    $note = $this->loadExistingNote($uid, 'lesson', $lesson_id);

    if (!$note) {
      $storage = $this->entityTypeManager->getStorage('lms_note');
      $note = $storage->create([
        'uid' => $uid,
        'field_context_type' => 'lesson',
        'field_context_id' => $lesson_id,
        'field_lesson' => $lesson_id,
        'field_course' => $course_id,
        'field_body' => ['value' => '', 'format' => 'basic_html'],
      ]);
    }

    $current_attachments = array_column($note->get('field_attachments')->getValue(), 'target_id');
    $current_attachments[] = $file->id();
    $note->set('field_attachments', $current_attachments);
    $note->save();

    $this->invalidateNoteCacheTags('lesson', $lesson_id, $uid);

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
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
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

    $note = $this->loadExistingNote($uid, 'lesson', $lesson_id);
    if (!$note) {
      return new JsonResponse(['error' => 'Note not found'], 404);
    }

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

    $this->invalidateNoteCacheTags('lesson', $lesson_id, $uid);

    return new JsonResponse(['status' => 'removed']);
  }

  /**
   * User Portal Page: My Notes.
   */
  public function myNotes(): array {
    // AccountProxyInterface::id() is documented as returning int, but in
    // practice (this Drupal version) hands back a numeric string — an
    // explicit cast avoids a TypeError against every private helper
    // below, whose `int $uid` parameters are strictly typed.
    $uid = (int) $this->currentUser->id();
    $storage = $this->entityTypeManager->getStorage('lms_note');
    $nids = $storage->getQuery()
      ->condition('uid', $uid)
      ->sort('changed', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $notes = $nids ? $storage->loadMultiple($nids) : [];

    $grouped_notes = [];
    $listening_notes = [];
    $reading_notes = [];

    foreach ($notes as $note) {
      $context_type = $note->get('field_context_type')->value ?: 'lesson';

      if ($context_type === 'listening_passage') {
        $passage = $this->entityTypeManager->getStorage('lms_listening_passage')->load($note->get('field_context_id')->value);
        if (!$passage) {
          continue;
        }
        $listening_notes[] = [
          'entity' => $note,
          'title' => $passage->label(),
          'passage_id' => $passage->id(),
          'text' => $note->get('field_body')->value,
          'last_updated' => $note->get('changed')->value,
        ];
        continue;
      }

      if ($context_type === 'reading_passage') {
        $passage = $this->entityTypeManager->getStorage('lms_reading_passage')->load($note->get('field_context_id')->value);
        if (!$passage) {
          continue;
        }
        $reading_notes[] = [
          'entity' => $note,
          'title' => $passage->label(),
          'passage_id' => $passage->id(),
          'text' => $note->get('field_body')->value,
          'last_updated' => $note->get('changed')->value,
        ];
        continue;
      }

      // Everything else is a course-lesson note — original behavior,
      // unchanged.
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
      '#listening_notes' => $listening_notes,
      '#reading_notes' => $reading_notes,
    ];
  }

}
