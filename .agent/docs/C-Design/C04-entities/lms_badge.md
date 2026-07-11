# C04 Entity - lms_badge

**Display name:** Badge
**Owning module(s):** [lms_badge](../C05-modules/lms_badge.md)
**Mode:** as-built (Content Entity) — ⚠️ award logic absent (BLK-003)

## Purpose
Định nghĩa một achievement badge (icon, type, XP reward, tiêu chí trao thưởng). Dự định được cấp khi đạt milestone.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | badge name |
| field_description | formatted description |
| field_icon | badge image |
| field_type | badge type (`badge_type` taxonomy) |
| field_xp_reward | XP/points granted |
| field_criteria | award criteria (definition) |

## Relationships
- Các instance đã cấp: `lms_user_badge.field_badge`.

## Validation rules
Nguồn: `web/modules/custom/lms_badge/src/Entity/Badge.php`.
- `name`: bắt buộc, tối đa 255 ký tự.
- `field_criteria`: string tối đa 1024 ký tự, mô tả JSON dạng `{"type":"courses_completed","count":5}` (chỉ ghi trong `setDescription()`, KHÔNG có validate cú pháp JSON ở entity/form level).
- `field_xp_reward`: integer, mặc định 0; không có ràng buộc non-negative trong code (client có thể lưu số âm nếu form không chặn).
- `field_type`: entity_reference tới taxonomy `badge_type`, không bắt buộc.

## Data invariants
- `field_criteria` được thiết kế để một engine đọc và tự động cấp badge, nhưng — như ghi ở Editorial/data management bên dưới — hoàn toàn không có code nào đánh giá field này (gap as-built, BLK-003/R-003). Badge hiện chỉ tồn tại như định nghĩa tĩnh, không kích hoạt cấp phát.
- Không có ràng buộc "tổng field_xp_reward toàn hệ thống" hay range hợp lệ nào được enforce.

## Calculations & derived values
- Không có trường nào trên `lms_badge` được tính toán/dẫn xuất — toàn bộ là dữ liệu nhập tay qua badge form.
- Vì `field_criteria` không được engine nào tiêu thụ (grep toàn bộ `web/modules/custom` không thấy `field_criteria` được đọc ngoài entity definition và seed data), badge award KHÔNG có công thức tính toán runtime nào tồn tại trong as-built.

## Editorial/data management
Admin định nghĩa badge qua badge form. ⚠️ **Khoảng trống as-built:** `field_criteria` đã được định nghĩa nhưng **không có code nào đánh giá nó để cấp badge** — xem [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md), [G02 R-003](../../G-Governance/G02-risk-register/_index.md).
