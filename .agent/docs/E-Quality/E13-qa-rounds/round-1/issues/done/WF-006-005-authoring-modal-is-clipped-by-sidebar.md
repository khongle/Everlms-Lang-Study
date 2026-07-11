# WF-006-005: Course authoring modal is clipped by the workspace sidebar

## Status
Done

## Workflow
WF-006 - Course authoring and publishing

## Evidence
- Screenshot `010-instructor-lesson-before-fill.png` shows the `Add chapter` modal opening beneath the fixed workspace sidebar.
- The modal title and left-side labels are partially hidden by the sidebar.
- The sidebar has `z-index: 1000`; the Drupal UI dialog override in `lms-admin-entity-form.html.twig` does not set a workspace-safe stacking order.

## Problem
Inline entity form modals can render under the admin sidebar, clipping critical form labels and controls.

## Impact
Authors cannot reliably read or interact with the left edge of chapter/lesson forms, especially on smaller desktop widths.

## Expected
Workspace dialogs should appear above the dimmed page/sidebar overlay without clipped content.

## Resolution Plan
- Scope dialog and overlay z-index fixes to workspace pages.
- Retest the chapter/lesson modal screenshots.

## Resolution
- Workspace UI dialog and Inline Entity Form overlay styles now keep dialogs
  above the dimmed workspace content and away from the fixed sidebar.
- The fix is scoped to workspace pages to avoid changing unrelated public/core
  dialogs.

## Verification
- Screenshot `010-instructor-lesson-before-fill.png` now shows the nested lesson
  form fully visible without sidebar clipping.
- The nested chapter/lesson save path completed successfully for course `133`.
