<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Link fields: title is editable text, uri + options are frozen machine values.
 */
#[FieldValueHandler(id: 'link_value', field_types: ['link'], weight: 60)]
final class LinkValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    $columns = $fd->getFieldStorageDefinition()->getColumns();
    $value = $item->getValue();
    $out = [];
    foreach (['uri', 'title', 'options'] as $col) {
      if (array_key_exists($col, $columns) && array_key_exists($col, $value)) {
        $out[$col] = $value[$col];
      }
    }
    return $out;
  }
}
