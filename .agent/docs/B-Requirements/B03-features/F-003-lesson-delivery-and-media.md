# F-003 - Lesson delivery & media

**Status:** built
**Primary module:** [lms_lesson](../../C-Design/C05-modules/lms_lesson.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-003](../B05-acceptance-criteria/F-003-lesson-delivery-and-media.md)
**Source:** `web/modules/custom/lms_lesson/`

## Requirement
Learner đã enroll có thể xem nội dung lesson — rich text body, video và attachment tải về được (`lms_lesson.field_attachments`) — trong course player, đánh dấu lesson hoàn thành và chuyển sang lesson tiếp theo. Lesson có thể đi kèm quiz (F-006).

## Business value
Lesson delivery là nơi việc học thực sự diễn ra; khả năng phát media ổn định và luồng "next" rõ ràng thúc đẩy tỷ lệ completion và điều kiện nhận certificate.

## Scope notes
- `lms_lesson` có 5 module dependency (content entity kết nối nhiều nhất) — nó liên kết course, chapter, progress và quiz.
- Media được tham chiếu qua file/attachment field; file lớn được reference chứ không nhúng trực tiếp (inline).
- Việc gating truy cập (phải enroll / lesson đã unlock) được enforce qua F-004.

## Cross references
- Design: [C04 lms_lesson](../../C-Design/C04-entities/lms_lesson.md), [C05 lms_lesson](../../C-Design/C05-modules/lms_lesson.md)
- Player page in [C11 pages](../../C-Design/C11-pages-components/pages/_index.md)
