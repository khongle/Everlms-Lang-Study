<?php

declare(strict_types=1);

namespace Drupal\lms_badge\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_user_badge',
  label: new TranslatableMarkup('User Badge'),
  base_table: 'lms_user_badge',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: ['views_data' => EntityViewsData::class, 'access' => 'Drupal\Core\Entity\EntityAccessControlHandler'],
)]
class UserBadge extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_badge'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Badge'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_badge');

    $fields['field_awarded_at'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Awarded At'));

    return $fields;
  }

}
