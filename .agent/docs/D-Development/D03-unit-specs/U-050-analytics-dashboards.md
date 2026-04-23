# D03 — Unit Spec: Analytics Dashboards (Admin/Manager)

**Unit ID:** U-050
**Group:** G-INTEGRATION
**Title:** Analytics dashboards (admin/manager)
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Build analytics dashboard pages using data from AnalyticsService (U-046). Two views: admin (platform-wide) and manager/instructor (per-course). Metrics: enrollment counts, completion rates, revenue totals, quiz pass rates, and active users.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/AnalyticsDashboardController.php` | Create | lms_analytics |
| `src/Service/AnalyticsService.php` | Modify | lms_analytics |
| `templates/analytics-admin.html.twig` | Create | lms_analytics |
| `templates/analytics-manager.html.twig` | Create | lms_analytics |
| `lms_analytics.routing.yml` | Modify | lms_analytics |

---

## Interfaces / Services

- `AnalyticsService::getPlatformStats(DateRange $range): array` — platform-wide
- `AnalyticsService::getCourseStats(int $courseId, DateRange $range): array` — per-course
- `AnalyticsService::getRevenueStats(DateRange $range): array` — admin only
- Existing 3 queries in AnalyticsService extended to support date range filtering
- Data displayed as tables + simple bar/line charts (Chart.js via lms_theme)
- Cached: 15 min cache for non-real-time data

---

## Acceptance Criteria

- [ ] Admin dashboard: total users, active users (30d), total enrollments, total revenue, top 10 courses by enrollment
- [ ] Manager/instructor dashboard: per-course enrollment, completion rate, avg quiz score
- [ ] Date range filter: last 7d / 30d / 90d / custom
- [ ] Charts render without external CDN (Chart.js bundled)
- [ ] Admin sees all courses; manager sees only own courses
- [ ] Dashboard data cached 15 min (not real-time)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | Access: manager cannot see other instructors' data |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-046 | AnalyticsService — queries extended here |
| U-025 | Order data for revenue stats |
| U-021 | UserCourse data for enrollment stats |

---

**Feature Flag:** None

*End of U-050-analytics-dashboards.md*
