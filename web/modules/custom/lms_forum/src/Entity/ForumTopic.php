<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Forum Topic entity.
 */
#[ContentEntityType(
  id: 'lms_forum_topic',
  label: new TranslatableMarkup('Forum Topic'),
  label_collection: new TranslatableMarkup('Forum Topics'),
  base_table: 'lms_forum_topic',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'name', 'owner' => 'uid'],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
  links: [
    'canonical' => '/forum/{lms_forum_topic}',
    'add-form' => '/forum/new',
    'edit-form' => '/forum/{lms_forum_topic}/edit',
    'delete-form' => '/forum/{lms_forum_topic}/delete',
    'collection' => '/forum',
  ],
)]
class ForumTopic extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Body'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setSetting('target_type', 'lms_course')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => -8])
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_replies_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Replies'))
      ->setDefaultValue(0);

    $fields['field_pinned'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Pinned'))
      ->setDefaultValue(FALSE);

    $fields['field_locked'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Locked'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
