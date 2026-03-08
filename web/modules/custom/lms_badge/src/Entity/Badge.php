<?php

declare(strict_types=1);

namespace Drupal\lms_badge\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_badge',
  label: new TranslatableMarkup('Badge'),
  label_collection: new TranslatableMarkup('Badges'),
  base_table: 'lms_badge',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'name'],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => ['default' => 'Drupal\Core\Entity\ContentEntityForm', 'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm'],
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/badges/{lms_badge}',
    'add-form' => '/manage/badges/add',
    'edit-form' => '/manage/badges/{lms_badge}/edit',
    'collection' => '/manage/badges',
  ],
)]
class Badge extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Badge Name'))
      ->setRequired(TRUE)->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_icon'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Icon'))
      ->setSetting('file_directory', 'badges/icons')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Badge Type'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['badge_type' => 'badge_type']])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_xp_reward'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('XP Reward'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_criteria'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Award Criteria'))
      ->setDescription(new TranslatableMarkup('JSON: {"type":"courses_completed","count":5}'))
      ->setSetting('max_length', 1024)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
