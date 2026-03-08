<?php

declare(strict_types=1);

namespace Drupal\lms_payment\Service;

/**
 * Payment gateway manager — resolves and manages gateways.
 */
final class PaymentManager {

  /**
   * @var PaymentGatewayInterface[]
   */
  private array $gateways = [];

  /**
   * Register a gateway.
   */
  public function addGateway(PaymentGatewayInterface $gateway): void {
    $this->gateways[$gateway->getId()] = $gateway;
  }

  /**
   * Get a gateway by ID.
   */
  public function getGateway(string $id): ?PaymentGatewayInterface {
    return $this->gateways[$id] ?? NULL;
  }

  /**
   * Get all available gateways.
   *
   * @return PaymentGatewayInterface[]
   */
  public function getAvailableGateways(): array {
    return $this->gateways;
  }

}
