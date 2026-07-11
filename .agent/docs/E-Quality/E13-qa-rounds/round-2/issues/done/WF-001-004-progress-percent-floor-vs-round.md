---
id: WF-001-004
workflow: WF-001
title: Progress percentage inconsistent between stored value and lesson player (floor vs round)
severity: low
category: consistency
status: done
roles: student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-001-004 - Same course shows 42% on dashboard but 43% in the lesson player

## Problem
Progress is computed two different ways:
- `ProgressApiController::completeLesson()` stores
  `field_progress = floor((completed / total) * 100)` → **42** for 3/7.
- `lms-lesson-view.html.twig` line 289 recomputes
  `(completed / total * 100)|round` → **43** for 3/7.

The dashboard/course cards read the stored value (42%) while the lesson player
shows 43% for the same enrolment — a visible inconsistency (C12 WF-001 requires
consistent progress rollup).

## Expected
One rounding rule everywhere. Use floor (conservative — never overstates, never
hits 100% before genuine completion) in both the stored value and all displays.

## Evidence
- `01-student-dashboard.png` course 88 = 42%.
- `07-student-lesson-before-complete.png` course 88 = 43% (3/7 lessons).

## Fix plan
- `lms-lesson-view.html.twig`: change `|round` to `|round(0, 'floor')` for
  `progress_percent`.

## Verification
- Lesson player and dashboard show the same integer for the same enrolment.

## Resolution (2026-07-11)
`lms-lesson-view.html.twig` `progress_percent` now uses `round(0, 'floor')`. Verified: learn page shows **57%** == stored `field_progress` 57.00.
