<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines one "Guess the meaning from context" exercise.
 *
 * Deliberately its own entity type rather than a bundle of lms_question:
 * this exercise needs two fields (the target word + the sentence it appears
 * in) that lms_question has no use for, and lms_reading is meant to stay
 * independent of lms_question except for the one thing it *does* reuse
 * (Question/Answer for the "Understand" step — see ReadingPassage).
 *
 * No dedicated admin UI/routes — created only via ReadingImportBatchService
 * or from ReadingPassageForm's inline entity_reference_autocomplete widget,
 * same "no list_builder" pattern as lms_flashcard's Review/CardStat.
 */
#[ContentEntityType(
  id: 'lms_reading_context_item',
  label: new TranslatableMarkup('Reading Context Item'),
  label_collection: new TranslatableMarkup('Reading Context Items'),
  base_table: 'lms_reading_context_item',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'access' => 'Drupal\lms_reading\Access\ReadingAccessControlHandler',
  ],
)]
class ReadingContextItem extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Target Word'))
      ->setDescription(new TranslatableMarkup('The word or phrase the student must guess the meaning of.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_context_sentence'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Context Sentence'))
      ->setDescription(new TranslatableMarkup('The sentence (usually taken from the passage) showing the word in use.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_choices'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Choices'))
      ->setDescription(new TranslatableMarkup('Multiple-choice options (A, B, C, D...), in order.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_correct_index'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Correct Choice Index'))
      ->setDescription(new TranslatableMarkup('Zero-based index into field_choices of the correct answer.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_explanation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Explanation'))
      ->setDescription(new TranslatableMarkup('Shown after the student answers.'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
