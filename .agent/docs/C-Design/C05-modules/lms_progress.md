# C05 Module - lms_progress

**Name:** Enrollment & progress · **Phase:** 3 · **Epic:** [E03](../../D-Development/D02-epics/E03-learning-progress.md) · **Status:** done

## Responsibility
Sở hữu enrollment + progress: join user-course (`lms_user_course`) và progress theo từng lesson (`lms_user_lesson`). Điều khiển content gating, resume, phát hiện completion, và điều kiện nhận certificate.

## Owned / related entities
- [lms_user_course](../C04-entities/lms_user_course.md)
- [lms_user_lesson](../C04-entities/lms_user_lesson.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-004](../../B-Requirements/B03-features/F-004-enrollment-and-content-gating.md) | Enrollment & content gating |
| [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md) | Progress tracking & resume |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- Progress được phục vụ qua mobile API ([F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)).

## Backing pages
| Label | URL |
| ----- | --- |
| My courses / dashboard | `/dashboard`, `/my-courses` |
| Enroll | `/course/{course}/enroll` |
| Progress (in player) | `/course/{course}/learn` |

## Cross references
- Entity [C04](../C04-entities/lms_user_course.md); completion feed sang [lms_certificate](lms_certificate.md).

## Dependencies
- lms_base, lms_course, lms_chapter, lms_lesson.

## Implementation notes
- Lesson completion cập nhật `lms_user_lesson`; rollup tính lại `lms_user_course.field_progress`. Hoàn tất các lesson bắt buộc + đậu quiz cuối → course complete (event cho certificate). ~81 enrollment đang live.
