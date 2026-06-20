# D03 — Unit Spec: Course Detail Page

**Unit ID:** U-011
**Group:** G-CONTENT
**Title:** Course detail page
**Status:** DONE
**Priority:** P0

---

## Scope

Public course detail page at `/courses/{lms_course}`. Renders full course information including syllabus (chapters/lessons), instructor profile, ratings, enrollment/purchase actions, and related courses. Implemented in `CoursePublicController::detail()`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` | `detail(Course $lms_course): array` method |
| `web/modules/custom/lms_course/lms_course.routing.yml` | `/courses/{lms_course}` route |
| `web/modules/custom/lms_course/templates/lms-course-detail.html.twig` | Course detail template |
| `web/modules/custom/lms_course/templates/lms-instructor-profile.html.twig` | Instructor profile partial |

---

## Entities / Services / Controllers / Templates

**CoursePublicController methods (relevant):**
- `detail(Course $lms_course): array` — full course page render array
- `instructorProfile(UserInterface $user): array` — instructor public profile

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Route resolves; template renders |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | All page sections render with data |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/courses/{id}` renders course title, description, thumbnail
- [x] Syllabus accordion shows chapters and lesson titles
- [x] Instructor name/avatar displayed
- [x] Enroll or purchase CTA shown based on course price
- [x] Star rating summary displayed
- [x] Course metadata (level, language, duration, category) shown

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
| U-005 | Course entity is data source |
| U-006 | Chapter entity for syllabus |
| U-007 | Lesson entity for syllabus items |
| U-009 | Catalog links to detail page |
| U-041 | Rating entity displayed on detail |
| U-025 | Order creation triggered from detail page |

---

*End of U-011-course-detail-page.md*
