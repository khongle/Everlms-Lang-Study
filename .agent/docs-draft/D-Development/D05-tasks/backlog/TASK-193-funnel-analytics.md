# Task: Funnel Analytics

**ID:** TASK-193
**Unit:** — (extends lms_analytics)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a funnel analytics view that tracks the conversion rate of users through seven stages: Registration → First course view → Enrollment → First lesson completed → First quiz passed → Course completed → Certificate issued. The report shows the count and drop-off rate at each stage, with a visual funnel chart and date range filtering. Platform admins use this to identify where users disengage and prioritize UX improvements.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/funnel` (admin only)
- [ ] Seven funnel stages displayed in order with user count + conversion rate from previous stage:
  1. Registered users
  2. Users who viewed ≥1 course (distinct page view of course detail)
  3. Users who enrolled in ≥1 course
  4. Users who completed ≥1 lesson
  5. Users who passed ≥1 quiz
  6. Users who completed ≥1 course
  7. Users who received ≥1 certificate
- [ ] Visual funnel chart: horizontal bar chart (Chart.js) showing stage width proportional to user count
- [ ] Drop-off insight text per stage: "X% of registered users never viewed a course" etc.
- [ ] Date range filter (registration date for stage 1; event date for subsequent stages)
- [ ] Segment filter: cohort, course category (applies to stages 3–7 for that course scope)
- [ ] Export CSV: stage name, user count, conversion rate, drop-off rate
- [ ] Data refreshed daily via cron + stored in `lms_funnel_cache` table; manual "Rebuild now" button
- [ ] Unit test: funnel stage counts are monotonically non-increasing (each stage ≤ previous)

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Report/FunnelReport.php` — `build(array $filters): FunnelResult` with 7-stage array
- Stage queries:
  1. `SELECT COUNT(DISTINCT uid) FROM users_field_data WHERE created BETWEEN :s AND :e`
  2. `SELECT COUNT(DISTINCT uid) FROM lms_analytics_event WHERE event_type='course_view' AND created BETWEEN :s AND :e`
  3. `SELECT COUNT(DISTINCT uid) FROM lms_enrollment WHERE created BETWEEN :s AND :e`
  4. `SELECT COUNT(DISTINCT uid) FROM lms_lesson_progress WHERE status='completed' AND updated BETWEEN :s AND :e`
  5. `SELECT COUNT(DISTINCT uid) FROM lms_quiz_attempt WHERE status='passed' AND created BETWEEN :s AND :e`
  6. `SELECT COUNT(DISTINCT uid) FROM lms_progress WHERE status='completed' AND updated BETWEEN :s AND :e`
  7. `SELECT COUNT(DISTINCT uid) FROM lms_certificate WHERE issued_at BETWEEN :s AND :e`
- Cache table: `lms_funnel_cache` (`date_computed`, `filters_hash`, `stage_data` JSON) — single row per filter combination
- Cron: extend `hook_cron()` to rebuild default (no-filter) funnel daily
- Chart.js: reuse library from TASK-192 setup

## Dependencies
- TASK-014 (analytics dashboards — event tables + lms_analytics module confirmed)
- TASK-192 (quiz performance — Chart.js integration precedent)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: stage query monotonicity + cache hit/miss; Behat: funnel page renders with chart)
- [ ] Drop-off insight text renders for all 7 stages
- [ ] Cron rebuild confirmed running without error on staging
