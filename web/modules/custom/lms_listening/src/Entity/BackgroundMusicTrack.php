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

/**
 * Defines the Background Music Track entity.
 *
 * A small shared library of mp3/ogg/m4a clips any logged-in user may add
 * (see BackgroundMusicController::upload()) for Passive Listening to loop
 * quietly underneath the narration — entirely separate from
 * ListeningPassage.field_audio (that's the spoken narration itself; this is
 * optional instrumental background). Published tracks (status = TRUE, the
 * default) are visible to every learner in the "Background music" picker on
 * /learn/listening-passive/*, not just their uploader — it's a shared
 * library, not a personal one. Only the uploader (or anyone with `edit
 * lms_course`) may delete a track — see BackgroundMusicAccessControlHandler.
 *
 * Deliberately has no view/edit/delete routes or list_builder of its own —
 * unlike ListeningPassage/Deck, there is no Drupal-admin-style CRUD UI for
 * this entity. It's managed entirely through
 * BackgroundMusicController::library()/upload()/delete(), a small bespoke
 * page + two AJAX endpoints, since the only things needed are "add a clip"
 * and "remove a clip I added" — a full entity form would be overkill.
 */
#[ContentEntityType(
  id: 'lms_listening_bg_track',
  label: new TranslatableMarkup('Background Music Track'),
  label_collection: new TranslatableMarkup('Background Music Tracks'),
  base_table: 'lms_listening_bg_track',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'owner' => 'uid',
    'published' => 'status',
  ],
  handlers: [
    'access' => 'Drupal\lms_listening\Access\BackgroundMusicAccessControlHandler',
  ],
)]
class BackgroundMusicTrack extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['field_file'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('Audio file'))
      ->setRequired(TRUE)
      ->setSetting('file_extensions', 'mp3 ogg m4a')
      ->setSetting('max_filesize', '15 MB')
      ->setSetting('uri_scheme', 'public');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
