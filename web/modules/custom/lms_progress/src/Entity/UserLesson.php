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
 * Tracks user's lesson-level progress.
 */
#[ContentEntityType(
  id: 'lms_user_lesson',
  label: new TranslatableMarkup('User Lesson Progress'),
  base_table: 'lms_user_lesson',
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
class UserLesson extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_lesson'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Lesson'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_lesson');

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('not_started')
      ->setSetting('allowed_values', [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
      ]);

    $fields['field_video_position'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Video Position'))
      ->setDescription(new TranslatableMarkup('Last video position in seconds.'))
      ->setDefaultValue(0);

    $fields['field_completed_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Completed At'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
