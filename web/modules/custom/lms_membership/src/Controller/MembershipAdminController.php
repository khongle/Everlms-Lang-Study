<?php

namespace Drupal\lms_membership\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class MembershipAdminController extends ControllerBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function pricingList(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = trim((string) $request->query->get('search', ''));
    $status_filter = (string) $request->query->get('status', 'all');
    if (!in_array($status_filter, ['all', 'active', 'inactive'], TRUE)) {
      $status_filter = 'all';
    }
    $billing = (string) $request->query->get('billing', 'all');
    if (!in_array($billing, ['all', 'monthly', 'quarterly', 'yearly', 'lifetime'], TRUE)) {
      $billing = 'all';
    }

    $plan_storage = $this->entityTypeManager->getStorage('lms_membership_plan');
    $query = $plan_storage->getQuery()->accessCheck(FALSE);

    if ($search) {
      $query->condition('name', '%' . $search . '%', 'LIKE');
    }

    if ($status_filter !== 'all') {
      $status_val = ($status_filter === 'active') ? 1 : 0;
      $query->condition('status', $status_val);
    }

    if ($billing !== 'all') {
      $query->condition('field_billing_cycle', $billing);
    }
    $ids = $query->sort('field_price', 'ASC')->pager(25)->execute();
    
    $plans = $ids ? $plan_storage->loadMultiple($ids) : [];

    $items = [];
    foreach ($plans as $plan) {
      $items[] = [
        'id' => $plan->id(),
        'name' => $plan->label(),
        'price' => (float) ($plan->get('field_price')->value ?? 0),
        'billing' => $plan->get('field_billing_cycle')->value ?? 'monthly',
        'status' => $plan->hasField('status') && $plan->get('status')->value ? 'Active' : 'Disabled',
        'edit_url' => Url::fromRoute('lms_membership.admin_pricing.edit', ['lms_membership_plan' => $plan->id()], ['query' => ['destination' => '/workspace/pricing']])->toString(),
        'toggle_url' => Url::fromRoute('lms_membership.admin_pricing.toggle', ['lms_membership_plan' => $plan->id()])->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_pricing',
      '#plans' => $items,
      '#pager' => ['#type' => 'pager'],
      '#filters' => [
        'search' => $search,
        'status' => $status_filter,
        'billing' => $billing,
      ],
      '#add_url' => Url::fromRoute('lms_membership.admin_pricing.add', [], ['query' => ['destination' => '/workspace/pricing']])->toString(),
      '#cache' => [
        'contexts' => ['url.query_args'],
        'tags' => ['lms_membership_plan_list'],
      ],
    ];
  }

  public function toggleStatus(\Drupal\Core\Entity\EntityInterface $lms_membership_plan) {
    if ($lms_membership_plan->hasField('status')) {
      $current_status = $lms_membership_plan->get('status')->value;
      $lms_membership_plan->set('status', !$current_status);
      $lms_membership_plan->save();
      $status_text = !$current_status ? 'activated' : 'deactivated';
      $this->messenger()->addMessage($this->t('Plan %name has been %status.', ['%name' => $lms_membership_plan->label(), '%status' => $status_text]));
    }
    return $this->redirect('lms_membership.admin_pricing');
  }

  public function pricingAddForm(): array {
    $plan = $this->entityTypeManager->getStorage('lms_membership_plan')->create([]);
    return $this->buildPlanForm($plan, 'Create Pricing Plan');
  }

  public function pricingEditForm(\Drupal\Core\Entity\EntityInterface $lms_membership_plan): array {
    return $this->buildPlanForm($lms_membership_plan, 'Edit Pricing Plan: ' . $lms_membership_plan->label());
  }

  private function buildPlanForm(\Drupal\Core\Entity\EntityInterface $plan, string $title): array {
    $form = \Drupal::service('entity.form_builder')->getForm($plan, 'default');
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('@title', ['@title' => $title]),
      '#active_menu' => 'pricing',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }
  public function userMembershipsList(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = trim((string) $request->query->get('search', ''));
    $status_filter = (string) $request->query->get('status', 'all');
    if (!in_array($status_filter, ['all', 'active', 'trial', 'expired', 'cancelled'], TRUE)) {
      $status_filter = 'all';
    }

    $storage = $this->entityTypeManager->getStorage('lms_user_membership');
    $query = $storage->getQuery()->accessCheck(FALSE);

    if ($status_filter !== 'all') {
      $query->condition('field_status', $status_filter);
    }

    if ($search !== '') {
      $user_ids = $this->matchingUserIds($search);
      $plan_ids = $this->entityTypeManager->getStorage('lms_membership_plan')->getQuery()
        ->accessCheck(FALSE)
        ->condition('name', '%' . $search . '%', 'LIKE')
        ->execute();
      $matches = $query->orConditionGroup();
      if ($user_ids) {
        $matches->condition('uid', $user_ids, 'IN');
      }
      if ($plan_ids) {
        $matches->condition('field_plan', $plan_ids, 'IN');
      }
      if ($user_ids || $plan_ids) {
        $query->condition($matches);
      }
      else {
        $query->condition('id', 0);
      }
    }
    $ids = $query->sort('created', 'DESC')->pager(25)->execute();
    $memberships = $ids ? $storage->loadMultiple($ids) : [];

    $items = [];
    $date_formatter = \Drupal::service('date.formatter');
    foreach ($memberships as $m) {
      $owner = $m->getOwner();
      $plan = $m->get('field_plan')->entity;
      
      $full_name = 'N/A';
      $avatar_url = '';
      if ($owner) {
        $first_name = $owner->hasField('field_first_name') && !$owner->get('field_first_name')->isEmpty() ? $owner->get('field_first_name')->value : '';
        $last_name = $owner->hasField('field_last_name') && !$owner->get('field_last_name')->isEmpty() ? $owner->get('field_last_name')->value : '';
        $full_name = trim($first_name . ' ' . $last_name);
        if (empty($full_name)) {
          $full_name = $owner->getDisplayName();
        }
        
        if ($owner->hasField('user_picture') && !$owner->get('user_picture')->isEmpty()) {
          $file = $owner->get('user_picture')->entity;
          if ($file) {
            $avatar_url = $file->createFileUrl();
          }
        }
      }
      
      if (empty($avatar_url)) {
        $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($full_name !== 'N/A' ? $full_name : 'U') . '&background=1d70f5&color=fff&size=80';
      }

      $items[] = [
        'id' => $m->id(),
        'user_name' => $full_name,
        'user_avatar' => $avatar_url,
        'email' => $owner ? $owner->getEmail() : '',
        'plan_name' => $plan ? $plan->label() : 'N/A',
        'start_date' => $m->get('field_start_date')->value ? $date_formatter->format($m->get('field_start_date')->value, 'short') : '',
        'end_date' => $m->get('field_end_date')->value ? $date_formatter->format($m->get('field_end_date')->value, 'short') : 'Lifetime',
        'status' => $m->get('field_status')->value,
        'auto_renew' => $m->get('field_auto_renew')->value ? 'Yes' : 'No',
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_membership.admin_user_memberships.edit', ['lms_user_membership' => $m->id()], ['query' => ['destination' => '/workspace/user-memberships']])->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_user_memberships',
      '#memberships' => $items,
      '#pager' => ['#type' => 'pager'],
      '#filters' => [
        'search' => $search,
        'status' => $status_filter,
      ],
      '#add_url' => \Drupal\Core\Url::fromRoute('lms_membership.admin_user_memberships.add', [], ['query' => ['destination' => '/workspace/user-memberships']])->toString(),
      '#cache' => [
        'contexts' => ['url.query_args'],
        'tags' => ['lms_user_membership_list', 'lms_membership_plan_list', 'user_list'],
      ],
    ];
  }

  /**
   * Matches names and email, including optional profile name fields.
   */
  private function matchingUserIds(string $search): array {
    $query = $this->entityTypeManager->getStorage('user')->getQuery()->accessCheck(FALSE);
    $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user');
    foreach (preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) as $term) {
      $matches = $query->orConditionGroup()
        ->condition('name', '%' . $term . '%', 'LIKE')
        ->condition('mail', '%' . $term . '%', 'LIKE');
      foreach (['field_first_name', 'field_last_name'] as $field) {
        if (isset($definitions[$field])) {
          $matches->condition($field, '%' . $term . '%', 'LIKE');
        }
      }
      $query->condition($matches);
    }
    return $query->execute();
  }

  public function userMembershipAddForm(): array {
    $membership = $this->entityTypeManager->getStorage('lms_user_membership')->create([]);
    return $this->buildUserMembershipForm($membership, 'Create User Membership');
  }

  public function userMembershipEditForm(\Drupal\Core\Entity\EntityInterface $lms_user_membership): array {
    return $this->buildUserMembershipForm($lms_user_membership, 'Edit User Membership: ' . $lms_user_membership->label());
  }

  private function buildUserMembershipForm(\Drupal\Core\Entity\EntityInterface $membership, string $title): array {
    $form = \Drupal::service('entity.form_builder')->getForm($membership, 'manager');
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('@title', ['@title' => $title]),
      '#active_menu' => 'user_memberships',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }
}
