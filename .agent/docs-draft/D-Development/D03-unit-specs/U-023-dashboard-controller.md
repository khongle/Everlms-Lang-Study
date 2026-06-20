# D03 — Unit Spec: Dashboard Controller + Template

**Unit ID:** U-023
**Group:** G-PROGRESS
**Title:** Dashboard controller + template
**Status:** DONE
**Priority:** P0

---

## Scope

User learning dashboard at `/dashboard` that aggregates enrolled courses, progress percentages, recent lesson activity, quiz results, and certificates. Implemented in `UserPortalController::dashboard()` with the `lms-dashboard.html.twig` template. Also provides the lesson view at `/courses/{lms_course}/lessons/{lms_lesson}`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` | `dashboard()`, `lessonView()` methods |
| `web/modules/custom/lms_progress/lms_progress.routing.yml` | `/dashboard`, lesson view routes |
| `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` | Dashboard template |
| `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` | Lesson player template |

---

## Entities / Services / Controllers / Templates

**UserPortalController methods:**
- `dashboard(): array` — enrolled courses with progress, recent activity
- `lessonView(Course $lms_course, Lesson $lms_lesson): array` — lesson content player
- `myCertificates(): array` — see U-024 (same controller)
- `profile(): array` — see U-024

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Routes resolve; templates render |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Dashboard displays enrolled courses with progress |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/dashboard` renders for authenticated users
- [x] Enrolled courses listed with progress percentage bars
- [x] Lesson player renders correct content type (video, text, H5P, etc.)
- [x] Progress marked via AJAX call to `/api/progress/mark-complete`
- [x] Unauthenticated users redirected to login

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-021 | UserCourse/UserLesson entities read here |
| U-022 | Progress API called from lesson template |
| U-024 | My courses + continue learning in same controller |

---

*End of U-023-dashboard-controller.md*
