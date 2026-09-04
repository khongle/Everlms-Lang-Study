<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\lms_flashcard\Entity\Card;
use Drupal\user\EntityOwnerInterface;

/**
 * Access control handler for Flashcard Deck and Card entities.
 *
 * Reuses the course-level view/edit permissions from
 * lms_course.permissions.yml, same as QuizAccessControlHandler, so
 * instructors who can manage courses can manage flashcard content without a
 * separate permission to remember to grant.
 *
 * On top of that, the entity's own author may always view/update/delete it,
 * even without the course-management permission — the JSON import form
 * (/manage/flashcard-decks/import) is open to any authenticated user, so a
 * student importing their own vocabulary still needs to be able to manage
 * what they created afterwards.
 */
final class FlashcardAccessControlHandler extends EntityAccessControlHandler {

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
   * Deck (and Review) carry their own uid directly. Card has no owner field
   * of its own — it inherits its deck's owner, since a card is only ever
   * managed through the deck it belongs to.
   */
  private function getOwnerId(EntityInterface $entity): ?int {
    if ($entity instanceof EntityOwnerInterface) {
      return (int) $entity->getOwnerId();
    }
    if ($entity instanceof Card) {
      $deck = $entity->get('field_deck')->entity;
      return $deck instanceof EntityOwnerInterface ? (int) $deck->getOwnerId() : NULL;
    }
    return NULL;
  }

}
