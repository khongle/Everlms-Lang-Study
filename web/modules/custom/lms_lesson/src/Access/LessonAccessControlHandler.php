<?php

declare(strict_types=1);

namespace Drupal\lms_lesson\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for Lesson entities.
 */
final class LessonAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    return match ($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view lms_course'),
      'update', 'delete' => AccessResult::allowedIfHasPermission($account, 'edit lms_course')
        ->orIf(AccessResult::allowedIf(
          $account->hasPermission('edit own lms_course') &&
          $this->isAttachedToOwnCourse($entity, $account)
        )->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity)),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'create lms_course');
  }

  /**
   * Checks whether a lesson belongs to a course owned by the account.
   */
  private function isAttachedToOwnCourse(EntityInterface $entity, AccountInterface $account): bool {
    $chapter_ids = \Drupal::entityTypeManager()->getStorage('lms_chapter')->getQuery()
      ->condition('field_lessons', $entity->id())
      ->accessCheck(FALSE)
      ->range(0, 10)
      ->execute();

    if (!$chapter_ids) {
      return FALSE;
    }

    $course_ids = \Drupal::entityTypeManager()->getStorage('lms_course')->getQuery()
      ->condition('field_chapters', $chapter_ids, 'IN')
      ->accessCheck(FALSE)
      ->range(0, 5)
      ->execute();

    if (!$course_ids) {
      return FALSE;
    }

    $courses = \Drupal::entityTypeManager()->getStorage('lms_course')->loadMultiple($course_ids);
    foreach ($courses as $course) {
      if (method_exists($course, 'getOwnerId') && (int) $course->getOwnerId() === (int) $account->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
