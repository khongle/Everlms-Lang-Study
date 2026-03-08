<?php

declare(strict_types=1);

namespace Drupal\lms_order\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Order Item entity.
 */
#[ContentEntityType(
  id: 'lms_order_item',
  label: new TranslatableMarkup('Order Item'),
  base_table: 'lms_order_item',
  admin_permission: 'administer lms_order',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class OrderItem extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setSetting('target_type', 'lms_course');

    $fields['field_price'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Price'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2);

    $fields['field_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Type'))
      ->setDefaultValue('course')
      ->setSetting('allowed_values', [
        'course' => 'Course',
        'bundle' => 'Bundle',
        'membership' => 'Membership',
      ]);

    return $fields;
  }

}
