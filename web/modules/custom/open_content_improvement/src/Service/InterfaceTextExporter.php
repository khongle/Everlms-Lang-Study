<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Tier 4: export-only interface translation inventory (opt-in, default OFF).
 * Reads via StringStorageInterface (locale.storage), never raw SQL. Degrades
 * noisily when the locale module is absent.
 */
final class InterfaceTextExporter {

  public function __construct(
    private readonly ?object $localeStorage,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly LanguageManagerInterface $languageManager,
  ) {}

  public function export(string $dir): int {
    $target = rtrim($dir, '/') . '/_readonly/interface';
    @mkdir($target, 0775, TRUE);
    if (!$this->moduleHandler->moduleExists('locale') || $this->localeStorage === NULL) {
      file_put_contents($target . '/README.md', "# Tier 4 — interface text\n\nThe locale module is not enabled, so interface strings were not exported.\n");
      return 0;
    }
    $strings = $this->localeStorage->getStrings([]);
    $out = [];
    foreach ($strings as $string) {
      $source = method_exists($string, 'getString') ? $string->getString() : (string) ($string->source ?? '');
      if ($source !== '') {
        $out[] = $source;
      }
    }
    file_put_contents($target . '/strings.yml', "# read-only interface strings (tier 4)\n" . \Symfony\Component\Yaml\Yaml::dump(['sources' => $out], PHP_INT_MAX, 2));
    file_put_contents($target . '/README.md', "# Tier 4 — interface text (read-only)\n\nExported for review only; never imported (see CUT-5).\n");
    return count($out);
  }
}
