<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Records one student's attempt at one Reading Passage.
 *
 * One row per submission (unlike lms_flashcard's Review/CardStat, which are
 * one row per uid+card updated in place) — a student may reread and retry a
 * passage, and the Review Screen / "Add unknown words to Flashcards" button
 * needs to refer back to a specific attempt's results, so each attempt gets
 * its own row rather than being merged into a running total.
 *
 * No dedicated admin UI/routes — written only from ReadingApiController.
 */
#[ContentEntityType(
  id: 'lms_reading_attempt',
  label: new TranslatableMarkup('Reading Attempt'),
  base_table: 'lms_reading_attempt',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\lms_reading\Access\ReadingAccessControlHandler',
  ],
)]
class ReadingAttempt extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_passage'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Passage'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_reading_passage');

    $fields['field_score'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Score (%)'))
      ->setDescription(new TranslatableMarkup('0-100, computed server-side from field_correct_count / field_question_count.'))
      ->setDefaultValue(0);

    $fields['field_correct_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Correct Answers'))
      ->setDefaultValue(0);

    $fields['field_question_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Question Count'))
      ->setDefaultValue(0);

    $fields['field_duration_seconds'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Duration (seconds)'))
      ->setDefaultValue(0);

    $fields['field_clicked_cards'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Clicked Cards'))
      ->setDescription(new TranslatableMarkup('Words clicked during this attempt that already had a matching Flashcard.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_flashcard_card');

    $fields['field_clicked_notes'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Clicked Word Notes'))
      ->setDescription(new TranslatableMarkup('Words clicked during this attempt that only had a ReadingWordNote (not yet in Flashcards).'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_reading_word_note');

    $fields['field_unknown_terms'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Unknown Terms'))
      ->setDescription(new TranslatableMarkup('Terms the student explicitly marked "I don\'t know this word" on the word-info popup (see js/reading-passage.js) — the source list for "Add unknown words to Flashcards".'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 255);

    $fields['field_added_to_flashcards'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Added to Flashcards'))
      ->setDescription(new TranslatableMarkup('Whether "Add unknown words to Flashcards" has already been used for this attempt, to guard against creating duplicate cards if the button is pressed twice.'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
