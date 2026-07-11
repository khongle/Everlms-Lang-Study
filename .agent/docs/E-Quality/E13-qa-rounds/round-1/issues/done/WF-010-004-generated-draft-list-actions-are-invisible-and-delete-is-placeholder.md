---
id: WF-010-004
workflow: WF-010
title: Generated draft is visible in course list but row actions are invisible and delete is a placeholder
severity: medium
category: ui-function
status: done
roles: instructor, content_editor
source: visual QA
found: 2026-06-30
---

# WF-010-004 - Course list actions unclear after AI draft save

## Problem
After saving the AI-generated draft, `/workspace/courses` shows the new draft
course, but the `Actions` cell is effectively blank at the tested desktop
viewport. The template also renders delete as `href="#"`, so one of the visible
row actions is not a real route.

## Evidence
- Screenshot
  `_screens/010-ai-assisted-content-generation/007-instructor-course-list-draft-visible.png`
  shows the generated draft row with no clear action buttons.
- `lms-admin-courses-table.html.twig` renders icon-only links with low-affordance
  inline color and a placeholder delete link.

## Impact
The AI workflow technically saves a draft, but the instructor has no reliable
visible affordance to open/review/edit/delete it from the management list.

## Expected
The course list row should show visible, keyboard-focusable view/edit/delete
icon buttons with real URLs and enough width/contrast to remain visible at the
standard desktop QA viewport.

## Fix Plan
- Provide real view/edit/delete URLs from `AdminController::courseList()`.
- Render action links with accessible labels/titles and stable classes.
- Add scoped CSS for visible action icon buttons in the workspace table.
- Retest WF-010 screenshot 007.

## Resolution
- `AdminController::courseList()` now supplies real view/edit/delete URLs for
  each row.
- `lms-admin-courses-table.html.twig` renders accessible action links with
  titles, aria labels, and stable classes.
- `ui-fixes-r2.css` keeps the action group visible within the table panel.

## Verification
- Screenshot `007-instructor-course-list-draft-visible.png` now shows visible
  view/edit/delete action buttons for the generated draft course row.
