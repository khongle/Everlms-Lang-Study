<?php

declare(strict_types=1);

namespace Drupal\lms_note\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_note',
  label: new TranslatableMarkup('Student Note'),
  base_table: 'lms_note',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: ['views_data' => EntityViewsData::class, 'access' => 'Drupal\Core\Entity\EntityAccessControlHandler'],
)]
class Note extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    // Course/Lesson stay specific to the original course-video note flow —
    // NOT required any more (a Listening/Reading note has neither) now
    // that field_context_type/field_context_id below cover every kind of
    // note generically. See NOTE_EXPANSION_PLAN.md.
    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setRequired(FALSE)->setSetting('target_type', 'lms_course');

    $fields['field_lesson'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Lesson'))
      ->setRequired(FALSE)->setSetting('target_type', 'lms_lesson');

    // Generic "what is this note about" pair, covering every note this
    // module handles — a course lesson (field_context_type = 'lesson',
    // still paired with field_course/field_lesson above for that one
    // case so the existing course-grouped My Notes view keeps working
    // unchanged) plus the newer content types (see
    // NoteController::CONTEXT_TYPES). One note per (uid, context_type,
    // context_id) — same "load existing or create" pattern
    // NoteController already used for field_lesson, generalized.
    $fields['field_context_type'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Context type'))
      ->setSetting('max_length', 32);

    $fields['field_context_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Context ID'))
      ->setSetting('unsigned', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Note'))
      ->setRequired(TRUE);

    $fields['field_video_timestamp'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Video Timestamp'))
      ->setDescription(new TranslatableMarkup('Seconds into the video.'))
      ->setDefaultValue(0);

    $fields['field_attachments'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Attachments'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('file_directory', 'notes/attachments/[current-user:uid]')
      ->setSetting('file_extensions', 'mp3 wav pdf jpg jpeg png gif')
      ->setSetting('max_filesize', '10 MB')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
