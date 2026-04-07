<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Entity;

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
 * Defines the Quiz entity.
 */
#[ContentEntityType(
  id: 'lms_quiz',
  label: new TranslatableMarkup('Quiz'),
  label_collection: new TranslatableMarkup('Quizzes'),
  base_table: 'lms_quiz',
  data_table: 'lms_quiz_field_data',
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
    'form' => [
      'default' => 'Drupal\lms_quiz\Form\QuizForm',
      'add' => 'Drupal\lms_quiz\Form\QuizForm',
      'edit' => 'Drupal\lms_quiz\Form\QuizForm',
      'admin_edit' => 'Drupal\lms_quiz\Form\QuizForm',
      'admin_create' => 'Drupal\lms_quiz\Form\QuizForm',
      'manager_edit' => 'Drupal\lms_quiz\Form\QuizForm',
      'manager_create' => 'Drupal\lms_quiz\Form\QuizForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_quiz\Access\QuizAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/quizzes/{lms_quiz}',
    'add-form' => '/manage/quizzes/add',
    'edit-form' => '/manage/quizzes/{lms_quiz}/edit',
    'delete-form' => '/manage/quizzes/{lms_quiz}/delete',
    'collection' => '/manage/quizzes',
  ],
)]
class Quiz extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Quiz Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Instructions'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_questions'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Questions'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_threshold'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Pass Threshold (%)'))
      ->setDefaultValue(70)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_time_limit'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Time Limit (minutes)'))
      ->setDescription(new TranslatableMarkup('0 = unlimited.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_display_right_answer'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Show Correct Answers'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_form_style'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Form Style'))
      ->setDefaultValue('one_page')
      ->setSetting('allowed_values', [
        'one_page' => 'All questions on one page',
        'question_step' => 'One question per step',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_question_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Random Question Count'))
      ->setDescription(new TranslatableMarkup('0 = use all questions.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -3])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_take_quiz_number'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Max Attempts'))
      ->setDescription(new TranslatableMarkup('0 = unlimited.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -2])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_prerequisite_quizzes'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Prerequisite Quizzes'))
      ->setSetting('target_type', 'lms_quiz')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published status'))
      ->setDescription(new TranslatableMarkup('A boolean indicating whether the Quiz is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 99,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
