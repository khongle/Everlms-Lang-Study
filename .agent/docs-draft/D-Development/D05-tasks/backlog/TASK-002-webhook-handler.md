# Task: Webhook Handler + Signature Verification

**ID:** TASK-002
**Unit:** U-031
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG (5% — stub only)

## Audit Notes
- lms_stripe has NO routing.yml → no webhook endpoint route exists
- StripeGateway::handleWebhook() is stub (logs + returns 200, no signature verification, no event parsing)
- lms_paypal module gateway is dead code (implements wrong interface, not discoverable by PluginManager)
- lms_payment's PayPalGateway plugin has empty webhook 
- ZaloPayGateway webhook is stub
- **No webhook routes registered anywhere in the system**

## Description
Xây dựng webhook handler cho Stripe và PayPal, bao gồm signature verification, idempotency check, và order state transitions.

## Acceptance Criteria
- [ ] Stripe webhook endpoint nhận events (payment_intent.succeeded, charge.refunded)
- [ ] PayPal IPN endpoint nhận notifications
- [ ] Signature verification cho cả Stripe và PayPal
- [ ] Idempotency check: duplicate webhook không tạo duplicate state transitions
- [ ] Order status update: pending → completed / failed / refunded
- [ ] Grant/revoke access based on final order status
- [ ] Audit logging cho tất cả webhook events

## Technical Notes
- Module: `lms_stripe`, `lms_paypal`, `lms_payment`
- Route: `/api/webhook/stripe`, `/api/webhook/paypal`
- Service: `StripeWebhookHandler`, `PayPalWebhookHandler`
- Security: Verify webhook signatures, reject invalid requests

## Dependencies
- U-028 (StripeGateway) ✅ DONE
- U-029 (PayPalGateway) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Unit tests cho signature verification
- [ ] Integration test cho webhook flow
- [ ] Security review passed (G4 Security category)
