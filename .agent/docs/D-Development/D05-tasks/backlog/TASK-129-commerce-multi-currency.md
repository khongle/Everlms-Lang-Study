# Task: Multi-Currency v1 — VND Primary, USD Secondary

**ID:** TASK-129
**Unit:** U-020 (lms_order), U-021 (lms_payment)
**Group:** G-COMMERCE (hardening)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Introduce multi-currency support with VND as the primary currency and USD as a secondary display currency. Users can toggle their preferred currency via a picker in the header. Stripe processes international card payments in USD. Exchange rates are refreshed daily via cron from a public API. All orders are stored in VND; displayed amounts are converted for presentation only.

## Context

- Module: `lms_order`, `lms_payment`
- VND is canonical storage currency; USD conversion is display-only except when Stripe charges a USD card
- ZaloPay and PayPal domestic transactions remain in VND
- Exchange rate source: ExchangeRate-API or similar (free tier: 1,500 requests/month)

## Acceptance Criteria

- [ ] Currency picker (VND / USD) in site header; preference stored in session + user account field
- [ ] Course prices displayed in user's preferred currency with real-time converted amount
- [ ] Conversion formula: `display_price_usd = ceil(vnd_price / exchange_rate)`
- [ ] Stripe checkout: when user has USD preference, charge in USD using converted amount
- [ ] All orders stored internally in VND; `field_display_currency` and `field_exchange_rate_at_purchase` stored for audit
- [ ] Cron refreshes exchange rate daily via API; rate stored in config `lms_payment.exchange_rates`
- [ ] Fallback rate hardcoded (e.g., 25,000 VND/USD) if API call fails; admin alerted
- [ ] Admin settings: enable/disable USD currency, fallback rate, API key
- [ ] Invoice PDF shows VND amount as primary with USD equivalent in parentheses

## Technical Notes

- New service: `web/modules/custom/lms_payment/src/CurrencyService.php`
- Methods: `convert(int $vnd, string $to_currency): int`, `getRate(string $currency): float`, `refreshRates(): void`
- HTTP client: use Drupal `http_client` service (GuzzleHttp); cache response for 24h via `cache.default`
- Stripe USD charge: send `amount` in USD cents; add `metadata['vnd_amount']` for reconciliation
- Exchange rate API: `https://api.exchangerate-api.com/v4/latest/VND` (or equivalent)
- User preference field: `field_currency_preference` (list: VND, USD) on user entity
- Display: Twig function `{{ price | lms_currency(user.currency_preference) }}`

## Dependencies

- TASK-029 (webhook hardening)
- TASK-030 (idempotency)
- TASK-031 (payment provider abstraction)
- TASK-050 (commerce hardening)
- TASK-127 (VAT calculation — applied in VND before conversion)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
