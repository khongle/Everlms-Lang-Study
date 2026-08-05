<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Copies binary files into and out of the export tree. The file module is
 * optional — when absent, Tier 2 degrades noisily.
 */
final class BinaryStore {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly StreamWrapperManagerInterface $streamWrapperManager,
    private readonly ?object $fileRepository,
    private readonly ?object $fileValidator,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly PathGuard $pathGuard,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
  ) {}

  public function available(): bool {
    return $this->moduleHandler->moduleExists('file');
  }

  /**
   * Copy the referenced file into $dir/files/, return the _files record.
   *
   * @return array
   *   {exported:bool, skip_reason?:string, target_id?:int, filename?:string,
   *    file?:string, sha256?:string, alt?, title?, description?}
   */
  public function exportItem(FieldItemInterface $item, string $dir): array {
    $config = $this->configFactory->get('open_content_improvement.settings');
    $value = $item->getValue();
    $record = array_intersect_key($value, ['target_id' => 1, 'alt' => 1, 'title' => 1, 'description' => 1, 'display' => 1]);
    if (!$this->available()) {
      return ['exported' => FALSE, 'skip_reason' => 'file_module_absent'] + $record;
    }
    if (!$config->get('include_binaries')) {
      return ['exported' => FALSE, 'skip_reason' => 'binaries_disabled'] + $record;
    }
    $fid = $value['target_id'] ?? NULL;
    if (!$fid) {
      return ['exported' => FALSE, 'skip_reason' => 'no_target'] + $record;
    }
    try {
      $file = $this->entityTypeManager->getStorage('file')->load($fid);
    }
    catch (\Throwable $e) {
      return ['exported' => FALSE, 'skip_reason' => 'file_load_failed'] + $record;
    }
    if ($file === NULL) {
      return ['exported' => FALSE, 'skip_reason' => 'file_missing'] + $record;
    }
    $uri = $file->getFileUri();
    $scheme = $this->streamWrapperManager->getScheme($uri) ?: 'public';
    $allowed = $config->get('exportable_schemes') ?: ['public'];
    if (!in_array($scheme, $allowed, TRUE)) {
      return ['exported' => FALSE, 'skip_reason' => 'scheme_not_exportable', 'filename' => $file->getFilename()] + $record;
    }
    $size = @filesize($uri);
    $max = (int) ($config->get('max_export_filesize') ?: 20971520);
    if ($size !== FALSE && $size > $max) {
      return ['exported' => FALSE, 'skip_reason' => 'too_large', 'filename' => $file->getFilename()] + $record;
    }
    $filesDir = rtrim($dir, '/') . '/files';
    if (!is_dir($filesDir)) {
      @mkdir($filesDir, 0775, TRUE);
    }
    $basename = $fid . '--' . $file->getFilename();
    $this->pathGuard->assertBasename($basename);
    $dest = $filesDir . '/' . $basename;
    $bytes = @file_get_contents($uri);
    if ($bytes === FALSE) {
      return ['exported' => FALSE, 'skip_reason' => 'unreadable', 'filename' => $file->getFilename()] + $record;
    }
    file_put_contents($dest, $bytes);
    return [
      'exported' => TRUE,
      'target_id' => (int) $fid,
      'filename' => $file->getFilename(),
      'file' => 'files/' . $basename,
      'sha256' => hash('sha256', $bytes),
    ] + $record;
  }

  /**
   * Import a file record; v1 only writes editable alt/title/description columns.
   * Binary replacement is out of scope for v1 (returns NULL = keep existing fid).
   */
  public function importItem(array $record): ?int {
    return NULL;
  }

  public function promote(array $fids): void {
    if (!$this->available() || !$fids) {
      return;
    }
    try {
      $files = $this->entityTypeManager->getStorage('file')->loadMultiple($fids);
      foreach ($files as $file) {
        if (method_exists($file, 'setPermanent')) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
    catch (\Throwable $e) {
      $this->logger->warning('Could not promote imported files: @m', ['@m' => $e->getMessage()]);
    }
  }
}
