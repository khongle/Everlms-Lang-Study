<?php

declare(strict_types=1);

namespace Drupal\lms_note\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_note',
  label: new TranslatableMarkup('Student Note'),
  base_table: 'lms_note',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: ['views_data' => EntityViewsData::class, 'access' => 'Drupal\Core\Entity\EntityAccessControlHandler'],
)]
class Note extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_lesson'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Lesson'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_lesson');

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Note'))
      ->setRequired(TRUE);

    $fields['field_video_timestamp'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Video Timestamp'))
      ->setDescription(new TranslatableMarkup('Seconds into the video.'))
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
