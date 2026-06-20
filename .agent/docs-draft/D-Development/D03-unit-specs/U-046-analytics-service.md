# D03 — Unit Spec: AnalyticsService (3 queries)

**Unit ID:** U-046
**Group:** G-INTEGRATION
**Title:** AnalyticsService (3 queries)
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_analytics` module with `AnalyticsService` providing 3 aggregate queries (total enrollments, total revenue, completion rate), plus dedicated report managers and controllers for course, quiz, and user reports with Twig templates.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_analytics/lms_analytics.info.yml` | Module declaration |
| `web/modules/custom/lms_analytics/lms_analytics.module` | Hook implementations |
| `web/modules/custom/lms_analytics/lms_analytics.routing.yml` | Analytics routes |
| `web/modules/custom/lms_analytics/lms_analytics.services.yml` | Service registration |
| `web/modules/custom/lms_analytics/lms_analytics.libraries.yml` | Chart.js library |
| `web/modules/custom/lms_analytics/src/Service/AnalyticsService.php` | Core analytics service |
| `web/modules/custom/lms_analytics/src/Service/CourseReportManager.php` | Course report queries |
| `web/modules/custom/lms_analytics/src/Service/QuizAnalyticsManager.php` | Quiz analytics queries |
| `web/modules/custom/lms_analytics/src/Service/UserReportManager.php` | User report queries |
| `web/modules/custom/lms_analytics/src/Controller/CourseReportController.php` | Course report controller |
| `web/modules/custom/lms_analytics/src/Controller/QuizReportController.php` | Quiz report controller |
| `web/modules/custom/lms_analytics/src/Controller/UserReportController.php` | User report controller |
| `web/modules/custom/lms_analytics/templates/lms-analytics-course-report.html.twig` | Course report template |
| `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-list.html.twig` | Quiz report list |
| `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-detail.html.twig` | Quiz report detail |
| `web/modules/custom/lms_analytics/templates/lms-analytics-user-report.html.twig` | User report template |

---

## Entities / Services / Controllers / Templates

**`AnalyticsService` methods:**
- `getTotalEnrollments(): int` — count of `lms_user_course` records
- `getTotalRevenue(): float` — sum of completed order totals
- `getCompletionRate(): float` — percentage of enrollments with status `completed`

**Managers:** `CourseReportManager`, `QuizAnalyticsManager`, `UserReportManager` provide domain-specific queries used by their respective controllers.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Module installs; all services register |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | All 3 AnalyticsService queries return expected types |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `AnalyticsService::getTotalEnrollments()` returns integer
- [x] `AnalyticsService::getTotalRevenue()` returns float
- [x] `AnalyticsService::getCompletionRate()` returns float 0-100
- [x] Course, quiz, and user report pages render with data
- [x] Admin dashboard summary uses AnalyticsService values

---

## Known Debt / Issues

- Admin/manager analytics dashboard UI (U-050) is SPEC — report pages exist but the full dashboard with charts is pending.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-021 | UserCourse entities queried by AnalyticsService |
| U-025 | Order entities queried for revenue |
| U-050 | Analytics dashboard UI (SPEC) |

---

*End of U-046-analytics-service.md*
