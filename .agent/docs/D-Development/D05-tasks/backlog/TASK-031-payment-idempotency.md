# Task: Payment Idempotency Keys

**ID:** TASK-031
**Unit:** U-062
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Triển khai idempotency cho payment callbacks. Đảm bảo duplicate webhook events hoặc duplicate payment requests không tạo duplicate orders/transactions.

## Acceptance Criteria
- [ ] Idempotency key tracking cho mỗi payment transaction
- [ ] Detect và skip duplicate webhook events (cùng event ID)
- [ ] Prevent duplicate order creation từ retried payments
- [ ] Database-level uniqueness constraint cho payment reference
- [ ] Log duplicate attempts cho audit trail
- [ ] Handle concurrent webhook deliveries (race condition)

## Technical Notes
- Module: `lms_payment`, `lms_order`
- Approach: unique constraint trên `payment_reference` field + transaction locking
- Cần xử lý cả Stripe và PayPal event dedup

## Dependencies
- TASK-029 (Stripe webhook sig)
- TASK-030 (PayPal webhook sig)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Zero duplicate orders từ webhook replays
