<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute for Open Content Improvement field value handler plugins.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class FieldValueHandler extends Plugin {

  /**
   * @param string $id
   *   The plugin ID.
   * @param string[] $field_types
   *   Field type plugin IDs this handler applies to, or ['*'] for the fallback.
   * @param int $weight
   *   Sort weight; lower wins.
   */
  public function __construct(
    public readonly string $id,
    public readonly array $field_types = ['*'],
    public readonly int $weight = 0,
  ) {}
}
