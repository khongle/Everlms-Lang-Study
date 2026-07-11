---
id: WF-001-003
workflow: WF-001
title: Dashboard hero shows "Resume from your latest checkpoint" for a not-started course
severity: medium
category: ux-content
status: done
roles: student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-001-003 - Dashboard "Continue learning" hero mis-selects and mis-labels

## Problem
`lms-dashboard.html.twig` picks the hero course as `recent_courses|first`
(line 70) and always prints the copy *"Resume from your latest checkpoint and
keep your progress moving without losing your place."* (line 107).

For `maria.santos` (uid 61) the hero surfaces **"Infection Control and
Prevention" at 0% ("Ready to start")** while an actually in-progress course
("Introduction to German Geriatric Care Standards", 42%) exists. The button
correctly says "Start Course", but the surrounding "Resume from your latest
checkpoint" copy contradicts a not-started course, and a resume spotlight should
prioritise a course that is genuinely in progress.

## Expected
- Hero prefers an `in_progress` course when one exists; falls back to first.
- Hero copy adapts to status: not_started → a "start" framing;
  in_progress → the resume framing; completed → a "review" framing.

## Evidence
- `01-student-dashboard.png` — hero shows 0% "Infection Control and Prevention"
  with "Resume from your latest checkpoint" copy while course 88 is at 42%.

## Fix plan
- Template: `featured_course = recent_courses|filter(c => c.status == 'in_progress')|first ?? recent_courses|first`.
- Template: make the spotlight copy conditional on `featured_course.status`.

## Verification
- Re-capture maria dashboard → hero surfaces the 42% course with resume copy,
  or shows start framing when nothing is in progress.

## Resolution (2026-07-11)
Dashboard hero now prefers an `in_progress` course (`recent_courses|filter(...)|first|default(...)`) and the spotlight copy adapts to status. Verified: maria's hero surfaces 'Introduction to German Geriatric Care Standards' (IN PROGRESS) with resume copy.
