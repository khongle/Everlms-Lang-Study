<?php

declare(strict_types=1);

namespace Drupal\lms_course\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the Course entity.
 */
#[ContentEntityType(
  id: 'lms_course',
  label: new TranslatableMarkup('Course'),
  label_collection: new TranslatableMarkup('Courses'),
  label_singular: new TranslatableMarkup('course'),
  label_plural: new TranslatableMarkup('courses'),
  base_table: 'lms_course',
  data_table: 'lms_course_field_data',
  translatable: TRUE,
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'langcode' => 'langcode',
    'owner' => 'uid',
    'published' => 'status',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'list_builder' => 'Drupal\lms_course\CourseListBuilder',
    'form' => [
      'default' => 'Drupal\lms_course\Form\CourseForm',
      'add' => 'Drupal\lms_course\Form\CourseForm',
      'edit' => 'Drupal\lms_course\Form\CourseForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_course\Access\CourseAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/courses/{lms_course}',
    'add-form' => '/manage/courses/add',
    'edit-form' => '/manage/courses/{lms_course}/edit',
    'delete-form' => '/manage/courses/{lms_course}/delete',
    'collection' => '/manage/courses',
  ],
)]
class Course extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Course Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_short_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Short Description'))
      ->setDescription(new TranslatableMarkup('Brief description for listing cards.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_thumbnail'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Thumbnail'))
      ->setDescription(new TranslatableMarkup('Card thumbnail image.'))
      ->setSetting('file_directory', 'courses/thumbnails')
      ->setSetting('alt_field', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_banner'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Banner'))
      ->setDescription(new TranslatableMarkup('Detail page banner image.'))
      ->setSetting('file_directory', 'courses/banners')
      ->setSetting('alt_field', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Category'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['course_category' => 'course_category']])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_tags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Tags'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['course_tags' => 'course_tags']])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete_tags',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_level'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Level'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['course_level' => 'course_level']])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_language'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Teaching Language'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['course_language' => 'course_language']])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_instructor'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Instructor'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_price'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Price'))
      ->setDescription(new TranslatableMarkup('0 = free course.'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_price_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Price Type'))
      ->setDefaultValue('free')
      ->setSetting('allowed_values', [
        'free' => 'Free',
        'paid' => 'Paid',
        'membership' => 'Membership Only',
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_duration'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Duration'))
      ->setDescription(new TranslatableMarkup('Total duration in minutes (auto-calculated).'))
      ->setDefaultValue(0)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_bestseller'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Bestseller'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_featured'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Featured'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
