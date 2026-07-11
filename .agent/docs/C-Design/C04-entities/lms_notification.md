# C04 Entity - lms_notification

**Display name:** Notification
**Owning module(s):** [lms_notification](../C05-modules/lms_notification.md)
**Mode:** as-built (Content Entity) — ⚠️ email/push channels partial (R-005)

## Purpose
Một bản ghi notification cho từng user (in-app/email/push) về một domain event, có read flag và một tham chiếu polymorphic tới source object.

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
- N→1 `user`; tham chiếu polymorphic mềm qua `field_reference_type` + `field_reference_id`.

## Validation rules
Nguồn: `web/modules/custom/lms_notification/src/Entity/Notification.php`.
- `field_title`: bắt buộc, tối đa 255 ký tự.
- `field_type`: `list_string` với 10 giá trị hợp lệ (`enrollment|quiz_result|certificate|payment|forum_reply|membership_expiry|new_lesson|announcement|reminder|system`), không bắt buộc.
- `field_channel`: `list_string` đa giá trị (unlimited), giá trị hợp lệ `in_app|email|push`, mặc định `['in_app']`.
- `field_reference_type`: string tối đa 64 ký tự, `setRequired(FALSE)` tường minh; `field_reference_id`: integer, cũng `setRequired(FALSE)` tường minh.

## Data invariants
- `field_reference_type` + `field_reference_id` tạo thành một tham chiếu polymorphic mềm (không phải `entity_reference` thật) tới nguồn sự kiện (order/course/topic…) — Drupal **không enforce referential integrity** cho cặp field kiểu string+integer này; nếu entity nguồn bị xoá, notification vẫn còn trỏ tới ID không tồn tại (gap as-built, cần dọn dẹp thủ công/cron).
- `field_channel` chứa `email`/`push` nhưng theo `## Editorial/data management` bên dưới, các kênh này mới hoạt động một phần (R-005) — nghĩa là giá trị field có thể không phản ánh đúng việc gửi thực tế đã thành công.

## Calculations & derived values
- Không có trường nào được tính toán trên chính entity — `field_read` là cờ boolean do user tương tác (đánh dấu đã đọc) set thủ công qua UI/API, không có logic suy luận tự động (ví dụ auto-read sau X ngày).

## Editorial/data management
Được hệ thống tạo khi có event; template do editor (manager) quản lý. Gửi in-app hoạt động tốt; ⚠️ email/push mới ở mức một phần/stub (INT-008/INT-009, R-005). Được dispatch qua event + cron. Xem [F-017](../../B-Requirements/B03-features/F-017-notifications.md).
