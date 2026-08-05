<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Long/formatted text. The 'value' (and 'summary') columns are editable; the
 * 'format' column is a frozen machine token carried alongside.
 */
#[FieldValueHandler(id: 'long_text', field_types: ['text_long', 'text_with_summary', 'string_long'], weight: 10)]
final class LongText extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    $columns = $fd->getFieldStorageDefinition()->getColumns();
    $value = $item->getValue();
    $out = [];
    foreach (['value', 'summary', 'format'] as $col) {
      if (array_key_exists($col, $columns) && array_key_exists($col, $value)) {
        $out[$col] = $value[$col];
      }
    }
    return $out;
  }
}
