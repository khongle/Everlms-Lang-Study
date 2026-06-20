# Task: Cache Strategy Implementation

**ID:** TASK-021
**Unit:** U-069
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG (15% — almost none)

## Audit Notes
- Cache tag invalidation: only 3 files use `invalidateTags()` (wishlist, note, course workspace)
- `#cache` metadata: only 5 render arrays and all set `max-age => 0` (kill caching)
- Zero use of `addCacheContexts()` besides one breadcrumb builder
- No Redis configuration in custom modules
- All analytics controllers return render arrays without cache metadata

## Description
Implement cache strategy: page cache, entity cache, render cache, Redis backend.

## Acceptance Criteria
- [ ] Redis cache backend configured
- [ ] Page cache cho public pages (homepage, catalog, course detail)
- [ ] Entity cache cho frequently read entities (courses, categories)
- [ ] Cache tags invalidation đúng khi entity update
- [ ] Render cache cho expensive blocks (course cards, stats)
- [ ] Cache warm-up strategy

## Technical Notes
- Drupal cache API: `\Drupal::cache()`, cache tags, cache contexts
- Redis: `drupal/redis` contrib module
- settings.php: Redis configuration
- Cache tags: `lms_course:{id}`, `lms_course_list`, etc.

## Dependencies
- None

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Performance improvement measurable
