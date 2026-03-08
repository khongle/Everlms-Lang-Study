<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Question Response entity.
 */
#[ContentEntityType(
  id: 'lms_question_response',
  label: new TranslatableMarkup('Question Response'),
  base_table: 'lms_question_response',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'access' => 'Drupal\lms_quiz\Access\QuizAccessControlHandler',
  ],
)]
class QuestionResponse extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['field_question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Question'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_question')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Quiz'))
      ->setSetting('target_type', 'lms_quiz')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_answers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Selected Answers'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_answer')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_correct'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Correct'))
      ->setDefaultValue(FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_score'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Score'))
      ->setDefaultValue(0)
      ->setSetting('precision', 5)
      ->setSetting('scale', 2)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_text_answer'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Text Answer'))
      ->setDescription(new TranslatableMarkup('For essay/short_answer types.'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_pending_review'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Pending Review'))
      ->setDefaultValue(FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_teacher_feedback'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Teacher Feedback'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
