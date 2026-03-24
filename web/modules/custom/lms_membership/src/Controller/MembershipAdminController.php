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
    $search = $request->query->get('search', '');
    $status_filter = $request->query->get('status', 'all');

    $plan_storage = $this->entityTypeManager->getStorage('lms_membership_plan');
    $query = $plan_storage->getQuery()->accessCheck(FALSE);

    if ($search) {
      $query->condition('name', '%' . $search . '%', 'LIKE');
    }

    if ($status_filter !== 'all') {
      $status_val = ($status_filter === 'active') ? 1 : 0;
      $query->condition('status', $status_val);
    }

    $ids = $query->sort('field_price', 'ASC')->execute();
    
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
      '#filters' => [
        'search' => $search,
        'status' => $status_filter,
      ],
      '#add_url' => Url::fromRoute('lms_membership.admin_pricing.add', [], ['query' => ['destination' => '/workspace/pricing']])->toString(),
      '#cache' => [
        'contexts' => ['url.query_args'],
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
}
