<?php

declare(strict_types=1);

namespace Drupal\lms_order\Entity;

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
 * Defines the Order entity.
 */
#[ContentEntityType(
  id: 'lms_order',
  label: new TranslatableMarkup('Order'),
  label_collection: new TranslatableMarkup('Orders'),
  base_table: 'lms_order',
  admin_permission: 'administer lms_order',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_order\Access\OrderAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/orders/{lms_order}',
    'collection' => '/manage/orders',
  ],
)]
class Order extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_order_number'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Order Number'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Items'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_order_item')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_subtotal'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Subtotal'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2);

    $fields['field_discount'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Discount'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2);

    $fields['field_total'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Total'))
      ->setDefaultValue(0)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_currency'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Currency'))
      ->setDefaultValue('USD')
      ->setSetting('max_length', 3);

    $fields['field_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDefaultValue('pending')
      ->setSetting('allowed_values', [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_payment_method'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Payment Method'))
      ->setSetting('max_length', 32);

    $fields['field_payment_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Payment Transaction ID'))
      ->setSetting('max_length', 128);

    $fields['field_coupon_code'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Coupon Code'))
      ->setSetting('max_length', 32);

    $fields['field_reminder_sent'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Reminder Sent'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

}
