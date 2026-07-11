# F-002 - Course authoring & structure

**Status:** built
**Primary module:** [lms_course](../../C-Design/C05-modules/lms_course.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-006 Course authoring & publishing](../B04-business-workflows/WF-006-course-authoring-and-publishing.md)
**Acceptance:** [B05 F-002](../B05-acceptance-criteria/F-002-course-authoring-and-structure.md)
**Source:** `web/modules/custom/lms_course/`, `lms_chapter/`, `lms_lesson/`

## Requirement
Instructor/editor có thể tạo và tổ chức course theo hierarchy ba cấp: **Course → Chapter → Lesson** (`lms_course.field_chapters` → `lms_chapter.field_lessons` → `lms_lesson`). Họ thiết lập metadata (category, level, language, tags, instructor, price), sắp xếp thứ tự chapter/lesson, và publish/unpublish.

## Business value
Authoring có cấu trúc là năng lực sản xuất cốt lõi; một hierarchy rõ ràng quyết định trải nghiệm learner, progress tracking và điều kiện nhận certificate.

## Scope notes
- Các entity-reference field kết nối hierarchy (`field_chapters`, `field_lessons`).
- Instructor được gán qua `lms_course.field_instructor`.
- Editorial-usability: toàn bộ nội dung course/chapter/lesson nằm trong entity field, chỉnh sửa được qua management UI — không có text hardcode.
- Soạn thảo hỗ trợ AI thuộc F-018/WF-010; chi tiết về media thuộc F-003.

## Cross references
- Design: [C04 lms_course](../../C-Design/C04-entities/lms_course.md), [C04 lms_chapter](../../C-Design/C04-entities/lms_chapter.md), [C04 lms_lesson](../../C-Design/C04-entities/lms_lesson.md), [C03 entity-module map](../../C-Design/C03-entity-module-map/_index.md)
