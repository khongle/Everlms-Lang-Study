# C13 Usecases - WF-006 Course authoring & publishing

## Workflow
- C12: [WF-006](../../C12-workflows/WF-006-course-authoring-and-publishing.md) · B04: [WF-006](../../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md)
- Modules: lms_course, lms_chapter, lms_lesson, lms_ai
- Business flow: đăng nhập qua form -> tạo draft -> dựng curriculum -> publish -> verify catalog.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-006-001](UC-WF-006-001-author-and-publish-course.md) | Author and publish a course | Instructor | Course authoring | P032, P033 |

## Completion rule
Đóng khi một instructor/content editor thật đăng nhập qua `/user/login`,
tạo một draft course từ `/workspace/courses/create`, thêm ít nhất một
chapter và một lesson qua UI lồng nhau, publish course từ
`/workspace/courses/{id}/edit`, và một learner anonymous có thể thấy course cùng
curriculum trên `/courses` và `/courses/{id}`.

Bằng chứng (evidence) phải bao gồm screenshot cho từng bước, trạng thái trước/sau khi điền form,
và một lần verify database về hệ thống phân cấp course/chapter/lesson đã lưu.
