# Task: Refund Flow — Stripe, PayPal, ZaloPay

**ID:** TASK-123
**Unit:** U-020 (lms_order), U-021 (lms_payment)
**Group:** G-COMMERCE (hardening)
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement admin-initiated refund flow for all three payment providers (Stripe, PayPal, ZaloPay). Supports full and partial refunds. On refund approval, the corresponding enrollment or membership is revoked, inventory adjustments are made, a confirmation email is sent to the learner, and the order status is updated. All refund actions are recorded in the audit log.

## Context

- Module: `lms_order`, `lms_payment`
- Refunds are admin-initiated (learner submits request via support; admin acts in back-office)
- Each provider has its own refund API: Stripe `refunds.create`, PayPal `captures/{id}/refund`, ZaloPay `refund` endpoint
- Partial refund: admin specifies amount ≤ original order total

## Acceptance Criteria

- [ ] "Refund" action available on order detail admin page for paid orders
- [ ] Refund form: radio Full/Partial; amount field (VND); reason dropdown + free text
- [ ] Stripe refund: calls `\Stripe\Refund::create(['charge' => ..., 'amount' => ...])` via provider service
- [ ] PayPal refund: calls PayPal Payments API `POST /v2/payments/captures/{id}/refund`
- [ ] ZaloPay refund: calls ZaloPay refund API with idempotency key (extends TASK-029/030 pattern)
- [ ] Full refund → enrollment status set to `refunded`; membership set to `cancelled`
- [ ] Partial refund → order noted with partial refund amount; enrollment retained unless admin explicitly revokes
- [ ] Refund confirmation email sent to learner with amount and reason
- [ ] Order status transitions: `paid` → `refunded` (full) or `partially_refunded` (partial)
- [ ] Refund record stored in `lms_payment` refund log (amount, provider_refund_id, timestamp, admin_uid)
- [ ] All refund actions logged to audit trail with admin user identity

## Technical Notes

- New refund interface: `RefundProviderInterface` with `refund(Order $order, int $amount_cents, string $reason): RefundResult`
- Implementations: `StripeRefundProvider`, `PaypalRefundProvider`, `ZaloPayRefundProvider` in `lms_payment/src/Provider/`
- Idempotency: use `order_id + refund_attempt_n` as idempotency key (extends TASK-029/031 pattern)
- Webhook handling: Stripe sends `charge.refund.updated`; handle in existing webhook controller (TASK-030)
- Enrollment revocation: call `EnrollmentManager::revoke()` for full refunds
- New field on order: `field_refund_total` (decimal), `field_refund_status` (string)

## Dependencies

- TASK-029 (ZaloPay webhook hardening)
- TASK-030 (webhook idempotency)
- TASK-031 (payment provider abstraction)
- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
