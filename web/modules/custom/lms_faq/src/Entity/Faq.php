<?php

declare(strict_types=1);

namespace Drupal\lms_faq\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_faq',
  label: new TranslatableMarkup('FAQ'),
  label_collection: new TranslatableMarkup('FAQs'),
  base_table: 'lms_faq',
  data_table: 'lms_faq_field_data',
  translatable: TRUE,
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'question', 'langcode' => 'langcode'],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => ['default' => 'Drupal\Core\Entity\ContentEntityForm', 'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm'],
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/faq/{lms_faq}',
    'add-form' => '/manage/faq/add',
    'edit-form' => '/manage/faq/{lms_faq}/edit',
    'collection' => '/manage/faq',
  ],
)]
class Faq extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['question'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Question'))
      ->setRequired(TRUE)->setTranslatable(TRUE)->setSetting('max_length', 512)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_answer'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Answer'))
      ->setRequired(TRUE)->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_scope'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Scope'))
      ->setDefaultValue('global')
      ->setSetting('allowed_values', [
        'global' => 'Global', 'course' => 'Course', 'lesson' => 'Lesson',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setSetting('target_type', 'lms_course')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Category'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['faq_category' => 'faq_category']])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Weight'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
