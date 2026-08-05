<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Boolean fields. Frozen — a boolean is a machine flag (e.g. field_correct on a
 * quiz answer). Never edited, and never coerced by YAML 1.1 yes/no promotion.
 */
#[FieldValueHandler(id: 'boolean_value', field_types: ['boolean'], weight: 90)]
final class BooleanValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }
}
