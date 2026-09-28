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
 * Tracks how many times a student has been shown a given card.
 *
 * One row per (uid, field_card) pair, created lazily on first view — mirrors
 * the Review entity's pattern (no dedicated admin UI, written only from
 * controllers). Deliberately kept separate from Review: that entity's mere
 * existence marks a card as "not new" for StudyQueueBuilder's due/new-card
 * logic and counts against the daily new-card budget (see
 * StudyQueueBuilder::buildQueue() and ::getRemainingNewCardBudget()), so
 * writing to it from a practice session (which must never touch real SRS
 * scheduling) would corrupt those. This entity is never read by
 * StudyQueueBuilder, so incrementing it in both practice and SRS sessions is
 * safe.
 */
#[ContentEntityType(
  id: 'lms_flashcard_card_stat',
  label: new TranslatableMarkup('Flashcard Card Stat'),
  base_table: 'lms_flashcard_card_stat',
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
class CardStat extends ContentEntityBase implements EntityOwnerInterface {

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

    $fields['field_times_seen'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Times Seen'))
      ->setDescription(new TranslatableMarkup('How many times this card has been shown to the student, across both practice and SRS study sessions.'))
      ->setDefaultValue(0);

    $fields['field_last_seen_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Last Seen At'));

    $fields['field_spell_level'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Spelling Level'))
      ->setDescription(new TranslatableMarkup('1 (Warm-up) to 5 (Mastered) — how hard the fill-in-the-missing-letters puzzle is for this card in Spelling Practice. +1 per correct check, -1 per incorrect one; never leaves the 1-5 range. Independent of field_times_seen and of the real SM-2 schedule on the Review entity (see FlashcardApiController::submitReview()), which is updated in parallel from the same check.'))
      ->setDefaultValue(1);

    $fields['field_quiz_level'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Quiz Level'))
      ->setDescription(new TranslatableMarkup('1 to 3 — which of the 3 example-sentence exercises Quiz Practice gives for this card (1: cloze multiple-choice, 2: listen-and-match the sentence, 3: listen-and-type the sentence from its translation). +1 per pass, -1 per fail; never leaves the 1-3 range. Independent of field_spell_level and of the real SM-2 schedule on the Review entity, which is updated in parallel — see FlashcardApiController::submitReview().'))
      ->setDefaultValue(1);

    $fields['field_pronunciation_level'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Pronunciation Level'))
      ->setDescription(new TranslatableMarkup('1 (Warm-up) to 5 (Mastered) — a single difficulty/mastery counter for Pronunciation Practice, same shape as field_spell_level since this is one skill (say the word correctly) rather than the 3 exercise types Quiz Practice cycles through. +1 per correct attempt, -1 per incorrect one; never leaves the 1-5 range. Purely informational — unlike field_spell_level/field_quiz_level it never changes what the exercise looks like, only the badge shown to the student. Independent of field_times_seen and of the real SM-2 schedule on the Review entity, which is updated in parallel from the same check — see FlashcardApiController::submitReview().'))
      ->setDefaultValue(1);

    $fields['field_mastered'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Mastered'))
      ->setDescription(new TranslatableMarkup("Set once this card has passed \"Study Now\" mastery level 3 (see FlashcardApiController::submitMastery()'s level3_pass handling) — unlike the mastery flow's in-session queue state, this persists across sessions, so StudyQueueBuilder::getMasteryPercent() can show a lasting \"% of this deck mastered\" on the deck card. Doesn't get \"un-mastered\" by being requeued in a later session, but IS deliberately cleared back to FALSE by \"Reset Mastery\" (StudyQueueBuilder::resetMastery()) so a student can re-earn the progress bar — see field_ever_mastered below for the flag that tracks reaching it at all, which a reset never touches."))
      ->setDefaultValue(FALSE);

    $fields['field_skipped'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Skipped'))
      ->setDescription(new TranslatableMarkup("This user's own choice to leave a card (they already know it) out of their study sessions for a shared/course deck — a personal, per-student flag, deliberately NOT Card.status (which is the deck owner's content-publish switch, shared by every student studying that deck). StudyQueueBuilder excludes a card from this user's queue/counts/mastery denominator when this is TRUE, alongside (not instead of) the Card.status check. Toggled from the deck detail page's 'Skip' / 'Study Again' button — see FlashcardApiController::toggleCardSkip()."))
      ->setDefaultValue(FALSE);

    $fields['field_ever_mastered'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Ever Mastered'))
      ->setDescription(new TranslatableMarkup("Set the first time this card ever passes \"Study Now\" mastery level 3, alongside field_mastered — but unlike that field, resetMastery() never clears this one back to FALSE. StudyQueueBuilder::isPracticeUnlocked() checks this (not field_mastered) to decide whether Spelling/Pronunciation Practice (and, from outside this module, Reading/Listening) stay unlocked for a deck: once a student has genuinely mastered every card in it at least once, resetting the progress bar to study it again shouldn't re-lock content they've already earned access to."))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
