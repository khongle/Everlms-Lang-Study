<?php

namespace Drupal\lms_forum;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Forum Topic entity.
 */
class ForumTopicAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer lms_course')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'update':
      case 'delete':
        // Allow authors to edit/delete their own topic.
        return AccessResult::allowedIf($account->isAuthenticated() && $account->id() === $entity->getOwnerId())->cachePerUser();
    }

    // Pass access logic to next layer.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Authenticated users can create topics (global or course-scoped based on form fields).
    return AccessResult::allowedIf($account->isAuthenticated())->cachePerUser();
  }

}
