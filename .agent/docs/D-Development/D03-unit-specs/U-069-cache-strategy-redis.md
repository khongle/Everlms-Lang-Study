# D03 — Unit Spec: Cache Strategy Implementation (Redis)

**Unit ID:** U-069
**Group:** G-HARDENING
**Title:** Cache strategy implementation (Redis)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned

---

## Scope

Configure Redis as Drupal cache backend for hot data caches. Define per-bin TTLs, implement cache tags for entity invalidation, and add explicit cache calls in high-read services (catalog, analytics, course detail).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `settings.php` / `settings.local.php` | Modify | root (DDEV) |
| `web/sites/default/services.yml` | Modify | root |
| `src/Service/CourseCatalogService.php` | Modify | lms_course |
| `src/Service/AnalyticsService.php` | Modify | lms_analytics |
| `src/Controller/CourseDetailController.php` | Modify | lms_course |

---

## Interfaces / Services

- Redis via `drupal/redis` contrib module (must be in composer.json)
- Cache bins to configure:
  - `cache.default` → Redis (TTL: 1 hour)
  - `cache.render` → Redis (TTL: 1 hour)
  - `cache.entity` → Redis (TTL: 30 min)
  - `cache.data` → Redis (TTL: 15 min) — analytics, catalog data
- Cache tags: `course:{id}`, `user_course:{user_id}` — invalidated on entity save
- Pattern: `$cache->get($cid)` before query; `$cache->set($cid, $data, Cache::PERMANENT, $tags)` after

---

## Acceptance Criteria

- [ ] Redis backend active in DDEV (verify via `drush php:eval "print_r(drupal_static('Drupal\Core\Cache\CacheBackendInterface'));"`
- [ ] Course catalog page: second load served from cache (no DB queries for course list)
- [ ] Analytics dashboard: data cached 15 min; cache tag `course:{id}` invalidated when course updated
- [ ] Course detail page: cached with `course:{id}` tag; invalidated on course save
- [ ] Cache hit ratio visible in Drupal debug toolbar (in dev) or Redis `INFO stats`
- [ ] Cache does NOT serve stale access control decisions (page cache bypass for authenticated routes)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Cache hit test, cache invalidation on entity save |
| G4 Adversarial | PENDING | Verify auth-gated content not leaked via page cache |

---

## Dependencies

- `drupal/redis` must be available in composer.json
- U-070 (performance) — complementary; run together

---

**Feature Flag:** None

*End of U-069-cache-strategy-redis.md*
