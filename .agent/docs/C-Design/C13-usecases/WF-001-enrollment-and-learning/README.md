# C13 Usecases - WF-001 Enrollment & learning

## Workflow
- C12 design: [WF-001](../../C12-workflows/WF-001-enrollment-and-learning.md) · B04: [WF-001](../../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md)
- Modules: lms_progress, lms_course, lms_lesson
- Business flow: khám phá → enroll → học → hoàn tất.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-001-001](UC-WF-001-001-enroll-and-complete-course.md) | Enroll and complete a course | Student | Enrollment & learning | P003, P017, P019 |

## Completion rule
Đóng khi learner có thể enroll đúng một lần cho mỗi course, tiến triển (progress) qua các lesson, và đạt tới course completion với progress được lưu lại và dashboard chỉ render một course card cho mỗi course.
