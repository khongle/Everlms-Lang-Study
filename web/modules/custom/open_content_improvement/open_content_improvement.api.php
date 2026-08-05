<?php

/**
 * @file
 * Documents the plugin types and alter hooks of Open Content Improvement.
 *
 * A project module extends this module without forking through: config
 * overrides, the four alter hooks below, or its own plugin classes placed in
 * src/Plugin/OpenContentImprovement/{FieldValue,Entity}/.
 */

declare(strict_types=1);

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\open_content_improvement\ExportContext;
use Drupal\open_content_improvement\ImportContext;

/**
 * Veto or allow writing a specific entity type / entity.
 */
function hook_open_content_improvement_entity_importable(string $entity_type_id, ?EntityInterface $entity): AccessResultInterface {
  return \Drupal\Core\Access\AccessResult::neutral();
}

/**
 * Force the surface of a field after FieldClassifier has decided.
 *
 * @param string $surface
 *   One of 'content', 'system', 'skip'. Passed by reference.
 */
function hook_open_content_improvement_field_surface_alter(string &$surface, FieldDefinitionInterface $fd, EntityTypeInterface $type): void {
}

/**
 * Alter the export payload before serialization.
 */
function hook_open_content_improvement_export_payload_alter(array &$payload, ContentEntityInterface $entity, ExportContext $ctx): void {
}

/**
 * Intervene immediately before the importer writes an entity.
 *
 * Throw \Drupal\open_content_improvement\Exception\OciRefusalException to refuse.
 */
function hook_open_content_improvement_pre_import(ContentEntityInterface $entity, array $payload, ImportContext $ctx): void {
}

/**
 * Alter the discovered entity handler plugin definitions.
 */
function hook_open_content_improvement_entity_handler_info_alter(array &$definitions): void {
}

/**
 * Alter the discovered field value handler plugin definitions.
 */
function hook_open_content_improvement_field_value_handler_info_alter(array &$definitions): void {
}
