# F-017 - Notifications

**Status:** partial (in-app functional; email/push channels stubbed — R-005)
**Primary module:** [lms_notification](../../C-Design/C05-modules/lms_notification.md)
**Epic:** [E09 Notifications](../../D-Development/D02-epics/E09-notifications.md)
**Related workflows:** [WF-001](../B04-business-workflows/WF-001-enrollment-and-learning.md), [WF-003](../B04-business-workflows/WF-003-certificate-issuance.md), [WF-004](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-017](../B05-acceptance-criteria/F-017-notifications.md)
**Source:** `web/modules/custom/lms_notification/`

## Requirement
Hệ thống notify user về các event quan trọng (enrollment, certificate issued, order paid, forum reply mới) qua các channel **in_app / email / push** (`lms_notification.field_channel`). User thấy notification in-app; email/push gửi ra ngoài kênh chính (out-of-band). Template do editor quản lý.

## Business value
Notification kịp thời thúc đẩy re-engagement, completion và sự tin tưởng (xác nhận payment/certificate).

## Scope notes
- Entity `lms_notification` với `field_channel` (in_app, email, push).
- ⚠️ As-built: in-app hoạt động; **việc gửi email và push vẫn ở dạng stub/chưa hoàn chỉnh** (R-005). Push phụ thuộc vào INT-009 (Firebase/APNs).
- Được dispatch qua event + cron (`ddev drush cron`); có hỗ trợ drush qua `LmsNotificationCommands`.

## Cross references
- Design: [C04 lms_notification](../../C-Design/C04-entities/lms_notification.md), [C06 INT-008 SMTP](../../C-Design/C06-apis/INT-008-smtp-email.md), [C06 INT-009 Push](../../C-Design/C06-apis/INT-009-push-firebase-apns.md)
