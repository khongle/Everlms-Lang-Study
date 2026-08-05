<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\open_content_improvement\ClassificationResult;
use Drupal\open_content_improvement\FieldSurface;
use Drupal\open_content_improvement\FieldValueHandlerManager;
use Psr\Log\LoggerInterface;

/**
 * The heart of portability: classifies a field purely from metadata and column
 * schema — never from a field-name list (except the operator's denylist).
 *
 * Five results: SKIPPED-INTERNAL · FROZEN-META · REFERENCE · FILE · EDITABLE,
 * expressed as the FieldSurface enum.
 */
final class FieldClassifier {

  /** Framework structural key slots — these are slot NAMES, not field names. */
  private const STRUCTURAL_KEY_SLOTS = [
    'id', 'uuid', 'revision', 'bundle', 'langcode',
    'default_langcode', 'revision_translation_affected', 'revision_default',
  ];

  private const STRING_COLUMN_TYPES = [
    'varchar', 'varchar_ascii', 'text', 'tinytext', 'mediumtext', 'longtext', 'char', 'string',
  ];

  private const NUMERIC_COLUMN_TYPES = ['int', 'serial', 'numeric', 'float'];

  /** A8 per-(type,bundle) probe cache. */
  private array $accessProbe = [];

  public function __construct(
    private readonly FieldValueHandlerManager $handlerManager,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Classify one field. $items optional; A8 falls back to a type-level probe.
   */
  public function classify(FieldDefinitionInterface $fd, EntityTypeInterface $type, ?FieldItemListInterface $items = NULL): ClassificationResult {
    $config = $this->configFactory->get('open_content_improvement.settings');

    // Phase A — exclusion gates.
    $reason = $this->exclusionReason($fd, $type, $items, $config);
    if ($reason !== NULL) {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::Skip, $reason), $fd, $type);
    }

    // Operator override.
    $override = $this->surfaceOverride($fd, $type, $config);
    if ($override !== NULL) {
      $surface = match ($override) {
        'content' => FieldSurface::Content,
        'skip' => FieldSurface::Skip,
        default => FieldSurface::System,
      };
      return $this->maybeAlter(new ClassificationResult($surface, 'override:' . $override), $fd, $type);
    }

    // Phase B — surface routing via the plugin + column schema.
    $handler = $this->handlerManager->handlerFor($fd);
    $storage = $fd->getFieldStorageDefinition();

    // B1 — frozen by a *Unique*-style constraint.
    if ($this->matchesFrozenConstraint($fd, $config)) {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::System, 'frozen_constraint'), $fd, $type);
    }
    // B2 — file references.
    if ($storage->getSetting('target_type') === 'file') {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::File, 'file_reference'), $fd, $type);
    }
    // B3 — entity references.
    if ($storage->getSetting('target_type') !== NULL) {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::Reference, 'entity_reference'), $fd, $type);
    }
    // B4 — changed/created machine timestamps.
    if (in_array($fd->getType(), ['changed', 'created'], TRUE)) {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::System, 'timestamp'), $fd, $type);
    }

    // B5..B8 — per-column routing, then let the plugin's coarse surface decide.
    $columns = $storage->getColumns();
    $columnSurfaces = [];
    $hasEditableColumn = FALSE;
    foreach ($columns as $col => $spec) {
      $ctype = $spec['type'] ?? '';
      if ($col === 'format') {
        $columnSurfaces[$col] = FieldSurface::System;
        continue;
      }
      if (in_array($ctype, self::STRING_COLUMN_TYPES, TRUE)) {
        $columnSurfaces[$col] = FieldSurface::Content;
        $hasEditableColumn = TRUE;
      }
      elseif (in_array($ctype, self::NUMERIC_COLUMN_TYPES, TRUE)) {
        $columnSurfaces[$col] = FieldSurface::System;
      }
      else {
        $columnSurfaces[$col] = FieldSurface::System;
      }
    }

    $pluginSurface = $handler->surface($fd);
    if ($pluginSurface === 'skip') {
      return $this->maybeAlter(new ClassificationResult(FieldSurface::Skip, 'plugin_skip'), $fd, $type);
    }
    $surface = ($pluginSurface === 'content' && $hasEditableColumn) ? FieldSurface::Content : FieldSurface::System;
    $reasonCode = $surface === FieldSurface::Content ? 'editable' : 'frozen';

    $annotations = $this->annotate($fd, $items);
    return $this->maybeAlter(new ClassificationResult($surface, $reasonCode, $annotations, $columnSurfaces), $fd, $type);
  }

  /**
   * Phase A gates. Returns the reason code of the first match, or NULL.
   */
  private function exclusionReason(FieldDefinitionInterface $fd, EntityTypeInterface $type, ?FieldItemListInterface $items, $config): ?string {
    $storage = $fd->getFieldStorageDefinition();
    // A1 computed.
    if ($fd->isComputed()) {
      return 'computed';
    }
    // A2 custom storage.
    if ($storage->hasCustomStorage()) {
      return 'custom_storage';
    }
    // A3 read-only.
    if ($fd->isReadOnly()) {
      return 'read_only';
    }
    // A4 structural key slots.
    $name = $fd->getName();
    $structural = array_filter(array_intersect_key($type->getKeys(), array_flip(self::STRUCTURAL_KEY_SLOTS)));
    if (in_array($name, $structural, TRUE)) {
      return 'entity_key';
    }
    // A5 revision metadata keys.
    $revisionMeta = method_exists($type, 'getRevisionMetadataKeys') ? array_filter($type->getRevisionMetadataKeys()) : [];
    if (in_array($name, $revisionMeta, TRUE)) {
      return 'revision_metadata';
    }
    // A6 password (also enforced by DeniedValue weight -1000).
    if ($fd->getType() === 'password') {
      return 'denied_type';
    }
    // A7 sensitive field types.
    $sensitive = $config->get('sensitive_field_types') ?: [];
    if (in_array($fd->getType(), $sensitive, TRUE)) {
      return 'sensitive_type';
    }
    // A9 content_translation metadata.
    if ($storage->getProvider() === 'content_translation') {
      return 'translation_metadata';
    }
    // A8 PII access probe (strict only).
    if (($config->get('pii_guard') ?: 'strict') === 'strict') {
      $denied = $this->accessDenied($fd, $type, $items);
      if ($denied === TRUE) {
        return 'access_denied';
      }
    }
    // A10 operator denylist (glob over entity_type.bundle.field_name).
    $patterns = $config->get('field_denylist_patterns') ?: [];
    if ($patterns) {
      $bundle = $fd->getTargetBundle() ?: $type->id();
      $path = $type->id() . '.' . $bundle . '.' . $name;
      foreach ($patterns as $glob) {
        if (fnmatch($glob, $path)) {
          return 'denylisted';
        }
      }
    }
    return NULL;
  }

  /**
   * A8: does anonymous lack view access to this field?
   *
   * With live items, ask directly; otherwise fall back to a type-level probe on
   * an unsaved stub. Returns TRUE (denied), FALSE (allowed) or NULL (unknown).
   */
  private function accessDenied(FieldDefinitionInterface $fd, EntityTypeInterface $type, ?FieldItemListInterface $items): ?bool {
    $anonymous = $this->entityTypeManager->getStorage('user')->load(0) ?? NULL;
    if ($items !== NULL) {
      try {
        return !$items->access('view', $anonymous ?: NULL);
      }
      catch (\Throwable $e) {
        return NULL;
      }
    }
    $bundle = $fd->getTargetBundle() ?: $type->id();
    $cacheKey = $type->id() . ':' . $bundle;
    if (!array_key_exists($cacheKey, $this->accessProbe)) {
      $this->accessProbe[$cacheKey] = $this->buildProbe($type, $bundle);
    }
    $probe = $this->accessProbe[$cacheKey];
    if ($probe === NULL) {
      return NULL;
    }
    try {
      $name = $fd->getName();
      if (!$probe->hasField($name)) {
        return NULL;
      }
      return !$probe->get($name)->access('view', $anonymous ?: NULL);
    }
    catch (\Throwable $e) {
      return NULL;
    }
  }

  private function buildProbe(EntityTypeInterface $type, string $bundle) {
    try {
      $storage = $this->entityTypeManager->getStorage($type->id());
      $bundleKey = $type->getKey('bundle');
      $values = $bundleKey ? [$bundleKey => $bundle] : [];
      return $storage->create($values);
    }
    catch (\Throwable $e) {
      return NULL;
    }
  }

  private function surfaceOverride(FieldDefinitionInterface $fd, EntityTypeInterface $type, $config): ?string {
    $overrides = $config->get('field_surface_overrides') ?: [];
    if (!$overrides) {
      return NULL;
    }
    $bundle = $fd->getTargetBundle() ?: $type->id();
    $path = $type->id() . '.' . $bundle . '.' . $fd->getName();
    foreach ($overrides as $row) {
      if (isset($row['pattern'], $row['surface']) && fnmatch($row['pattern'], $path)) {
        return $row['surface'];
      }
    }
    return NULL;
  }

  private function matchesFrozenConstraint(FieldDefinitionInterface $fd, $config): bool {
    $patterns = $config->get('frozen_constraint_patterns') ?: [];
    if (!$patterns) {
      return FALSE;
    }
    foreach (array_keys($fd->getConstraints()) as $constraintId) {
      foreach ($patterns as $glob) {
        if (fnmatch($glob, (string) $constraintId)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  private function maybeAlter(ClassificationResult $result, FieldDefinitionInterface $fd, EntityTypeInterface $type): ClassificationResult {
    $surface = $result->surface->blockSurface();
    $before = $surface;
    $this->moduleHandler->alter('open_content_improvement_field_surface', $surface, $fd, $type);
    if ($surface === $before) {
      return $result;
    }
    $forced = match ($surface) {
      'content' => FieldSurface::Content,
      'skip' => FieldSurface::Skip,
      default => FieldSurface::System,
    };
    return new ClassificationResult($forced, 'altered:' . $surface, $result->annotations, $result->columnSurfaces);
  }

  /**
   * @return array<string, ClassificationResult>
   */
  public function classifyAll(string $entity_type_id, string $bundle): array {
    $type = $this->entityTypeManager->getDefinition($entity_type_id);
    $definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $out = [];
    foreach ($definitions as $name => $fd) {
      $out[$name] = $this->classify($fd, $type);
    }
    return $out;
  }

  /**
   * TRUE when every field of a (type, bundle) resolves to Skip/System/Reference/File.
   */
  public function isTypeEmpty(string $entity_type_id, string $bundle): bool {
    foreach ($this->classifyAll($entity_type_id, $bundle) as $result) {
      if ($result->surface === FieldSurface::Content) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Advisory annotations — never a gate.
   */
  public function annotate(FieldDefinitionInterface $fd, ?FieldItemListInterface $items): array {
    $annotations = [];
    if ($fd->isDisplayConfigurable('form')) {
      $annotations['_primary'] = TRUE;
    }
    return $annotations;
  }

  /**
   * Advisory caution/shape class for a single scalar string value.
   */
  public function annotateValue(mixed $value): array {
    if (!is_string($value)) {
      return [];
    }
    $out = [];
    if ($this->isJson($value)) {
      $out['_caution'] = ['class' => 'json_like', 'note' => 'value parses as JSON'];
    }
    elseif (preg_match('/\{\{|\{%|<\?php|<[a-z][^>]*>|\[[a-z0-9_]+:[^\]]+\]/i', $value)) {
      $out['_caution'] = ['class' => 'code_like', 'note' => 'value contains markup or tokens'];
    }
    if (preg_match('/^[A-Za-z0-9_.:\/-]+$/', $value) && !str_contains($value, ' ')) {
      $out['_shape_class'] = 'machine_token';
    }
    return $out;
  }

  private function isJson(string $value): bool {
    $trimmed = trim($value);
    if ($trimmed === '' || (!str_starts_with($trimmed, '{') && !str_starts_with($trimmed, '['))) {
      return FALSE;
    }
    json_decode($trimmed);
    return json_last_error() === JSON_ERROR_NONE;
  }
}
