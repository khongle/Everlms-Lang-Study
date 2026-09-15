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
 * Tracks how many times a student has opened a given Reading Passage.
 *
 * One row per (uid, field_passage) pair, created lazily on first view —
 * mirrors lms_flashcard's CardStat entity (its "Seen N times" counter,
 * shown to the student on the flashcard study screen) and lms_listening's
 * own ListeningPassageStat exactly, kept as an independent copy per this
 * module's independence from both. Deliberately separate from
 * ReadingAttempt: an attempt is only recorded once the comprehension
 * questions are submitted, but this counts every time the Read step is
 * opened — matching CardStat's own "shown to the student" semantics rather
 * than "completed a review" — so a student who re-reads without finishing
 * the questions still sees that reflected. Never consulted for anything
 * but the display count; nothing else reads it.
 */
#[ContentEntityType(
  id: 'lms_reading_passage_stat',
  label: new TranslatableMarkup('Reading Passage Stat'),
  base_table: 'lms_reading_passage_stat',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class ReadingPassageStat extends ContentEntityBase implements EntityOwnerInterface {

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

    $fields['field_times_seen'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Times Seen'))
      ->setDescription(new TranslatableMarkup('How many times this passage has been opened by the student on the Read step.'))
      ->setDefaultValue(0);

    $fields['field_last_seen_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Last Seen At'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
