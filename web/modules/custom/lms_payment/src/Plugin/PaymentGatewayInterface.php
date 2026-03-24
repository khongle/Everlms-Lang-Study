<?php

namespace Drupal\lms_payment\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for PaymentGateway plugins.
 */
interface PaymentGatewayInterface extends PluginInspectionInterface {

  /**
   * Process a payment.
   *
   * @param \Drupal\Core\Entity\EntityInterface $order
   *   The order entity.
   * @param array $payment_details
   *   Detailed payment info (card token, etc.).
   *
   * @return array
   *   Result of the payment processing (status, transaction_id, message).
   */
  public function processPayment($order, array $payment_details = []);

  /**
   * Handle webhook from the gateway.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming webhook request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response to the gateway.
   */
  public function handleWebhook($request);

  /**
   * Return the gateway label.
   *
   * @return string
   *   The label.
   */
  public function getLabel();

}
