<?php

declare(strict_types=1);

namespace Drupal\lms_lesson\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Lesson entity.
 */
#[ContentEntityType(
  id: 'lms_lesson',
  label: new TranslatableMarkup('Lesson'),
  label_collection: new TranslatableMarkup('Lessons'),
  base_table: 'lms_lesson',
  data_table: 'lms_lesson_field_data',
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
      'default' => 'Drupal\lms_lesson\Form\LessonForm',
      'add' => 'Drupal\lms_lesson\Form\LessonForm',
      'edit' => 'Drupal\lms_lesson\Form\LessonForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_lesson\Access\LessonAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/lessons/{lms_lesson}',
    'add-form' => '/manage/lessons/add',
    'edit-form' => '/manage/lessons/{lms_lesson}/edit',
    'delete-form' => '/manage/lessons/{lms_lesson}/delete',
    'collection' => '/manage/lessons',
  ],
)]
class Lesson extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Lesson Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);



    $fields['field_content'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Content'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Lesson Type'))
      ->setRequired(TRUE)
      ->setDefaultValue('text')
      ->setSetting('allowed_values', [
        'video' => 'Video',
        'text' => 'Text',
        'quiz' => 'Quiz',
        'h5p' => 'H5P Interactive',
        'scorm' => 'SCORM',
        'pdf' => 'PDF Document',
        'pptx' => 'PowerPoint',
        'zoom' => 'Zoom Live',
        'mixed' => 'Mixed',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_h5p_embed'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('H5P Embed Code'))
      ->setDisplayOptions('form', ['type' => 'string_textarea', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_video_url'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Video URL'))
      ->setDescription(new TranslatableMarkup('YouTube or Vimeo URL.'))
      ->setSetting('max_length', 512)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_video_file'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Video File'))
      ->setSetting('file_directory', 'lessons/videos')
      ->setSetting('file_extensions', 'mp4 webm ogg')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_zoom_meeting_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Zoom Meeting ID'))
      ->setSetting('max_length', 64)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_zoom_schedule'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Zoom Schedule'))
      ->setDisplayOptions('form', ['type' => 'datetime_timestamp', 'weight' => -3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_scorm_package'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('SCORM Package'))
      ->setSetting('file_directory', 'lessons/scorm')
      ->setSetting('file_extensions', 'zip')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -2])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_pptx_attachment'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('PowerPoint File'))
      ->setSetting('file_directory', 'lessons/pptx')
      ->setSetting('file_extensions', 'ppt pptx')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_attachments'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Attachments'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('file_directory', 'lessons/attachments')
      ->setSetting('file_extensions', 'pdf doc docx ppt pptx xls xlsx zip')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_duration'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Duration'))
      ->setDescription(new TranslatableMarkup('Duration in minutes.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_free_preview'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Free Preview'))
      ->setDescription(new TranslatableMarkup('Allow viewing without enrollment.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_drip_days'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Drip Days'))
      ->setDescription(new TranslatableMarkup('Days after enrollment before unlocking (0 = immediate).'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 2])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Quiz'))
      ->setSetting('target_type', 'lms_quiz')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_comments'] = BaseFieldDefinition::create('comment')
      ->setLabel(new TranslatableMarkup('Comments'))
      ->setDescription(new TranslatableMarkup('Lesson comments and Q&A.'))
      ->setDefaultValue([
        'status' => \Drupal\comment\Plugin\Field\FieldType\CommentItemInterface::OPEN,
      ])
      ->setSetting('comment_type', 'lesson_comment')
      ->setDisplayOptions('form', [
        'type' => 'comment_default',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'comment_default',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Weight'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 5])
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
