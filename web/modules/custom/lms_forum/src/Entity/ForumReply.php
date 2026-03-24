<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Forum Reply entity (threaded).
 */
#[ContentEntityType(
  id: 'lms_forum_reply',
  label: new TranslatableMarkup('Forum Reply'),
  base_table: 'lms_forum_reply',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: [
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => '\Drupal\lms_forum\Form\ForumReplyForm', 
      'delete' => '\Drupal\lms_forum\Form\ForumReplyDeleteForm'
    ],
    'access' => 'Drupal\lms_forum\ForumReplyAccessControlHandler',
  ],
  links: [
    'delete-form' => '/forum/reply/{lms_forum_reply}/delete',
  ],
)]
class ForumReply extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_topic'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Topic'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_forum_topic');

    $fields['field_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(new TranslatableMarkup('Reply'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['field_parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Parent Reply'))
      ->setSetting('target_type', 'lms_forum_reply');

    $fields['field_best_answer'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Best Answer'))
      ->setDefaultValue(FALSE);

    $fields['field_upvotes'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Upvotes'))
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
