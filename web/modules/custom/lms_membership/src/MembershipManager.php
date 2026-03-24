<?php

namespace Drupal\lms_membership;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Service for managing user memberships and checking access.
 */
class MembershipManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MembershipManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks if a user has an active membership.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to check.
   *
   * @return bool
   *   TRUE if the user has an active membership, FALSE otherwise.
   */
  public function hasActiveMembership(AccountInterface $account): bool {
    if ($account->hasPermission('administer lms_course')) {
      return TRUE;
    }

    $storage = $this->entityTypeManager->getStorage('lms_user_membership');
    $query = $storage->getQuery()
      ->condition('uid', $account->id())
      ->condition('field_status', ['active', 'trial'], 'IN')
      ->condition('field_end_date', time(), '>') // Must not be expired yet
      ->accessCheck(FALSE)
      ->range(0, 1);
    
    $mids = $query->execute();
    return !empty($mids);
  }

}
