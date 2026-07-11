# WF-006-002: Instructor role cannot access course authoring workflow

## Status
Done

## Workflow
WF-006 - Course authoring and publishing

## Evidence
- C12/C13 define `instructor` as the primary actor for create → curriculum → publish.
- `config/sync/user.role.instructor.yml` only grants comment permissions.
- `/workspace/courses` and `/workspace/courses/create` require `administer lms_course`.
- The existing entity access handlers already define narrower permissions (`create lms_course`, `edit own lms_course`, `view lms_course`), but the workspace routes bypass them.

## Problem
Real instructors cannot start WF-006 even though the workflow documentation names them as the initiator. Granting `administer lms_course` would over-permit instructors and expose unrelated management surfaces.

## Impact
The documented role model fails with real users. QA must use a manager/admin account, which hides authoring permission regressions and weakens separation of duties.

## Expected
Instructors and content editors should be able to create courses and edit their own courses through the workspace course authoring routes. Managers/admins should continue to manage all courses.

## Resolution Plan
- Grant least-privilege authoring permissions to `instructor` and `content_editor`.
- Let `/workspace/courses/create` use entity create access instead of `administer lms_course`.
- Let `/workspace/courses` use course-view permission and filter non-managers to their own courses.
- Allow chapter/lesson update access for entities attached to a course owned by an author.
- Retest WF-006 with a real instructor account.

## Resolution
- `instructor` and `content_editor` now receive least-privilege course
  authoring permissions through configuration and update hook.
- `/workspace/courses/create` now uses `_entity_create_access: lms_course`.
- `/workspace/courses` now uses `view lms_course` and filters non-manager users
  to their own courses.
- Course, chapter, and lesson access checks support own-course authoring without
  granting full course administration.

## Verification
- Retested with real `/user/login` form as instructor `sabine.mueller` (uid `56`).
- Instructor created, edited, and published own course `133`.
- Database verification: course `133` owner uid is `56`; chapter `484` and
  lesson `1778` are linked through the UI-created hierarchy.
