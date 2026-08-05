<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Discovers every surveyable entity type on the site, 100% dynamically.
 *
 * No entity type ID is ever hard-coded. Content vs config is decided by
 * instanceof, never by getGroup().
 */
final class EntityTypeDiscovery {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
    private readonly EntityTypeBundleInfoInterface $bundleInfo,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Every entity type with a skip_reason (NULL when exportable).
   *
   * @return array<string, array>
   *   entity_type_id => descriptor.
   */
  public function discover(): array {
    $denylist = $this->configFactory->get('open_content_improvement.settings')->get('entity_type_denylist') ?: [];
    $out = [];
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      $out[$id] = $this->describe($type, $denylist);
    }
    return $out;
  }

  public function descriptor(string $entity_type_id): ?array {
    $type = $this->entityTypeManager->getDefinition($entity_type_id, FALSE);
    if ($type === NULL) {
      return NULL;
    }
    $denylist = $this->configFactory->get('open_content_improvement.settings')->get('entity_type_denylist') ?: [];
    return $this->describe($type, $denylist);
  }

  private function describe(EntityTypeInterface $type, array $denylist): array {
    $skip = $this->skipReason($type, $denylist);
    $keys = $type->getKeys();
    return [
      'id' => $type->id(),
      'label' => (string) $type->getLabel(),
      'content' => $type instanceof ContentEntityTypeInterface,
      'fieldable' => $type->entityClassImplements(FieldableEntityInterface::class),
      'translatable' => $type->isTranslatable(),
      'revisionable' => $type->isRevisionable(),
      'has_uuid' => !empty($keys['uuid']),
      'keys' => $keys,
      'bundles' => $skip === NULL && $type->entityClassImplements(FieldableEntityInterface::class)
        ? array_keys($this->bundlesOf($type->id()))
        : [],
      'skip_reason' => $skip,
      'export_only' => empty($keys['uuid']),
    ];
  }

  /**
   * Exclusion gates EX-G1..EX-G8. Returns a reason code or NULL.
   */
  private function skipReason(EntityTypeInterface $type, array $denylist): ?string {
    if (in_array($type->id(), $denylist, TRUE)) {
      return 'denylisted';
    }
    if (!$type instanceof ContentEntityTypeInterface) {
      return 'config_entity';
    }
    if (!$type->entityClassImplements(FieldableEntityInterface::class)) {
      return 'not_fieldable';
    }
    $storageClass = $type->getStorageClass();
    if ($storageClass && str_contains($storageClass, 'NullEntityStorage')) {
      return 'null_storage';
    }
    $keys = $type->getKeys();
    if (empty($keys['id'])) {
      return 'no_id_key';
    }
    if ($type->isInternal()) {
      return 'internal';
    }
    return NULL;
  }

  public function bundlesOf(string $entity_type_id): array {
    return $this->bundleInfo->getBundleInfo($entity_type_id);
  }

  /**
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  public function fieldDefinitions(string $entity_type_id, string $bundle): array {
    $type = $this->entityTypeManager->getDefinition($entity_type_id, FALSE);
    if ($type === NULL || !$type->entityClassImplements(FieldableEntityInterface::class)) {
      throw new \LogicException(sprintf('Entity type %s is not fieldable.', $entity_type_id));
    }
    return $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
  }

  /**
   * @return array<string, string>
   *   entity_type_id => skip reason code, for _skipped.yml.
   */
  public function skipReasons(): array {
    $out = [];
    foreach ($this->discover() as $id => $d) {
      if ($d['skip_reason'] !== NULL) {
        $out[$id] = $d['skip_reason'];
      }
    }
    return $out;
  }

  /**
   * Entity types that export but cannot import (no uuid key).
   *
   * @return string[]
   */
  public function exportOnlyTypes(): array {
    $out = [];
    foreach ($this->discover() as $id => $d) {
      if ($d['skip_reason'] === NULL && $d['export_only']) {
        $out[] = $id;
      }
    }
    return $out;
  }

  /**
   * Exportable content entity types (skip_reason NULL).
   *
   * @return array<string, array>
   */
  public function exportableTypes(): array {
    return array_filter($this->discover(), static fn(array $d) => $d['skip_reason'] === NULL);
  }
}
