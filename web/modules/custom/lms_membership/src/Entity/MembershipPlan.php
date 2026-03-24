<?php

declare(strict_types=1);

namespace Drupal\lms_membership\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Membership Plan entity.
 */
#[ContentEntityType(
  id: 'lms_membership_plan',
  label: new TranslatableMarkup('Membership Plan'),
  label_collection: new TranslatableMarkup('Membership Plans'),
  base_table: 'lms_membership_plan',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'list_builder' => 'Drupal\lms_membership\MembershipPlanListBuilder',
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/memberships/{lms_membership_plan}',
    'add-form' => '/manage/memberships/add',
    'edit-form' => '/manage/memberships/{lms_membership_plan}/edit',
    'delete-form' => '/manage/memberships/{lms_membership_plan}/delete',
    'collection' => '/manage/memberships',
  ],
)]
class MembershipPlan extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Plan Name'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_price'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Price'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_duration_days'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Duration (days)'))
      ->setDefaultValue(30)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_billing_cycle'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Billing Cycle'))
      ->setDefaultValue('monthly')
      ->setSetting('allowed_values', [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
        'lifetime' => 'Lifetime',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_features'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Features'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_course_access'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Course Access'))
      ->setDefaultValue('all')
      ->setSetting('allowed_values', [
        'all' => 'All Courses',
        'selected' => 'Selected Categories',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_trial_days'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Trial Days'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -3])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_recommended'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Recommended Plan'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => -2])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Active'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 10])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
