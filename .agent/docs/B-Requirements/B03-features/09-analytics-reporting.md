---
id: F-009
name: Analytics & Reporting
priority: Must
status: APPROVED
parent-requirements: [BR-010]
owning_modules: [lms_analytics]
primary_source: C05
story_anchors: []
---

# F-009 — Analytics & Reporting

## User story

As an **administrator or manager**, I want **drill-down reports on course performance, user progress, and quiz outcomes with CSV export**, so that **I can make data-informed decisions about curriculum quality and learner engagement**.

## Description

EverLMS provides internal SQL-based reporting through three dedicated report managers (`CourseReportManager`, `UserReportManager`, `QuizAnalyticsManager`) backed by raw MariaDB queries across live entity tables. Three controller-routed report pages (course, user, quiz) are fully implemented with paginated tabular output and CSV export via `StreamedResponse` with UTF-8 BOM. All report logic lives in `lms_analytics` (`web/modules/custom/lms_analytics/`).

The module's `.info.yml` description mentions "GA4 integration" — **this is inaccurate**. No Google Analytics 4 code, tag injection, or event tracking exists anywhere in the module. The module is internal reporting only. This misleading description is a known documentation discrepancy.

There is no automated email delivery of scheduled reports, no instructor-scoped dashboard, and no revenue trend chart — these remain target-state requirements.

## In scope (as-built)

- **Course report** (`/admin/lms/reports/courses`) — per-course: title, category, status, enrollment count, active learners (enrolled minus completed), completed count, completion rate (%), and revenue from completed orders. Access-controlled: managers without `administer lms_course` see only their own courses.
- **User report** (`/admin/lms/reports/users`) — per-user: enrolled courses, completed courses, quiz attempts, average quiz score, certificates earned; expandable course-level and quiz-level tracking rows. Filter by search text and inactive days.
- **Quiz report list** (`/admin/lms/reports/quizzes`) — per-quiz: total attempts, unique attempts, average score, pass rate, fail rate.
- **Quiz report detail** (`/admin/lms/reports/quizzes/{quiz_id}`) — score distribution (5 bins: 0–20, 21–40, 41–60, 61–80, 81–100) for Chart.js; item-analysis table of hardest questions (correct-response rate, ascending).
- **CSV export** for course report (`/admin/lms/reports/courses/export`), user report (`/admin/lms/reports/users/export`), and quiz item analysis (`/admin/lms/reports/quizzes/{quiz_id}/export`).
- **Summary KPIs** via `AnalyticsService`: total enrollments, total revenue from completed orders, overall completion rate.
- **Permission gating**: course report requires `administer lms_course`; user report requires `administer users`; quiz report requires `administer lms_quiz`.

## Out of scope

- Google Analytics 4 / GA4 event tracking — **not implemented** (description claim is inaccurate).
- Scheduled / emailed reports — not implemented.
- Revenue trend charts or time-series analytics — not implemented (only flat totals).
- Instructor-specific dashboard scoped to their own courses — not a separate route; managers see own-course data when lacking admin permission via access control in `CourseReportManager`.
- Excel/PDF export — only CSV supported.
- Real-time / live dashboard with WebSocket updates — not implemented.
- Question-bank-level analytics beyond hardest-questions list — not implemented.

## Acceptance summary

An administrator navigating to `/admin/lms/reports/courses` sees a table of all courses with enrollment, completion, and revenue columns, and can download a UTF-8 CSV. The quiz detail page at `/admin/lms/reports/quizzes/{id}` shows a score distribution and the five hardest questions ranked by correct-response rate. Full Given/When/Then criteria live in `../B05-acceptance-criteria/F-009.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Report UI (tables, labels) | Platform administrator | administrator / manager | Template files in `lms_analytics/templates/` | Not end-user editable; code change required |
| CSV column headers | Platform administrator | developer | Controller `export()` methods | Hardcoded in controllers |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 (Course Management) | Course data is the primary subject of course reports |
| Feature | F-003 (Learning Progress) | `lms_user_course`, `lms_user_lesson` tables are queried directly |
| Feature | F-002 (Assessment System) | `lms_quiz_result`, `lms_question_response` tables queried for quiz analytics |
| Feature | F-004 (Order & Membership) | `lms_order`, `lms_order_item` queried for revenue figures |
| Feature | F-006 (Certification) | `lms_user_certificate` queried for certificate counts in user report |

## Open questions

- [ ] GA4 integration is listed as a requirement in the original draft and in the module description — confirm whether this is deferred or permanently out of scope, then update module description accordingly.
- [ ] `getHardestQuestions()` assumes a `lms_question_response` table with `field_quiz`, `field_question`, `field_is_correct` columns — verify this table is created by quiz submission flows (no schema declaration found in `lms_analytics`).
- [ ] Instructor-scoped reporting route: should instructors have a `/workspace/reports/` page showing only their courses? Currently only achievable via `/admin/lms/reports/courses` with access-control filtering.
- [ ] Scheduled email reports: target-state or out-of-scope for this release?
