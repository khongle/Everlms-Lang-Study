<?php

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lms_course\Entity\Course;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Url;

/**
 * Controller for handling course enrollments.
 */
class CourseEnrollmentController extends ControllerBase {

  /**
   * Enrolls a user in a free course.
   *
   * @param \Drupal\lms_course\Entity\Course $lms_course
   *   The course entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the course learning space.
   */
  public function enrollFree(Course $lms_course) {
    $user = $this->currentUser();

    // 1. Check Authentication.
    if (!$user->isAuthenticated()) {
      $destination = \Drupal::destination()->get();
      $login_url = Url::fromRoute('user.login', [], ['query' => ['destination' => $destination]])->toString();
      return new RedirectResponse($login_url);
    }

    // 2. Validate Price Type (Security check).
    $price_type = $lms_course->get('field_price_type')->value;
    if ($price_type !== 'free') {
      throw new AccessDeniedHttpException('This course is not free. Please proceed to checkout.');
    }

    $course_id = $lms_course->id();
    $user_id = $user->id();

    // 3. Check for Duplicate Enrollment.
    $storage = $this->entityTypeManager()->getStorage('lms_user_course');
    $query = $storage->getQuery()
      ->condition('uid', $user_id)
      ->condition('field_course', $course_id)
      ->accessCheck(FALSE);
    $existing_enrollments = $query->execute();

    if (empty($existing_enrollments)) {
      // 4. Create Enrollment Record.
      $enrollment = $storage->create([
        'uid' => $user_id,
        'field_course' => $course_id,
        'field_status' => 'active',
        'field_progress' => 0.0,
        'field_enrolled_at' => time(),
      ]);
      $enrollment->save();

      // Trigger notification if module exists.
      if (\Drupal::moduleHandler()->moduleExists('lms_notification')) {
         // Create a welcome notification.
         $notification_storage = $this->entityTypeManager()->getStorage('lms_notification');
         $notification = $notification_storage->create([
           'field_user' => $user_id,
           'field_title' => 'Welcome to the course!',
           'field_message' => 'You have successfully enrolled in "' . $lms_course->label() . '". Happy learning!',
           'field_type' => 'enrollment',
           'field_is_read' => 0,
         ]);
         $notification->save();
      }

      $this->messenger()->addStatus($this->t('You have successfully enrolled in %course.', ['%course' => $lms_course->label()]));
    }
    else {
      $this->messenger()->addWarning($this->t('You are already enrolled in this course.'));
    }

    // 5. Redirect to Learning Dashboard / Course Player.
    // For now, redirecting to the course detail page until the course player is distinct.
    $url = Url::fromRoute('lms_course.detail', ['lms_course' => $course_id])->toString();
    return new RedirectResponse($url);
  }

}
