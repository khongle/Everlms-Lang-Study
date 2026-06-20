# D03 — Unit Spec: Catalog Page + Search/Filter

**Unit ID:** U-009
**Group:** G-CONTENT
**Title:** Catalog page + search/filter
**Status:** DONE
**Priority:** P0

---

## Scope

Public course catalog at `/courses` with keyword search, category/level/price filtering, and a JSON search API endpoint. Implemented in `CoursePublicController::catalog()` and `::searchApi()`. The catalog template `lms-catalog.html.twig` renders filtered course card grids.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` | `catalog()`, `searchApi()` methods |
| `web/modules/custom/lms_course/lms_course.routing.yml` | `/courses` and search API routes |
| `web/modules/custom/lms_course/templates/lms-catalog.html.twig` | Catalog page template |
| `web/modules/custom/lms_course/templates/lms-course-card.html.twig` | Individual course card partial |

---

## Entities / Services / Controllers / Templates

**CoursePublicController methods (relevant):**
- `catalog(Request $request): array` — server-side filtered catalog
- `searchApi(Request $request): JsonResponse` — AJAX search endpoint
- `buildCourseCards(array $courses): array` — private card builder

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | `/courses` route resolves; template renders |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Keyword and category filter returns correct results |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/courses` renders paginated course catalog
- [x] Keyword search filters by title/description
- [x] Category filter uses `course_category` taxonomy
- [x] Level filter uses `course_level` taxonomy
- [x] JSON search API returns course cards as JSON
- [x] Empty state shown when no results

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
| U-004 | Category/level taxonomies for filters |
| U-010 | Category management provides taxonomy data |
| U-011 | Course detail linked from catalog cards |

---

*End of U-009-catalog-search.md*
