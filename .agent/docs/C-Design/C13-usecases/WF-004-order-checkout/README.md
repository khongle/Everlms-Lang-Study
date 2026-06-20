---
workflow: WF-004
title: Order & Checkout — Use-Case Catalog
status: as-built
---

# C13 — WF-004: Order & Checkout — Use-Case Catalog

Use cases covering the end-to-end order and payment flow for course purchases. The checkout system supports Stripe (real), bank transfer (real/manual), PayPal (stub — no server-side verification), and ZaloPay (stub — hardcoded sandbox URL). Free courses bypass payment entirely.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF004-001-stripe-checkout.md](UC-WF004-001-stripe-checkout.md) | UC-WF004-001 | happy | Student purchases a paid course via Stripe |
| [UC-WF004-002-bank-transfer-receipt.md](UC-WF004-002-bank-transfer-receipt.md) | UC-WF004-002 | error/edge | Student selects bank transfer; admin approves receipt |
| [UC-WF004-003-anon-checkout-redirect.md](UC-WF004-003-anon-checkout-redirect.md) | UC-WF004-003 | permission | Anonymous user attempts checkout and is redirected to login |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-004-order-management.md](../../../B-Requirements/B04-business-workflows/WF-004-order-management.md)
- Acceptance criteria: [../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
- Primary modules: `lms_order`, `lms_payment`, `lms_stripe`
- Entities mutated: `lms_order`, `lms_order_item`, `lms_user_course`, `lms_notification`

## Known gaps (document; do not work around)

| Gap | Detail |
|-----|--------|
| No webhook routes | No Stripe/PayPal/ZaloPay webhook endpoint exists anywhere; async payment confirmation not receivable |
| PaymentIntent on page build | `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` on every form render, including page refreshes, leaving orphaned intents at Stripe |
| PayPal stub | `lms_payment\Plugin\PaymentGateway\PayPalGateway::processPayment()` accepts any `paypal_order_id` without server-side verification |
| lms_paypal dead code | `lms_paypal\Plugin\PaymentGateway\PayPalGateway` uses wrong interface and has no `@PaymentGateway` annotation — never discovered or instantiated |
| Free membership double-set bug | `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice |
