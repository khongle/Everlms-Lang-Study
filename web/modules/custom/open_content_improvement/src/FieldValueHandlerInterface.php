<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Serializes and deserializes a field item value for the export tree.
 */
interface FieldValueHandlerInterface {

  /**
   * Whether this handler applies to a given field definition.
   */
  public function applies(FieldDefinitionInterface $fd): bool;

  /**
   * Coarse block surface: 'content' | 'system' | 'skip'.
   */
  public function surface(FieldDefinitionInterface $fd): string;

  /**
   * Export a single field item to a plain, serializable array (or scalar).
   *
   * @return mixed
   *   The exportable representation of this item's storable columns.
   */
  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed;

  /**
   * Coerce a raw value read from the tree back into a storable columns array.
   *
   * @return array
   *   Columns keyed by column name, ready for FieldItemListInterface::setValue().
   */
  public function importItem(mixed $raw, FieldDefinitionInterface $fd): array;
}
