# WF-006 - Course authoring & publishing

## Business flow
Instructor/editor tạo một course, định nghĩa metadata (category, level, language, tags, instructor, price), rồi xây dựng curriculum: thêm chapter (`field_chapters`), thêm lesson vào từng chapter (`field_lessons`) với body/media/attachment, và tùy chọn gắn quiz (WF-002). Họ có thể dùng AI assistant (WF-010) để soạn thảo (draft) nội dung. Sau khi review, họ publish course, giúp nó hiển thị trong catalog (F-001). Nội dung Blog/FAQ (F-016) theo cùng mô hình publish do editor quản lý.

## Participating modules
[lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md), [lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Related features (B03)
[F-002](../B03-features/F-002-course-authoring-and-structure.md), [F-016](../B03-features/F-016-blog-and-faq-content.md), [F-018](../B03-features/F-018-ai-authoring-assistant.md)

## Trigger
Instructor/editor tạo hoặc sửa một course rồi publish.

## Outcome
Một course đã publish với chapter/lesson (và quiz tùy chọn) hiển thị trong catalog; toàn bộ nội dung do editor quản lý.

## Design link
[C12 WF-006](../../C-Design/C12-workflows/WF-006-course-authoring-and-publishing.md)
