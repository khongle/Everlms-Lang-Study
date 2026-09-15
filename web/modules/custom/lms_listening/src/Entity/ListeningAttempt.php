<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Records one student's attempt at one Listening Passage.
 *
 * One row per submission — mirrors lms_reading's ReadingAttempt (a student
 * may relisten and retry). field_replay_count/field_hints_used/
 * field_transcript_shown/field_translation_shown are recorded (see
 * LISTENING_TRAINING_PLAN.txt section 14 "Replay counter") for future use
 * by a listening-specific spaced-repetition/scoring pass — v1 does not yet
 * fold them into field_score, which stays a plain correct/total percentage
 * like ReadingAttempt's, kept deliberately simple until real usage data
 * shows a scoring curve is worth adding.
 *
 * No dedicated admin UI/routes — written only from ListeningApiController.
 */
#[ContentEntityType(
  id: 'lms_listening_attempt',
  label: new TranslatableMarkup('Listening Attempt'),
  base_table: 'lms_listening_attempt',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\lms_listening\Access\ListeningAccessControlHandler',
  ],
)]
class ListeningAttempt extends ContentEntityBase implements EntityOwnerInterface {

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
      ->setSetting('target_type', 'lms_listening_passage');

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

    $fields['field_replay_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Replay Count'))
      ->setDescription(new TranslatableMarkup('How many extra times the student replayed the audio before answering — see LISTENING_TRAINING_PLAN.txt section 14.'))
      ->setDefaultValue(0);

    $fields['field_hints_used'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Hints Used'))
      ->setDefaultValue(0);

    $fields['field_transcript_shown'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Transcript Shown'))
      ->setDefaultValue(FALSE);

    $fields['field_translation_shown'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Translation Shown'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
