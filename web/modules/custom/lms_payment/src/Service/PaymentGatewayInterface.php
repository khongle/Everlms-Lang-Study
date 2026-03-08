<?php

declare(strict_types=1);

namespace Drupal\lms_payment\Service;

/**
 * Payment Gateway interface.
 */
interface PaymentGatewayInterface {

  /**
   * Get the gateway ID.
   */
  public function getId(): string;

  /**
   * Get the gateway label.
   */
  public function getLabel(): string;

  /**
   * Create a payment session/intent.
   *
   * @param float $amount
   *   Amount to charge.
   * @param string $currency
   *   Currency code.
   * @param array $metadata
   *   Additional metadata (order_id, etc.).
   *
   * @return array
   *   Payment session data with 'redirect_url' key.
   */
  public function createPayment(float $amount, string $currency, array $metadata = []): array;

  /**
   * Verify a payment callback/webhook.
   *
   * @param array $data
   *   Callback data from the gateway.
   *
   * @return array
   *   Verification result with 'status' and 'transaction_id'.
   */
  public function verifyPayment(array $data): array;

  /**
   * Process a refund.
   *
   * @param string $transactionId
   *   Original transaction ID.
   * @param float $amount
   *   Amount to refund.
   *
   * @return bool
   *   TRUE if refund was successful.
   */
  public function refund(string $transactionId, float $amount): bool;

}
