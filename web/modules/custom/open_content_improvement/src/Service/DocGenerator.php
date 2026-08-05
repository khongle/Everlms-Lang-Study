<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Generates human-facing docs (README/index/glossary/_skipped). Never read at
 * import time — purely for the editor/AI.
 */
final class DocGenerator {

  public function __construct(
    private readonly EntityTypeDiscovery $discovery,
    private readonly FieldClassifier $classifier,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LanguageManagerInterface $languageManager,
  ) {}

  public function indexMd(array $exportedTypes): string {
    $lines = [
      '# Open Content Improvement — export tree',
      '',
      'This tree contains the admin-editable text of every content entity on the site.',
      'Edit only the `content:` block of each `entity.yml` and the `text/*.html` sidecars.',
      'Do not touch `_meta`, `_system`, or `_files` records (except alt/title/description).',
      '',
      '## Entity types',
      '',
    ];
    foreach ($exportedTypes as $id => $count) {
      $lines[] = sprintf('- `%s` — %d entities', $id, $count);
    }
    return implode("\n", $lines) . "\n";
  }

  public function typeReadme(string $entity_type_id): string {
    $descriptor = $this->discovery->descriptor($entity_type_id);
    $label = $descriptor['label'] ?? $entity_type_id;
    return "# $label (`$entity_type_id`)\n\nOne folder per entity. Improve the `content:` block and the `text/*.html` sidecars.\n";
  }

  public function skippedYml(array $skipReasons, array $extra = []): string {
    return "# Entity types and fields that were NOT exported, with reason codes.\n" . Yaml::encode(['entity_types' => $skipReasons] + $extra);
  }

  public function glossaryMd(): string {
    return "# Glossary\n\n- **content** — editable text.\n- **_system** — frozen machine values, references, dates, numbers.\n- **_files** — binary records; only alt/title/description are editable.\n- **_meta** — identity + integrity seal; never edit.\n";
  }
}
