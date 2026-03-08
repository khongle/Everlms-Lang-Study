<?php

declare(strict_types=1);

namespace Drupal\lms_course\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for Course entities.
 */
final class CourseAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    return match ($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view lms_course'),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit lms_course')
        ->orIf(AccessResult::allowedIf(
          $account->hasPermission('edit own lms_course') &&
          $entity->getOwnerId() === (int) $account->id()
        )),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete lms_course'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'create lms_course');
  }

}
