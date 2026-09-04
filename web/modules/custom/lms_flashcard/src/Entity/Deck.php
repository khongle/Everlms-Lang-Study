<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Entity;

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
 * Defines the Flashcard Deck entity (container for vocabulary cards).
 */
#[ContentEntityType(
  id: 'lms_flashcard_deck',
  label: new TranslatableMarkup('Flashcard Deck'),
  label_collection: new TranslatableMarkup('Flashcard Decks'),
  base_table: 'lms_flashcard_deck',
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
    'list_builder' => 'Drupal\lms_flashcard\DeckListBuilder',
    'form' => [
      'default' => 'Drupal\lms_flashcard\Form\DeckForm',
      'add' => 'Drupal\lms_flashcard\Form\DeckForm',
      'edit' => 'Drupal\lms_flashcard\Form\DeckForm',
      'admin_edit' => 'Drupal\lms_flashcard\Form\DeckForm',
      'admin_create' => 'Drupal\lms_flashcard\Form\DeckForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_flashcard\Access\FlashcardAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/flashcard-decks/{lms_flashcard_deck}',
    'add-form' => '/manage/flashcard-decks/add',
    'edit-form' => '/manage/flashcard-decks/{lms_flashcard_deck}/edit',
    'delete-form' => '/manage/flashcard-decks/{lms_flashcard_deck}/delete',
    'collection' => '/admin/structure/lms_flashcard_deck',
  ],
)]
class Deck extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Deck Name'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_machine_name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Machine Name'))
      ->setDescription(new TranslatableMarkup('A short, stable, unique code for this deck (e.g. "family-home-101"), auto-generated from the deck name if left blank. The JSON importer uses it to tell decks apart reliably — re-importing a "new deck" payload with a machine name that already exists is rejected instead of silently creating a near-duplicate deck.'))
      ->setSetting('max_length', 64)
      ->addConstraint('UniqueField')
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -9.5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_topic'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Topic'))
      ->setDescription(new TranslatableMarkup('Category to organize/browse decks by (e.g. Family, Travel, Business). Matched by name — a new topic is created automatically the first time it\'s used.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['flashcard_topic' => 'flashcard_topic']])
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -9.2])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setDescription(new TranslatableMarkup('Optional: scope this deck to a course for study-in-context. Leave empty for a standalone vocabulary deck.'))
      ->setSetting('target_type', 'lms_course')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_lesson'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Lesson'))
      ->setDescription(new TranslatableMarkup('Optional: scope this deck to a specific lesson.'))
      ->setSetting('target_type', 'lms_lesson')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -7])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_cards'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Cards'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_flashcard_card')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -6])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_language'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Vocabulary Language'))
      ->setDefaultValue('en')
      ->setSetting('allowed_values', [
        'en' => 'English',
        'vi' => 'Vietnamese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'fr' => 'French',
        'de' => 'German',
        'es' => 'Spanish',
        'other' => 'Other',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_native_language'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Definition Language'))
      ->setDescription(new TranslatableMarkup('Language the definitions/translations on this deck\'s cards are written in.'))
      ->setDefaultValue('vi')
      ->setSetting('allowed_values', [
        'en' => 'English',
        'vi' => 'Vietnamese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'fr' => 'French',
        'de' => 'German',
        'es' => 'Spanish',
        'other' => 'Other',
      ])
      ->setDisplayOptions('form', ['type' => 'options_select', 'weight' => -4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published status'))
      ->setDescription(new TranslatableMarkup('A boolean indicating whether the deck is published.'))
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
   * Auto-fills field_machine_name from the deck name when left blank, so
   * an editor never has to invent a slug by hand (via DeckForm or the JSON
   * importer). This only supplies a default value — it does NOT enforce
   * uniqueness itself: the UniqueField constraint on the field catches
   * collisions for form-based saves (ContentEntityForm validates before
   * saving), and ImportBatchService's callers check explicitly via
   * ImportValidator before ever creating an entity, since batch operations
   * save directly without going through form validation.
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);

    if ($this->get('field_machine_name')->isEmpty() && !$this->get('name')->isEmpty()) {
      $slug = mb_strtolower(trim((string) $this->label()));
      $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
      $slug = trim($slug, '-');
      $this->set('field_machine_name', mb_substr($slug, 0, 64));
    }
  }

}
