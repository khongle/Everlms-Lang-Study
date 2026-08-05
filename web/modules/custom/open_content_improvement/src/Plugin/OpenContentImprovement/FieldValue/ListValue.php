<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Allowed-values list fields. Frozen by default (keys are machine tokens);
 * an operator opts a specific field in via field_surface_overrides.
 */
#[FieldValueHandler(id: 'list_value', field_types: ['list_string', 'list_integer', 'list_float'], weight: 40)]
final class ListValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }
}
