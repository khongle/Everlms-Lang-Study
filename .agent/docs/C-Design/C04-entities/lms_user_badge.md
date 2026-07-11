# C04 Entity - lms_user_badge

**Display name:** User Badge (awarded)
**Owning module(s):** [lms_badge](../C05-modules/lms_badge.md)
**Mode:** as-built (Content Entity) — ⚠️ never written by the system (BLK-003)

## Purpose
Một badge được trao cho một user tại một thời điểm.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | recipient |
| field_badge | ref → lms_badge |
| field_awarded_at | award timestamp |

## Relationships
- N→1 `user`, N→1 `lms_badge`.

## Validation rules
Nguồn: `web/modules/custom/lms_badge/src/Entity/UserBadge.php`.
- `field_badge`: bắt buộc (`setRequired(TRUE)`), `entity_reference` tới `lms_badge`.
- `uid` (owner, qua `EntityOwnerTrait`): bắt buộc theo base field owner mặc định của Drupal.
- `field_awarded_at`: kiểu `created`, không set required riêng — tự nhận request time nếu không truyền khi tạo entity.
- Không có `addConstraint()` tuỳ biến; entity không khai báo form handler nên không có validate nghiệp vụ nào ở tầng form.

## Data invariants
- Kỳ vọng nghiệp vụ: một cặp (uid, field_badge) là duy nhất (user không nhận trùng cùng 1 badge). Không tìm thấy ràng buộc unique ở DB hay guard ở code — hệ quả trực tiếp của BLK-003 (không có trigger trao thưởng nào chạy).
- `lms_badge.field_criteria` (JSON `{"type":"courses_completed","count":5}`) mô tả điều kiện cấp badge tự động, nhưng theo Editorial/data management bên dưới, chưa có engine nào tiêu thụ field này — bảng `lms_user_badge` chỉ có dữ liệu khi seed thủ công.

## Calculations & derived values
- `field_awarded_at` = timestamp tại thời điểm entity được save (hành vi mặc định của field type `created`), không phải logic nghiệp vụ tuỳ biến.
- Không có trường nào khác được tính toán/dẫn xuất trên entity này.

## Editorial/data management
Dự định được hệ thống tạo khi đạt milestone. ⚠️ **Không tồn tại trigger trao thưởng nào** — bảng vẫn trống trừ khi được seed. Được theo dõi trong [E06](../../E-Quality/E06-implementation-status/_index.md). Xem [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md).
