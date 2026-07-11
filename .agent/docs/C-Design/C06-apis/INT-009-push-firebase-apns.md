# C06 API/Contract - INT-009 Push (Firebase / APNs)

**Requirement:** [B06 INT-009](../../B-Requirements/B06-integration-requirements/INT-009-push-firebase-apns.md)
**Participating modules:** [lms_mobile_api](../C05-modules/lms_mobile_api.md), [lms_notification](../C05-modules/lms_notification.md)

## Purpose
Gửi push notification tới app Flutter qua FCM (Android) + APNs (iOS). ⚠️ Kênh push mới hoàn thiện một phần (R-005).

## Proposed contract
- **REST namespace:** gọi ra ngoài tới FCM/APNs; đăng ký device-token `POST /api/v1/devices`.
- **Auth:** FCM server key + APNs auth key (`app/APN_AuthKey_*.p8`) — SENSITIVE (R-001).
- **Authorization:** app user đã authenticated tự đăng ký device token của mình; hệ thống gửi push.
- **Response envelope:** `{ data: {sent, failed}, error|null, meta: {provider: "fcm|apns"} }`.
- **Audit:** ghi log delivery/failure; dọn các token đã cũ (stale).

## Test requirements
- CI: mock FCM/APNs client; assert payload, không gửi push thật.
- Auth+authz: token phải thuộc về user đã authenticated.
- Failure/fallback: gửi push thất bại → ghi log + retry; có fallback in-app.
- Contract test: register-device + cấu trúc payload send-push.
