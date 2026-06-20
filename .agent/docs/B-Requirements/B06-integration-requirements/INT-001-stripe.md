# INT-001 - Stripe

## Requirement
Capture card payments at checkout (F-011/WF-004) via the Stripe PHP SDK, behind the `@PaymentGateway` plugin `stripe` (`lms_stripe`). Server-side charge/intent creation; no card data touches EverLMS servers.

## Participating modules
[lms_stripe](../../C-Design/C05-modules/lms_stripe.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Contract expectations
1. **Auth/authz:** Stripe secret/publishable keys; ⚠️ currently stored in config YAML (R-001) — must move to Key/env. API calls server-side only.
2. **Failure/retry/fallback:** on declined card / API error, order stays `pending`, buyer sees a clear error and can retry or pick another gateway; no entitlement granted on failure.
3. **CI test fixture/stub:** Stripe test-mode keys + a mocked Stripe client for kernel/unit tests; never hit live Stripe in CI.
4. **Operational owner + alerting:** Commerce owner; ⚠️ **no webhook endpoint today (R-002)** → no async reconciliation/alerting on charge state — high-priority gap.

## Design link
[C06 INT-001](../../C-Design/C06-apis/INT-001-stripe.md)
