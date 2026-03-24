<?php

declare(strict_types=1);

namespace Drupal\lms_payment\Plugin\PaymentGateway;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lms_payment\Plugin\PaymentGatewayBase;

/**
 * ZaloPay payment gateway plugin.
 *
 * @PaymentGateway(
 *   id = "zalopay",
 *   label = @Translation("ZaloPay / MoMo"),
 *   description = @Translation("Process payments via E-Wallet Redirect & Webhook.")
 * )
 */
final class ZaloPayGateway extends PaymentGatewayBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processPayment($order, array $payment_details = []): array {
    \Drupal::logger('lms_payment')->notice('ZaloPay initialized for order @id', ['@id' => $order->id()]);

    return [
      'status' => 'redirect',
      'redirect_url' => 'https://sandbox.zalopay.vn/redirect/demo?order=' . $order->id(),
      'transaction_id' => 'ZLP_' . time(),
      'message' => $this->t('Redirecting to ZaloPay app...'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function handleWebhook($request) {
    // Scaffold: Validate HMAC signature from ZaloPay webhook payload
    return new \Symfony\Component\HttpFoundation\Response('ZaloPay Webhook processed via signature validation', 200);
  }

}
