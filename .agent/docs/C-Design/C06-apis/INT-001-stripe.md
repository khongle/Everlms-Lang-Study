# C06 API/Contract - INT-001 Stripe

**Requirement:** [B06 INT-001](../../B-Requirements/B06-integration-requirements/INT-001-stripe.md)
**Participating modules:** [lms_stripe](../C05-modules/lms_stripe.md), [lms_payment](../C05-modules/lms_payment.md)

## Purpose
Charge card payments at checkout via the Stripe PHP SDK behind the `stripe` `@PaymentGateway`. Outbound integration; no card data stored by EverLMS.

## Proposed contract
- **REST namespace:** outbound to Stripe API (`api.stripe.com`) via SDK; inbound return at `/payment/stripe/return`. **Recommended (gap):** webhook `POST /api/v1/payment/stripe/webhook`.
- **Auth:** Stripe secret key (server) + publishable key (client). ⚠️ stored in `lms_payment.settings.yml` (R-001) — move to Key/env.
- **Authorization:** only an authenticated buyer with a pending order may initiate; gateway calls server-side only.
- **Response envelope (internal normalisation):** `{ data: {payment_id, status}, error: {code, message}|null, meta: {gateway: "stripe"} }`.
- **Audit:** log charge attempt/result + `field_payment_id` on the order; never log full card/PAN.

## Test requirements
- CI: mocked Stripe client (no live calls); Stripe test-mode keys for integration env.
- Auth+authz: only order owner can pay; reject unauthenticated.
- Failure/retry/fallback: declined card → order stays pending, clear error, retry/other gateway.
- Contract test: success + decline + (target) webhook signature verification.
