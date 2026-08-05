<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\FieldValue;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;
use Drupal\open_content_improvement\FieldValueHandlerBase;

/**
 * Entity references, exported as portable {label, ref} where ref is
 * <type>:("uuid"|"id"):<key>. Never editable.
 */
#[FieldValueHandler(id: 'entity_reference', field_types: [], weight: 30)]
final class EntityReference extends FieldValueHandlerBase {

  public function applies(FieldDefinitionInterface $fd): bool {
    $target = $fd->getFieldStorageDefinition()->getSetting('target_type');
    return $target !== NULL && $target !== 'file';
  }

  public function surface(FieldDefinitionInterface $fd): string {
    return 'system';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    $target_type = $fd->getFieldStorageDefinition()->getSetting('target_type');
    $value = $item->getValue();
    $target_id = $value['target_id'] ?? NULL;
    $ref = NULL;
    $label = NULL;
    $referenced = NULL;
    try {
      $referenced = $item->get('entity')->getValue();
    }
    catch (\Throwable $e) {
      $referenced = NULL;
    }
    if ($referenced instanceof EntityInterface) {
      $label = $referenced->label();
      $uuid = $referenced->uuid();
      if ($uuid !== NULL) {
        $ref = sprintf('%s:"uuid":%s', $target_type, $uuid);
      }
      else {
        $ref = sprintf('%s:"id":%s', $target_type, $referenced->id());
      }
    }
    elseif ($target_id !== NULL) {
      $ref = sprintf('%s:"id":%s', $target_type, $target_id);
    }
    return ['label' => $label, 'ref' => $ref];
  }

  public function importItem(mixed $raw, FieldDefinitionInterface $fd): array {
    // References are frozen; ReferenceResolver handles any allowed rewrite.
    return [];
  }
}
