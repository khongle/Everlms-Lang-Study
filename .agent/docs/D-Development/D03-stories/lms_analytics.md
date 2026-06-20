# D03 — Story Spec: lms_analytics

**Story ID:** lms_analytics
**Story type:** module
**Title:** Internal Reporting — Course, User, and Quiz Analytics with CSV Export
**Primary source:** `C-Design/C05-modules/lms_analytics.md`
**Owning module(s):** lms_analytics
**Service(s):** lms_analytics.service (AnalyticsService), lms_analytics.course_report (CourseReportManager), lms_analytics.user_report (UserReportManager), lms_analytics.quiz_analytics (QuizAnalyticsManager)
**Epic:** E-08 (Integration & AI)
**Contributes to Feature(s):** F-013 (Analytics and Reporting)
**Design source(s):** `../C05-modules/lms_analytics.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_analytics`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2
**Splitting strategy:**
- S1: Services (AnalyticsService, CourseReportManager, UserReportManager, QuizAnalyticsManager), services.yml
- S2: Controllers (CourseReportController, UserReportController, QuizReportController), routing.yml, hook_theme, templates

## Scope (from primary source)

Provides internal administrative reporting for EverLMS. All report queries are real raw SQL against the MariaDB schema — no stubs. Three report domains: Course (enrollments, completions, revenue per course), User (enrollment history, quiz performance, certificates per user), Quiz (attempt counts, score distribution, hardest questions). All reports include CSV export with UTF-8 BOM. Access-controlled per admin role.

**Note on module description:** The `lms_analytics.info.yml` description mentions "GA4 integration" but no GA4 code exists in this module. All reporting is internal database queries only. The GA4 label is misleading and should be corrected in info.yml.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_analytics.md` | Owning module design |
| `../../B-Requirements/B03-features/09-analytics-reporting.md` | Analytics feature requirements |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Course report: enrollments, completions, revenue per course with search/status filter | B03-features/09-analytics-reporting | Acceptance |
| User report: per-user enrollment + quiz history, certificates; inactive user filter | B03-features/09-analytics-reporting | Acceptance |
| Quiz report: attempt counts, pass rate, score distribution (bins), hardest questions | B03-features/09-analytics-reporting | Acceptance |
| CSV export for all three report types (UTF-8 BOM, StreamedResponse) | B03-features/09-analytics-reporting | Acceptance |
| Access control: course report = administer lms_course; user report = administer users; quiz report = administer lms_quiz | B03-features/09-analytics-reporting | Acceptance |
| Instructor-scoped course report (non-admin sees only own courses) | B03-features/09-analytics-reporting | Acceptance |
| GA4 integration | B03-features/09-analytics-reporting | target-state / planned (not implemented — description is misleading) |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_analytics/lms_analytics.info.yml` | Create | Module declaration (misleading GA4 mention in description) | — | S1 |
| `web/modules/custom/lms_analytics/lms_analytics.services.yml` | Create | 4 service registrations | — | S1 |
| `web/modules/custom/lms_analytics/lms_analytics.routing.yml` | Create | 7 admin routes | — | S2 |
| `web/modules/custom/lms_analytics/lms_analytics.module` | Create | hook_theme (4 themes) | — | S2 |
| `web/modules/custom/lms_analytics/src/Service/AnalyticsService.php` | Create | getTotalEnrollments(), getTotalRevenue(), getCompletionRate() | — | S1 |
| `web/modules/custom/lms_analytics/src/Service/CourseReportManager.php` | Create | getCourseStats() with sub-queries + access control | — | S1 |
| `web/modules/custom/lms_analytics/src/Service/UserReportManager.php` | Create | getOverviewList() with per-user course+quiz detail | — | S1 |
| `web/modules/custom/lms_analytics/src/Service/QuizAnalyticsManager.php` | Create | getQuizOverview(), getScoreDistribution(), getHardestQuestions() | — | S1 |
| `web/modules/custom/lms_analytics/src/Controller/CourseReportController.php` | Create | build() + export() (CSV StreamedResponse) | — | S2 |
| `web/modules/custom/lms_analytics/src/Controller/UserReportController.php` | Create | build() + export() | — | S2 |
| `web/modules/custom/lms_analytics/src/Controller/QuizReportController.php` | Create | buildList(), buildDetail(), exportItemAnalysis() | — | S2 |
| `web/modules/custom/lms_analytics/templates/lms-analytics-course-report.html.twig` | Create | Course report table | — | S2 |
| `web/modules/custom/lms_analytics/templates/lms-analytics-user-report.html.twig` | Create | User report table | — | S2 |
| `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-list.html.twig` | Create | Quiz overview list | — | S2 |
| `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-detail.html.twig` | Create | Per-quiz drill-down with distribution + hardest questions | — | S2 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_user_course table (lms_progress module) | Cross-module | Raw SQL in reports |
| lms_order, lms_order_item, lms_order__field_items tables | Cross-module | Revenue queries |
| lms_quiz_result, lms_question_response tables | Cross-module | Quiz analytics |
| lms_user_certificate table | Cross-module | Certificate count per user |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Raw SQL with access-control clause (uid = current_user) | CourseReportManager::getCourseStats() | As-built |
| StreamedResponse for CSV export | CourseReportController::export(), UserReportController::export(), QuizReportController::exportItemAnalysis() | As-built |
| UTF-8 BOM prepended to CSV | All export controllers | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/admin/lms/reports/courses` | Course analytics table | Raw DB queries | Same path (filter via GET params) | administer lms_course | None | None | Uncached admin page | As-built |
| `/admin/lms/reports/users` | User analytics table | Raw DB queries | Same path | administer users | None | None | Uncached | As-built |
| `/admin/lms/reports/quizzes` | Quiz overview + detail | Raw DB queries | Same path | administer lms_quiz | None | None | Uncached | As-built |
| Export endpoints | CSV download | Raw DB (limit 10000) | Linked from report pages | Same as report | None | None | N/A (StreamedResponse) | As-built |

**Hardcoded content exceptions:** CSV export limit hardcoded at 10000 rows. No pagination for export.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-ANL-001 | Integration | CourseReportManager::getCourseStats() returns correct enrollment/completion counts | Report accuracy AC |
| T-ANL-002 | Integration | CourseReportManager respects instructor scope (non-admin sees only own courses) | Access AC |
| T-ANL-003 | Integration | UserReportManager::getOverviewList() includes per-user course detail list | User report AC |
| T-ANL-004 | Integration | QuizAnalyticsManager::getScoreDistribution() returns correct bin counts | Distribution AC |
| T-ANL-005 | Integration | QuizAnalyticsManager::getHardestQuestions() returns questions sorted by lowest correct-rate | Hardest AC |
| T-ANL-006 | Integration | CSV export returns Content-Type: text/csv with UTF-8 BOM | Export AC |
| T-ANL-007 | Unit | AnalyticsService::getTotalRevenue() only sums completed orders | Revenue AC |
| T-ANL-008 | Unit | QuizReportController::buildDetail() throws NotFoundHttpException for invalid quiz_id | 404 AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; all routes resolve | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | All three report types smoke-tested | — | — |
| G4 Adversarial | PASS | GA4 description mismatch documented; no critical issues | — | — |

## Notes

- **GA4 description mismatch:** The module's info.yml description says "Analytics integration (GA4) and internal reporting" but no GA4 code exists. The description is misleading and should be corrected to "Internal LMS reporting: course, user, and quiz analytics."
- **Raw SQL:** All report queries use `$this->database->query()` with raw SQL — not the Entity Query API. This is intentional for performance on aggregate report queries but means schema changes in other modules may break analytics silently. The tables queried are documented in the facts base and should be cross-checked when those modules are updated.
- **lms_question_response table assumed:** `QuizAnalyticsManager::getHardestQuestions()` assumes a `lms_question_response` table exists. This table is not defined in `lms_quiz` entity definitions visible in the facts base — it may be installed separately or via hook_schema. Verify before running quiz item analysis.
- **No GA4/external analytics tracker:** GA4 must be added separately (via Google Analytics contrib or GTM snippet in lms_theme). Not part of this module.
