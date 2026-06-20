---
feature: F-009
status: DRAFT
---

# B05 — Acceptance Criteria for F-009 — Analytics & Reporting

> Per-feature Given/When/Then criteria grounded in code. Module: `lms_analytics` (services: `AnalyticsService`, `CourseReportManager`, `UserReportManager`, `QuizAnalyticsManager`).
>
> Note: `lms_analytics.info.yml` description mentions "GA4 integration" but no GA4 code exists. All reporting is internal database queries only.

---

## AC-089: Course report — data accuracy and instructor-scoped access control

**Priority:** Must
**Given** a user with `administer lms_course` navigates to `/admin/lms/reports/courses`
**When** `CourseReportController::build()` renders the course report
**Then** each row shows: course title, category (from `taxonomy_term_field_data` JOIN), status, enrollments (COUNT from `lms_user_course`), active learners (enrolled minus completed), completed count, completion rate %, and revenue (SUM of completed `lms_order_item.field_price`)
**And** data matches direct table counts with no off-by-one; no caching is applied by the service itself

**Given** a `manager` user (with `administer lms_course` but without `manager edit lms_course`)
**When** `CourseReportManager::getCourseStats()` runs
**Then** results are filtered to courses owned by that user (`c.uid = current_user_id`) — instructor-scoped view

---

## AC-090: Course report — search and status filter applied via query params

**Priority:** Must
**Given** the course report page is loaded
**When** the user appends `?search=Python&status=1` to the URL
**Then** `CourseReportController::build()` passes these params to `CourseReportManager::getCourseStats()` which applies `LIKE '%Python%'` on `lms_course_field_data.name` and `status=1` (published only) as WHERE clauses
**And** only matching published courses appear in results

---

## AC-091: Course report — CSV export with UTF-8 BOM

**Priority:** Should
**Given** the course report page is loaded (with or without filters)
**When** the user navigates to `/admin/lms/reports/courses/export`
**Then** `CourseReportController::export()` queries with `limit=10000` and returns a `StreamedResponse` with `Content-Type: text/csv; charset=UTF-8`
**And** the file starts with a UTF-8 BOM (for Excel compatibility) and columns match the report screen

---

## AC-092: User report — overview with aggregated sub-queries

**Priority:** Must
**Given** a user with `administer users` navigates to `/admin/lms/reports/users`
**When** `UserReportController::build()` renders the user report
**Then** each row shows: uid, name, email, created date, last login, enrolled count, completed count, quiz attempts, average quiz score, certificate count — all derived from sub-queries joining `lms_user_course`, `lms_quiz_result`, and `lms_user_certificate`
**And** the expanded per-user detail includes a list of course titles with progress and quiz titles with scores (from `lms_quiz_result JOIN lms_quiz_field_data`)

---

## AC-093: User report — inactive users filter

**Priority:** Should
**Given** the user report page
**When** the user appends `?inactive_days=30` to the URL
**Then** `UserReportManager::getOverviewList()` applies `access < (now - 30 days)` filter on `users_field_data.access`
**And** only users whose last login is older than 30 days appear in results

---

## AC-094: User report — CSV export with formatted dates

**Priority:** Should
**Given** the user report page is loaded
**When** the user navigates to `/admin/lms/reports/users/export`
**Then** `UserReportController::export()` streams a UTF-8 BOM CSV with all user rows up to limit; date fields (created, last_login) are formatted as readable strings (not Unix timestamps)

---

## AC-095: Quiz report — overview list with attempt and pass-rate data

**Priority:** Must
**Given** a user with `administer lms_quiz` navigates to `/admin/lms/reports/quizzes`
**When** `QuizReportController::buildList()` renders the quiz overview
**Then** each row shows: quiz title, pass threshold (`field_threshold`), total attempts, unique attempts (distinct UIDs), average score, pass rate % (attempts where score >= threshold / total), fail rate %
**And** all data is from `lms_quiz_result JOIN lms_quiz_field_data`

---

## AC-096: Quiz report — per-quiz drill-down (score distribution + hardest questions)

**Priority:** Must
**Given** an admin clicks a quiz in the overview list (navigates to `/admin/lms/reports/quizzes/{quiz_id}`)
**When** `QuizReportController::buildDetail(quiz_id)` executes
**Then** the page shows:
- Score distribution binned into five 20-point ranges (0–20, 21–40, 41–60, 61–80, 81–100) suitable for Chart.js
- Table of the 5 hardest questions sorted by ascending correct-response rate (from `lms_question_response` JOIN `lms_question_field_data`)
**And** if the quiz_id does not exist, `NotFoundHttpException` (HTTP 404) is returned

---

## AC-097: Quiz item-analysis CSV export

**Priority:** Should
**Given** an admin is on the quiz detail report for quiz ID N
**When** they navigate to `/admin/lms/reports/quizzes/{quiz_id}/export`
**Then** `QuizReportController::exportItemAnalysis(quiz_id)` streams a CSV containing per-question analysis: question text, bundle type, total responses, correct responses, correct-response rate

---

## AC-098: Analytics service — aggregate scalar metrics

**Priority:** Must
**Given** any internal consumer calls `AnalyticsService` methods directly
**When** `getTotalEnrollments()`, `getTotalRevenue()`, and `getCompletionRate()` are called
**Then** each returns a live scalar value from raw SQL:
- `getTotalEnrollments()`: `COUNT(*)` from `lms_user_course`
- `getTotalRevenue()`: `SUM(field_total)` from `lms_order` WHERE `field_status='completed'`
- `getCompletionRate()`: percentage of `lms_user_course` WHERE `field_status='completed'`
**And** no caching is applied by the service itself

---

## AC-099: GA4 integration absent — internal reporting only (as-built)

**Priority:** Must (document current state)
**Given** `lms_analytics.info.yml` description states "Analytics integration (GA4)"
**When** the module is examined
**Then** no GA4 code, no external tracking calls, and no `analytics.js` integration exist anywhere in `lms_analytics`
**And** all reporting is internal DB queries only; GA4 integration is **target-state / planned** and not yet implemented

---

## Local traceability (F-009 only)

| AC ID  | Test Case | Implementing Story / Module          | Status  |
|--------|-----------|--------------------------------------|---------|
| AC-089 | T-089     | — / lms_analytics (CourseReport)     | PENDING |
| AC-090 | T-090     | — / lms_analytics (CourseReport)     | PENDING |
| AC-091 | T-091     | — / lms_analytics (CourseReport)     | PENDING |
| AC-092 | T-092     | — / lms_analytics (UserReport)       | PENDING |
| AC-093 | T-093     | — / lms_analytics (UserReport)       | PENDING |
| AC-094 | T-094     | — / lms_analytics (UserReport)       | PENDING |
| AC-095 | T-095     | — / lms_analytics (QuizReport)       | PENDING |
| AC-096 | T-096     | — / lms_analytics (QuizReport)       | PENDING |
| AC-097 | T-097     | — / lms_analytics (QuizReport)       | PENDING |
| AC-098 | T-098     | — / lms_analytics (AnalyticsService) | PENDING |
| AC-099 | T-099     | — / lms_analytics (gap: no GA4)      | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
