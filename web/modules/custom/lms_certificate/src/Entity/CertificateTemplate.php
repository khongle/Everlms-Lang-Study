<?php

declare(strict_types=1);

namespace Drupal\lms_certificate\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Certificate Template entity.
 */
#[ContentEntityType(
  id: 'lms_certificate_template',
  label: new TranslatableMarkup('Certificate Template'),
  label_collection: new TranslatableMarkup('Certificate Templates'),
  base_table: 'lms_certificate_template',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'name'],
  handlers: [
    'list_builder' => '\Drupal\lms_certificate\CertificateTemplateListBuilder',
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => '\Drupal\lms_certificate\Form\CertificateTemplateForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/certificates/{lms_certificate_template}',
    'add-form' => '/manage/certificates/add',
    'edit-form' => '/manage/certificates/{lms_certificate_template}/edit',
    'delete-form' => '/manage/certificates/{lms_certificate_template}/delete',
    'collection' => '/manage/certificates',
  ],
)]
class CertificateTemplate extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Template Name'))
      ->setRequired(TRUE)->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Certificate Body'))
      ->setDescription(new TranslatableMarkup('Use tokens: [student_name], [course_name], [issue_date].'))
      ->setDisplayOptions('form', ['type' => 'hidden'])
      ->setDisplayConfigurable('form', FALSE)->setDisplayConfigurable('view', TRUE);

    $fields['field_design'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Design'))
      ->setDefaultValue('classic')
      ->setSetting('allowed_values', [
        'classic' => 'Classic', 'modern' => 'Modern',
        'elegant' => 'Elegant', 'minimal' => 'Minimal',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_background'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Background Image'))
      ->setSetting('file_directory', 'certificates/backgrounds')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_signature'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Signature Image'))
      ->setSetting('file_directory', 'certificates/signatures')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_logo'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Logo Image'))
      ->setSetting('file_directory', 'certificates/logos')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_seal'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Seal Image (Con dấu)'))
      ->setSetting('file_directory', 'certificates/seals')
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_orientation'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Orientation'))
      ->setDefaultValue('landscape')
      ->setSetting('allowed_values', [
        'landscape' => 'Landscape', 'portrait' => 'Portrait',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_layout'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Twig Template'))
      ->setDescription(new TranslatableMarkup('Input the full HTML/Twig structure for the certificate here.'))
      ->setDisplayOptions('form', ['type' => 'string_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_color_scheme'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Color Scheme'))
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -2])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Active'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 10])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
