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
 * A lightweight "mini glossary" entry for a word inside a passage that has
 * no matching card in the passage's field_cards.
 *
 * Popup info for a highlighted word is resolved in this order (see
 * ReadingApiController::wordInfo()): (1) an lms_flashcard_card in the
 * passage's field_cards whose name matches, (2) a ReadingWordNote for the
 * passage whose name matches. This entity only exists for words the
 * passage introduces that AREN'T in Flashcards yet (typically at Normal/
 * Challenge level) — a word already in field_cards never needs one.
 *
 * No dedicated admin UI/routes, same as Review/CardStat in lms_flashcard —
 * created only via ReadingImportBatchService or ReadingPassageForm's
 * autocomplete widget.
 */
#[ContentEntityType(
  id: 'lms_reading_word_note',
  label: new TranslatableMarkup('Reading Word Note'),
  label_collection: new TranslatableMarkup('Reading Word Notes'),
  base_table: 'lms_reading_word_note',
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
class ReadingWordNote extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['field_passage'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Passage'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_reading_passage')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Term'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_meaning'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Meaning'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_pronunciation'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Pronunciation'))
      ->setDescription(new TranslatableMarkup('IPA transcription, e.g. /pri.skrɪpʃn/.'))
      ->setSetting('max_length', 128)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Same allowed values as Card::field_part_of_speech, kept independent
    // (copy, not a shared list) since these two entities live in different
    // modules and lms_reading must not reach into lms_flashcard's field
    // storage definitions.
    $fields['field_part_of_speech'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Part of Speech'))
      ->setSetting('allowed_values', [
        'noun' => 'Noun',
        'verb' => 'Verb',
        'adjective' => 'Adjective',
        'adverb' => 'Adverb',
        'pronoun' => 'Pronoun',
        'preposition' => 'Preposition',
        'conjunction' => 'Conjunction',
        'interjection' => 'Interjection',
        'determiner' => 'Determiner',
        'article' => 'Article',
        'phrase' => 'Phrase',
        'idiom' => 'Idiom',
        'other' => 'Other',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
