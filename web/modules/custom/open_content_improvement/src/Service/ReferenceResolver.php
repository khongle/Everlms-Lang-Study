<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Parses and resolves portable reference strings <type>:("uuid"|"id"):<key>.
 */
final class ReferenceResolver {

  public function __construct(
    private readonly EntityRepositoryInterface $entityRepository,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @return array{type:string, mode:string, key:string}|null
   */
  public function parse(string $ref): ?array {
    if (!preg_match('/^([a-z0-9_]+):"(uuid|id)":(.+)$/', $ref, $m)) {
      return NULL;
    }
    return ['type' => $m[1], 'mode' => $m[2], 'key' => $m[3]];
  }

  /**
   * Resolve a parsed reference to a local entity id, or NULL when missing.
   */
  public function resolve(array $parsed, FieldDefinitionInterface $fd): int|string|null {
    $type = $parsed['type'];
    if ($parsed['mode'] === 'uuid') {
      $entity = $this->entityRepository->loadEntityByUuid($type, $parsed['key']);
      return $entity?->id();
    }
    $entity = $this->entityTypeManager->getStorage($type)->load($parsed['key']);
    return $entity?->id();
  }
}
