# Task: Trending Courses Computation

**ID:** TASK-181
**Unit:** — (extends lms_analytics)
**Group:** G-CONTENT
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Compute a daily trending score for each course and surface trending courses on the homepage rail and category pages. The score is a weighted combination of enrollment velocity (enrollments last 7 days vs prior 7 days) and view velocity (page views last 7 days vs prior 7 days). A cron job refreshes the pre-computed ranking table daily. The implementation lives in `lms_analytics` as a new service and uses the existing analytics event tables populated by TASK-014.

## Acceptance Criteria
- [ ] `TrendingService` in `lms_analytics` computes trending score for all published courses
- [ ] Score formula: `(enroll_7d / max(enroll_prior_7d, 1)) * 0.6 + (view_7d / max(view_prior_7d, 1)) * 0.4` — configurable weights via `lms_analytics.settings`
- [ ] Results stored in `lms_trending_courses` table (`course_id`, `score`, `enroll_7d`, `view_7d`, `computed_at`)
- [ ] `lms_analytics_cron()` triggers rebuild; completes in ≤2 min for 500 courses
- [ ] Homepage rail "Trending now": ≤8 course cards, visible to all users (anon + auth)
- [ ] Category page: top 6 trending courses in that category shown in sidebar block
- [ ] Admin UI: `/admin/lms/analytics/trending` shows current scores table (sortable) — read-only view
- [ ] Unit test: score function is deterministic given fixture velocity data

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Service/TrendingService.php` — `rebuild()`, `getTopCourses(int $limit, int $categoryId = NULL): array`
- DB schema: `lms_trending_courses` (`course_id` int PK, `score` decimal(8,4), `enroll_7d` int, `view_7d` int, `computed_at` timestamp). Add to `lms_analytics.install`.
- Cron: extend `hook_cron()` in `lms_analytics.module`; use `\Drupal::state()` to store `lms_analytics.last_trending_rebuild` timestamp and skip if run within last 23 h
- Block: `src/Plugin/Block/TrendingCoursesBlock.php` — configurable limit + category context
- Data source: query `lms_analytics_event` table (from TASK-014) for `event_type IN ('enrollment_created', 'course_view')` grouped by `course_id` + date window
- Settings: `config/install/lms_analytics.settings.yml` — add `trending_weight_enrollment: 0.6`, `trending_weight_views: 0.4`

## Dependencies
- TASK-014 (analytics dashboards — confirms lms_analytics_event table populated)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: score formula + rebuild; Behat: trending rail visible on homepage)
- [ ] Cron runtime benchmarked and within 2 min bound at 500-course fixture
