<?php

declare(strict_types=1);

namespace Drupal\lms_membership;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Membership Plan entities.
 */
class MembershipPlanListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Plan Name');
    $header['price'] = $this->t('Price');
    $header['billing_cycle'] = $this->t('Billing Cycle');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\lms_membership\Entity\MembershipPlan $entity */
    $row['name'] = ['data' => $entity->toLink($entity->label(), 'edit-form')->toRenderable()];
    $row['price'] = '$' . $entity->get('field_price')->value;
    $row['billing_cycle'] = ucfirst($entity->get('field_billing_cycle')->value);
    
    $status = $entity->get('status')->value;
    $status_markup = $status ? '<span class="badge-lms badge-lms--success">' . $this->t('Active') . '</span>' : '<span class="badge-lms badge-lms--danger">' . $this->t('Inactive') . '</span>';
    $row['status'] = ['data' => ['#markup' => $status_markup]];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    $build['table']['#attributes']['class'] = ['lms-table'];
    $build['table']['#prefix'] = '<div class="card-lms"><div class="lms-table-responsive">';
    $build['table']['#suffix'] = '</div></div>';

    $add_button = [
      '#type' => 'link',
      '#title' => $this->t('+ Add Membership Plan'),
      '#url' => \Drupal\Core\Url::fromRoute('entity.lms_membership_plan.add_form'),
      '#attributes' => [
        'class' => ['btn-lms-primary'],
        'style' => 'width: fit-content;',
      ],
    ];

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-admin__main']],
      'header' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-admin__header']],
        'title' => [
          '#markup' => '<h1 class="lms-admin__title" style="margin-bottom:0;">Membership Plans</h1>',
        ],
        'actions' => $add_button,
      ],
      'content' => $build,
    ];
  }

}
