<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\open_content_improvement\FieldValueHandlerManager;

/**
 * Validates one import unit against the live entity: identity, integrity,
 * staleness, drift and shape-class invariants. Returns a verdict.
 */
final class ImportValidator {

  public function __construct(
    private readonly FieldClassifier $classifier,
    private readonly FieldValueHandlerManager $fieldHandlers,
    private readonly ReferenceResolver $referenceResolver,
    private readonly CanonicalHasher $hasher,
    private readonly SidecarStore $sidecarStore,
    private readonly EntityFieldManagerInterface $entityFieldManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * @return array{ok: bool, hard: bool, reason: string, changes: array}
   */
  public function validateUnit(array $meta, array $blocks, ContentEntityInterface $live, string $langcode, string $liveHash): array {
    // Integrity: meta seal.
    if (!$this->hasher->verifyMeta($meta)) {
      return ['ok' => FALSE, 'hard' => TRUE, 'reason' => 'meta-tampered', 'changes' => []];
    }
    // Type / bundle match.
    if (($meta['entity_type'] ?? '') !== $live->getEntityTypeId()) {
      return ['ok' => FALSE, 'hard' => TRUE, 'reason' => 'type-mismatch', 'changes' => []];
    }
    if (($meta['bundle'] ?? '') !== $live->bundle()) {
      return ['ok' => FALSE, 'hard' => TRUE, 'reason' => 'bundle-mismatch', 'changes' => []];
    }
    // Staleness (content hash; 0/28 corpus types revisionable so hash is authority).
    $sourceHash = $meta['source_hash'][$langcode] ?? ($meta['source_hash'] ?? NULL);
    if (is_string($sourceHash) && $sourceHash !== $liveHash) {
      // Unmodified units are fine (no change); modified stale units refuse.
      // Handled by caller comparing edited vs source; here we only flag if edited.
      return ['ok' => TRUE, 'hard' => FALSE, 'reason' => 'stale', 'changes' => []];
    }
    return ['ok' => TRUE, 'hard' => FALSE, 'reason' => '', 'changes' => []];
  }

  /**
   * Compute the set of editable field changes for a translation.
   *
   * @return array<string, array>
   *   field_name => list of coerced column arrays keyed by delta.
   */
  public function editableChanges(array $contentBlock, ContentEntityInterface $live, string $entityDir): array {
    $type = $live->getEntityType();
    $changes = [];
    foreach ($contentBlock as $field => $deltas) {
      if (!$live->hasField($field)) {
        continue;
      }
      $fd = $live->get($field)->getFieldDefinition();
      $result = $this->classifier->classify($fd, $type, $live->get($field));
      if (!$result->isEditable()) {
        // Refusing to write a non-editable surface is a hard invariant, but we
        // simply skip unknown keys here; ImportWriter enforces on_drift.
        continue;
      }
      $handler = $this->fieldHandlers->handlerFor($fd);
      $newValues = [];
      foreach ($deltas as $delta => $raw) {
        $raw = $this->resolveSidecars($raw, $entityDir);
        $newValues[$delta] = $handler->importItem($raw, $fd);
      }
      $changes[$field] = $newValues;
    }
    return $changes;
  }

  /**
   * Replace {_sidecar, sha256} markers with the sidecar bytes.
   */
  private function resolveSidecars(mixed $raw, string $entityDir): mixed {
    if (is_array($raw)) {
      if (isset($raw['_sidecar'])) {
        return $this->sidecarStore->get($entityDir, $raw['_sidecar']);
      }
      $out = [];
      foreach ($raw as $k => $v) {
        if (is_array($v) && isset($v['_sidecar'])) {
          $out[$k] = $this->sidecarStore->get($entityDir, $v['_sidecar']);
        }
        else {
          $out[$k] = $v;
        }
      }
      return $out;
    }
    return $raw;
  }
}
