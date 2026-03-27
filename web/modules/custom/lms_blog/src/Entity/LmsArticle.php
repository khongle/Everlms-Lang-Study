<?php

declare(strict_types=1);

namespace Drupal\lms_blog\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the LMS Article entity.
 */
#[ContentEntityType(
  id: 'lms_article',
  label: new TranslatableMarkup('Article'),
  label_collection: new TranslatableMarkup('Articles'),
  base_table: 'lms_article',
  data_table: 'lms_article_field_data',
  translatable: TRUE,
  admin_permission: 'administer lms_blog',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'title',
    'langcode' => 'langcode',
    'published' => 'status',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\lms_blog\Form\LmsArticleForm',
      'add' => 'Drupal\lms_blog\Form\LmsArticleForm',
      'edit' => 'Drupal\lms_blog\Form\LmsArticleForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'list_builder' => 'Drupal\lms_blog\LmsArticleListBuilder',
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_blog\Access\LmsArticleAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/blog/{lms_article}',
    'add-form' => '/manage/blog/add',
    'edit-form' => '/manage/blog/{lms_article}/edit',
    'delete-form' => '/manage/blog/{lms_article}/delete',
    'collection' => '/manage/blog',
  ],
)]
class LmsArticle extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['content'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Content'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['thumbnail'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Thumbnail'))
      ->setSetting('file_directory', 'blog/thumbnails')
      ->setSetting('file_extensions', 'png jpg jpeg webp')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['seo_title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('SEO Title'))
      ->setSetting('max_length', 60)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['seo_description'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('SEO Description'))
      ->setSetting('max_length', 160)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

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
