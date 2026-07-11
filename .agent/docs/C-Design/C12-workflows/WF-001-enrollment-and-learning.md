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

## As-built enrolment & access (round-2 verified)
- **Canonical `field_status` vocabulary:** `enrolled` → `in_progress` → `completed` (learner-facing). The legacy `active` value and the orphaned `lms_course.enroll_free` route/controller were removed in round-2; free enrolment is written as `enrolled`.
- **Enrolment paths:** the course-detail "Enroll Now" CTA (non-enrolled learner) links to `lms_order.checkout` (`/checkout/{course_id}`). `CheckoutController` short-circuits a **free** course into a direct enrolment (`lms_user_course` with `field_status = enrolled`, **no order row**) and redirects back to the detail page with a success message. Paid courses continue through WF-004.
- **Membership unlock:** an active membership (`lms_membership.manager::hasActiveMembership`) makes `is_enrolled` true for every course **without** creating an `lms_user_course` row. Such a learner has access but no progress until they open a lesson.
- **CTA semantics:** enrolled/unlocked **with** lesson progress → "Continue Learning"; enrolled/unlocked **without** progress yet → "Start Learning". `has_progress` = existence of any `lms_user_lesson` row for the course's lessons. The dashboard, catalog card, and course-detail sidebar must agree.
- **Progress rounding:** stored `lms_user_course.field_progress` and every display (dashboard, card, lesson player) use `floor((completed / total) * 100)` so the same enrolment never shows two different percentages.

## Notifications
`lms_notification` on enroll + completion (in_app ok; email/push partial R-005). Sensitive links point to role-protected pages.

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/001-enrollment-and-learning/`. Issues found + fixed: `WF-001-001..006` (issues/done). Real sessions: `maria.santos` (uid 61, member), `julia.nowak` (uid 71, non-member free enrol).
