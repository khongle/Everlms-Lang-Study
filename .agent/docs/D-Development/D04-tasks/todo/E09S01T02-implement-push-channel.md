---
id: E09S01T02
task_id: E09S01T02
title: Implement push notification channel
status: todo
story: E09S01
owning_modules: [lms_notification, lms_mobile_api]
modules: [lms_notification, lms_mobile_api]
difficulty: 3
risk: R-005
adequacy: NOT_RUN
---

# E09S01T02 - Implement push notification channel

## Goal
Gửi push đến app Flutter qua FCM/APNs (INT-009); hiện tại vẫn partial/stubbed (R-005, P2).

## Steps
1. Endpoint đăng ký (register) device-token (`/api/v1/devices`).
2. Gửi push cho `field_channel=push`; dọn (prune) token đã cũ (stale).
3. Test FCM/APNs dạng mock.

## Acceptance / gates
- Đăng ký token; push payload chính xác; khi lỗi → log + in-app fallback.

## Links
[story](../../D03-stories/E09S01-lms_notification.md) · [INT-009](../../../C-Design/C06-apis/INT-009-push-firebase-apns.md) · [G02 R-005](../../../G-Governance/G02-risk-register/_index.md)
