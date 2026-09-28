<?php

declare(strict_types=1);

namespace Drupal\lms_rating\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for managing Ratings via Workspace.
 */
class RatingManagerController extends ControllerBase {

  /**
   * Renders the rating list table.
   */
  public function list(Request $request) {
    $storage = $this->entityTypeManager()->getStorage('lms_rating');
    $filters = [
      'search' => trim((string) $request->query->get('search', '')),
      'status' => (string) $request->query->get('status', 'all'),
      'score' => (string) $request->query->get('score', 'all'),
    ];
    
    // Default sorting and pagination
    $query = $storage->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->pager(15);
      
    if ($filters['search'] !== '') {
      $query->condition('field_review', '%' . $filters['search'] . '%', 'LIKE');
    }
    if (in_array($filters['status'], ['approved', 'pending'], TRUE)) {
      $query->condition('field_approved', $filters['status'] === 'approved' ? 1 : 0);
    }
    if (in_array($filters['score'], ['1', '2', '3', '4', '5'], TRUE)) {
      $query->condition('field_score', (int) $filters['score']);
    }
    $ids = $query->execute();
    $ratings = $ids ? $storage->loadMultiple($ids) : [];

    $items = [];
    foreach ($ratings as $rating) {
      // Get student details (Owner)
      $student_name = 'Anonymous';
      $avatar_url = '';
      if ($rating->getOwnerId()) {
        $owner = $rating->getOwner();
        if ($owner) {
          $student_name = $owner->getDisplayName();
          // Profile Picture
          if ($owner->hasField('user_picture') && !$owner->get('user_picture')->isEmpty()) {
            $file = $owner->get('user_picture')->entity;
            if ($file) {
              $avatar_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
            }
          }
          if (empty($avatar_url)) {
            $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($student_name) . '&background=random&color=fff';
          }
        }
      }

      // Get Course details
      $course_title = 'Deleted Course';
      if (!$rating->get('field_course')->isEmpty()) {
        $course = $rating->get('field_course')->entity;
        if ($course) {
          $course_title = $course->label();
        }
      }

      $items[] = [
        'id' => $rating->id(),
        'course' => $course_title,
        'student' => $student_name,
        'avatar' => $avatar_url,
        'score' => (float) $rating->get('field_score')->value,
        'review' => $rating->get('field_review')->value,
        'approved' => (bool) $rating->get('field_approved')->value,
        'featured' => $rating->hasField('field_is_featured') ? (bool) $rating->get('field_is_featured')->value : FALSE,
        'created' => $rating->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_admin_ratings',
      '#ratings' => $items,
      '#filters' => $filters,
      '#cache' => ['contexts' => ['url.query_args'], 'tags' => ['lms_rating_list', 'user_list', 'lms_course_list']],
      '#pager' => ['#type' => 'pager'],
      '#active' => 'content_ratings',
    ];
  }

  /**
   * Toggles properties on the Rating.
   */
  public function executeAction(Request $request, $lms_rating, $action) {
    if (!$lms_rating) {
      $this->messenger()->addError($this->t('Invalid Rating Entity.'));
      return new RedirectResponse(Url::fromRoute('lms_rating.admin_list')->toString());
    }

    if ($action === 'approve') {
      $current = (bool) $lms_rating->get('field_approved')->value;
      $lms_rating->set('field_approved', !$current);
      $lms_rating->save();
      $this->messenger()->addStatus($this->t('Rating approval toggled.'));
    }
    elseif ($action === 'feature') {
      if ($lms_rating->hasField('field_is_featured')) {
        $current = (bool) $lms_rating->get('field_is_featured')->value;
        $lms_rating->set('field_is_featured', !$current);
        
        // If featuring, auto-approve it.
        if (!$current) {
          $lms_rating->set('field_approved', TRUE);
        }
        $lms_rating->save();
        $this->messenger()->addStatus($this->t('Rating feature status toggled.'));
      }
    }
    elseif ($action === 'delete') {
      $lms_rating->delete();
      $this->messenger()->addStatus($this->t('Rating deleted successfully.'));
    }

    return new RedirectResponse(Url::fromRoute('lms_rating.admin_list')->toString());
  }
}
