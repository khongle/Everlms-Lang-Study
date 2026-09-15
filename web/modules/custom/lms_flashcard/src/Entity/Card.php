<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Defines the Flashcard entity (a single vocabulary term within a deck).
 */
#[ContentEntityType(
  id: 'lms_flashcard_card',
  label: new TranslatableMarkup('Flashcard'),
  label_collection: new TranslatableMarkup('Flashcards'),
  base_table: 'lms_flashcard_card',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'published' => 'status',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'list_builder' => 'Drupal\lms_flashcard\CardListBuilder',
    'form' => [
      'default' => 'Drupal\lms_flashcard\Form\CardForm',
      'add' => 'Drupal\lms_flashcard\Form\CardForm',
      'edit' => 'Drupal\lms_flashcard\Form\CardForm',
      'admin_edit' => 'Drupal\lms_flashcard\Form\CardForm',
      'admin_create' => 'Drupal\lms_flashcard\Form\CardForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_flashcard\Access\FlashcardAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/flashcard-cards/{lms_flashcard_card}',
    'add-form' => '/manage/flashcard-cards/add',
    'edit-form' => '/manage/flashcard-cards/{lms_flashcard_card}/edit',
    'delete-form' => '/manage/flashcard-cards/{lms_flashcard_card}/delete',
    'collection' => '/admin/structure/lms_flashcard_card',
  ],
)]
class Card extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Term'))
      ->setDescription(new TranslatableMarkup('The word or phrase shown on the front of the card.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_deck'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Deck'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_flashcard_deck')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_definition'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Definition'))
      ->setDescription(new TranslatableMarkup('The meaning shown on the back of the card.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_translation'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Translation'))
      ->setDescription(new TranslatableMarkup('The term translated into the deck\'s native language (see Deck::field_native_language), e.g. "pineapple" → "quả dứa".'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -7.5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

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
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_pronunciation'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Pronunciation'))
      ->setDescription(new TranslatableMarkup('IPA transcription, e.g. /əˈbændən/.'))
      ->setSetting('max_length', 128)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_example_sentence'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Example Sentence'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_example_translation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Example Translation'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_example_sentences'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Example Sentences'))
      ->setDescription(new TranslatableMarkup('Add one or more example sentences. Keep them in the same order as their translations below.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => ['rows' => 2],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_example_translations'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Example Translations'))
      ->setDescription(new TranslatableMarkup('Add each translation in the same position as its example sentence above.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
        'settings' => ['rows' => 2],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_collocations'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Collocations'))
      ->setDescription(new TranslatableMarkup('Common word combinations this term appears in (e.g. "make a decision" for "decision"). Keep them in the same order as their translations below.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -3.5,
        'settings' => ['rows' => 1],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_collocation_translations'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Collocation Translations'))
      ->setDescription(new TranslatableMarkup('Add each translation in the same position as its collocation above.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -3.4,
        'settings' => ['rows' => 1],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_audio'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Pronunciation Audio'))
      ->setSetting('file_directory', 'flashcards/audio/[date:custom:Y-m]')
      ->setSetting('file_extensions', 'mp3 wav ogg')
      ->setSetting('max_filesize', '5 MB')
      ->setDisplayOptions('form', ['type' => 'file_generic', 'weight' => -3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_image'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setSetting('file_directory', 'flashcards/images')
      ->setSetting('alt_field', TRUE)
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => -2])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_difficulty'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Difficulty'))
      ->setDefaultValue('medium')
      ->setSetting('allowed_values', [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published'))
      ->setDescription(new TranslatableMarkup('Unpublished cards are skipped entirely by every study mode (StudyQueueBuilder\'s queue/count/mastery queries and StudyController::buildQuizPool()\'s distractor pool) but still included in a deck\'s Export JSON — this only hides a card from being studied, it never deletes or excludes it from export/management.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 98])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
