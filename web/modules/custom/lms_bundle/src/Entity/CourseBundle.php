<?php

declare(strict_types=1);

namespace Drupal\lms_bundle\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_course_bundle',
  label: new TranslatableMarkup('Course Bundle'),
  label_collection: new TranslatableMarkup('Course Bundles'),
  base_table: 'lms_course_bundle',
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
    'canonical' => '/manage/bundles/{lms_course_bundle}',
    'add-form' => '/manage/bundles/add',
    'edit-form' => '/manage/bundles/{lms_course_bundle}/edit',
    'collection' => '/manage/bundles',
  ],
)]
class CourseBundle extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Bundle Name'))
      ->setRequired(TRUE)->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_courses'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Courses'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_course')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_price'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Bundle Price'))
      ->setDefaultValue(0)->setSetting('precision', 10)->setSetting('scale', 2)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_discount_percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Discount (%)'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_thumbnail'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Thumbnail'))
      ->setSetting('file_directory', 'bundles/thumbnails')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Active'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 10])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
