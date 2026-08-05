<?php

declare(strict_types=1);

namespace Drupal\Tests\open_content_improvement\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The portability guard (P-A1..P-A10).
 *
 * This module's central promise is that it carries NOTHING from the site it was
 * born on: no host entity-type ids, no `\Drupal::` static calls in src/, no
 * dependencies beyond Drupal core, and no non-empty denylists baked into the
 * shipped config. These are pure static-analysis assertions over the module
 * tree, so the test needs no Drupal bootstrap and extends the plain PHPUnit
 * TestCase — it can run in isolation, in CI, or on a bare checkout.
 *
 * @group open_content_improvement
 */
final class NoProjectCouplingTest extends TestCase {

  /**
   * Absolute path to the module root.
   */
  private static function moduleRoot(): string {
    // tests/src/Unit/ -> tests/src/ -> tests/ -> module root.
    return dirname(__DIR__, 3);
  }

  /**
   * Every *.php file under src/.
   *
   * @return string[]
   */
  private static function srcFiles(): array {
    $root = self::moduleRoot() . '/src';
    $files = [];
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
      if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
      }
    }
    sort($files);
    return $files;
  }

  /**
   * P-A1 — no token of the host project leaks into src/.
   *
   * The module was authored on an EverLMS site (entity types lms_*, vendor
   * package EverLMS, sibling module open_switcher). None of that may appear in
   * portable code: discovery and classification are 100% metadata-driven.
   */
  public function testNoProjectTokensInSrc(): void {
    // Case-insensitive substrings that would betray coupling to the birth site.
    $forbidden = ['lms_', 'everlms', 'befit', 'ironpeak', 'open_switcher'];
    $hits = [];
    foreach (self::srcFiles() as $file) {
      $body = strtolower((string) file_get_contents($file));
      foreach ($forbidden as $token) {
        if (str_contains($body, $token)) {
          $hits[] = basename($file) . ' => ' . $token;
        }
      }
    }
    $this->assertSame([], $hits, 'src/ must not reference the host project: ' . implode(', ', $hits));
  }

  /**
   * P-A3 — zero `\Drupal::` static service locators in src/.
   *
   * Static calls are permitted ONLY in the procedural .module/.install files;
   * every class receives its collaborators by injection. A single `\Drupal::`
   * in a service is both untestable and a portability landmine.
   */
  public function testNoStaticDrupalCallsInSrc(): void {
    $hits = [];
    foreach (self::srcFiles() as $file) {
      if (str_contains((string) file_get_contents($file), '\\Drupal::')) {
        $hits[] = basename($file);
      }
    }
    $this->assertSame([], $hits, 'src/ must contain no \\Drupal:: calls; found in: ' . implode(', ', $hits));
  }

  /**
   * P-A2 — `use` imports come only from allowed vendor roots.
   *
   * Anything outside core, this module, Symfony, Drush, Psr or Consolidation
   * would drag in a contrib/site dependency the info.yml does not declare.
   */
  public function testUseStatementsAreVendorPortable(): void {
    $allowed = [
      'Drupal\\Core\\',
      'Drupal\\Component\\',
      'Drupal\\open_content_improvement\\',
      'Symfony\\',
      'Drush\\',
      'Psr\\',
      'Consolidation\\',
    ];
    $offenders = [];
    foreach (self::srcFiles() as $file) {
      foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        if (!preg_match('/^use\s+([^\s;]+)/', $line, $m)) {
          continue;
        }
        $fqcn = ltrim($m[1], '\\');
        $ok = FALSE;
        foreach ($allowed as $prefix) {
          if (str_starts_with($fqcn, $prefix)) {
            $ok = TRUE;
            break;
          }
        }
        if (!$ok) {
          $offenders[] = basename($file) . ' => ' . $fqcn;
        }
      }
    }
    $this->assertSame([], $offenders, 'Non-portable use imports: ' . implode(', ', $offenders));
  }

  /**
   * P-A4 — info.yml is core-only: it carries no `dependencies:` key at all.
   *
   * An empty `dependencies: {}` would still be wrong; the byte-shape mirrors the
   * repo's portable precedent (open_switcher) which simply omits the key.
   */
  public function testInfoYmlHasNoDependencies(): void {
    $info = (string) file_get_contents(self::moduleRoot() . '/open_content_improvement.info.yml');
    $this->assertDoesNotMatchRegularExpression('/^dependencies:/m', $info, 'info.yml must not declare dependencies (core-only).');
  }

  /**
   * P-A5 — the three suppression lists ship EMPTY.
   *
   * A shipped denylist would silently hide entity types/fields on every install
   * site — the opposite of "discover everything, decide by metadata".
   */
  public function testShippedDenylistsAreEmpty(): void {
    $file = self::moduleRoot() . '/config/install/open_content_improvement.settings.yml';
    $settings = \Symfony\Component\Yaml\Yaml::parseFile($file);
    foreach (['entity_type_denylist', 'field_denylist_patterns', 'importable_entity_types'] as $key) {
      $this->assertArrayHasKey($key, $settings, "Missing config key: $key");
      $this->assertSame([], $settings[$key], "$key must ship empty.");
    }
  }

  /**
   * P-A6 — exactly one file-bearing field handler and one entity handler.
   *
   * File handling is a single, auditable code path (BinaryStore via one plugin);
   * a second one would fork the safety-critical temporary-then-promote lifecycle.
   */
  public function testExactlyOneFileAndOneEntityHandler(): void {
    $fieldValueDir = self::moduleRoot() . '/src/Plugin/OpenContentImprovement/FieldValue';
    $fileHandlers = [];
    foreach (glob($fieldValueDir . '/*.php') ?: [] as $plugin) {
      // The file-bearing handler is the one that routes items to the File
      // surface / BinaryStore.
      if (str_contains((string) file_get_contents($plugin), 'FieldSurface::File')
        || preg_match('/file/i', basename($plugin))) {
        $fileHandlers[] = basename($plugin);
      }
    }
    $this->assertContains('FileReference.php', $fileHandlers);

    $entityDir = self::moduleRoot() . '/src/Plugin/OpenContentImprovement/Entity';
    $entityHandlers = glob($entityDir . '/*.php') ?: [];
    $this->assertCount(1, $entityHandlers, 'Exactly one entity handler plugin expected.');
  }

}
