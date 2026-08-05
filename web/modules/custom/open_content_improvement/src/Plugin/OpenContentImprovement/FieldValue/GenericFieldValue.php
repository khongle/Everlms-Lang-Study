<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * The always-applicable fallback. An unknown field type routes per column
 * schema type: string columns editable, numeric/machine columns frozen. Zero
 * new code for a field type the module has never seen.
 */
#[FieldValueHandler(id: 'generic_field_value', field_types: ['*'], weight: 1000)]
final class GenericFieldValue extends FieldValueHandlerBase {

  private const STRING_TYPES = ['varchar', 'varchar_ascii', 'text', 'tinytext', 'mediumtext', 'longtext', 'char', 'string'];

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    // Editable only if at least one storable column is a string type.
    foreach ($fd->getFieldStorageDefinition()->getColumns() as $spec) {
      if (in_array($spec['type'] ?? '', self::STRING_TYPES, TRUE)) {
        return 'content';
      }
    }
    return 'system';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    return $this->storableColumns($item, $fd);
  }
}
