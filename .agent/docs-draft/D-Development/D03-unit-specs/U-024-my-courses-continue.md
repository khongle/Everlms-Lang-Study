# D03 — Unit Spec: My Courses + Continue Learning

**Unit ID:** U-024
**Group:** G-PROGRESS
**Title:** My Courses + Continue learning
**Status:** DONE
**Priority:** P0

---

## Scope

User-facing pages for viewing enrolled courses, certificates, and profile. Implements the "My Courses" listing and "Continue Learning" (resume last lesson) functionality. Also exposes `myCertificates()` and `profile()` pages in the same `UserPortalController`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` | `myCourses()`, `myCertificates()`, `profile()` methods |
| `web/modules/custom/lms_progress/lms_progress.routing.yml` | `/my-courses`, `/my-certificates`, `/profile` routes |
| `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` | My courses template |
| `web/modules/custom/lms_progress/templates/lms-my-certificates.html.twig` | My certificates template |

---

## Entities / Services / Controllers / Templates

**UserPortalController methods:**
- `myCourses(): array` — lists all enrolled courses; finds last incomplete lesson for "continue" button
- `myCertificates(): array` — lists earned `lms_user_certificate` records
- `profile(): array` — user profile summary

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Routes resolve; templates render |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | My Courses page shows enrolled courses |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/my-courses` lists all enrolled courses with progress
- [x] "Continue" button links to next incomplete lesson
- [x] Completed courses distinguished from in-progress
- [x] `/my-certificates` lists earned certificates
- [x] `/profile` renders user information

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
| U-021 | UserCourse entities listed here |
| U-023 | Dashboard links to my-courses |
| U-035 | UserCertificate entities listed in my-certificates |

---

*End of U-024-my-courses-continue.md*
