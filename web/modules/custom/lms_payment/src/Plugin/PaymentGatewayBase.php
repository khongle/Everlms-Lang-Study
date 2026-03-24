<?php

namespace Drupal\lms_payment\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for PaymentGateway plugins.
 */
abstract class PaymentGatewayBase extends PluginBase implements PaymentGatewayInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function processPayment($order, array $payment_details = []) {
    // To be implemented by specific gateway plugins.
    return [
      'status' => 'failed',
      'message' => 'Payment processing not implemented.',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function handleWebhook($request) {
    // To be implemented by specific gateway plugins.
    return new \Symfony\Component\HttpFoundation\Response('Webhook not implemented.', 500);
  }

}
