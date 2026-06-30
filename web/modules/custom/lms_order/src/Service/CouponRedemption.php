<?php

declare(strict_types=1);

namespace Drupal\lms_order\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Validates configured coupon codes and calculates order discounts.
 */
final class CouponRedemption {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Calculates coupon redemption against a subtotal.
   *
   * @return array{
   *   valid: bool,
   *   code: string,
   *   reason: string,
   *   discount: float,
   *   total: float,
   *   coupon?: array<string, mixed>
   * }
   */
  public function apply(?string $code, float $subtotal): array {
    $normalized = $this->normalizeCode($code);
    $subtotal = max(0.0, round($subtotal, 2));

    if ($normalized === '') {
      return $this->result(FALSE, '', 'empty', 0.0, $subtotal);
    }

    $coupon = $this->findCoupon($normalized);
    if ($coupon === NULL) {
      return $this->result(FALSE, $normalized, 'not_found', 0.0, $subtotal);
    }

    if (empty($coupon['enabled'])) {
      return $this->result(FALSE, $normalized, 'disabled', 0.0, $subtotal);
    }

    $minimum = isset($coupon['minimum_order']) ? (float) $coupon['minimum_order'] : 0.0;
    if ($subtotal < $minimum) {
      return $this->result(FALSE, $normalized, 'minimum_order', 0.0, $subtotal, $coupon);
    }

    $amount = isset($coupon['amount']) ? (float) $coupon['amount'] : 0.0;
    $type = (string) ($coupon['type'] ?? 'fixed');
    $discount = match ($type) {
      'percent' => $subtotal * max(0.0, min(100.0, $amount)) / 100,
      default => max(0.0, $amount),
    };
    $discount = round(min($subtotal, $discount), 2);

    return $this->result(TRUE, $normalized, 'accepted', $discount, round($subtotal - $discount, 2), $coupon);
  }

  /**
   * Normalizes coupon codes for storage and comparison.
   */
  public function normalizeCode(?string $code): string {
    return strtoupper(trim((string) $code));
  }

  /**
   * Finds a configured coupon by normalized code.
   *
   * @return array<string, mixed>|null
   *   Coupon configuration, or NULL when no match exists.
   */
  private function findCoupon(string $normalized): ?array {
    $coupons = $this->configFactory->get('lms_order.settings')->get('coupons') ?? [];
    foreach ($coupons as $coupon) {
      if (!is_array($coupon)) {
        continue;
      }
      $code = $this->normalizeCode($coupon['code'] ?? '');
      if ($code === $normalized) {
        $coupon['code'] = $code;
        return $coupon;
      }
    }

    return NULL;
  }

  /**
   * Builds a consistent result array.
   *
   * @param array<string, mixed> $coupon
   *   Matched coupon configuration.
   *
   * @return array<string, mixed>
   *   Redemption result.
   */
  private function result(bool $valid, string $code, string $reason, float $discount, float $total, array $coupon = []): array {
    $result = [
      'valid' => $valid,
      'code' => $code,
      'reason' => $reason,
      'discount' => $discount,
      'total' => $total,
    ];

    if ($coupon !== []) {
      $result['coupon'] = $coupon;
    }

    return $result;
  }

}
