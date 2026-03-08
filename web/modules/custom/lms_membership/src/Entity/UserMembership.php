<?php

declare(strict_types=1);

namespace Drupal\lms_membership\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the User Membership entity.
 */
#[ContentEntityType(
  id: 'lms_user_membership',
  label: new TranslatableMarkup('User Membership'),
  base_table: 'lms_user_membership',
  admin_permission: 'administer lms_order',
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
class UserMembership extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_plan'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Plan'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_membership_plan');

    $fields['field_start_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Start Date'));

    $fields['field_end_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('End Date'));

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('active')
      ->setSetting('allowed_values', [
        'active' => 'Active',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
      ]);

    $fields['field_auto_renew'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Auto Renew'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
