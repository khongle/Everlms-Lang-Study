<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Numeric fields. Frozen. Decimals are emitted as quoted strings to preserve
 * scale (PHP returns DB decimals as strings; a float cast loses scale).
 */
#[FieldValueHandler(id: 'numeric_value', field_types: ['integer', 'decimal', 'float'], weight: 80)]
final class NumericValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    $out = $this->storableColumns($item, $fd);
    if ($fd->getType() === 'decimal' && isset($out['value'])) {
      $out['value'] = (string) $out['value'];
    }
    return $out;
  }
}
