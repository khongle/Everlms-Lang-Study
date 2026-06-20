# Task: PayPal Webhook Signature Verification

**ID:** TASK-030
**Unit:** U-061
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Triển khai xác thực chữ ký webhook từ PayPal. Verify tất cả IPN/webhook events trước khi xử lý payment callbacks.

## Acceptance Criteria
- [ ] Verify PayPal webhook signature sử dụng PayPal SDK
- [ ] Reject requests thiếu hoặc sai signature
- [ ] Log tất cả failed verification attempts
- [ ] Configurable webhook ID qua Drupal config
- [ ] Handle cả Sandbox và Production modes
- [ ] Return 400 cho invalid signatures

## Technical Notes
- Module: `lms_paypal`
- PayPal SDK: `PayPalHttpClient` + webhook verification API
- Liên quan trực tiếp đến TASK-002 (webhook handler U-031)

## Dependencies
- TASK-002 (Webhook handler)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed (đặc biệt G4 Security)
- [ ] No webhook processed without valid signature
