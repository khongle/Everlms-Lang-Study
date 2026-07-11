# F-015 - Learner notes & wishlist

**Status:** built
**Primary module:** [lms_note](../../C-Design/C05-modules/lms_note.md), [lms_wishlist](../../C-Design/C05-modules/lms_wishlist.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-015](../B05-acceptance-criteria/F-015-learner-notes-and-wishlist.md)
**Source:** `web/modules/custom/lms_note/`, `lms_wishlist/`

## Requirement
Learner ghi **note** cá nhân (`lms_note`) gắn với một lesson/course (riêng tư, chỉ user đó thấy) và thêm course vào **wishlist** (`lms_wishlist`) để mua/enroll sau. Cả hai đều được hiển thị trên dashboard và mobile API.

## Business value
Note cải thiện hiệu quả học tập và độ gắn bó (stickiness); wishlist ghi nhận purchase intent phục vụ re-marketing.

## Scope notes
- `lms_note`: riêng tư theo user, gắn với ngữ cảnh lesson/course.
- `lms_wishlist`: danh sách đã lưu user ↔ course; có thể chuyển thành một order (F-010).
- Hai entity nhỏ nhưng tách biệt, được gộp nhóm thành "công cụ cá nhân của learner."

## Cross references
- Design: [C04 lms_note](../../C-Design/C04-entities/lms_note.md), [C04 lms_wishlist](../../C-Design/C04-entities/lms_wishlist.md)
