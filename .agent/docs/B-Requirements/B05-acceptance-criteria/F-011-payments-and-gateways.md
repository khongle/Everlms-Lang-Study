# F-011 Acceptance Criteria - Payments & gateways

## Core criteria
1. **Given** checkout, **when** the buyer selects a gateway, **then** `PaymentGatewayManager` loads the matching `@PaymentGateway` plugin (`bank_transfer`/`stripe`/`paypal`/`zalopay`).
2. **Given** the Stripe gateway, **when** a valid card is charged, **then** payment captures and the order is marked paid.
3. **Given** the bank_transfer gateway, **when** chosen, **then** the order awaits manual confirmation and is marked paid on confirm.
4. **(partial)** **Given** the paypal/zalopay gateways, **when** invoked, **then** they are recognised but may be stubbed — documented in E06, not silently failing.

## Evidence expected
- Modules: [lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_stripe](../../C-Design/C05-modules/lms_stripe.md).
- Test: gateway-plugin resolution test; Stripe capture test with a test-mode fixture; ⚠️ secrets via config (R-001), no webhooks (R-002).
