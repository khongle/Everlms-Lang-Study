# INT-009 - Push (Firebase / APNs)

## Requirement
Gửi push notification (F-017) đến Flutter app qua Firebase Cloud Messaging (Android) và Apple APNs (iOS); device token được đăng ký qua mobile API (F-019).

## Participating modules
[lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Contract expectations
1. **Auth/authz:** FCM server key + APNs auth key (`app/APN_AuthKey_*.p8`) — SENSITIVE (R-001); không bao giờ để trong docs/repo-shared docs.
2. **Failure/retry/fallback:** khi push thất bại, log + retry; fallback về in-app notification; device token đã cũ bị prune.
3. **CI test fixture/stub:** FCM/APNs client được mock; assert payload, không push thật trong CI.
4. **Operational owner + alerting:** Mobile owner; theo dõi delivery/failure rate. ⚠️ As-built: push channel **chỉ partial/stubbed** (R-005).

## Design link
[C06 INT-009](../../C-Design/C06-apis/INT-009-push-firebase-apns.md)
