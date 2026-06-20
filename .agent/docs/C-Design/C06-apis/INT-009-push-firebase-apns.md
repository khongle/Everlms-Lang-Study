# C06 API/Contract - INT-009 Push (Firebase / APNs)

**Requirement:** [B06 INT-009](../../B-Requirements/B06-integration-requirements/INT-009-push-firebase-apns.md)
**Participating modules:** [lms_mobile_api](../C05-modules/lms_mobile_api.md), [lms_notification](../C05-modules/lms_notification.md)

## Purpose
Deliver push notifications to the Flutter app via FCM (Android) + APNs (iOS). ⚠️ Push channel partial (R-005).

## Proposed contract
- **REST namespace:** outbound to FCM/APNs; device-token register `POST /api/v1/devices`.
- **Auth:** FCM server key + APNs auth key (`app/APN_AuthKey_*.p8`) — SENSITIVE (R-001).
- **Authorization:** authenticated app user registers own device token; system sends pushes.
- **Response envelope:** `{ data: {sent, failed}, error|null, meta: {provider: "fcm|apns"} }`.
- **Audit:** log delivery/failure; prune stale tokens.

## Test requirements
- CI: mocked FCM/APNs client; assert payload, no live push.
- Auth+authz: token belongs to the authenticated user.
- Failure/fallback: push failure → log + retry; in-app fallback.
- Contract test: register-device + send-push payload shape.
