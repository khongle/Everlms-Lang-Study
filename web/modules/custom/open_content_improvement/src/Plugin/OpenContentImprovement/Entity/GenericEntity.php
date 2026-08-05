<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Plugin\OpenContentImprovement\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\open_content_improvement\Attribute\EntityHandler;
use Drupal\open_content_improvement\EntityHandlerBase;

/**
 * The single, always-applicable entity handler. Zero entity-name knowledge.
 */
#[EntityHandler(id: 'generic_entity', entity_types: ['*'], weight: 1000)]
final class GenericEntity extends EntityHandlerBase {

  public function applies(EntityTypeInterface $type): bool {
    return TRUE;
  }
}
