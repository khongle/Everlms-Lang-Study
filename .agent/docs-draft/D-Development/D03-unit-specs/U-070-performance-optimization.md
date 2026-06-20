# D03 — Unit Spec: Performance Optimization (N+1 Queries, Lazy Load)

**Unit ID:** U-070
**Group:** G-HARDENING
**Title:** Performance optimization (N+1 queries, lazy load)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned

---

## Scope

Profile and fix N+1 query patterns on high-traffic pages (catalog, dashboard, course detail). Implement lazy loading for lesson media. Target: catalog page < 10 DB queries, dashboard < 15 queries, course detail < 8 queries.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/CourseCatalogService.php` | Modify | lms_course |
| `src/Controller/DashboardController.php` | Modify | lms_progress |
| `src/Controller/CourseDetailController.php` | Modify | lms_course |
| `templates/lesson-video.html.twig` | Modify | lms_lesson |
| `templates/lesson-list.html.twig` | Modify | lms_lesson |

---

## Interfaces / Services

**Anti-patterns to fix:**
1. `foreach ($courses as $course) { $course->getInstructor()->load(); }` → batch load with `entityTypeManager->loadMultiple()`
2. `foreach ($chapters as $ch) { count($ch->getLessons()); }` → single JOIN query
3. Lesson images loaded eagerly on catalog → `loading="lazy"` + srcset

**Entity bulk loading pattern:**
```php
// BAD: N+1
foreach ($courses as $c) { $c->get('instructor')->entity->getDisplayName(); }

// GOOD: bulk
$instructorIds = array_map(fn($c) => $c->get('instructor')->target_id, $courses);
$instructors = $this->entityTypeManager->getStorage('user')->loadMultiple($instructorIds);
```

---

## Acceptance Criteria

- [ ] Catalog page: <= 10 DB queries per page (Drupal debug toolbar verification)
- [ ] Dashboard: <= 15 DB queries per page
- [ ] Course detail: <= 8 DB queries per page
- [ ] Lesson media (video thumbnails) use `loading="lazy"` attribute
- [ ] No `entityLoad` inside a loop on any list page
- [ ] Symfony profiler or Drupal query log used to verify counts in dev

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Query count assertions per page |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- U-069 — Redis cache reduces repeated queries further
- Profile before caching: fix N+1 first, then cache

---

**Feature Flag:** None

*End of U-070-performance-optimization.md*
