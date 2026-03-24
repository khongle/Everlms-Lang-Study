<?php

declare(strict_types=1);

namespace Drupal\lms_membership\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\lms_membership\Entity\UserMembership;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for Membership routes.
 */
class MembershipController extends ControllerBase {

  public function myMembership() {
    $user_id = $this->currentUser()->id();
    
    // Find active memberships for the current user.
    $storage = $this->entityTypeManager()->getStorage('lms_user_membership');
    $query = $storage->getQuery()
      ->condition('uid', $user_id)
      ->condition('field_status', ['active', 'trial'], 'IN')
      ->accessCheck(FALSE)
      ->sort('field_end_date', 'DESC');
    $mids = $query->execute();

    $memberships = [];
    if (!empty($mids)) {
      $entities = $storage->loadMultiple($mids);
      foreach ($entities as $m) {
        $plan = $m->get('field_plan')->entity;
        $memberships[] = [
          'id' => $m->id(),
          'plan_name' => $plan ? $plan->label() : t('Unknown Plan'),
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
