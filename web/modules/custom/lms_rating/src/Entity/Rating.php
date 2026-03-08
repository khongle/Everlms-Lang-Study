<?php

declare(strict_types=1);

namespace Drupal\lms_rating\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Course Rating entity.
 */
#[ContentEntityType(
  id: 'lms_rating',
  label: new TranslatableMarkup('Rating'),
  base_table: 'lms_rating',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: [
    'views_data' => EntityViewsData::class,
    'form' => ['default' => 'Drupal\Core\Entity\ContentEntityForm', 'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm'],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class Rating extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_course');

    $fields['field_score'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Score'))
      ->setDescription(new TranslatableMarkup('1-5 stars.'))
      ->setDefaultValue(5)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_review'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Review'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_approved'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Approved'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
