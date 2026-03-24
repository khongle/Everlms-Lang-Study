<?php

declare(strict_types=1);

namespace Drupal\lms_membership;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of User Membership entities.
 */
class UserMembershipListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['user'] = [
      'data' => $this->t('User'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    $header['plan'] = [
      'data' => $this->t('Plan'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    $header['start_date'] = [
      'data' => $this->t('Start Date'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    $header['end_date'] = [
      'data' => $this->t('End Date'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    $header['status'] = [
      'data' => $this->t('Status'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    $header['auto_renew'] = [
      'data' => $this->t('Auto Renew'),
      'class' => ['px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-400', 'uppercase', 'tracking-wider'],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\lms_membership\Entity\UserMembership $entity */
    
    // User label
    $owner = $entity->getOwner();
    $row['user'] = [
      'data' => $owner ? $owner->toLink()->toRenderable() : ['#markup' => ''],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'font-medium', 'text-white'],
    ];

    // Plan label
    $plan = $entity->get('field_plan')->entity;
    $row['plan'] = [
      'data' => ['#markup' => $plan ? $plan->label() : ''],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-300'],
    ];

    // Dates
    $date_formatter = \Drupal::service('date.formatter');
    $start_date = $entity->get('field_start_date')->value;
    $end_date = $entity->get('field_end_date')->value;
    
    $row['start_date'] = [
      'data' => ['#markup' => $start_date ? $date_formatter->format($start_date, 'short') : ''],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-400'],
    ];
    $row['end_date'] = [
      'data' => ['#markup' => $end_date ? $date_formatter->format($end_date, 'short') : ''],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-400'],
    ];

    $status = $entity->get('field_status')->value;
    $status_color = $status == 'active' ? 'bg-green-100 text-green-800' : ($status == 'trial' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800');
    $status_markup = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_color . '">' . ucfirst($status) . '</span>';
    $row['status'] = [
      'data' => ['#markup' => $status_markup],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm'],
    ];

    $row['auto_renew'] = [
      'data' => ['#markup' => $entity->get('field_auto_renew')->value ? $this->t('Yes') : $this->t('No')],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-400'],
    ];
    
    $parent_row = parent::buildRow($entity);
    $row['operations'] = [
      'data' => $parent_row['operations'],
      'class' => ['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'font-medium'],
    ];

    $row['#attributes']['class'][] = 'hover:bg-gray-800/50 transition-colors border-b border-gray-700/50';

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    $build['table']['#attributes']['class'] = ['min-w-full', 'divide-y', 'divide-gray-800', 'bg-gray-800/30', 'rounded-xl', 'overflow-hidden', 'ring-1', 'ring-white/5'];

    $add_button = [
      '#type' => 'link',
      '#title' => $this->t('+ Add User Membership'),
      '#url' => \Drupal\Core\Url::fromRoute('entity.lms_user_membership.add_form'),
      '#attributes' => [
        'class' => ['lms-btn', 'lms-btn--primary', 'inline-flex', 'items-center', 'mb-6'],
      ],
    ];

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'mx-auto', 'py-8', 'px-4', 'max-w-7xl']],
      'title' => [
        '#markup' => '<h1 class="text-3xl font-bold text-white mb-6">Manage User Subscriptions</h1>',
      ],
      'add_button' => $add_button,
      'content' => $build,
    ];
  }

}
