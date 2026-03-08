<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Tracks user's progress in a course.
 */
#[ContentEntityType(
  id: 'lms_user_course',
  label: new TranslatableMarkup('User Course Progress'),
  base_table: 'lms_user_course',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class UserCourse extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_course');

    $fields['field_progress'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Progress (%)'))
      ->setDefaultValue(0)
      ->setSetting('precision', 5)
      ->setSetting('scale', 2);

    $fields['field_enrolled_at'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Enrolled At'));

    $fields['field_completed_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Completed At'));

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('enrolled')
      ->setSetting('allowed_values', [
        'enrolled' => 'Enrolled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
      ]);

    return $fields;
  }

}
