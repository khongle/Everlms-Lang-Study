<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Short plain-text fields. Editable (varchar). email/telephone are excluded at
 * classification time via sensitive_field_types; if opted back in, editable.
 */
#[FieldValueHandler(id: 'plain_text', field_types: ['string', 'uri', 'email', 'telephone'], weight: 70)]
final class PlainText extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    return TRUE;
  }
}
