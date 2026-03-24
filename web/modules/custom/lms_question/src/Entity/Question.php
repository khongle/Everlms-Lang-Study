<?php

declare(strict_types=1);

namespace Drupal\lms_question\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Question entity with 8 bundles.
 */
#[ContentEntityType(
  id: 'lms_question',
  label: new TranslatableMarkup('Question'),
  label_collection: new TranslatableMarkup('Questions'),
  base_table: 'lms_question',
  data_table: 'lms_question_field_data',
  translatable: TRUE,
  admin_permission: 'administer lms_course',
  bundle_label: new TranslatableMarkup('Question Type'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'bundle' => 'type',
    'label' => 'name',
    'langcode' => 'langcode',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\lms_question\Form\QuestionForm',
      'add' => 'Drupal\lms_question\Form\QuestionForm',
      'edit' => 'Drupal\lms_question\Form\QuestionForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_question\Access\QuestionAccessControlHandler',
    'inline_form' => 'Drupal\inline_entity_form\Form\EntityInlineForm',
  ],
  links: [
    'canonical' => '/manage/questions/{lms_question}',
    'add-form' => '/manage/questions/add/{type}',
    'edit-form' => '/manage/questions/{lms_question}/edit',
    'delete-form' => '/manage/questions/{lms_question}/delete',
    'collection' => '/manage/questions',
  ],
  bundle_entity_type: 'lms_question_type',
)]
class Question extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Question'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 1024)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Details'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_points'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Points'))
      ->setDefaultValue(1)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_explanation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Explanation'))
      ->setDescription(new TranslatableMarkup('Shown after submission.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_difficulty'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Difficulty'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['quiz_difficulty' => 'quiz_difficulty']])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Bundle-specific: Multiple/Single Choice
    $fields['field_choices'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Choices'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_answer')
      ->setDisplayOptions('form', ['type' => 'inline_entity_form_complex', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Bundle-specific: True/False
    $fields['field_correct_answer'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Correct Answer'))
      ->setDescription(new TranslatableMarkup('TRUE or FALSE.'))
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    // Bundle-specific: Fill Blank / Short Answer
    $fields['field_correct_answers'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Correct Answers'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 512)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_case_sensitive'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Case Sensitive'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE);

    // Bundle-specific: Matching
    $fields['field_matching_pairs'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Matching Pairs'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_matching_pair')
      ->setDisplayOptions('form', ['type' => 'inline_entity_form_complex', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    // Bundle-specific: Ordering
    $fields['field_ordered_items'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Ordered Items'))
      ->setDescription(new TranslatableMarkup('Items in correct order.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 512)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    // Bundle-specific: Essay
    $fields['field_min_words'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Minimum Words'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_max_words'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Maximum Words'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
