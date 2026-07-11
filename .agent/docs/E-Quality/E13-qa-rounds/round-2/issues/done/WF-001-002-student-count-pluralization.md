---
id: WF-001-002
workflow: WF-001
title: Course detail shows "1 students" / "0 students" (bad pluralization)
severity: low
category: content
status: done
roles: student, anonymous
source: round-2 real-session QA
found: 2026-07-11
---

# WF-001-002 - Enrolled-student count is not singular/plural aware

## Problem
The course detail hero meta renders `{{ enrolled_students }} {{ 'students'|t }}`,
producing "1 students" and "0 students".

## Expected
- 1 → "1 student"
- 0 / n → "0 students" / "n students"

## Evidence
- `11-newstudent-enroll-destination.png` shows "1 students" after julia enrolled.

## Fix plan
- `lms-course-detail.html.twig` line ~58: use a singular/plural aware label,
  e.g. `{{ enrolled_students == 1 ? 'student'|t : 'students'|t }}`.

## Verification
- Course with 1 enrolment shows "1 student"; 0 shows "0 students".

## Resolution (2026-07-11)
`lms-course-detail.html.twig` uses a singular/plural aware label. Verified: course 108 (1 enrolment) shows **'1 student'**.
