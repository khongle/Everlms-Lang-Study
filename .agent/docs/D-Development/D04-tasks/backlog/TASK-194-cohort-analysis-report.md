# Task: Cohort Analysis Report

**ID:** TASK-194
**Unit:** — (extends lms_analytics)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a cohort analysis report that groups users by their signup month and tracks their course completion rate and platform retention over subsequent months. Output is a heatmap table (signup cohort × months since signup) showing the percentage of each cohort still active or having completed a course. Platform admins use this to evaluate whether platform changes improve long-term retention.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/cohort-analysis` (admin only)
- [ ] Cohort dimension: signup month (e.g., "2025-01", "2025-02" … up to current month)
- [ ] Retention metric options (selectable via radio): (a) "Active" = logged in at least once in the period, (b) "Completion" = completed ≥1 course in the period
- [ ] Time columns: months 0, 1, 2, 3, 6, 9, 12 since signup month
- [ ] Heatmap table: cell value = percentage of cohort; cell color: green gradient (0% = white, 100% = dark green)
- [ ] Row header shows cohort month + cohort size (N users)
- [ ] Hover tooltip on cell: "X of Y users (Z%)" 
- [ ] Date range filter: limit which signup cohorts are shown (e.g., last 12 months)
- [ ] Export CSV: cohort month, month offset, user count, active/completed count, percentage
- [ ] Pre-computed daily via cron; stored in `lms_cohort_cache` table
- [ ] Unit test: percentage calculation correct; cell color mapping correct for 0%, 50%, 100%

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Report/CohortReport.php` — `build(string $metric, string $startMonth, string $endMonth): CohortMatrix`
- Core query (active metric): for cohort month C and offset M, count `uid` where `signup_month = C AND last_login BETWEEN date_add(C, INTERVAL M MONTH) AND date_add(C, INTERVAL M+1 MONTH - 1 DAY)`
- Core query (completion metric): same but using `lms_progress.updated` + `status='completed'`
- Pre-compute: `lms_cohort_cache` (`metric`, `cohort_month`, `month_offset`, `cohort_size`, `active_count`, `computed_at`); daily cron refresh
- Heatmap rendering: HTML table with inline CSS background-color; JS optional enhancement (Chart.js matrix plugin or custom)
- JS: `web/modules/custom/lms_analytics/js/cohort-heatmap.js` — tooltip on cell hover
- SCSS: `_cohort-heatmap.scss` — color scale variables `$heatmap-0` through `$heatmap-100`

## Dependencies
- TASK-014 (analytics dashboards — lms_analytics module + login event tracking confirmed)
- TASK-193 (funnel analytics — precedent for multi-stage analytics queries + cron cache pattern)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: percentage calculation; Behat: heatmap table renders with correct row count)
- [ ] Heatmap color scale is accessible (sufficient contrast; not color-only differentiation — percentage text in cell)
- [ ] Cron cache rebuild tested with 24-month fixture
