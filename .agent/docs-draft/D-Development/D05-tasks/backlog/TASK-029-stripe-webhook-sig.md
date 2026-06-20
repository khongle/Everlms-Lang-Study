# Task: Stripe Webhook Signature Verification

**ID:** TASK-029
**Unit:** U-060
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Triển khai xác thực chữ ký webhook từ Stripe. Đảm bảo tất cả webhook events đều được verify signature trước khi xử lý, ngăn chặn spoofed webhooks.

## Acceptance Criteria
- [ ] Verify Stripe webhook signature sử dụng `stripe-php` SDK
- [ ] Reject requests thiếu hoặc sai signature header (`Stripe-Signature`)
- [ ] Log tất cả failed verification attempts
- [ ] Configurable webhook signing secret qua Drupal config
- [ ] Timing-safe comparison cho signature
- [ ] Return 400 cho invalid signatures (không leak thông tin)

## Technical Notes
- Module: `lms_stripe`
- Stripe SDK: `\Stripe\Webhook::constructEvent()`
- Signing secret: lưu trong Drupal config entity hoặc settings.php
- Liên quan trực tiếp đến TASK-002 (webhook handler U-031)

## Dependencies
- TASK-002 (Webhook handler)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed (đặc biệt G4 Security)
- [ ] No webhook processed without valid signature
