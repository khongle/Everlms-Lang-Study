# C04 Entity - lms_membership_plan

**Display name:** Membership Plan
**Owning module(s):** [lms_membership](../C05-modules/lms_membership.md)
**Mode:** as-built (Content Entity)

## Purpose
Một subscription plan có thể mua, cấp quyền truy cập course rộng/theo tier trong một khoảng thời gian, có trial tùy chọn.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | plan name |
| field_description | formatted description |
| field_price | price |
| field_duration_days | access duration |
| field_billing_cycle | cycle (monthly/yearly/…) |
| field_course_access | scope of courses granted |
| field_trial_days | free trial length |
| field_features | **multi** feature bullets |
| field_recommended | highlight flag |
| status | active |

## Relationships
- Được subscribe qua `lms_user_membership.field_plan`; được mua qua `lms_order_item`.

## Validation rules
Nguồn: `web/modules/custom/lms_membership/src/Entity/MembershipPlan.php`.
- `name`: bắt buộc, tối đa 255 ký tự.
- `field_duration_days`: integer, mặc định 30.
- `field_billing_cycle`: `list_string`, giá trị hợp lệ `monthly|quarterly|yearly|lifetime`, mặc định `monthly`.
- `field_course_access`: `list_string`, giá trị hợp lệ `all|selected`, mặc định `all`.
- `field_features`: string đa giá trị (unlimited), tối đa 255 ký tự/dòng.
- Không có field nào ngoài `name` bị `setRequired(TRUE)`.

## Data invariants
- `field_billing_cycle = lifetime` về mặt nghiệp vụ nên bỏ qua `field_duration_days` (không hết hạn), nhưng field `field_duration_days` vẫn giữ giá trị mặc định 30 và **không có logic đặc biệt nào loại trừ trường hợp lifetime** ở entity level — việc diễn giải "lifetime bỏ qua duration" phụ thuộc code tiêu thụ ở nơi khác (chưa xác nhận).
- `field_course_access = selected` ngụ ý cần một danh sách category/course được chọn kèm theo, nhưng entity **không có field lưu danh sách "selected" categories** — gap giữa enum và field model (tương tự gap đã ghi nhận ở `lms_faq.field_scope = lesson`).

## Calculations & derived values
- Không có trường nào trên `lms_membership_plan` được tính toán tự động; toàn bộ do manager nhập qua plan form.
- `field_trial_days`/`field_duration_days` được các entity phụ thuộc (`lms_user_membership`) dùng làm input để tính `field_end_date` khi subscribe, nhưng phép tính đó (nếu có) nằm ở code tạo `lms_user_membership`, không lưu giá trị dẫn xuất trên `lms_membership_plan` — xem `lms_user_membership.md`.

## Editorial/data management
Manager tạo plan qua plan entity form. Toàn bộ nội dung/feature nằm trong field. Xem [F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md), [WF-005](../C12-workflows/WF-005-membership-subscription.md).
