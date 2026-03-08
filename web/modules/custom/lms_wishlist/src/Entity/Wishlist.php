<?php

declare(strict_types=1);

namespace Drupal\lms_wishlist\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_wishlist',
  label: new TranslatableMarkup('Wishlist Item'),
  base_table: 'lms_wishlist',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: ['views_data' => EntityViewsData::class, 'access' => 'Drupal\Core\Entity\EntityAccessControlHandler'],
)]
class Wishlist extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_course');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Added At'));

    return $fields;
  }

}
