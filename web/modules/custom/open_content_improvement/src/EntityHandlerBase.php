<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for entity handlers.
 */
abstract class EntityHandlerBase extends PluginBase implements EntityHandlerInterface {

  public function directoryName(EntityTypeInterface $type): string {
    return $type->id();
  }

  public function entityLabel(ContentEntityInterface $entity): string {
    $label = $entity->label();
    return is_string($label) && $label !== '' ? $label : (string) $entity->id();
  }
}
