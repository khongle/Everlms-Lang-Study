# C05 Module - lms_progress

**Name:** Enrollment & progress · **Phase:** 3 · **Epic:** [E03](../../D-Development/D02-epics/E03-learning-progress.md) · **Status:** done

## Responsibility
Owns enrollment + progress: the user-course join (`lms_user_course`) and per-lesson progress (`lms_user_lesson`). Drives content gating, resume, completion detection, and certificate eligibility.

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
- Progress served over the mobile API ([F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)).

## Backing pages
| Label | URL |
| ----- | --- |
| My courses / dashboard | `/dashboard`, `/my-courses` |
| Enroll | `/course/{course}/enroll` |
| Progress (in player) | `/course/{course}/learn` |

## Cross references
- Entities [C04](../C04-entities/lms_user_course.md); completion feeds [lms_certificate](lms_certificate.md).

## Dependencies
- lms_base, lms_course, lms_chapter, lms_lesson.

## Implementation notes
- Lesson completion updates `lms_user_lesson`; rollup recomputes `lms_user_course.field_progress`. Completion of required lessons + final quiz pass → course complete (event for certificate). ~81 enrollments live.
