<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\open_content_improvement\Exception\OciRefusalException;

/**
 * Path safety: no traversal, no symlinks, always contained under the root.
 */
final class PathGuard {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly StreamWrapperManagerInterface $streamWrapperManager,
  ) {}

  public function assertBasename(string $p): void {
    $base = basename($p);
    if ($base !== $p || $p === '' || $p === '.' || $p === '..' || str_contains($p, '/') || str_contains($p, "\0")) {
      throw new OciRefusalException(sprintf('Unsafe basename: %s', $p), 'path_unsafe');
    }
  }

  /**
   * Real, contained path or refusal.
   */
  public function assertContained(string $path, string $root): string {
    $realRoot = $this->realpath($root);
    $realPath = $this->realpath($path);
    if ($realRoot === FALSE || $realPath === FALSE) {
      // Path may not exist yet (write target); check textual containment.
      $normRoot = rtrim($this->normalize($root), '/');
      $norm = $this->normalize($path);
      if (!str_starts_with($norm, $normRoot . '/') && $norm !== $normRoot) {
        throw new OciRefusalException(sprintf('Path escapes export root: %s', $path), 'path_escape');
      }
      return $path;
    }
    if (!str_starts_with($realPath, rtrim($realRoot, '/') . '/') && $realPath !== $realRoot) {
      throw new OciRefusalException(sprintf('Path escapes export root: %s', $path), 'path_escape');
    }
    return $realPath;
  }

  public function assertNotSymlink(string $p): void {
    $real = $this->fileSystem->realpath($p) ?: $p;
    if (is_link($real)) {
      throw new OciRefusalException(sprintf('Symlink refused: %s', $p), 'symlink');
    }
  }

  private function realpath(string $uri): string|false {
    $local = $this->fileSystem->realpath($uri);
    return $local !== FALSE ? $local : realpath($uri);
  }

  private function normalize(string $uri): string {
    $local = $this->fileSystem->realpath($uri);
    if ($local !== FALSE) {
      return $local;
    }
    // Collapse ../ segments textually for containment check.
    $parts = [];
    foreach (explode('/', str_replace('\\', '/', $uri)) as $seg) {
      if ($seg === '..') {
        array_pop($parts);
      }
      elseif ($seg !== '.' && $seg !== '') {
        $parts[] = $seg;
      }
    }
    return '/' . implode('/', $parts);
  }
}
