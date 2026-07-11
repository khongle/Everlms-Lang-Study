# WF-006-004: Nested lesson type fields do not collapse in course authoring

## Status
Done

## Workflow
WF-006 - Course authoring and publishing

## Evidence
- After fixing the nested lesson AJAX fatal, the lesson form appears inside `Add chapter`.
- Default `Lesson Type` is `Text`.
- Fields for H5P, video, uploaded video, Zoom, SCORM, attachments, PowerPoint, and quiz remain visible at the same time.
- `LessonForm` uses the states selector `select[name="field_type"]`, but nested IEF names the control `field_chapters[form][0][field_lessons][form][0][field_type]`.

## Problem
The lesson form conditional visibility does not work in nested inline entity forms. Authors see every type-specific field even when creating a simple text lesson.

## Impact
The authoring step is cluttered and error-prone. It also makes required/optional meaning unclear and increases the chance that instructors attach content to the wrong lesson type.

## Expected
Type-specific lesson fields should appear only when the selected lesson type requires them, both in standalone lesson forms and nested course authoring forms.

## Resolution Plan
- Update lesson form `#states` selectors to match standalone and nested IEF field names.
- Retest text lesson creation inside course authoring and verify irrelevant media fields are hidden.

## Resolution
- `LessonForm` now attaches a small Drupal behavior for lesson type visibility.
- The behavior supports both standalone lesson forms and nested Inline Entity Form
  field names.
- Text lessons hide incompatible video, Zoom, SCORM, PowerPoint, H5P, and quiz
  fields while leaving common lesson fields available.

## Verification
- Screenshot `011-instructor-lesson-after-fill.png` shows a text lesson after
  fill with incompatible type-specific fields collapsed.
- The saved hierarchy for course `133` includes text lesson `1778` with status
  `1`.
