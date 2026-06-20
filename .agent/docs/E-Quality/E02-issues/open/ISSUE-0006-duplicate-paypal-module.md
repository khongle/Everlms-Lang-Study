# ISSUE-0006 - Duplicate lms_paypal gateway (dead code)

**Severity:** P2 · **Status:** open · **Risk:** R-004 · **Task:** [E06S04T01](../../../D-Development/D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md)

## Description
A PayPal `@PaymentGateway` plugin exists in BOTH `lms_paypal` and `lms_payment` (`src/Plugin/PaymentGateway/PayPalGateway.php`). The `lms_paypal` copy is effectively dead/duplicate.

## Impact
Maintenance confusion; risk of editing the wrong copy; plugin-id collision potential.

## Fix
Keep one (recommend `lms_payment`); uninstall + remove `lms_paypal`; update references; regression-test PayPal checkout.
