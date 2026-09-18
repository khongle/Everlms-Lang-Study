<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\lms_listening\Entity\BackgroundMusicTrack;
use Drupal\user\EntityOwnerInterface;

/**
 * Access control handler for BackgroundMusicTrack.
 *
 * Deliberately does NOT reuse ListeningAccessControlHandler's `view
 * lms_course` / `create lms_course` gating — background music tracks are a
 * shared library any logged-in user may contribute to and browse (see the
 * product decision recorded in BackgroundMusicTrack's docblock), not
 * course-management content restricted to staff. Only update/delete stay
 * owner-or-staff, so a student can always remove a clip they added even
 * without `edit lms_course`.
 */
final class BackgroundMusicAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    if (!$entity instanceof BackgroundMusicTrack) {
      return AccessResult::neutral();
    }

    $ownerId = $entity instanceof EntityOwnerInterface ? (int) $entity->getOwnerId() : NULL;
    $isOwner = $ownerId !== NULL && $ownerId === (int) $account->id();
    $ownResult = AccessResult::allowedIf($isOwner)
      ->cachePerUser()
      ->addCacheableDependency($entity);

    return match ($operation) {
      // Every logged-in user may browse published tracks; an unpublished
      // one (soft-hidden, no UI toggle yet — see the entity's docblock) is
      // visible only to its owner or staff.
      'view' => AccessResult::allowedIf($account->isAuthenticated() && (bool) $entity->get('status')->value)
        ->addCacheableDependency($entity)
        ->orIf($ownResult)
        ->orIf(AccessResult::allowedIfHasPermission($account, 'edit lms_course')),
      'update', 'delete' => AccessResult::allowedIfHasPermission($account, 'edit lms_course')->orIf($ownResult),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   *
   * Any authenticated user may add a track to the shared library — see
   * lms_listening.api_music_upload's `_user_is_logged_in` requirement,
   * which this mirrors.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIf($account->isAuthenticated());
  }

}
