# C04 Entity - lms_user_membership

**Display name:** User Membership (subscription)
**Owning module(s):** [lms_membership](../C05-modules/lms_membership.md)
**Mode:** as-built (Content Entity)

## Purpose
Một instance subscription active/expired cấp quyền cho user truy cập các course của một plan trong khoảng thời gian hiệu lực.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | subscriber |
| field_plan | ref → lms_membership_plan |
| field_start_date, field_end_date | validity window |
| field_status | active / expired / cancelled |
| field_auto_renew | renewal flag |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_membership_plan`. Được tra cứu bởi content gating của F-004.

## Validation rules
Nguồn: `web/modules/custom/lms_membership/src/Entity/UserMembership.php`.
- `field_plan`: bắt buộc, entity_reference tới `lms_membership_plan`.
- `field_status`: `list_string`, giá trị hợp lệ `active|trial|expired|cancelled`, mặc định `active`.
- `field_auto_renew`: boolean, mặc định TRUE.
- `field_start_date`, `field_end_date`: `timestamp`, không bắt buộc, không có ràng buộc `field_end_date >= field_start_date` trong code (gap as-built).

## Data invariants
- `field_end_date < now()` về nghiệp vụ nên kéo theo `field_status = expired` — quan hệ này **được đảm bảo bởi cron** (xem Calculations), không phải bởi validate ở entity/save time; nếu cron không chạy, một membership hết hạn theo ngày vẫn có thể hiển thị `field_status = active` cho tới lần cron kế tiếp (độ trễ tối đa = chu kỳ cron).
- `field_auto_renew = TRUE` chỉ là một cờ dữ liệu — không tìm thấy service tự động tạo `lms_order`/gia hạn `field_end_date` dựa trên cờ này trong phạm vi rà soát; nghĩa là auto-renew hiện có thể chưa được thực thi tự động (gap as-built cần xác nhận thêm với đội phát triển/`lms_membership` cron đầy đủ).

## Calculations & derived values
Xác nhận trong `web/modules/custom/lms_membership/lms_membership.module` (cron): với các `lms_user_membership` có `field_end_date <= now()`, cron set `field_status = 'expired'`. Đây là cơ chế "hết hạn được xử lý qua date + cron" đã ghi ở Editorial/data management.
- `field_start_date`/`field_end_date` không có công thức tính từ `lms_membership_plan.field_duration_days` được xác nhận trong entity này — việc tính `field_end_date = field_start_date + field_duration_days` (nếu có) nằm ở code tạo membership khi checkout, chưa được đối chiếu chi tiết trong phạm vi rà soát này.

## Editorial/data management
Được tạo khi mua plan (order đã paid). Việc hết hạn được xử lý qua date + cron/status. Xem [F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md).
