<?php

declare(strict_types=1);

namespace Drupal\lms_stripe\Plugin\PaymentGateway;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lms_payment\Plugin\PaymentGatewayBase;

/**
 * Stripe payment gateway plugin.
 *
 * @PaymentGateway(
 *   id = "stripe",
 *   label = @Translation("Stripe"),
 *   description = @Translation("Process payments via Stripe Checkout.")
 * )
 */
final class StripeGateway extends PaymentGatewayBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processPayment($order, array $payment_details = []): array {
    if (empty($payment_details['payment_intent_id'])) {
      return [
        'status' => 'error',
        'message' => $this->t('Missing Payment Intent ID.'),
      ];
    }

    try {
      $secret_key = \Drupal::config('lms_payment.settings')->get('stripe_secret_key');
      if (empty($secret_key)) {
        throw new \Exception('Stripe Secret Key is not configured in Payment Settings.');
      }
      \Stripe\Stripe::setApiKey($secret_key);
      $intent = \Stripe\PaymentIntent::retrieve($payment_details['payment_intent_id']);

      if ($intent->status === 'succeeded') {
        \Drupal::logger('lms_stripe')->notice('Stripe payment succeeded for order @id, intent: @intent', [
          '@id' => $order->id(),
          '@intent' => $intent->id,
        ]);

        return [
          'status' => 'success',
          'transaction_id' => $intent->id,
          'message' => $this->t('Payment successful via Stripe.'),
        ];
      } else {
        return [
          'status' => 'error',
          'message' => $this->t('Payment not successful. Status: @status', ['@status' => $intent->status]),
        ];
      }
    } catch (\Exception $e) {
      \Drupal::logger('lms_stripe')->error('Stripe retrieval error: ' . $e->getMessage());
      return [
        'status' => 'error',
        'message' => $this->t('Error contacting Stripe: @err', ['@err' => $e->getMessage()]),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function handleWebhook($request) {
    // TODO: Implement webhook verification.
    \Drupal::logger('lms_stripe')->notice('Received webhook from Stripe.');
    return new \Symfony\Component\HttpFoundation\Response('Webhook processed', 200);
  }

}
