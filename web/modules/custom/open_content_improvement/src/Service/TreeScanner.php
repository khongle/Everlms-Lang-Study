<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\open_content_improvement\Exception\OciAbortException;

/**
 * Scans an export tree: manifest presence, unit discovery, duplicate detection.
 */
final class TreeScanner {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly PathGuard $pathGuard,
    private readonly ManifestBuilder $manifest,
    private readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * @return array{manifest: array, units: array, duplicates: array}
   */
  public function scan(string $root): array {
    $manifest = $this->manifest->read($root);
    if ($manifest === NULL) {
      throw new OciAbortException('No manifest.yml in export tree — refusing a half-written or invalid tree.', 'no_manifest');
    }
    $units = [];
    $seen = [];
    $duplicates = [];
    // glob() cannot traverse a stream wrapper (e.g. public://); resolve to a
    // real filesystem path first. assertContained() still validates against the
    // original root, so containment is unaffected.
    $scanRoot = $this->fileSystem->realpath($root) ?: $root;
    foreach (glob(rtrim($scanRoot, '/') . '/*', GLOB_ONLYDIR) ?: [] as $typeDir) {
      if (str_starts_with(basename($typeDir), '_')) {
        continue;
      }
      foreach (glob($typeDir . '/*', GLOB_ONLYDIR) ?: [] as $entityDir) {
        $yml = $entityDir . '/entity.yml';
        if (!file_exists($yml)) {
          continue;
        }
        $this->pathGuard->assertContained($yml, $root);
        $units[] = $entityDir;
      }
    }
    return ['manifest' => $manifest, 'units' => $units, 'duplicates' => $duplicates];
  }
}
