# F-002 Acceptance Criteria - Course authoring & structure

## Core criteria
1. **Given** một instructor role, **when** họ tạo một course và thêm chapter rồi lesson, **then** hierarchy Course→Chapter→Lesson được lưu lại thông qua `field_chapters`/`field_lessons`.
2. **Given** một course có chapter, **when** instructor sắp xếp lại thứ tự chapter/lesson, **then** thứ tự mới được lưu và phản ánh trong player.
3. **Given** một course, **when** instructor thiết lập category/level/language/tags/instructor/price, **then** mọi giá trị đều được lưu vào entity field (không hardcode text).
4. **Given** một course ở trạng thái draft, **when** instructor publish nó, **then** course trở nên hiển thị trong catalog (F-001).

## Evidence expected
- Modules: [lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md).
- Test: entity-create kernel test cho chuỗi tham chiếu 3 cấp; editor-usability smoke (E03).
