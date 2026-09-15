<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\lms_reading\Entity\ReadingContextItem;
use Drupal\lms_reading\Entity\ReadingWordNote;
use Drupal\user\EntityOwnerInterface;

/**
 * Access control handler for all lms_reading entities.
 *
 * Reuses lms_course's view/edit/create permissions, same as
 * FlashcardAccessControlHandler and QuestionAccessControlHandler, so staff
 * who can already manage courses/flashcards/questions can manage reading
 * content without a separate permission to grant. The entity's own author
 * may always view/update/delete it, matching FlashcardAccessControlHandler
 * — the JSON import form is open to any authenticated user.
 */
final class ReadingAccessControlHandler extends EntityAccessControlHandler {

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

    $ownerId = $this->getOwnerId($entity);
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

  /**
   * Resolves the uid that "owns" an entity for own-content access checks.
   *
   * ReadingPassage and ReadingAttempt carry their own uid directly.
   * ReadingContextItem/ReadingWordNote have no owner of their own — they
   * inherit their passage's owner, mirroring how lms_flashcard's Card
   * inherits its deck's owner.
   */
  private function getOwnerId(EntityInterface $entity): ?int {
    if ($entity instanceof EntityOwnerInterface) {
      return (int) $entity->getOwnerId();
    }
    if ($entity instanceof ReadingWordNote) {
      $passage = $entity->get('field_passage')->entity;
      return $passage instanceof EntityOwnerInterface ? (int) $passage->getOwnerId() : NULL;
    }
    if ($entity instanceof ReadingContextItem) {
      // Not referenced back to its passage directly (see
      // ReadingPassage::field_context_items, the reference runs the other
      // way) — fall back to permission-only access, same as a Question/
      // Answer entity with no owner concept of its own.
      return NULL;
    }
    return NULL;
  }

}
