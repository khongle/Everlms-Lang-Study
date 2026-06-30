<?php

declare(strict_types=1);

namespace Drupal\lms_order\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_membership\Entity\MembershipPlan;
use Drupal\lms_order\Entity\Order;
use Drupal\lms_order\Entity\OrderItem;
use Drupal\lms_order\Service\CouponRedemption;
use Drupal\lms_payment\Plugin\PaymentGatewayManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class MembershipCheckoutForm extends FormBase {

  public function __construct(
    protected PaymentGatewayManager $paymentGatewayManager,
    protected CouponRedemption $couponRedemption,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.payment_gateway'),
      $container->get('lms_order.coupon_redemption'),
    );
  }

  public function getFormId(): string {
    return 'lms_membership_checkout_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, MembershipPlan $plan = NULL, string $coupon_code = ''): array {
    if (!$plan) {
      return $form;
    }
    
    $form_state->set('plan_id', $plan->id());
    $price = $plan->hasField('field_price') ? (float) $plan->get('field_price')->value : 0.00;
    $form_state->set('price', $price);

    $form['#attributes']['id'] = 'lms-checkout-form';
    $coupon_value = $form_state->getValue('coupon_code');
    if ($coupon_value === NULL) {
      $coupon_value = $coupon_code;
    }
    $coupon_value = $this->couponRedemption->normalizeCode((string) $coupon_value);
    $coupon_result = $coupon_value !== ''
      ? $this->couponRedemption->apply($coupon_value, $price)
      : NULL;
    $charge_total = is_array($coupon_result) && !empty($coupon_result['valid'])
      ? (float) $coupon_result['total']
      : $price;

    $form['coupon'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-checkout-coupon']],
      '#weight' => -10,
    ];
    $form['coupon']['coupon_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Coupon code'),
      '#default_value' => $coupon_value,
      '#maxlength' => 32,
      '#description' => $this->t('Use a valid code before completing purchase.'),
      '#attributes' => [
        'autocomplete' => 'off',
        'placeholder' => $this->t('Example: SILVERCARE10'),
      ],
    ];
    $form['coupon']['apply_coupon'] = [
      '#type' => 'submit',
      '#name' => 'apply_coupon',
      '#value' => $this->t('Apply coupon'),
      '#submit' => ['::applyCoupon'],
      '#limit_validation_errors' => [['coupon_code']],
      '#attributes' => ['class' => ['button', 'button--secondary']],
    ];

    if (is_array($coupon_result) && !empty($coupon_result['valid'])) {
      $form['coupon']['coupon_summary'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-checkout-coupon__summary']],
        'message' => [
          '#markup' => $this->t('@code applied. Discount: $@discount. New total: $@total.', [
            '@code' => $coupon_result['code'],
            '@discount' => number_format((float) $coupon_result['discount'], 2),
            '@total' => number_format((float) $coupon_result['total'], 2),
          ]),
        ],
      ];
    }

    $config_payment = \Drupal::config('lms_payment.settings');
    $enable_stripe = $config_payment->get('enable_stripe') ?? 1;

    // Call Stripe API to generate PaymentIntent
    $client_secret = '';
    if ($charge_total > 0 && $enable_stripe) {
      try {
        $secret_key = \Drupal::config('lms_payment.settings')->get('stripe_secret_key');
        if (empty($secret_key)) {
          throw new \Exception('Stripe Secret Key is not configured.');
        }
        \Stripe\Stripe::setApiKey($secret_key);
        $intent = \Stripe\PaymentIntent::create([
          'amount' => (int) round($charge_total * 100),
          'currency' => 'usd',
          'automatic_payment_methods' => ['enabled' => true],
        ]);
        $client_secret = $intent->client_secret;
      } catch (\Exception $e) {
        \Drupal::logger('lms_payment')->error('Stripe Exception: ' . $e->getMessage());
      }
    }

    $options = [];
    if ($enable_stripe) {
      $options['stripe'] = $this->t('<i class="fab fa-stripe fa-lg" style="color: #6366f1; margin-right: 0.5rem;"></i> Credit / Debit Card (Stripe)');
    }
    if ($config_payment->get('enable_paypal') ?? 1) {
      $options['paypal'] = $this->t('<i class="fab fa-paypal fa-lg" style="color: #0070ba; margin-right: 0.5rem;"></i> PayPal');
    }
    if ($config_payment->get('enable_zalopay') ?? 0) {
      $options['zalopay'] = $this->t('<i class="fas fa-wallet fa-lg" style="color: #0088FF; margin-right: 0.5rem;"></i> ZaloPay / MoMo');
    }
    if ($config_payment->get('enable_bank_transfer') ?? 1) {
      $options['bank_transfer'] = $this->t('<i class="fas fa-university fa-lg" style="color: #10b981; margin-right: 0.5rem;"></i> Bank Transfer (VietQR)');
    }

    $form['payment_method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Payment Method'),
      '#options' => $options,
      '#default_value' => !empty($options) ? array_key_first($options) : '',
      '#required' => TRUE,
    ];

    // Placeholder for Stripe Elements
    $form['stripe_elements_container'] = [
      '#markup' => '<div id="stripe-card-container" style="display: none; background: #1e293b; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 1rem;"><div id="card-element"></div><div id="card-errors" role="alert" style="color: #f5576c; margin-top: 0.5rem; font-size: 0.875rem;"></div></div>',
    ];

    // Placeholder for PayPal Elements
    $form['paypal_elements_container'] = [
      '#markup' => '<div id="paypal-button-container" style="display: none; margin-bottom: 1rem; width: 100%;"></div>',
    ];

    $form['payment_intent_id'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'payment-intent-id'],
    ];

    $form['paypal_order_id'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'paypal-order-id'],
    ];

    $publishable_key = \Drupal::config('lms_payment.settings')->get('stripe_publishable_key');
    $form['#attached']['drupalSettings']['lms_payment']['client_secret'] = $client_secret;
    $form['#attached']['drupalSettings']['lms_payment']['publishable_key'] = $publishable_key;
    $form['#attached']['library'][] = 'lms_order/stripe_elements'; 

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Complete Purchase →'),
      '#attributes' => [
        'class' => ['btn-lms-primary'],
        'style' => 'width: 100%; margin-top: 1rem; padding: 1.125rem; font-size: 1.125rem; border-radius: 0.5rem; justify-content: center;',
        'id' => 'submit-button',
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $coupon_code = $this->couponRedemption->normalizeCode((string) $form_state->getValue('coupon_code'));
    if ($coupon_code === '') {
      return;
    }

    $price = (float) $form_state->get('price');
    $coupon_result = $this->couponRedemption->apply($coupon_code, $price);
    if (empty($coupon_result['valid'])) {
      $form_state->setErrorByName('coupon_code', $this->couponErrorMessage($coupon_result));
      return;
    }

    $form_state->set('coupon_result', $coupon_result);
  }

  public function applyCoupon(array &$form, FormStateInterface $form_state): void {
    $coupon_code = $this->couponRedemption->normalizeCode((string) $form_state->getValue('coupon_code'));
    $coupon_result = $form_state->get('coupon_result');
    $plan_id = (int) $form_state->get('plan_id');

    if (is_array($coupon_result) && !empty($coupon_result['valid'])) {
      $form_state->setRedirect('lms_order.checkout_membership', ['plan_id' => $plan_id], ['query' => ['coupon' => $coupon_code]]);
      return;
    }

    $form_state->setRebuild(TRUE);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $plan_id = $form_state->get('plan_id');
    $price = $form_state->get('price');
    $method = $form_state->getValue('payment_method');
    $user_id = $this->currentUser()->id();
    $coupon_result = $form_state->get('coupon_result');
    $discount = is_array($coupon_result) && !empty($coupon_result['valid']) ? (float) $coupon_result['discount'] : 0.00;
    $total = is_array($coupon_result) && !empty($coupon_result['valid']) ? (float) $coupon_result['total'] : (float) $price;
    $coupon_code = is_array($coupon_result) && !empty($coupon_result['valid']) ? (string) $coupon_result['code'] : '';

    // Create Order
    $order = Order::create([
      'uid' => $user_id,
      'field_order_number' => 'MEM-' . time() . '-' . rand(100, 999),
      'field_status' => 'pending',
      'field_total' => $total,
      'field_subtotal' => $price,
      'field_discount' => $discount,
      'field_coupon_code' => $coupon_code,
      'field_currency' => 'USD',
      'field_payment_method' => $method,
    ]);
    
    $order_item = OrderItem::create([
      'field_membership_plan' => $plan_id,
      'field_price' => $price,
      'field_type' => 'membership',
    ]);
    $order_item->save();
    $order->set('field_items', [$order_item->id()]);
    $order->save();

    try {
      if ($method === 'stripe') {
        $payment_intent_id = $form_state->getValue('payment_intent_id');
        if (empty($payment_intent_id)) {
          throw new \Exception('Payment verification failed. No Intent ID.');
        }
        $gateway = $this->paymentGatewayManager->createInstance($method);
        $result = $gateway->processPayment($order, ['payment_intent_id' => $payment_intent_id]);
      } elseif ($method === 'paypal') {
        $paypal_order_id = $form_state->getValue('paypal_order_id');
        if (empty($paypal_order_id)) {
          throw new \Exception('Payment verification failed. No PayPal Order ID.');
        }
        $gateway = $this->paymentGatewayManager->createInstance($method);
        $result = $gateway->processPayment($order, ['paypal_order_id' => $paypal_order_id]);
      } else {
        $gateway = $this->paymentGatewayManager->createInstance($method);
        $result = $gateway->processPayment($order);
      }
      
      if ($result['status'] === 'success') {
        $order->set('field_status', 'completed');
        $order->set('field_payment_id', $result['transaction_id'] ?? '');
        $order->save();

        $this->messenger()->addStatus($result['message']);
        $form_state->setRedirect('lms_membership.my_membership');
      } elseif ($result['status'] === 'pending') {
        $order->set('field_status', 'pending');
        $order->save();

        $form_state->setRedirect('lms_order.bank_transfer', ['lms_order' => $order->id()]);
      } elseif ($result['status'] === 'redirect') {
        $order->set('field_status', 'pending');
        $order->set('field_payment_id', $result['transaction_id'] ?? '');
        $order->save();

        $this->messenger()->addStatus($result['message']);
        $response = new \Symfony\Component\HttpFoundation\RedirectResponse($result['redirect_url']);
        $response->send();
        exit;
      } else {
        $this->messenger()->addError($this->t('Payment failed: @msg', ['@msg' => $result['message']]));
        $form_state->setRebuild(TRUE);
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Checkout error: @err', ['@err' => $e->getMessage()]));
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Builds a user-facing coupon validation error.
   *
   * @param array<string, mixed> $coupon_result
   *   Coupon redemption result.
   */
  private function couponErrorMessage(array $coupon_result): \Drupal\Core\StringTranslation\TranslatableMarkup {
    return match ($coupon_result['reason'] ?? 'not_found') {
      'empty' => $this->t('Enter a coupon code.'),
      'disabled' => $this->t('This coupon is no longer active.'),
      'minimum_order' => $this->t('This coupon requires a higher order subtotal.'),
      default => $this->t('Coupon code @code is invalid.', ['@code' => $coupon_result['code'] ?? '']),
    };
  }

}
