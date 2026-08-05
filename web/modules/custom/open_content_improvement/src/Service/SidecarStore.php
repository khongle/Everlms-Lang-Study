<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Stores long text values as byte-verbatim sidecar files under text/.
 */
final class SidecarStore {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly PathGuard $pathGuard,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  public function threshold(): int {
    return (int) ($this->configFactory->get('open_content_improvement.settings')->get('sidecar_threshold') ?: 200);
  }

  /**
   * @return array{file: string, sha256: string}
   */
  public function put(string $dir, string $field, int $delta, string $langcode, string $bytes): array {
    $textDir = rtrim($dir, '/') . '/text';
    if (!is_dir($textDir)) {
      @mkdir($textDir, 0775, TRUE);
    }
    $name = sprintf('%s.%d.%s.html', $field, $delta, $langcode);
    $this->pathGuard->assertBasename($name);
    $path = $textDir . '/' . $name;
    file_put_contents($path, $bytes);
    return ['file' => 'text/' . $name, 'sha256' => hash('sha256', $bytes)];
  }

  public function get(string $dir, string $relative): string {
    $path = rtrim($dir, '/') . '/' . ltrim($relative, '/');
    $bytes = file_get_contents($path);
    return $bytes === FALSE ? '' : $bytes;
  }

  public function hash(string $dir, string $relative): string {
    return hash('sha256', $this->get($dir, $relative));
  }
}
