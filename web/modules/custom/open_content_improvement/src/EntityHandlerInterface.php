<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Decides the on-disk directory name and a stable label for an entity.
 */
interface EntityHandlerInterface {

  public function applies(EntityTypeInterface $type): bool;

  /**
   * The directory name for the entity type, e.g. "node--article".
   */
  public function directoryName(EntityTypeInterface $type): string;

  /**
   * A human label for a single entity (may be the id when no label key).
   */
  public function entityLabel(ContentEntityInterface $entity): string;
}
