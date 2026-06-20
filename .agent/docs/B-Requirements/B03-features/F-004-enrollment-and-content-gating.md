# F-004 - Enrollment & content gating

**Status:** built
**Primary module:** [lms_progress](../../C-Design/C05-modules/lms_progress.md)
**Epic:** [E03 Learning & progress](../../D-Development/D02-epics/E03-learning-progress.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-004](../B05-acceptance-criteria/F-004-enrollment-and-content-gating.md)
**Source:** `web/modules/custom/lms_progress/`, `lms_course/`

## Requirement
A user becomes enrolled in a course (via free enrollment, a paid order F-010, or a membership F-012). Enrollment creates a `lms_user_course` record. Lesson/course content is **gated**: only enrolled (or membership-entitled) users can open lesson content; non-enrolled users see the catalog/landing page and a call-to-action.

## Business value
Gating is what makes the catalog monetisable and ties payment/membership to access; it is the control point between marketing and delivery.

## Scope notes
- `lms_user_course` is the enrollment/progress join entity (user ↔ course).
- Entitlement sources: direct free enroll, paid order, active membership plan.
- Access checks live in route access / controllers; must be consistent on web and mobile API (F-019).

## Cross references
- Design: [C04 lms_user_course](../../C-Design/C04-entities/lms_user_course.md), [C12 WF-001](../../C-Design/C12-workflows/WF-001-enrollment-and-learning.md)
- Related: F-010 Orders, F-012 Memberships, F-005 Progress
