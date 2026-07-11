# WF-006-001: Workspace course create page is a static prototype, not a working authoring form

## Status
Done

## Workflow
WF-006 - Course authoring and publishing

## Evidence
- `/workspace/courses/create` returns `#theme => lms_admin_course_create`.
- `lms-admin-course-create.html.twig` renders mock inputs/buttons but no Drupal Form API form, submit handler, validation, or persisted course.
- C12/C13 require create → curriculum → publish with real course/chapter/lesson data.

## Problem
The primary workspace create route looks like a wizard but cannot save a draft, create a course, or publish content. It is prototype UI rather than functional authoring.

## Impact
Instructors/content editors cannot complete WF-006 from the documented workspace route. QA may falsely pass a visual prototype while no data is written.

## Expected
`/workspace/courses/create` should render a real `lms_course` entity form inside the workspace shell, persist the course, and return to the workspace course list after save.

## Resolution Plan
- Replace the static create wizard route with a real Course entity add form.
- Keep the workspace admin shell and active navigation.
- Redirect successful workspace saves to `/workspace/courses`.
- Retest with real authoring user, real chapter/lesson references, and catalog visibility.

## Resolution
- `AdminController::courseCreateWizard()` now builds a real `lms_course` entity
  add form and renders it in `lms_admin_entity_form`.
- `CourseForm::save()` redirects workspace saves back to `/workspace/courses`.
- The static prototype is no longer the active authoring surface for this route.

## Verification
- Retested with real `/user/login` form as instructor `sabine.mueller`.
- Screenshots:
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/006-course-authoring-and-publishing/004-instructor-course-create-before-fill.png`
  through
  `017-instructor-course-list-published.png`.
- Database verification: course `133`, owner `56`, title
  `WF006 Mobility Safety Essentials 20260630-164521`, published status `1`.
