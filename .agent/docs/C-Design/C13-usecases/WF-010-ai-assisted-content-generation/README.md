# C13 Usecases - WF-010 AI-assisted content generation

## Workflow
- C12: [WF-010](../../C12-workflows/WF-010-ai-assisted-content-generation.md) · B04: [WF-010](../../../B-Requirements/B04-business-workflows/WF-010-ai-assisted-content-generation.md)
- Modules: lms_ai, lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question
- Business flow: prompt + provider → preview draft → review → lưu draft entity.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-010-001](UC-WF-010-001-generate-course-draft.md) | Generate a course draft with AI | Instructor | AI content generation | P044 |

## Completion rule
Đóng khi một editor biến một prompt thành draft đã review, được lưu dưới dạng hệ thống phân cấp course chưa publish kèm lesson và quiz tùy chọn.
