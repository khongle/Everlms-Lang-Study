# INT-009 - Push (Firebase / APNs)

## Requirement
Deliver push notifications (F-017) to the Flutter app via Firebase Cloud Messaging (Android) and Apple APNs (iOS); device tokens registered through the mobile API (F-019).

## Participating modules
[lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Contract expectations
1. **Auth/authz:** FCM server key + APNs auth key (`app/APN_AuthKey_*.p8`) — SENSITIVE (R-001); never in docs/repo-shared docs.
2. **Failure/retry/fallback:** on push failure, log + retry; fall back to in-app notification; stale device tokens pruned.
3. **CI test fixture/stub:** mocked FCM/APNs client; assert payload, no live push in CI.
4. **Operational owner + alerting:** Mobile owner; monitor delivery/failure rate. ⚠️ As-built: push channel **partial/stubbed** (R-005).

## Design link
[C06 INT-009](../../C-Design/C06-apis/INT-009-push-firebase-apns.md)
