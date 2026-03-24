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
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'view_builder' => 'Drupal\Core\Entity\EntityViewBuilder',
    'list_builder' => 'Drupal\lms_membership\UserMembershipListBuilder',
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'add' => 'Drupal\Core\Entity\ContentEntityForm',
      'edit' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/user-memberships/{lms_user_membership}',
    'add-form' => '/manage/user-memberships/add',
    'edit-form' => '/manage/user-memberships/{lms_user_membership}/edit',
    'delete-form' => '/manage/user-memberships/{lms_user_membership}/delete',
    'collection' => '/manage/user-memberships',
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
      ->setSetting('target_type', 'lms_membership_plan')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_start_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Start Date'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_end_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('End Date'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('active')
      ->setSetting('allowed_values', [
        'active' => 'Active',
        'trial' => 'Trial',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_auto_renew'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Auto Renew'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
