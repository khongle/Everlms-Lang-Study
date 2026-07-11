# C04 Entity - lms_wishlist

**Display name:** Wishlist item
**Owning module(s):** [lms_wishlist](../C05-modules/lms_wishlist.md)
**Mode:** as-built (Content Entity)

## Purpose
Một course được lưu lại để xem sau trong wishlist của user; ghi nhận ý định mua hàng.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner |
| field_course | ref → lms_course |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_course`. Có thể chuyển đổi thành một order (F-010).

## Validation rules
Nguồn: `web/modules/custom/lms_wishlist/src/Entity/Wishlist.php`.
- `field_course`: bắt buộc, entity_reference tới `lms_course`.
- Không có field nào khác ngoài `uid`/`field_course`/`created`; không có `addConstraint()` tuỳ biến.

## Data invariants
- Cặp (uid, field_course) là duy nhất về mặt nghiệp vụ (một course chỉ xuất hiện 1 lần trong wishlist của 1 user) — **không có unique constraint ở DB/entity level**, nhưng được đảm bảo bởi logic toggle ở tầng controller: `WishlistController` (`web/modules/custom/lms_wishlist/src/Controller/WishlistController.php`) truy vấn `uid` + `field_course` trước khi tạo; nếu bản ghi đã tồn tại thì XOÁ (toggle-off) thay vì tạo thêm, nếu chưa tồn tại thì tạo mới (toggle-on). Đây là kiểm soát duy nhất ở application layer, không phải ràng buộc dữ liệu cứng.

## Calculations & derived values
- Không có trường nào được tính toán trên `lms_wishlist` — entity chỉ lưu `uid`, `field_course`, `created` do hành động add/remove của user quyết định trực tiếp.

## Editorial/data management
Do user quản lý (add/remove). Được hiển thị trên dashboard + mobile. Xem [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md).
