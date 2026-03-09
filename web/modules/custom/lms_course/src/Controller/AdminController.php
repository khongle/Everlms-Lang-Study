<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_base\Service\LmsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin/Management dashboard and entity listing pages.
 */
final class AdminController extends ControllerBase {

  protected LmsHelper $lmsHelper;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LmsHelper $lms_helper,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->lmsHelper = $lms_helper;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_base.helper'),
    );
  }

  /**
   * Admin dashboard — KPI cards + recent orders.
   */
  public function dashboard(): array {
    // Course count.
    $course_count = (int) $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Active learners (users with enrollments).
    $learner_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Revenue from orders.
    $order_ids = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()->accessCheck(FALSE)->execute();
    $orders = $order_ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($order_ids)
      : [];
    $revenue = 0;
    foreach ($orders as $order) {
      $revenue += (float) ($order->get('field_total')->value ?? 0);
    }

    // Quiz completion rate.
    $quiz_results = (int) $this->entityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Recent orders.
    $recent_order_ids = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()
      ->sort('created', 'DESC')
      ->range(0, 10)
      ->accessCheck(FALSE)
      ->execute();
    $recent_orders = $recent_order_ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($recent_order_ids)
      : [];

    $order_items = [];
    foreach ($recent_orders as $order) {
      $user = $order->getOwner();
      $order_items[] = [
        'id' => $order->id(),
        'learner' => $user?->getDisplayName() ?? 'Unknown',
        'total' => (float) ($order->get('field_total')->value ?? 0),
        'status' => $order->get('field_status')->value ?? 'pending',
        'created' => $order->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_admin_dashboard',
      '#stats' => [
        'courses' => $course_count,
        'learners' => $learner_count,
        'revenue' => $revenue,
        'quiz_results' => $quiz_results,
      ],
      '#recent_orders' => $order_items,
    ];
  }

  /**
   * Course management list.
   */
  public function courseList(): array {
    $ids = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($courses as $course) {
      $category = $course->get('field_category')->entity;
      $items[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'category' => $category?->label() ?? 'N/A',
        'price' => (float) ($course->get('field_price')->value ?? 0),
        'status' => $course->get('status')->value ? 'Published' : 'Draft',
        'created' => $course->get('created')->value,
        'edit_url' => $course->toUrl('edit-form')->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_courses',
      '#courses' => $items,
    ];
  }

  /**
   * Quiz management list.
   */
  public function quizList(): array {
    $ids = $this->entityTypeManager->getStorage('lms_quiz')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $quizzes = $ids
      ? $this->entityTypeManager->getStorage('lms_quiz')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($quizzes as $quiz) {
      $items[] = [
        'id' => $quiz->id(),
        'name' => $quiz->label(),
        'question_count' => count($quiz->get('field_questions')),
        'time_limit' => (int) ($quiz->get('field_time_limit')->value ?? 0),
        'pass_score' => (float) ($quiz->get('field_pass_score')->value ?? 0),
        'status' => $quiz->get('status')->value ? 'Active' : 'Draft',
        'edit_url' => $quiz->toUrl('edit-form')->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_quizzes',
      '#quizzes' => $items,
    ];
  }

  /**
   * Order management list.
   */
  public function orderList(): array {
    $ids = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $orders = $ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($orders as $order) {
      $user = $order->getOwner();
      $items[] = [
        'id' => $order->id(),
        'learner' => $user?->getDisplayName() ?? 'Unknown',
        'total' => (float) ($order->get('field_total')->value ?? 0),
        'status' => $order->get('field_status')->value ?? 'pending',
        'created' => $order->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_admin_orders',
      '#orders' => $items,
    ];
  }

  /**
   * User management list.
   */
  public function userList(): array {
    $ids = $this->entityTypeManager->getStorage('user')
      ->getQuery()
      ->condition('uid', 0, '>')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $users = $ids
      ? $this->entityTypeManager->getStorage('user')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($users as $user) {
      // Count enrolled courses.
      $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('uid', $user->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      $items[] = [
        'uid' => $user->id(),
        'name' => $user->getDisplayName(),
        'email' => $user->getEmail(),
        'roles' => implode(', ', $user->getRoles(TRUE)),
        'enrolled' => $enrollment_count,
        'created' => $user->get('created')->value,
        'status' => $user->isActive() ? 'Active' : 'Blocked',
      ];
    }

    return [
      '#theme' => 'lms_admin_users',
      '#users' => $items,
    ];
  }

  /**
   * Reports page.
   */
  public function reports(): array {
    // Enrollment by month.
    $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();
    $completion_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->count()
      ->execute();
    $certificate_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    return [
      '#theme' => 'lms_admin_reports',
      '#metrics' => [
        'total_enrollments' => $enrollment_count,
        'completions' => $completion_count,
        'certificates_issued' => $certificate_count,
        'completion_rate' => $enrollment_count > 0
          ? round(($completion_count / $enrollment_count) * 100, 1)
          : 0,
      ],
    ];
  }

}
