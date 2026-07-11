---
id: WF-001-005
workflow: WF-001
title: Dead lms_course.enroll_free route/controller writes non-canonical enrollment status
severity: low
category: code-quality
status: done
roles: student
source: round-2 code + real-session QA
found: 2026-07-11
---

# WF-001-005 - Orphaned free-enroll route with divergent status vocabulary

## Problem
`lms_course.enroll_free` (`/courses/{id}/enroll-free`) and
`CourseEnrollmentController::enrollFree()` exist but are **not referenced by any
template, JS, or menu** (only the mobile API has its own separate route). The
actual free-enrolment path is `CheckoutController::checkout()` (reached from the
"Enroll Now" button → `/checkout/{id}`), which creates the `lms_user_course`
with the canonical `field_status = 'enrolled'`.

The dead controller instead writes `field_status = 'active'`, a value not in the
WF-001 state vocabulary (`enrolled`, `in_progress`, `completed`). If ever wired
up it would inject an inconsistent status.

## Expected
Least-code, single enrolment pipeline. Remove the orphaned route + controller
(the checkout path already covers free enrolment), or, if retained, align its
status to `enrolled`.

## Evidence
- `grep` shows no template/JS reference to `enroll_free` / `enroll-free`.
- julia.nowak free-enrolled via `/checkout/108` → row 217 `field_status = enrolled`.

## Fix plan
- Remove the `lms_course.enroll_free` route from `lms_course.routing.yml` and
  delete `CourseEnrollmentController.php`.

## Verification
- Route no longer resolvable; free enrolment via "Enroll Now" still works and
  still writes `enrolled`.

## Resolution (2026-07-11)
Removed `lms_course.enroll_free` route and deleted `CourseEnrollmentController.php`. Verified: route resolver reports **REMOVED**; free enrolment via checkout still writes canonical `enrolled`.
