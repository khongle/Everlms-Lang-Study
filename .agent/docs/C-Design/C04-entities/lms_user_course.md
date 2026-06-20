# C04 Entity - lms_user_course

**Display name:** User Course (enrollment + course progress)
**Owning module(s):** [lms_progress](../C05-modules/lms_progress.md)
**Mode:** as-built (Content Entity)

## Purpose
The enrollment + rolled-up progress record joining a user to a course. The control point for content gating and the trigger source for certificate eligibility.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | enrolled user |
| field_course | ref → lms_course |
| field_progress | percent complete (rollup) |
| field_enrolled_at | enrollment timestamp |
| field_completed_at | completion timestamp |
| field_status | enrolled / in-progress / completed |

## Relationships
- N→1 `user`, N→1 `lms_course`. Aggregates `lms_user_lesson` progress.

## Editorial/data management
System-managed (created on enroll, updated as lessons complete); not editor-authored. Read on dashboard + analytics + mobile API. ~81 live enrollments. See [F-004](../../B-Requirements/B03-features/F-004-enrollment-and-content-gating.md), [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md).
