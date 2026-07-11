# F-015 Acceptance Criteria - Learner notes & wishlist

## Core criteria
1. **Given** một learner đang ở lesson/course, **when** họ lưu một note (`lms_note`), **then** note được lưu lại và chỉ hiển thị với riêng họ.
2. **Given** một course trong catalog, **when** learner thêm nó vào wishlist (`lms_wishlist`), **then** course xuất hiện trong wishlist của họ.
3. **Given** một course trong wishlist, **when** learner xóa nó, **then** course biến mất khỏi wishlist.
4. **Given** một course trả phí trong wishlist, **when** learner checkout, **then** item trong wishlist chuyển thành một order (F-010).

## Evidence expected
- Modules: [lms_note](../../C-Design/C05-modules/lms_note.md), [lms_wishlist](../../C-Design/C05-modules/lms_wishlist.md).
- Test: note privacy test (user khác không đọc được); wishlist add/remove test (E03).
