<?php

namespace Drupal\lms_order\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_order\Entity\Order;
use Drupal\lms_order\Entity\OrderItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles checkout operations.
 */
class CheckoutController extends ControllerBase {

  /**
   * The payment gateway plugin manager.
   *
   * @var \Drupal\lms_payment\Plugin\PaymentGatewayManager
   */
  protected $paymentGatewayManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->paymentGatewayManager = $container->get('plugin.manager.payment_gateway');
    return $instance;
  }

  /**
   * Builds the checkout page logic.
   *
   * @param int $course_id
   *   The course ID to checkout.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Markup or redirect.
   */
  public function checkout(int $course_id, Request $request) {
    // 1. Load the course.
    $course = Course::load($course_id);
    if (!$course) {
      throw new NotFoundHttpException();
    }

    $user = $this->currentUser();
    if ($user->isAnonymous()) {
      // Basic approach: redirect to login if anonymous.
      $this->messenger()->addWarning($this->t('Please login to checkout.'));
      return $this->redirect('user.login', [], ['query' => ['destination' => '/checkout/' . $course_id]]);
    }

    // Determine the price. Assuming course has 'field_price' or 'price'.
    // If we don't have exact field names mapped, we use a placeholder 0 for now.
    $price = $course->hasField('field_price') ? (float) $course->get('field_price')->value : 0.00;

    if ($price <= 0) {
      // Free course. Create a fully paid order and enroll immediately.
      $this->createAndFulfillFreeOrder($course_id, $user->id());
      $this->messenger()->addStatus($this->t('You have successfully enrolled in @course.', ['@course' => $course->label()]));
      return $this->redirect('lms_course.detail', ['lms_course' => $course_id]);
    }

    $form = \Drupal::formBuilder()->getForm('\Drupal\lms_order\Form\CheckoutForm', $course);

    $thumbnail_url = '';
    if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
      $file = $course->get('field_thumbnail')->entity;
      if ($file) {
        $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'lms_checkout',
      '#form' => $form,
      '#course' => $course,
      '#price' => $price,
      '#thumbnail' => $thumbnail_url,
    ];
  }

  /**
   * Handles free course enrollment directly.
   */
  protected function createAndFulfillFreeOrder(int $course_id, int $user_id) {
    $order = Order::create([
      'uid' => $user_id,
      'field_order_number' => 'FREE-' . time() . '-' . rand(100, 999),
      'field_status' => 'completed',
      'field_total' => 0.00,
    ]);
    $order_item = OrderItem::create([
      'field_course' => $course_id,
      'field_price' => 0.00,
      'field_type' => 'course',
    ]);
    $order_item->save();
    $order->set('field_items', [$order_item->id()]);
    $order->save();
  }

  /**
   * Builds the checkout page logic for memberships.
   */
  public function checkoutMembership(int $plan_id, Request $request) {
    $plan = \Drupal\lms_membership\Entity\MembershipPlan::load($plan_id);
    if (!$plan) {
      throw new NotFoundHttpException();
    }

    $user = $this->currentUser();
    if ($user->isAnonymous()) {
      $this->messenger()->addWarning($this->t('Please login to checkout.'));
      return $this->redirect('user.login', [], ['query' => ['destination' => '/checkout/membership/' . $plan_id]]);
    }

    $price = $plan->hasField('field_price') ? (float) $plan->get('field_price')->value : 0.00;

    if ($price <= 0) {
      $this->createAndFulfillFreeMembershipOrder($plan_id, $user->id());
      $this->messenger()->addStatus($this->t('You have successfully subscribed to @plan.', ['@plan' => $plan->label()]));
      return $this->redirect('lms_membership.my_membership');
    }

    $form = \Drupal::formBuilder()->getForm('\Drupal\lms_order\Form\MembershipCheckoutForm', $plan);

    return [
      '#theme' => 'lms_checkout',
      '#form' => $form,
      '#course' => $plan, // Reuse course variable for template generic display
      '#price' => $price,
      '#thumbnail' => 'https://ui-avatars.com/api/?name=' . urlencode($plan->label()) . '&background=8B5CF6&color=fff&size=512&font-size=0.4',
    ];
  }

  protected function createAndFulfillFreeMembershipOrder(int $plan_id, int $user_id) {
    $order = Order::create([
      'uid' => $user_id,
      'field_order_number' => 'FREE-MEM-' . time() . '-' . rand(100, 999),
      'field_status' => 'completed',
      'field_total' => 0.00,
    ]);
    $order_item = OrderItem::create([
      'field_membership_plan' => $plan_id,
      'field_price' => 0.00,
      'field_type' => 'membership',
    ]);
    $order_item->save();
    $order->set('field_items', [$order_item->id()]);
    $order->set('field_items', [$order_item->id()]);
    $order->save();
  }

  /**
   * Displays Bank Transfer instructions and VietQR.
   */
  public function bankTransferInstruction(\Drupal\lms_order\Entity\Order $lms_order) {
    if ($lms_order->get('field_status')->value !== 'pending') {
      $this->messenger()->addWarning($this->t('This order is not pending payment.'));
      return $this->redirect('<front>');
    }

    return $this->formBuilder()->getForm('\Drupal\lms_order\Form\BankTransferForm', $lms_order);
  }

}
