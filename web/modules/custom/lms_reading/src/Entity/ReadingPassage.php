<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the Reading Passage entity.
 *
 * A passage always belongs to exactly one Flashcard deck (field_deck) and
 * covers at least one of that deck's cards (field_cards) — this is what
 * lets a student go Flashcards -> Reading and back. lms_reading only ever
 * *reads* lms_flashcard_deck/lms_flashcard_card through the entity API and
 * (via ReadingApiController::addUnknownWords()) *creates new* Card entities
 * the same way ImportBatchService does — it never modifies lms_flashcard's
 * own code, so existing Flashcard study modes are unaffected by this module.
 */
#[ContentEntityType(
  id: 'lms_reading_passage',
  label: new TranslatableMarkup('Reading Passage'),
  label_collection: new TranslatableMarkup('Reading Passages'),
  base_table: 'lms_reading_passage',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'owner' => 'uid',
    'published' => 'status',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'list_builder' => 'Drupal\Core\Entity\EntityListBuilder',
    'form' => [
      'default' => 'Drupal\lms_reading\Form\ReadingPassageForm',
      'add' => 'Drupal\lms_reading\Form\ReadingPassageForm',
      'edit' => 'Drupal\lms_reading\Form\ReadingPassageForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_reading\Access\ReadingAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/reading-passages/{lms_reading_passage}',
    'add-form' => '/manage/reading-passages/add',
    'edit-form' => '/manage/reading-passages/{lms_reading_passage}/edit',
    'delete-form' => '/manage/reading-passages/{lms_reading_passage}/delete',
    'collection' => '/admin/structure/lms_reading_passage',
  ],
)]
class ReadingPassage extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -20])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_machine_name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Machine Name'))
      ->setDescription(new TranslatableMarkup('A short, stable, unique code for this passage, auto-generated from the title if left blank. The JSON importer uses it the same way Deck.field_machine_name is used — re-importing a payload with a machine name that already exists is rejected instead of silently creating a near-duplicate passage.'))
      ->setSetting('max_length', 64)
      ->addConstraint('UniqueField')
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -19])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_deck'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Flashcard Deck'))
      ->setDescription(new TranslatableMarkup('The vocabulary deck this passage is built from.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_flashcard_deck')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -18])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_cards'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Covered Cards'))
      ->setDescription(new TranslatableMarkup('The specific cards (from field_deck) this passage highlights. At least one is required — this is what lets "Add unknown words to Flashcards" on the Review screen point back at the right deck, and lets the reading view show word info straight from the matching Card instead of duplicating it.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_flashcard_card')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -17])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_level'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Level'))
      ->setRequired(TRUE)
      ->setDefaultValue('normal')
      ->setSetting('allowed_values', [
        'easy' => 'Easy',
        'normal' => 'Normal',
        'challenge' => 'Challenge',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -16])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_format'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Format'))
      ->setDescription(new TranslatableMarkup('Narrative: a plain paragraph (field_body), read/spoken sentence by sentence. Dialogue: a back-and-forth conversation (field_dialogue_speakers/field_dialogue_lines below), read/spoken turn by turn, optionally with an alternating voice per speaker.'))
      ->setRequired(TRUE)
      ->setDefaultValue('narrative')
      ->setSetting('allowed_values', [
        'narrative' => 'Narrative (paragraph)',
        'dialogue' => 'Dialogue (conversation)',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -15.6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Passage Text'))
      ->setDescription(new TranslatableMarkup('For a Narrative passage, the whole passage text — required. For a Dialogue passage, an optional one- or two-sentence scene-setting intro shown above the conversation; the conversation itself lives in field_dialogue_speakers/field_dialogue_lines. Kept as plain text — word highlighting is done client-side by matching field_cards/word-note terms against this text, so no offsets need to be stored.'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -15, 'settings' => ['rows' => 10]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_dialogue_speakers'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Dialogue Speakers'))
      ->setDescription(new TranslatableMarkup('Only used when field_format is "Dialogue". One speaker name per turn (e.g. "David", "Pharmacist") — keep this in the same order, and the same length, as field_dialogue_lines below (turn N here is who says turn N there).'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -14.6, 'settings' => ['size' => 30]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_dialogue_lines'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Dialogue Lines'))
      ->setDescription(new TranslatableMarkup('Only used when field_format is "Dialogue". One line of dialogue per turn — same order/length as field_dialogue_speakers above.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -14.5,
        'settings' => ['rows' => 2],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_word_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Word Count'))
      ->setDescription(new TranslatableMarkup('Computed automatically on save, from field_body (Narrative) or field_body + every field_dialogue_lines entry (Dialogue).'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', ['weight' => -14])
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_questions'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Comprehension Questions'))
      ->setDescription(new TranslatableMarkup('"Understand" step questions — reused from the shared Question bank (lms_question) so multiple choice / true-false / fill-in-the-blank / short answer all work out of the box.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -13])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_context_items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Guess-from-Context Items'))
      ->setDescription(new TranslatableMarkup('"Guess the meaning from context" exercises for this passage.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_reading_context_item')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -12])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_topics'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Topics'))
      ->setDescription(new TranslatableMarkup('Optional topic tags, reserved for a future "Daily Reading" mode that mixes vocabulary across several decks/topics. Not used by anything yet.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['reading_topic' => 'reading_topic']])
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -11])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published status'))
      ->setDescription(new TranslatableMarkup('A boolean indicating whether the passage is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 99])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   *
   * Auto-fills field_machine_name from the title (mirrors
   * Deck::preSave()) and recomputes field_word_count so it never drifts
   * from the actual passage text, whether saved via the admin form or via
   * ReadingImportBatchService — counting field_body alone for a Narrative
   * passage, or field_body (the optional intro) plus every
   * field_dialogue_lines entry for a Dialogue one.
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);

    if ($this->get('field_machine_name')->isEmpty() && !$this->get('name')->isEmpty()) {
      $slug = mb_strtolower(trim((string) $this->label()));
      $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
      $slug = trim($slug, '-');
      $this->set('field_machine_name', mb_substr($slug, 0, 64));
    }

    $text_parts = [strip_tags((string) $this->get('field_body')->value)];
    if ($this->get('field_format')->value === 'dialogue') {
      foreach ($this->get('field_dialogue_lines')->getValue() as $delta) {
        $text_parts[] = strip_tags((string) $delta['value']);
      }
    }
    $text = trim(implode(' ', array_filter($text_parts, static fn ($part) => trim($part) !== '')));
    $word_count = $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    $this->set('field_word_count', $word_count);
  }

}
