<?php

declare(strict_types=1);

namespace Drupal\lms_order\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

final class OrderAccessControlHandler extends EntityAccessControlHandler {

  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    if ($account->hasPermission('administer lms_order')) {
      return AccessResult::allowed();
    }
    return match ($operation) {
      'view' => AccessResult::allowedIf($entity->getOwnerId() === (int) $account->id()),
      default => AccessResult::forbidden(),
    };
  }

  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'create lms_order');
  }

}
