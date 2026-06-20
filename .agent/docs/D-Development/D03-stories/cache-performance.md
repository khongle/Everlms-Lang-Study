# D03 — Story Spec: Cache & Performance

**Story ID:** cache-performance
**Story type:** nfr
**Title:** Cache & Performance — Redis backend, N+1 query elimination, cache tags
**Primary source:** `C-Design/C05-modules/lms_course.md`, `C-Design/C05-modules/lms_analytics.md`
**Owning module(s):** lms_course, lms_analytics, lms_progress, lms_lesson (plus root Drupal config)
**Service(s):** CourseCatalogService (lms_course), AnalyticsService (lms_analytics), DashboardController (lms_progress)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-012 (Security & Performance)
**Design source(s):** `C-Design/C05-modules/lms_course.md`, `C-Design/C05-modules/lms_analytics.md`, `C-Design/C05-modules/lms_lesson.md`
**Round:** N/A
**Driver:** NFR gap; G-HARDENING wave 4 (draft D02-group-specs/10-G-HARDENING.md §Wave 4); page cache disabled (`max_age: 0` in current config)
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: cache-performance` — maps to draft TASK-021, TASK-022

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2-3
**Splitting strategy:**
- S1: N+1 query fixes on catalog page + dashboard + course detail (profile first, fix second)
- S2: Redis cache backend configuration + per-bin TTLs + cache tag implementation
- S3 (if needed): Verify cache invalidation on entity save; auth-gated content not leaked via page cache

## Scope (from primary source)

Two complementary performance improvements that must be applied in order (profile → fix N+1 → then cache):

**Part A — N+1 query elimination:**
- **Catalog page** (`CourseCatalogService`): target ≤ 10 DB queries per page. Current anti-pattern: `foreach ($courses as $c) { $c->get('instructor')->entity->getDisplayName(); }` — one query per course. Fix: batch-load all instructor user entities with `entityTypeManager->getStorage('user')->loadMultiple($instructorIds)`.
- **Dashboard** (`DashboardController`): target ≤ 15 DB queries per page. Fix `foreach ($chapters as $ch) { count($ch->getLessons()); }` — replace with a single JOIN query for lesson counts.
- **Course detail** (`CourseDetailController`): target ≤ 8 DB queries per page.
- **Lesson lists** (`templates/lesson-list.html.twig`): lesson images currently loaded eagerly — add `loading="lazy"` attribute + srcset for lesson thumbnails.

**Part B — Redis cache backend:**
- Configure `drupal/redis` contrib module as cache backend (must be added to `composer.json` if not present)
- Cache bins and TTLs:
  - `cache.default` → Redis, TTL: 1 hour
  - `cache.render` → Redis, TTL: 1 hour
  - `cache.entity` → Redis, TTL: 30 min
  - `cache.data` → Redis, TTL: 15 min (analytics, catalog data)
- Cache tags: `course:{id}`, `user_course:{user_id}` — invalidated on entity save hook
- Implementation pattern in hot services: `$cache->get($cid)` before query; `$cache->set($cid, $data, Cache::PERMANENT, $tags)` after
- **Critical:** page cache must bypass for authenticated routes (prevent stale access-control decisions). Set `Cache-Control: no-store` for authenticated session pages.
- Current state: `system.performance.yml` has `max_age: 0` (page cache disabled — dev mode). Production must have explicit `max_age` per route family.

**Real implementation status (as-built):**
- Page cache disabled globally (`max_age: 0` in config). This is correct for dev but must be configured for production.
- No Redis configuration in `settings.php`. No `drupal/redis` module.
- N+1 query patterns are present in `CourseCatalogService` and other services (confirmed by code analysis in facts base).
- No cache tags implemented for LMS entities.
- `AnalyticsService` has 3 raw aggregate queries — these are candidates for caching with `cache.data` bin.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_course.md` | CourseCatalogService, CourseDetailController |
| `C-Design/C05-modules/lms_analytics.md` | AnalyticsService — 3 aggregate queries to cache |
| `C-Design/C05-modules/lms_progress.md` | DashboardController — N+1 in chapter/lesson loading |
| `C-Design/C05-modules/lms_lesson.md` | Lesson list template — eager image load |
| `B02-technical-requirements.md` | Performance NFRs (page load, query counts, cache strategy) |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Catalog page: ≤ 10 DB queries per page (Drupal debug toolbar verification) | B02-technical-requirements.md §Performance | Non-Functional |
| Dashboard: ≤ 15 DB queries per page | B02-technical-requirements.md §Performance | Non-Functional |
| Course detail: ≤ 8 DB queries per page | B02-technical-requirements.md §Performance | Non-Functional |
| Lesson media (video thumbnails) use `loading="lazy"` | B02-technical-requirements.md §Performance | Non-Functional |
| No `entityLoad` inside a loop on any list page | B02-technical-requirements.md §Performance | Non-Functional |
| Redis cache backend active; course catalog served from cache on 2nd load | B02-technical-requirements.md §Performance | Non-Functional |
| Cache tags invalidated on entity save (stale data not served) | B02-technical-requirements.md §Performance | Non-Functional |
| Auth-gated content not served via page cache to unauthorized users | B02-technical-requirements.md §Security | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_course/src/Service/CourseCatalogService.php` | Modify | Batch-load instructors; add cache.data caching with course tags | TASK-022 | — |
| `web/modules/custom/lms_course/src/Controller/CourseDetailController.php` | Modify | N+1 fix for chapter/lesson loading; add cache.render tags | TASK-022 | — |
| `web/modules/custom/lms_analytics/src/Service/AnalyticsService.php` | Modify | Cache aggregate queries with 15-min TTL in cache.data | TASK-021 | — |
| `web/modules/custom/lms_progress/src/Controller/DashboardController.php` | Modify | N+1 fix for chapter lesson-count; batch-load enrolled courses | TASK-022 | — |
| `web/modules/custom/lms_lesson/templates/lesson-list.html.twig` | Modify | Add `loading="lazy"` + srcset to lesson thumbnail `<img>` | TASK-022 | — |
| `web/modules/custom/lms_lesson/templates/lesson-video.html.twig` | Modify | Add `loading="lazy"` to video poster image | TASK-022 | — |
| `web/sites/default/settings.php` (or `settings.local.php`) | Modify | Configure Redis cache bins via `$settings['cache']['bins']` | TASK-021 | — |
| `web/sites/default/services.yml` | Modify | Configure Redis cache service backend | TASK-021 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| N+1 fixes must be profiled before implementation (measure → fix → measure) | Pre-condition | Drupal Devel/Kint toolbar or query log |
| `drupal/redis` contrib module — must be in composer.json | External | composer.json (may need `composer require drupal/redis`) |
| Redis server available in DDEV environment | Infrastructure | setup-infra.md (DDEV) |
| Story `hardening-security` — DI cleanup pass reduces risk of static service calls affecting cache | Intra-Epic | this D03-stories/ |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP files | PHPCS Drupal standard |
| Cache bypass for authenticated users | All routes with session-sensitive data | G4 adversarial check |

## Editorial Usability / Entity-First Evidence

Not applicable — this Story modifies backend services and infrastructure config only; no visible content changes (lazy-load is a rendering optimization).

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | No feature flags; Redis is infrastructure-level | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-CP-001 | Integration | Catalog page: DB query count ≤ 10 (Drupal debug toolbar assertion) | TASK-022 AC |
| T-CP-002 | Integration | Dashboard: DB query count ≤ 15 | TASK-022 AC |
| T-CP-003 | Integration | Course detail: DB query count ≤ 8 | TASK-022 AC |
| T-CP-004 | Integration | Catalog page 2nd load served from Redis cache (0 DB queries for course list) | TASK-021 AC |
| T-CP-005 | Integration | Analytics data cached; `course:{id}` cache tag invalidated on course save | TASK-021 AC |
| T-CP-006 | Integration | Lesson thumbnails have `loading="lazy"` attribute in rendered HTML | TASK-022 AC |
| T-CP-007 | Security | Authenticated-only content (dashboard, order history) not served via page cache to anonymous | TASK-021 AC |
| T-CP-008 | Performance | Catalog page Lighthouse mobile score ≥ 70 (baseline; Story ui-polish targets ≥ 80) | TASK-022 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | — | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: cache poisoning, stale auth decisions, cache bypass for sensitive routes | 0/3 | — |

## Notes

- Profile before caching. Fix N+1 first, then add Redis. Caching N+1 queries just caches the slow path.
- DDEV has a Redis service available via `.ddev/config.yaml` `additional_services: [redis]`. Verify this is enabled or add it before S2.
- `drupal/redis` module configuration in `settings.php`: use `PhpRedis` client (not Predis) for better performance if `phpredis` extension is available in DDEV PHP container.
- Image styles: current config uses `image_convert_avif` effect (despite the name, output is WebP). This is correct and already optimized. Lazy loading is complementary.
- `AnalyticsService` has 3 aggregate queries: total learners, total courses, total revenue. These are ideal candidates for `cache.data` with a 15-minute TTL and `cache_clear_all()` on any order/enrollment change.
- Do NOT cache user-specific data (enrollment status, progress %) in the shared render cache — only cache globally-shared catalog data.
