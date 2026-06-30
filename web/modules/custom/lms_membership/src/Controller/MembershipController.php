<?php

declare(strict_types=1);

namespace Drupal\lms_membership\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\lms_membership\Entity\UserMembership;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for Membership routes.
 */
class MembershipController extends ControllerBase {

  public function __construct(
    protected TimeInterface $time,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('datetime.time'),
    );
  }

  public function myMembership() {
    $user_id = $this->currentUser()->id();
    $now = $this->time->getRequestTime();
    
    // Find current active memberships for the current user.
    $storage = $this->entityTypeManager()->getStorage('lms_user_membership');
    $query = $storage->getQuery()
      ->condition('uid', $user_id)
      ->condition('field_status', ['active', 'trial'], 'IN')
      ->condition('field_end_date', $now, '>')
      ->accessCheck(FALSE)
      ->sort('field_end_date', 'DESC');
    $mids = $query->execute();

    $memberships = [];
    $seen_plan_ids = [];
    if (!empty($mids)) {
      $entities = $storage->loadMultiple($mids);
      foreach ($entities as $m) {
        $plan = $m->get('field_plan')->entity;
        if (!$plan) {
          continue;
        }
        $plan_id = (int) $plan->id();
        if (isset($seen_plan_ids[$plan_id])) {
          continue;
        }
        $seen_plan_ids[$plan_id] = TRUE;

        $memberships[] = [
          'id' => $m->id(),
          'plan_name' => $plan->label(),
          'start_date' => $m->get('field_start_date')->value,
          'end_date' => $m->get('field_end_date')->value,
          'auto_renew' => $m->get('field_auto_renew')->value,
          'status' => $m->get('field_status')->value,
        ];
      }
    }

    return [
      '#theme' => 'lms_my_membership',
      '#memberships' => $memberships,
    ];
  }

  public function cancelMembership(UserMembership $lms_user_membership) {
    if ($lms_user_membership->getOwnerId() == $this->currentUser()->id()) {
      $lms_user_membership->set('field_auto_renew', FALSE);
      $lms_user_membership->save();
      $this->messenger()->addMessage($this->t('Your membership auto-renewal has been cancelled.'));
    } else {
      $this->messenger()->addError($this->t('You do not have permission to modify this membership.'));
    }

    return new RedirectResponse(Url::fromRoute('lms_membership.my_membership')->toString());
  }

}
