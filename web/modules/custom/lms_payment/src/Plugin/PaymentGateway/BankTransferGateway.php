<?php

declare(strict_types=1);

namespace Drupal\lms_payment\Plugin\PaymentGateway;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lms_payment\Plugin\PaymentGatewayBase;

/**
 * Bank Transfer payment gateway plugin.
 *
 * @PaymentGateway(
 *   id = "bank_transfer",
 *   label = @Translation("Bank Transfer"),
 *   description = @Translation("Process payments manually via Bank Transfer / VietQR.")
 * )
 */
final class BankTransferGateway extends PaymentGatewayBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processPayment($order, array $payment_details = []): array {
    \Drupal::logger('lms_payment')->notice('Manual Bank Transfer selected for order @id', ['@id' => $order->id()]);

    return [
      'status' => 'pending', // Marks that payment requires manual verification.
      'transaction_id' => 'MANUAL_' . $order->id(),
      'message' => $this->t('Please transfer the amount to complete your enrollment.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function handleWebhook($request) {
    // Bank transfer does not use automatic webhook in Phase 1.
    return new \Symfony\Component\HttpFoundation\Response('No webhook for manual transfer', 200);
  }

}
