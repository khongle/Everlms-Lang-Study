# WF-006 - Course authoring & publishing

## Business flow
An instructor/editor creates a course, defines its metadata (category, level, language, tags, instructor, price), then builds the curriculum: add chapters (`field_chapters`), add lessons to each chapter (`field_lessons`) with body/media/attachments, and optionally attach quizzes (WF-002). They may use the AI assistant (WF-010) to draft content. After review, they publish the course, making it visible in the catalog (F-001). Blog/FAQ content (F-016) follows the same editor-managed publishing model.

## Participating modules
[lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md), [lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Related features (B03)
[F-002](../B03-features/F-002-course-authoring-and-structure.md), [F-016](../B03-features/F-016-blog-and-faq-content.md), [F-018](../B03-features/F-018-ai-authoring-assistant.md)

## Trigger
Instructor/editor creates or edits a course and publishes.

## Outcome
A published course with chapters/lessons (and optional quizzes) visible in the catalog; all copy editor-managed.

## Design link
[C12 WF-006](../../C-Design/C12-workflows/WF-006-course-authoring-and-publishing.md)
