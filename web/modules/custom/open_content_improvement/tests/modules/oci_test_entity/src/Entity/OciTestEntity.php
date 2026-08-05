<?php

declare(strict_types=1);

namespace Drupal\oci_test_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * A deliberately tiny, translatable content entity for round-trip tests.
 *
 * It carries exactly the field shapes the round-trip must cover with nothing
 * more: an editable label (string), an editable rich-text body routed to a
 * sidecar (text_long + format), and a frozen machine value (weight, integer)
 * that classification must keep out of the `content:` surface. No field config,
 * no contrib — so the exporter discovers and classifies it purely by metadata.
 */
#[ContentEntityType(
  id: 'oci_test_entity',
  label: new TranslatableMarkup('OCI test entity'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'langcode' => 'langcode',
  ],
  base_table: 'oci_test_entity',
  data_table: 'oci_test_entity_field_data',
  translatable: TRUE,
  admin_permission: 'administer oci test entity',
)]
final class OciTestEntity extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'string_textfield']);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Body'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea']);

    // A frozen machine value: has no form widget, so classification routes it to
    // the frozen _system surface, never to content:.
    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Weight'))
      ->setDefaultValue(0);

    return $fields;
  }

}
