# C04 Entity - lms_notification

**Display name:** Notification
**Owning module(s):** [lms_notification](../C05-modules/lms_notification.md)
**Mode:** as-built (Content Entity) — ⚠️ email/push channels partial (R-005)

## Purpose
A per-user notification record (in-app/email/push) about a domain event, with a read flag and a polymorphic reference to the source object.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | recipient |
| field_type | notification type/category |
| field_title | short title |
| field_message | formatted body |
| field_link | deep link to detail |
| field_channel | **multi**: in_app / email / push |
| field_read | read flag |
| field_reference_type, field_reference_id | polymorphic ref to source (order/course/topic…) |
| created | timestamp |

## Relationships
- N→1 `user`; soft polymorphic ref via `field_reference_type` + `field_reference_id`.

## Editorial/data management
System-created on events; templates editor-managed (manager). In-app delivery works; ⚠️ email/push are partial/stubbed (INT-008/INT-009, R-005). Dispatched via events + cron. See [F-017](../../B-Requirements/B03-features/F-017-notifications.md).
