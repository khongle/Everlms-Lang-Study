<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Entity;

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
 * Defines the Listening Passage entity.
 *
 * Covers both "Level 3" (format=sentence: one short utterance + a single
 * comprehension question, built straight from a Flashcard's own example
 * sentence) and "Level 4" (format=narrative/dialogue: a longer story or
 * conversation with progressive hint->transcript->translation reveal) from
 * LISTENING_TRAINING_PLAN.txt's consolidated 5-level design — one entity
 * type/import pipeline instead of two, distinguished only by field_format,
 * the same way ReadingPassage tells Narrative from Dialogue apart.
 *
 * Deliberately modeled on lms_reading's ReadingPassage (deck-scoped,
 * covers specific cards, reuses the shared lms_question bank) but kept as
 * an independent copy in its own module — lms_listening never modifies
 * lms_reading's or lms_flashcard's own code, only *reads* their entities
 * through the entity API, so existing Reading/Flashcard study modes are
 * unaffected by this module existing (or being absent).
 *
 * Unlike Reading, the passage text (field_body/field_dialogue_lines) is NOT
 * shown to the student until they choose to reveal it (Hint/Transcript) —
 * enforced entirely client-side in js/listening-passage.js, since "don't
 * show the text before the learner listens" is the whole point of a
 * listening exercise (see LISTENING_TRAINING_PLAN.txt section 1). There is
 * no word-click glossary popup here (unlike Reading) — out of scope for v1.
 */
#[ContentEntityType(
  id: 'lms_listening_passage',
  label: new TranslatableMarkup('Listening Passage'),
  label_collection: new TranslatableMarkup('Listening Passages'),
  base_table: 'lms_listening_passage',
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
      'default' => 'Drupal\lms_listening\Form\ListeningPassageForm',
      'add' => 'Drupal\lms_listening\Form\ListeningPassageForm',
      'edit' => 'Drupal\lms_listening\Form\ListeningPassageForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_listening\Access\ListeningAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/listening-passages/{lms_listening_passage}',
    'add-form' => '/manage/listening-passages/add',
    'edit-form' => '/manage/listening-passages/{lms_listening_passage}/edit',
    'delete-form' => '/manage/listening-passages/{lms_listening_passage}/delete',
    'collection' => '/admin/structure/lms_listening_passage',
  ],
)]
class ListeningPassage extends ContentEntityBase implements EntityOwnerInterface {

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
      ->setDescription(new TranslatableMarkup('A short, stable, unique code for this passage, auto-generated from the title if left blank. The JSON importer uses it the same way ReadingPassage.field_machine_name is used — re-importing a payload with a machine name that already exists is rejected instead of silently creating a near-duplicate passage.'))
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
      ->setDescription(new TranslatableMarkup('The specific cards (from field_deck) this passage practices listening for. At least one is required.'))
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
      ->setDescription(new TranslatableMarkup('Sentence: one short utterance + a single comprehension question (the "Level 3" exercise). Narrative: a paragraph-length story (field_body), listened to sentence by sentence. Dialogue: a back-and-forth conversation (field_dialogue_speakers/field_dialogue_lines), listened to turn by turn. Narrative/Dialogue support progressive hint reveal (field_hints) — Sentence normally does not need any.'))
      ->setRequired(TRUE)
      ->setDefaultValue('sentence')
      ->setSetting('allowed_values', [
        'sentence' => 'Sentence (single utterance)',
        'narrative' => 'Narrative (paragraph)',
        'dialogue' => 'Dialogue (conversation)',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -15.6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Transcript'))
      ->setDescription(new TranslatableMarkup('For Sentence/Narrative, the text that is spoken aloud — required. For Dialogue, an optional one-sentence scene-setting intro shown above the conversation; the conversation itself lives in field_dialogue_speakers/field_dialogue_lines. Kept as plain text and never shown to the student until they choose to reveal it (see js/listening-passage.js) — no offsets need to be stored.'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -15, 'settings' => ['rows' => 6]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_dialogue_speakers'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Dialogue Speakers'))
      ->setDescription(new TranslatableMarkup('Only used when field_format is "Dialogue". One speaker name per turn — keep this in the same order/length as field_dialogue_lines below (turn N here is who says turn N there).'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -14.8, 'settings' => ['size' => 30]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_dialogue_lines'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Dialogue Lines'))
      ->setDescription(new TranslatableMarkup('Only used when field_format is "Dialogue". One line of dialogue per turn — same order/length as field_dialogue_speakers above.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -14.7,
        'settings' => ['rows' => 2],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_hints'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Hints'))
      ->setDescription(new TranslatableMarkup('Optional, ordered list of progressively more revealing hints (e.g. a single keyword, then a short phrase) shown one at a time — see LISTENING_TRAINING_PLAN.txt section 6 ("Listening without transcript"). Usually left empty for a Sentence passage; most useful for Narrative/Dialogue.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -14,
        'settings' => ['rows' => 1],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_translation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Translation'))
      ->setDescription(new TranslatableMarkup('Optional full translation of the transcript, revealed last (after Hint/Transcript) — same "don\'t give it away too early" ordering as field_hints.'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -13.5, 'settings' => ['rows' => 4]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_prompt'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Generation Prompt'))
      ->setDescription(new TranslatableMarkup('Optional — the prompt you gave an AI tool (ChatGPT or similar) to generate this passage\'s JSON, kept for your own reference if you want to regenerate or tweak it later. Never shown to students anywhere; only visible here and to whoever else can edit this passage.'))
      ->setDisplayOptions('form', ['type' => 'string_textarea', 'weight' => -13.4, 'settings' => ['rows' => 6]])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['field_word_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Word Count'))
      ->setDescription(new TranslatableMarkup('Computed automatically on save, from field_body plus every field_dialogue_lines entry.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', ['weight' => -13])
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_questions'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Comprehension Questions'))
      ->setDescription(new TranslatableMarkup('Reused from the shared Question bank (lms_question) so multiple choice / true-false / fill-in-the-blank / short answer all work out of the box — a Sentence passage typically has exactly one.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -12])
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
   * Auto-fills field_machine_name from the title and recomputes
   * field_word_count so it never drifts from the actual transcript —
   * mirrors ReadingPassage::preSave() exactly.
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
