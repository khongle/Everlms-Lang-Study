---
id: WF-001-006
workflow: WF-001
title: Lesson player Overview tab shows "No overview description available" for text lessons
severity: low
category: ux-content
status: done
roles: student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-001-006 - Redundant/alarming empty Overview tab on text lessons

## Problem
On a text lesson the full body is rendered in the main content area, but the
"Overview" tab (`lms-lesson-view.html.twig`, guarded by `type != 'text'`) then
shows **"No overview description available for this lesson."** This reads like
missing data even though the lesson content is present directly above.

## Expected
For text lessons the Overview tab should not present a "missing data" message.
Show the lesson summary/description if one exists, otherwise a neutral note that
the reading is shown above (or omit the empty placeholder).

## Evidence
- `07-student-lesson-before-complete.png` — text lesson body visible, yet the
  Overview tab reads "No overview description available for this lesson."

## Fix plan
- In the Overview pane, for text lessons show `field_short_description`/summary
  when present; otherwise a neutral "The full lesson is shown above." message
  instead of the "no description" placeholder.

## Verification
- Text lesson Overview tab no longer shows the missing-data phrasing.

## Resolution (2026-07-11)
Overview tab shows a neutral note ('The full lesson reading is shown above...') for text lessons instead of the missing-data message. Verified: new copy renders.
