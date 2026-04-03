<?php

namespace Drupal\lms_rating\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lms_rating\Entity\Rating;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for Rating actions.
 */
class RatingController extends ControllerBase {

  /**
   * Toggles the approved status.
   */
  public function toggleApprove(Rating $lms_rating) {
    $status = $lms_rating->get('field_approved')->value;
    $lms_rating->set('field_approved', !$status);
    $lms_rating->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Rating %id rejected.', ['%id' => $lms_rating->id()]));
    }
    else {
      $this->messenger()->addStatus($this->t('Rating %id approved.', ['%id' => $lms_rating->id()]));
    }

    $url = \Drupal\Core\Url::fromRoute('lms_rating.collection')->toString();
    return new RedirectResponse($url);
  }

  /**
   * Toggles the featured status.
   */
  public function toggleFeatured(Rating $lms_rating) {
    if (!$lms_rating->hasField('field_is_featured')) {
      $this->messenger()->addError($this->t('Field field_is_featured does not exist. Please update database.'));
    }
    else {
      $status = $lms_rating->get('field_is_featured')->value;
      $lms_rating->set('field_is_featured', !$status);
      $lms_rating->save();

      if ($status) {
         $this->messenger()->addStatus($this->t('Rating %id removed from homepage.', ['%id' => $lms_rating->id()]));
      }
      else {
         $this->messenger()->addStatus($this->t('Rating %id featured on homepage.', ['%id' => $lms_rating->id()]));
      }
    }

    $url = \Drupal\Core\Url::fromRoute('lms_rating.collection')->toString();
    return new RedirectResponse($url);
  }

}
