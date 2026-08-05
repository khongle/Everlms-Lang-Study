<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Absolute security invariant: password fields are never exported. No config or
 * contrib plugin can outrank weight -1000.
 */
#[FieldValueHandler(id: 'denied_value', field_types: ['password'], weight: -1000)]
final class DeniedValue extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'skip';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    return NULL;
  }

  public function importItem(mixed $raw, FieldDefinitionInterface $fd): array {
    return [];
  }
}
