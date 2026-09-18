<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Controller;

use Drupal\Component\Utility\Bytes;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\lms_listening\Entity\BackgroundMusicTrack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages the shared Background Music Track library (see
 * BackgroundMusicTrack's docblock for the product shape) — a standalone
 * page plus two AJAX endpoints, deliberately not a Drupal entity-form CRUD
 * UI since there's nothing to edit once uploaded, only add/remove.
 *
 * Follows ListeningApiController's file-upload conventions (same
 * validate-extension-then-size-then-fileRepository->writeData() shape as
 * its saveUploadedAudioFile(), same deleteFileIfUnused() cleanup pattern)
 * as an independent copy rather than a shared dependency, so neither
 * controller has to change if the other's upload handling evolves.
 */
final class BackgroundMusicController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected FileRepositoryInterface $fileRepository,
    protected FileSystemInterface $fileSystem,
    protected FileUsageInterface $fileUsage,
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
      $container->get('file.repository'),
      $container->get('file_system'),
      $container->get('file.usage'),
    );
  }

  /**
   * Page: /manage/listening-music — upload form + the current user's own
   * tracks (with a Delete action on each). Every published track from
   * every user is what actually populates the picker on the Passive
   * Listening page itself (see ListeningController::buildBgTracks()) — this
   * page only ever lists/manages the current user's own uploads, so nobody
   * can review or delete tracks they didn't add.
   */
  public function library(): array {
    $storage = $this->entityTypeManager->getStorage('lms_listening_bg_track');
    $ids = $storage->getQuery()
      ->condition('uid', (int) $this->currentUser->id())
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $urlGenerator = \Drupal::service('file_url_generator');
    $tracks = [];
    foreach ($storage->loadMultiple($ids) as $track) {
      /** @var \Drupal\lms_listening\Entity\BackgroundMusicTrack $track */
      $file = $track->get('field_file')->entity;
      if (!$file) {
        continue;
      }
      $tracks[] = [
        'id' => (int) $track->id(),
        'name' => $track->label(),
        'url' => $urlGenerator->generateAbsoluteString($file->getFileUri()),
      ];
    }

    return [
      '#theme' => 'lms_listening_music_library',
      '#tracks' => $tracks,
      '#attached' => [
        'library' => ['lms_listening/listening_music_library'],
        'drupalSettings' => [
          'lmsListeningMusicLibrary' => [
            'uploadUrl' => '/api/lms/listening/music',
            'deleteUrlTemplate' => '/api/lms/listening/music/__ID__/delete',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * POST /api/lms/listening/music — adds a track to the shared library.
   * multipart/form-data: `name` (optional, falls back to the uploaded
   * filename) + `file` (single upload, required).
   */
  public function upload(Request $request): JsonResponse {
    if (!$this->currentUser->id()) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $upload = $request->files->get('file');
    if (!$upload || !$upload->isValid()) {
      return new JsonResponse(['error' => 'Please choose an audio file to upload'], 400);
    }

    $storage = $this->entityTypeManager->getStorage('lms_listening_bg_track');
    $fieldDef = $storage->create()->get('field_file')->getFieldDefinition();
    $allowedExtensions = preg_split('/\s+/', trim((string) $fieldDef->getSetting('file_extensions'))) ?: [];
    $extension = strtolower($upload->getClientOriginalExtension());
    if (!in_array($extension, $allowedExtensions, TRUE)) {
      return new JsonResponse(['error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)], 400);
    }
    $maxSize = Bytes::toNumber((string) $fieldDef->getSetting('max_filesize'));
    if ($maxSize && $upload->getSize() > $maxSize) {
      return new JsonResponse(['error' => 'File exceeds the size limit'], 400);
    }

    $name = trim((string) $request->request->get('name', ''));
    if ($name === '') {
      $name = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME);
    }

    $directory = 'public://listening/bg-music/' . date('Y-m');
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    try {
      $file = $this->fileRepository->writeData(
        file_get_contents($upload->getPathname()),
        $directory . '/' . $upload->getClientOriginalName(),
        FileSystemInterface::EXISTS_RENAME
      );
    }
    catch (\Exception $e) {
      return new JsonResponse(['error' => 'Failed to save the audio file'], 500);
    }
    $file->setPermanent();
    $file->save();

    $track = $storage->create([
      'name' => $name,
      'field_file' => ['target_id' => $file->id()],
      'uid' => (int) $this->currentUser->id(),
      'status' => TRUE,
    ]);
    $track->save();

    $urlGenerator = \Drupal::service('file_url_generator');
    return new JsonResponse([
      'id' => (int) $track->id(),
      'name' => $track->label(),
      'url' => $urlGenerator->generateAbsoluteString($file->getFileUri()),
    ]);
  }

  /**
   * POST /api/lms/listening/music/{lms_listening_bg_track}/delete — owner
   * or `edit lms_course` only (BackgroundMusicAccessControlHandler).
   */
  public function delete(BackgroundMusicTrack $lms_listening_bg_track): JsonResponse {
    if (!$lms_listening_bg_track->access('delete', $this->currentUser)) {
      return new JsonResponse(['error' => 'Forbidden'], 403);
    }

    $file = $lms_listening_bg_track->get('field_file')->entity;
    $lms_listening_bg_track->delete();
    if ($file instanceof FileInterface && empty($this->fileUsage->listUsage($file))) {
      $file->delete();
    }

    return new JsonResponse(['success' => TRUE]);
  }

}
