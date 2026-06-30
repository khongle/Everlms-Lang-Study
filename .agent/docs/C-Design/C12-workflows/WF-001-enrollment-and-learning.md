# C12 Workflow - WF-001 Enrollment & learning

## Business source
[B04 WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md)

## Modules
[lms_progress](../C05-modules/lms_progress.md), [lms_course](../C05-modules/lms_course.md), [lms_lesson](../C05-modules/lms_lesson.md)

## Related features (B03)
[F-004](../../B-Requirements/B03-features/F-004-enrollment-and-content-gating.md), [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md), [F-003](../../B-Requirements/B03-features/F-003-lesson-delivery-and-media.md)

## State model
`lms_user_course.field_status`:
| State | Meaning |
| ----- | ------- |
| submitted (enrolled) | enrollment created (free/paid/membership) |
| in_review (in-progress) | lessons being completed; progress accruing |
| approved_or_completed | all required lessons + final quiz passed |
| archived | course archived/unenrolled (rare) |

Enrollment invariant: a learner should have one active `lms_user_course` record per course. Progress rollup must update the learner/course enrollment consistently and learner-facing pages must not render duplicate course cards if legacy duplicate rows exist.

## Permission model
- Initiator: learner (self-enroll free / via order / membership).
- Responsible: learner (progress).
- Admin: manager can enroll/unenroll a user.

## Notifications
`lms_notification` on enroll + completion (in_app ok; email/push partial R-005). Sensitive links point to role-protected pages.
