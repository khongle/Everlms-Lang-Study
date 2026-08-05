<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\File\FileSystemInterface;

/**
 * Packs entity directories into character-budgeted batches for hand-off.
 */
final class BatchPlanner {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly DocGenerator $docGenerator,
  ) {}

  /**
   * @return array{batches: array, oversize: array}
   */
  public function plan(string $root, int $maxChars): array {
    $batches = [];
    $oversize = [];
    $current = ['dirs' => [], 'size' => 0];
    $n = 0;
    foreach ($this->entityDirs($root) as $dir) {
      $size = $this->dirSize($dir);
      if ($size > $maxChars) {
        $oversize[] = $dir;
        continue;
      }
      if ($current['size'] + $size > $maxChars && $current['dirs']) {
        $batches[$n++] = $current;
        $current = ['dirs' => [], 'size' => 0];
      }
      $current['dirs'][] = $dir;
      $current['size'] += $size;
    }
    if ($current['dirs']) {
      $batches[$n] = $current;
    }
    return ['batches' => $batches, 'oversize' => $oversize];
  }

  private function entityDirs(string $root): array {
    $dirs = [];
    foreach (glob(rtrim($root, '/') . '/*', GLOB_ONLYDIR) ?: [] as $typeDir) {
      if (str_starts_with(basename($typeDir), '_')) {
        continue;
      }
      foreach (glob($typeDir . '/*', GLOB_ONLYDIR) ?: [] as $entityDir) {
        $dirs[] = $entityDir;
      }
    }
    return $dirs;
  }

  private function dirSize(string $dir): int {
    $total = 0;
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
      $total += $file->getSize();
    }
    return $total;
  }
}
