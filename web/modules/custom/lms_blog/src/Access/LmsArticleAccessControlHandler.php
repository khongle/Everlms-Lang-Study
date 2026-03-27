<?php

declare(strict_types=1);

namespace Drupal\lms_blog\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the LMS Article entity.
 */
class LmsArticleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer lms_blog')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        if (!$entity->get('status')->value) {
          return AccessResult::allowedIfHasPermission($account, 'edit any lms_article')
            ->cachePerPermissions()
            ->addCacheableDependency($entity);
        }
        return AccessResult::allowed();

      case 'update':
        if ($account->hasPermission('edit any lms_article')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        return AccessResult::allowedIfHasPermission($account, 'edit own lms_article')
          ->andIf(AccessResult::allowedIf($entity->getOwnerId() === $account->id()))
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);

      case 'delete':
        if ($account->hasPermission('delete any lms_article')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        return AccessResult::allowedIfHasPermission($account, 'delete own lms_article')
          ->andIf(AccessResult::allowedIf($entity->getOwnerId() === $account->id()))
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    if ($account->hasPermission('administer lms_blog')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return AccessResult::allowedIfHasPermission($account, 'create lms_article')->cachePerPermissions();
  }

}
