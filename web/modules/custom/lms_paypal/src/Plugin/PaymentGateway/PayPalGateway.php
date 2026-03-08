<?php

declare(strict_types=1);

namespace Drupal\lms_paypal\Plugin\PaymentGateway;

use Drupal\lms_payment\Service\PaymentGatewayInterface;

/**
 * PayPal payment gateway.
 *
 * @todo Implement with PayPal REST SDK.
 */
final class PayPalGateway implements PaymentGatewayInterface {

  public function getId(): string {
    return 'paypal';
  }

  public function getLabel(): string {
    return 'PayPal';
  }

  public function createPayment(float $amount, string $currency, array $metadata = []): array {
    // TODO: Implement PayPal order creation.
    return ['redirect_url' => '', 'order_id' => ''];
  }

  public function verifyPayment(array $data): array {
    // TODO: Implement PayPal webhook/IPN verification.
    return ['status' => 'pending', 'transaction_id' => ''];
  }

  public function refund(string $transactionId, float $amount): bool {
    // TODO: Implement PayPal refund.
    return FALSE;
  }

}
