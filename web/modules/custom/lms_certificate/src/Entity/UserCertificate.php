<?php

declare(strict_types=1);

namespace Drupal\lms_certificate\Entity;

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
 * Defines the User Certificate (issued) entity.
 */
#[ContentEntityType(
  id: 'lms_user_certificate',
  label: new TranslatableMarkup('User Certificate'),
  base_table: 'lms_user_certificate',
  admin_permission: 'administer lms_course',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'owner' => 'uid'],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'access' => 'Drupal\Core\Entity\EntityAccessControlHandler',
  ],
)]
class UserCertificate extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_template'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Template'))
      ->setRequired(TRUE)->setSetting('target_type', 'lms_certificate_template');

    $fields['field_quiz_result'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Quiz Result'))
      ->setSetting('target_type', 'lms_quiz_result');

    $fields['field_course'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Course'))
      ->setSetting('target_type', 'lms_course');

    $fields['field_certificate_number'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Certificate Number'))
      ->setSetting('max_length', 32);

    $fields['field_pdf_file'] = BaseFieldDefinition::create('file')
      ->setLabel(new TranslatableMarkup('PDF File'))
      ->setSetting('file_directory', 'certificates/issued')
      ->setSetting('file_extensions', 'pdf');

    $fields['field_issued_at'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Issued At'));

    $fields['field_expiry_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Expiry Date'))
      ->setSetting('datetime_type', 'date');

    $fields['field_revoked'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Revoked'))
      ->setDefaultValue(FALSE);

    $fields['field_revoke_reason'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Revocation Reason'))
      ->setSetting('max_length', 255);

    return $fields;
  }

}
