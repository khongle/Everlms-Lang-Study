<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;

/**
 * Tier 3: export-only display text held in config (opt-in, default OFF).
 * Walks TypedConfigManager for translatable leaves; never imports.
 */
final class ConfigTextExporter {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly TypedConfigManagerInterface $typedConfig,
    private readonly StorageInterface $configStorage,
  ) {}

  /**
   * @return int
   *   Number of config text leaves written.
   */
  public function export(string $dir): int {
    $settings = $this->configFactory->get('open_content_improvement.settings');
    if (!$settings->get('export_config_text')) {
      return 0;
    }
    $denyPaths = $settings->get('config_deny_paths') ?: [];
    $target = rtrim($dir, '/') . '/_readonly/config';
    @mkdir($target, 0775, TRUE);
    $written = 0;
    foreach ($this->configStorage->listAll() as $name) {
      $data = $this->configFactory->get($name)->getRawData();
      $leaves = [];
      $this->collectStrings($name, '', $data, $denyPaths, $leaves);
      if ($leaves) {
        file_put_contents($target . '/' . $name . '.yml', "# read-only config text (tier 3)\n" . \Symfony\Component\Yaml\Yaml::dump($leaves, PHP_INT_MAX, 2));
        $written += count($leaves);
      }
    }
    file_put_contents($target . '/README.md', "# Tier 3 — config display text (read-only)\n\nThis is exported for review only and is never imported.\n");
    return $written;
  }

  private function collectStrings(string $configName, string $path, mixed $data, array $denyPaths, array &$out): void {
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $this->collectStrings($configName, $path === '' ? (string) $k : $path . '.' . $k, $v, $denyPaths, $out);
      }
      return;
    }
    if (!is_string($data) || $data === '') {
      return;
    }
    $leaf = $configName . ':' . $path;
    foreach ($denyPaths as $glob) {
      if (fnmatch($glob, $leaf)) {
        return;
      }
    }
    // Heuristic: keep leaves that look like display text (contain a space).
    if (str_contains($data, ' ')) {
      $out[$path] = $data;
    }
  }
}
