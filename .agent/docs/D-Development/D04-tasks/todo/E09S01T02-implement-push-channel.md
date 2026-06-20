---
id: E09S01T02
title: Implement push notification channel
status: todo
story: E09S01
owning_modules: [lms_notification, lms_mobile_api]
risk: R-005
adequacy: NOT_RUN
---

# E09S01T02 - Implement push notification channel

## Goal
Deliver push to the Flutter app via FCM/APNs (INT-009); currently partial/stubbed (R-005, P2).

## Steps
1. Device-token register endpoint (`/api/v1/devices`).
2. Send push for `field_channel=push`; prune stale tokens.
3. Mocked FCM/APNs tests.

## Acceptance / gates
- Token register; push payload correct; failure → log + in-app fallback.

## Links
[story](../../D03-stories/E09S01-lms_notification.md) · [INT-009](../../../C-Design/C06-apis/INT-009-push-firebase-apns.md) · [G02 R-005](../../../G-Governance/G02-risk-register/_index.md)
