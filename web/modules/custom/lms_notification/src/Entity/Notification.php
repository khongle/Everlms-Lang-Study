<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the Notification entity.
 */
#[ContentEntityType(
  id: 'lms_notification',
  label: new TranslatableMarkup('Notification'),
  base_table: 'lms_notification',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class Notification extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Type'))
      ->setSetting('allowed_values', [
        'enrollment' => 'Enrollment', 'quiz_result' => 'Quiz Result',
        'certificate' => 'Certificate', 'payment' => 'Payment',
        'forum_reply' => 'Forum Reply', 'system' => 'System',
      ]);

    $fields['field_title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)->setSetting('max_length', 255);

    $fields['field_message'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Message'));

    $fields['field_link'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link'))
      ->setSetting('max_length', 512);

    $fields['field_read'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Read'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
