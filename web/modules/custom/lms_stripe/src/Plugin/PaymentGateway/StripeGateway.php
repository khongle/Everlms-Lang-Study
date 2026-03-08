<?php

declare(strict_types=1);

namespace Drupal\lms_stripe\Plugin\PaymentGateway;

use Drupal\lms_payment\Service\PaymentGatewayInterface;

/**
 * Stripe payment gateway.
 *
 * @todo Implement with Stripe PHP SDK.
 */
final class StripeGateway implements PaymentGatewayInterface {

  public function getId(): string {
    return 'stripe';
  }

  public function getLabel(): string {
    return 'Stripe';
  }

  public function createPayment(float $amount, string $currency, array $metadata = []): array {
    // TODO: Implement Stripe Checkout Session.
    return ['redirect_url' => '', 'session_id' => ''];
  }

  public function verifyPayment(array $data): array {
    // TODO: Implement webhook verification.
    return ['status' => 'pending', 'transaction_id' => ''];
  }

  public function refund(string $transactionId, float $amount): bool {
    // TODO: Implement Stripe refund.
    return FALSE;
  }

}
