<?php

declare(strict_types=1);

namespace Drupal\lms_wishlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller for managing student wishlists.
 */
class WishlistController extends ControllerBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Toggles a course in the user's wishlist.
   */
  public function toggle(Request $request): JsonResponse {
    $uid = $this->currentUser()->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);

    if (empty($data['course_id'])) {
      return new JsonResponse(['error' => 'Missing required field: course_id'], 400);
    }

    $course_id = (int) $data['course_id'];
    $storage = $this->entityTypeManager->getStorage('lms_wishlist');

    $query = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_course', $course_id)
      ->accessCheck(FALSE);
    $wids = $query->execute();

    if ($wids) {
      // Remove from wishlist.
      $items = $storage->loadMultiple($wids);
      $storage->delete($items);
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_wishlist:' . $uid]);
      return new JsonResponse(['status' => 'removed', 'course_id' => $course_id]);
    } else {
      // Add to wishlist.
      $item = $storage->create([
        'uid' => $uid,
        'field_course' => $course_id,
      ]);
      $item->save();
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['lms_wishlist:' . $uid]);
      return new JsonResponse(['status' => 'added', 'course_id' => $course_id]);
    }
  }

  /**
   * Gets the wishlist status for an array of courses.
   */
  public function getStatus(Request $request): JsonResponse {
    $uid = $this->currentUser()->id();
    if (!$uid) {
      return new JsonResponse(['status' => [], 'count' => 0]);
    }

    $course_ids = $request->query->get('course_ids');
    $ids = !empty($course_ids) ? explode(',', $course_ids) : [];

    $storage = $this->entityTypeManager->getStorage('lms_wishlist');
    
    $wishlisted = [];
    if (!empty($ids)) {
      $query = $storage->getQuery()
        ->condition('uid', $uid)
        ->condition('field_course', $ids, 'IN')
        ->accessCheck(FALSE);
      $wids = $query->execute();

      if ($wids) {
        $items = $storage->loadMultiple($wids);
        foreach ($items as $item) {
          if (!$item->get('field_course')->isEmpty()) {
            $wishlisted[] = $item->get('field_course')->target_id;
          }
        }
      }
    }

    $result = [];
    foreach ($ids as $id) {
      $result[$id] = in_array((int)$id, $wishlisted);
    }

    $total_count = $storage->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return new JsonResponse(['status' => $result, 'count' => (int) $total_count]);
  }

  /**
   * Renders the My Wishlist user portal page.
   */
  public function myWishlist(): array {
    $uid = $this->currentUser()->id();
    $storage = $this->entityTypeManager->getStorage('lms_wishlist');
    
    $wids = $storage->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $cards = [];
    if ($wids) {
      $items = $storage->loadMultiple($wids);
      
      foreach ($items as $item) {
        if (!$item->get('field_course')->isEmpty()) {
          $course = $item->get('field_course')->entity;
          if ($course && $course->get('status')->value) {
            $card = $this->buildCourseCard($course);
            // Pre-set as wishlisted to avoid AJAX glitching on the wishlist page
            $card['is_wishlisted'] = TRUE;
            $cards[] = $card;
          }
        }
      }
    }

    return [
      '#theme' => 'lms_my_wishlist',
      '#cards' => $cards,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['lms_wishlist:' . $uid],
      ],
    ];
  }

  private function buildCourseCard($course): array {
    $price = (float) $course->get('field_price')->value;
    $category = $course->get('field_category')->entity;
    $level = $course->get('field_level')->entity;
    $duration = (int) $course->get('field_duration')->value;

    $instructor_refs = $course->get('field_instructor')->referencedEntities();
    $instructor_entity = !empty($instructor_refs) ? reset($instructor_refs) : $course->getOwner();
    $instructor_name = $instructor_entity ? $instructor_entity->getDisplayName() : '';
    if ($instructor_entity) {
      $first_name = $instructor_entity->hasField('field_first_name') ? $instructor_entity->get('field_first_name')->value : '';
      $last_name = $instructor_entity->hasField('field_last_name') ? $instructor_entity->get('field_last_name')->value : '';
      $full_name = trim("$first_name $last_name");
      if (!empty($full_name)) {
        $instructor_name = $full_name;
      }
    }
    $instructor_url = $instructor_entity ? \Drupal\Core\Url::fromRoute('lms_course.instructor_profile', ['user' => $instructor_entity->id()])->toString() : '';
    
    $instructor_avatar = '';
    if ($instructor_entity && $instructor_entity->hasField('user_picture') && !$instructor_entity->get('user_picture')->isEmpty()) {
      $file = $instructor_entity->get('user_picture')->entity;
      if ($file) {
         $instructor_avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }

    $uid = $this->currentUser()->id();
    $is_enrolled = FALSE;
    if ($uid) {
      $enrollment_count = $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('uid', $uid)
        ->condition('field_course', $course->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();
      $is_enrolled = $enrollment_count > 0;
    }
    $continue_url = \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString();

    $lmsHelper = \Drupal::service('lms_base.helper');
      $thumbnail_url = '';
      if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
        $file = $course->get('field_thumbnail')->entity;
        if ($file && file_exists($file->getFileUri())) {
          $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

    return [
      'id' => $course->id(),
      'name' => $course->label(),
      'short_description' => $course->get('field_short_description')->value ?? '',
      'price' => $price > 0 ? '$' . number_format($price, 2) : 'Free',
      'price_raw' => $price,
      'rating' => $course->hasField('field_rating') && !$course->get('field_rating')->isEmpty() ? (float) $course->get('field_rating')->value : 5.0,
      'category' => $category?->label() ?? '',
      'category_id' => $category?->id() ?? '',
      'level' => $level?->label() ?? '',
      'level_id' => strtolower($level?->label() ?? ''),
      'duration' => $lmsHelper->formatDuration($duration),
      'bestseller' => (bool) $course->get('field_bestseller')->value,
      'featured' => (bool) $course->get('field_featured')->value,
      'has_thumbnail' => !empty($thumbnail_url),
      'thumbnail_url' => $thumbnail_url,
      'url' => \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString(),
      'continue_url' => $continue_url,
      'instructor_name' => $instructor_name,
      'instructor_avatar' => $instructor_avatar,
      'instructor_url' => $instructor_url,
      'is_enrolled' => $is_enrolled,
    ];
  }
}
