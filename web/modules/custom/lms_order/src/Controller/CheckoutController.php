<?php

declare(strict_types=1);

namespace Drupal\lms_order\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_membership\Entity\MembershipPlan;
use Drupal\lms_order\Entity\Order;
use Drupal\lms_order\Entity\OrderItem;
use Drupal\lms_order\Form\CheckoutForm;
use Drupal\lms_order\Form\MembershipCheckoutForm;
use Drupal\lms_order\Service\CouponRedemption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles checkout operations.
 */
final class CheckoutController extends ControllerBase {

  public function __construct(
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected CouponRedemption $couponRedemption,
    protected EntityTypeManagerInterface $lmsEntityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_url_generator'),
      $container->get('lms_order.coupon_redemption'),
      $container->get('entity_type.manager'),
    );
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
  public function checkout(int $course_id, Request $request): array|RedirectResponse {
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
      if ($this->isUserEnrolled($course_id, (int) $user->id())) {
        $this->messenger()->addWarning($this->t('You are already enrolled in @course.', ['@course' => $course->label()]));
        return $this->redirect('lms_course.detail', ['lms_course' => $course_id]);
      }

      // Free course. Create a fully paid order and enroll immediately.
      $this->createAndFulfillFreeOrder($course_id, (int) $user->id());
      $this->messenger()->addStatus($this->t('You have successfully enrolled in @course.', ['@course' => $course->label()]));
      return $this->redirect('lms_course.detail', ['lms_course' => $course_id]);
    }

    $coupon_code = $this->couponRedemption->normalizeCode((string) $request->query->get('coupon', ''));
    $pricing = $this->buildPricingSummary($price, $coupon_code);
    $form = $this->formBuilder()->getForm(CheckoutForm::class, $course, $coupon_code);

    $thumbnail_url = '';
    if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
      $file = $course->get('field_thumbnail')->entity;
      if ($file) {
        $thumbnail_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'lms_checkout',
      '#form' => $form,
      '#course' => $course,
      '#price' => $price,
      '#subtotal' => $pricing['subtotal'],
      '#discount' => $pricing['discount'],
      '#total' => $pricing['total'],
      '#coupon_code' => $pricing['coupon_code'],
      '#coupon_valid' => $pricing['coupon_valid'],
      '#thumbnail' => $thumbnail_url,
    ];
  }

  /**
   * Handles free course enrollment directly.
   */
  protected function createAndFulfillFreeOrder(int $course_id, int $user_id): void {
    $order = Order::create([
      'uid' => $user_id,
      'field_order_number' => 'FREE-' . time() . '-' . random_int(100, 999),
      'field_status' => 'completed',
      'field_subtotal' => 0.00,
      'field_discount' => 0.00,
      'field_total' => 0.00,
      'field_currency' => 'USD',
      'field_payment_method' => 'free',
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
  public function checkoutMembership(int $plan_id, Request $request): array|RedirectResponse {
    $plan = MembershipPlan::load($plan_id);
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

    $coupon_code = $this->couponRedemption->normalizeCode((string) $request->query->get('coupon', ''));
    $pricing = $this->buildPricingSummary($price, $coupon_code);
    $form = $this->formBuilder()->getForm(MembershipCheckoutForm::class, $plan, $coupon_code);

    return [
      '#theme' => 'lms_checkout',
      '#form' => $form,
      '#course' => $plan, // Reuse course variable for template generic display
      '#price' => $price,
      '#subtotal' => $pricing['subtotal'],
      '#discount' => $pricing['discount'],
      '#total' => $pricing['total'],
      '#coupon_code' => $pricing['coupon_code'],
      '#coupon_valid' => $pricing['coupon_valid'],
      '#thumbnail' => '',
    ];
  }

  /**
   * Handles free membership enrollment directly.
   */
  protected function createAndFulfillFreeMembershipOrder(int $plan_id, int $user_id): void {
    $order = Order::create([
      'uid' => $user_id,
      'field_order_number' => 'FREE-MEM-' . time() . '-' . random_int(100, 999),
      'field_status' => 'completed',
      'field_subtotal' => 0.00,
      'field_discount' => 0.00,
      'field_total' => 0.00,
      'field_currency' => 'USD',
      'field_payment_method' => 'free',
    ]);
    $order_item = OrderItem::create([
      'field_membership_plan' => $plan_id,
      'field_price' => 0.00,
      'field_type' => 'membership',
    ]);
    $order_item->save();
    $order->set('field_items', [$order_item->id()]);
    $order->save();
  }

  /**
   * Displays Bank Transfer instructions and VietQR.
   */
  public function bankTransferInstruction(Order $lms_order): array|RedirectResponse {
    if ($lms_order->get('field_status')->value !== 'pending') {
      $this->messenger()->addWarning($this->t('This order is not pending payment.'));
      return $this->redirect('<front>');
    }

    return $this->formBuilder()->getForm('\Drupal\lms_order\Form\BankTransferForm', $lms_order);
  }

  /**
   * Builds the checkout summary totals from an optional coupon query value.
   *
   * @return array{
   *   subtotal: float,
   *   discount: float,
   *   total: float,
   *   coupon_code: string,
   *   coupon_valid: bool
   * }
   */
  private function buildPricingSummary(float $subtotal, string $coupon_code): array {
    $subtotal = round(max(0.00, $subtotal), 2);
    $coupon_result = $coupon_code !== ''
      ? $this->couponRedemption->apply($coupon_code, $subtotal)
      : NULL;

    $coupon_valid = is_array($coupon_result) && !empty($coupon_result['valid']);

    return [
      'subtotal' => $subtotal,
      'discount' => $coupon_valid ? (float) $coupon_result['discount'] : 0.00,
      'total' => $coupon_valid ? (float) $coupon_result['total'] : $subtotal,
      'coupon_code' => $coupon_valid ? (string) $coupon_result['code'] : '',
      'coupon_valid' => $coupon_valid,
    ];
  }

  /**
   * Checks whether a learner already has an enrollment for a course.
   */
  private function isUserEnrolled(int $course_id, int $user_id): bool {
    $ids = $this->lmsEntityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $user_id)
      ->condition('field_course', $course_id)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    return !empty($ids);
  }

}
