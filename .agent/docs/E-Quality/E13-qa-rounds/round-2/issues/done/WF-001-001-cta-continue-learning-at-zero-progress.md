---
id: WF-001-001
workflow: WF-001
title: Enrolled/unlocked CTA says "Continue Learning" even at 0% progress
severity: medium
category: ux-content
status: done
roles: student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-001-001 - Enrolled CTA says "Continue Learning" before the learner has started

## Problem
On the course detail page (`/courses/{id}`) and on catalog course cards, an
enrolled or membership-unlocked learner always sees the primary CTA labelled
**"Continue Learning"**, even when they have never opened a lesson (0% progress).

Reproduced with:
- `julia.nowak` (uid 71) immediately after enrolling in course 108 (0 progress) —
  CTA reads "Continue Learning".
- `maria.santos` (uid 61, member) on course 108 which she has never started —
  CTA reads "Continue Learning".

The dashboard already gets this right (`Start Course` for `not_started`,
`Continue Learning` for `in_progress`), so the product is internally
inconsistent.

## Expected
- Enrolled/unlocked + no progress yet → **"Start Learning"** / "Start Course".
- Enrolled/unlocked + progress > 0 → **"Continue Learning"**.
- Behaviour matches the dashboard card logic.

## Evidence
- `03-student-course-detail-not-enrolled.png` (maria, unlocked, "Continue Learning")
- `11-newstudent-enroll-destination.png` (julia, just enrolled 0%, "Continue Learning")

## Fix plan
- `CoursePublicController::detail()` compute `has_progress` (any `lms_user_lesson`
  record for this course's lessons for the user) and pass to the template.
- `lms-course-detail.html.twig`: label = `has_progress ? 'Continue Learning' : 'Start Learning'`.
- Mirror in the course-card helper/template.

## Verification
- Re-capture course detail for a 0-progress enrolled learner → "Start Learning".
- Re-capture course 88 for maria (42% progress) → still "Continue Learning".

## Resolution (2026-07-11)
`CoursePublicController::detail()` computes `has_progress` (declared in `hook_theme` + render array); detail template CTA uses `has_progress ? 'Continue Learning' : 'Start Learning'`. Verified: course 108 (0 progress, member) -> **Start Learning**; course 88 (57%) -> **Continue Learning**.
