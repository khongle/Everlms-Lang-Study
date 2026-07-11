# WF-006-003: Nested lesson creation fails inside course authoring modal

## Status
Done

## Workflow
WF-006 - Course authoring and publishing

## Evidence
- Real instructor login through `/user/login`.
- `/workspace/courses/create` opens the real course entity form.
- `Chapter & lesson` → `Add new chapter` opens a chapter modal.
- Inside that modal, clicking `Add new lesson` shows Drupal AJAX error text: `Oops, something went wrong. Check your browser's developer console for more details.`

## Problem
The documented create → chapter → lesson authoring flow cannot complete from the workspace UI. The course can start, and chapter creation begins, but nested lesson creation breaks before the lesson form appears.

## Impact
Instructors cannot build the required course → chapter → lesson hierarchy end-to-end from the UI, so catalog content can be published without learner-ready lessons or must be assembled outside the documented workflow.

## Expected
Clicking `Add new lesson` inside the chapter inline form should render a lesson form with required lesson fields, allow save into the chapter, then allow saving the complete course.

## Resolution Plan
- Inspect Drupal AJAX/watchdog errors for the nested lesson form.
- Fix the lesson form/widget configuration or conditional code causing the AJAX failure.
- Retest nested lesson creation from the real workspace UI with a real instructor login.

## Resolution
- The incompatible H5P upload widget was removed from the active lesson form
  display and moved to hidden fields through configuration/update hook.
- H5P embed remains available; the upload widget can be restored only after its
  Drupal 11/contrib widget path is validated.

## Verification
- Retested nested `Add new chapter` -> `Add new lesson` inside
  `/workspace/courses/create` with instructor form login.
- Screenshots:
  `010-instructor-lesson-before-fill.png`,
  `011-instructor-lesson-after-fill.png`, and
  `012-instructor-chapter-after-lesson-created.png`.
- Course `133` persisted linked lesson `1778` under chapter `484`.
