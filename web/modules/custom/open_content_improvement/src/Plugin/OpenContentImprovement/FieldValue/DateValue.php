<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Date/time fields. Frozen — dates are structured machine values.
 */
#[FieldValueHandler(id: 'date_value', field_types: ['datetime', 'daterange', 'timestamp', 'created', 'changed'], weight: 50)]
final class DateValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }
}
