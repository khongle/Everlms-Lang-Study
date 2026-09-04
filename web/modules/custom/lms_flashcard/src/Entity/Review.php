<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Tracks a single student's SM-2 spaced-repetition state for one card.
 *
 * One row per (uid, field_card) pair, created lazily on first grade
 * submission — a card with no Review row for a given user is implicitly
 * "new" (mirrors the lms_progress UserLesson pattern: no dedicated admin UI,
 * written only from controllers).
 */
#[ContentEntityType(
  id: 'lms_flashcard_review',
  label: new TranslatableMarkup('Flashcard Review State'),
  base_table: 'lms_flashcard_review',
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
class Review extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_card'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Card'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_flashcard_card');

    $fields['field_ease_factor'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Ease Factor'))
      ->setDescription(new TranslatableMarkup('SM-2 easiness factor, floor 1.30.'))
      ->setDefaultValue(2.50)
      ->setSetting('precision', 3)
      ->setSetting('scale', 2);

    $fields['field_interval_days'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Interval (days)'))
      ->setDefaultValue(0);

    $fields['field_repetitions'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Repetitions'))
      ->setDescription(new TranslatableMarkup('Consecutive successful (quality >= 3) reviews.'))
      ->setDefaultValue(0);

    $fields['field_next_review_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Next Review At'));

    $fields['field_last_reviewed_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Last Reviewed At'));

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('new')
      ->setSetting('allowed_values', [
        'new' => 'New',
        'learning' => 'Learning',
        'review' => 'Review',
        'relearning' => 'Relearning',
      ]);

    $fields['field_lapses'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Lapses'))
      ->setDescription(new TranslatableMarkup('Number of times this card has been graded "Again".'))
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
