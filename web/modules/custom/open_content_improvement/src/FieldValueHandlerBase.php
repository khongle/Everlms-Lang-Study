<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for field value handlers; provides a column-filtered default.
 */
abstract class FieldValueHandlerBase extends PluginBase implements FieldValueHandlerInterface {

  public function surface(FieldDefinitionInterface $fd): string {
    return 'content';
  }

  public function exportItem(FieldItemInterface $item, FieldDefinitionInterface $fd): mixed {
    return $this->storableColumns($item, $fd);
  }

  public function importItem(mixed $raw, FieldDefinitionInterface $fd): array {
    if (is_array($raw)) {
      return $raw;
    }
    $columns = array_keys($fd->getFieldStorageDefinition()->getColumns());
    $main = $columns[0] ?? 'value';
    return [$main => $raw];
  }

  /**
   * Value filtered to real storage columns only.
   */
  protected function storableColumns(FieldItemInterface $item, FieldDefinitionInterface $fd): array {
    $value = $item->getValue();
    $columns = $fd->getFieldStorageDefinition()->getColumns();
    return array_intersect_key($value, $columns);
  }
}
