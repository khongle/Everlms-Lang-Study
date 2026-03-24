<?php

declare(strict_types=1);

namespace Drupal\lms_chapter\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Chapter entity.
 */
#[ContentEntityType(
  id: 'lms_chapter',
  label: new TranslatableMarkup('Chapter'),
  label_collection: new TranslatableMarkup('Chapters'),
  base_table: 'lms_chapter',
  data_table: 'lms_chapter_field_data',
  translatable: TRUE,
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'langcode' => 'langcode',
    'published' => 'status',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\lms_chapter\Form\ChapterForm',
      'add' => 'Drupal\lms_chapter\Form\ChapterForm',
      'edit' => 'Drupal\lms_chapter\Form\ChapterForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_chapter\Access\ChapterAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/chapters/{lms_chapter}',
    'add-form' => '/manage/chapters/add',
    'edit-form' => '/manage/chapters/{lms_chapter}/edit',
    'delete-form' => '/manage/chapters/{lms_chapter}/delete',
    'collection' => '/manage/chapters',
  ],
)]
class Chapter extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Chapter Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);



    $fields['field_lessons'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Lessons'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_lesson')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Weight'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 10])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
