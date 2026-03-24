<?php

declare(strict_types=1);

namespace Drupal\lms_payment\Plugin\PaymentGateway;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lms_payment\Plugin\PaymentGatewayBase;

/**
 * PayPal payment gateway plugin.
 *
 * @PaymentGateway(
 *   id = "paypal",
 *   label = @Translation("PayPal"),
 *   description = @Translation("Process payments via PayPal Smart Buttons.")
 * )
 */
final class PayPalGateway extends PaymentGatewayBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processPayment($order, array $payment_details = []): array {
    if (empty($payment_details['paypal_order_id'])) {
      return [
        'status' => 'error',
        'message' => $this->t('Missing PayPal Order ID.'),
      ];
    }
    
    // In a real integration, we use the PayPal REST SDK to verify the order validation internally.
    \Drupal::logger('lms_payment')->notice('PayPal payment succeeded for order @id, paypal_order: @paypal', [
      '@id' => $order->id(),
      '@paypal' => $payment_details['paypal_order_id'],
    ]);

    return [
      'status' => 'success',
      'transaction_id' => $payment_details['paypal_order_id'],
      'message' => $this->t('Payment successful via PayPal.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function handleWebhook($request) {
    return new \Symfony\Component\HttpFoundation\Response('PayPal Webhook processed', 200);
  }

}
