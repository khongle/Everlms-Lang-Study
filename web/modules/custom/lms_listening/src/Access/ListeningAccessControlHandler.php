<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Access control handler for all lms_listening entities.
 *
 * Reuses lms_course's view/edit/create permissions, same as
 * ReadingAccessControlHandler, so staff who can already manage
 * courses/flashcards/questions/reading can manage listening content
 * without a separate permission to grant. The entity's own author may
 * always view/update/delete it — the JSON import form is open to any
 * authenticated user.
 */
final class ListeningAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    $permission = match ($operation) {
      'view' => 'view lms_course',
      'update', 'delete' => 'edit lms_course',
      default => NULL,
    };
    if ($permission === NULL) {
      return AccessResult::neutral();
    }

    $ownerId = $entity instanceof EntityOwnerInterface ? (int) $entity->getOwnerId() : NULL;
    $ownResult = AccessResult::allowedIf($ownerId !== NULL && $ownerId === (int) $account->id())
      ->cachePerUser()
      ->addCacheableDependency($entity);

    return AccessResult::allowedIfHasPermission($account, $permission)->orIf($ownResult);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'create lms_course');
  }

}
