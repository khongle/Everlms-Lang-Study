<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Writes the entity.yml with a fixed block order and comments between blocks.
 * Block order: _meta → _context → content → _system → _files.
 */
final class AnnotatedYamlWriter {

  private const BLOCK_ORDER = ['_meta', '_context', 'content', '_system', '_files'];

  private const BLOCK_COMMENTS = [
    '_meta' => 'Identity + integrity. Do not edit — sealed by meta_sig.',
    '_context' => 'Read-only context for the editor/AI. Not imported.',
    'content' => 'EDITABLE. Improve the text here.',
    '_system' => 'Frozen machine values, references and metadata. Do not edit.',
    '_files' => 'Binary file records. Edit alt/title/description only.',
  ];

  public function __construct(private readonly PathGuard $pathGuard) {}

  /**
   * @param array<string, mixed> $blocks
   *   Keyed by block name.
   */
  public function write(string $uri, array $blocks, array $comments = []): void {
    $out = "# oci-entity-v1\n";
    foreach (self::BLOCK_ORDER as $name) {
      if (!array_key_exists($name, $blocks)) {
        continue;
      }
      $comment = $comments[$name] ?? (self::BLOCK_COMMENTS[$name] ?? '');
      if ($comment !== '') {
        $out .= "\n# " . $comment . "\n";
      }
      $dump = SymfonyYaml::dump(
        [$name => $blocks[$name]],
        PHP_INT_MAX,
        2,
        SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE | SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
      );
      $out .= $dump;
    }
    $out = rtrim($out, "\n") . "\n";
    $dir = dirname($uri);
    if (!is_dir($dir)) {
      @mkdir($dir, 0775, TRUE);
    }
    file_put_contents($uri, $out);
  }

  /**
   * Parse an entity.yml back into its blocks.
   */
  public function read(string $uri): array {
    $bytes = file_get_contents($uri);
    if ($bytes === FALSE) {
      return [];
    }
    return SymfonyYaml::parse($bytes) ?: [];
  }
}
