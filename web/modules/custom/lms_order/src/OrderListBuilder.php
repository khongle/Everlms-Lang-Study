<?php

namespace Drupal\lms_order;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Order entities.
 *
 * @ingroup lms_order
 */
class OrderListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['order_number'] = $this->t('Order Number');
    $header['owner'] = $this->t('Customer');
    $header['total'] = $this->t('Total');
    $header['payment_method'] = $this->t('Payment Method');
    $header['status'] = $this->t('Status');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\lms_order\Entity\Order $entity */
    $row['id'] = $entity->id();
    $row['order_number'] = Link::createFromRoute(
      $entity->get('field_order_number')->value ?: 'N/A',
      'entity.lms_order.edit_form',
      ['lms_order' => $entity->id()]
    );
    
    $owner = $entity->getOwner();
    $row['owner'] = $owner ? $owner->getDisplayName() : 'Anonymous';
    
    $row['total'] = '$' . number_format((float) $entity->get('field_total')->value, 2);
    $row['payment_method'] = $entity->get('field_payment_method')->value;
    
    // Status with color code
    $status = $entity->get('field_status')->value;
    $status_label = ucfirst($status);
    if ($status === 'completed') {
       $row['status']['data'] = ['#markup' => '<span style="color: #10b981; font-weight: bold;">' . $status_label . '</span>'];
    } elseif ($status === 'pending') {
       $row['status']['data'] = ['#markup' => '<span style="color: #f59e0b; font-weight: bold;">' . $status_label . '</span>'];
    } elseif ($status === 'cancelled') {
       $row['status']['data'] = ['#markup' => '<span style="color: #ef4444; font-weight: bold;">' . $status_label . '</span>'];
    } else {
       $row['status'] = $status_label;
    }

    $row['created'] = \Drupal::service('date.formatter')->format($entity->get('created')->value, 'short');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['edit']['title'] = $this->t('View / Edit / Approve');
    return $operations;
  }

}
