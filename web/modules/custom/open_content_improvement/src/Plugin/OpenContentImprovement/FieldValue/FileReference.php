<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * File/image references. Binary handled by BinaryStore; alt/title/description
 * are editable text carried in the _files record.
 */
#[FieldValueHandler(id: 'file_reference', field_types: [], weight: 20)]
final class FileReference extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return $fd->getFieldStorageDefinition()->getSetting('target_type') === 'file';
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    return $this->storableColumns($item, $fd);
  }
}
