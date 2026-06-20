# F-017 - Notifications

**Status:** partial (in-app functional; email/push channels stubbed — R-005)
**Primary module:** [lms_notification](../../C-Design/C05-modules/lms_notification.md)
**Epic:** [E09 Notifications](../../D-Development/D02-epics/E09-notifications.md)
**Related workflows:** [WF-001](../B04-business-workflows/WF-001-enrollment-and-learning.md), [WF-003](../B04-business-workflows/WF-003-certificate-issuance.md), [WF-004](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-017](../B05-acceptance-criteria/F-017-notifications.md)
**Source:** `web/modules/custom/lms_notification/`

## Requirement
The system notifies users about key events (enrollment, certificate issued, order paid, new forum reply) across channels **in_app / email / push** (`lms_notification.field_channel`). Users see in-app notifications; email/push deliver out-of-band. Templates are editor-managed.

## Business value
Timely notifications drive re-engagement, completion, and trust (payment/certificate confirmations).

## Scope notes
- `lms_notification` entity with `field_channel` (in_app, email, push).
- ⚠️ As-built: in-app works; **email and push delivery are stubbed/incomplete** (R-005). Push depends on INT-009 (Firebase/APNs).
- Dispatched via events + cron (`ddev drush cron`); `LmsNotificationCommands` drush support.

## Cross references
- Design: [C04 lms_notification](../../C-Design/C04-entities/lms_notification.md), [C06 INT-008 SMTP](../../C-Design/C06-apis/INT-008-smtp-email.md), [C06 INT-009 Push](../../C-Design/C06-apis/INT-009-push-firebase-apns.md)
