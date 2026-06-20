---
module: lms_analytics
service: analytics
route-groups: [Admin]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Analytics

**Directory:** `web/modules/custom/lms_analytics/`
**Responsibility:** Provides internal reporting for courses, users, and quiz performance via raw SQL queries against LMS database tables, with CSV export; does not integrate with GA4 despite the module description claiming otherwise.

> **Note on module description:** `lms_analytics.info.yml` description reads "Analytics integration (GA4) and internal reporting." — **no GA4 code exists** in this module. All reporting is internal SQL only.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|---|---|---|---|
| Admin | `/admin/lms/reports` | `CourseReportController` | `/courses` (view), `/courses/export` (CSV) |
| Admin | `/admin/lms/reports` | `UserReportController` | `/users` (view), `/users/export` (CSV) |
| Admin | `/admin/lms/reports` | `QuizReportController` | `/quizzes` (list), `/quizzes/{quiz_id}` (detail), `/quizzes/{quiz_id}/export` (CSV) |

---

## Public Interface

| Export | Type | Description |
|---|---|---|
| `lms_analytics.service` | Service (`AnalyticsService`) | Global aggregates: total enrollments, total revenue, completion rate |
| `lms_analytics.course_report` | Service (`CourseReportManager`) | Per-course stats with enrollment, completion, revenue breakdowns |
| `lms_analytics.user_report` | Service (`UserReportManager`) | Per-user activity: enrolled/completed courses, quiz attempts, average score, certificates |
| `lms_analytics.quiz_analytics` | Service (`QuizAnalyticsManager`) | Quiz-level analytics: pass rate, score distribution, hardest questions |

---

## Internal Structure

```
web/modules/custom/lms_analytics/
+-- lms_analytics.info.yml      # deps: lms_base; lifecycle: stable
+-- lms_analytics.routing.yml   # 7 routes
+-- lms_analytics.services.yml  # 4 services
+-- lms_analytics.module        # hook_theme() only
+-- src/
|   +-- Service/
|   |   +-- AnalyticsService.php         # Global aggregates (3 methods)
|   |   +-- CourseReportManager.php      # getCourseStats() with access control + sub-queries
|   |   +-- UserReportManager.php        # getOverviewList() with per-user tracking
|   |   +-- QuizAnalyticsManager.php     # getQuizOverview(), getScoreDistribution(), getHardestQuestions()
|   +-- Controller/
|       +-- CourseReportController.php   # build(), export()
|       +-- UserReportController.php     # build(), export()
|       +-- QuizReportController.php     # buildList(), buildDetail(), exportItemAnalysis()
+-- templates/
    +-- lms-analytics-course-report.html.twig
    +-- lms-analytics-user-report.html.twig
    +-- lms-analytics-quiz-report-list.html.twig
    +-- lms-analytics-quiz-report-detail.html.twig
```

---

## Service Details

### `AnalyticsService` (as-built)

| Method | SQL | Returns |
|---|---|---|
| `getTotalEnrollments()` | `COUNT(*)` from `lms_user_course` | int |
| `getTotalRevenue()` | `SUM(field_total)` from `lms_order` WHERE `field_status='completed'` | float |
| `getCompletionRate()` | `COUNT(*) WHERE field_status='completed'` / `COUNT(*)` from `lms_user_course` | float (percentage) |

### `CourseReportManager::getCourseStats(filters, limit, offset)` (as-built)

Selects from `lms_course_field_data c` with LEFT JOIN `taxonomy_term_field_data` (course category name).

**Access control:** if current user lacks `administer lms_course` or `manager edit lms_course`, filters by `c.uid = current_user` (instructor scope).

**Filters:** `status` (published/draft), `search` (LIKE on course name).

**Sub-queries:**
1. `lms_user_course`: COUNT enrollments + SUM(field_status='completed') GROUP BY `field_course`
2. `lms_order__field_items` JOIN `lms_order_item` JOIN `lms_order`: SUM(`field_price`) WHERE status='completed' GROUP BY `field_course`

**Returns per course:** id, title, category, status, price_type, enrollments, active (enrolled minus completed), completed, completion_rate, revenue.

### `UserReportManager::getOverviewList(filters, limit, offset)` (as-built)

Selects from `users_field_data` (uid > 1 excludes anonymous/admin).

**Filters:** `search` (LIKE on name/mail), `inactive_days` (access < NOW() - threshold).

**Sub-queries:**
1. `lms_user_course`: enrolled count + completed sum per uid
2. `lms_quiz_result`: attempt count + AVG(score) per uid
3. `lms_user_certificate`: certificate count per uid
4. `lms_user_course` JOIN `lms_course_field_data`: per-user course tracking list (title, status, progress)
5. `lms_quiz_result` JOIN `lms_quiz_field_data`: per-user quiz tracking list (title, score)

**Returns per user:** uid, name, email, created, last_login, enrolled, completed, quizzes, avg_score, certs, tracking.{courses, quizzes}.

### `QuizAnalyticsManager` (as-built)

| Method | Description |
|---|---|
| `getQuizOverview(filters, limit, offset)` | Selects from `lms_quiz_field_data`; JOINs `lms_quiz_result` for: total_attempts, unique_attempts, avg_score, passed_attempts (score >= field_threshold) |
| `getScoreDistribution(quiz_id)` | Fetches all scores; bins into 0–20, 21–40, 41–60, 61–80, 81–100 ranges; returns distribution array for Chart.js |
| `getHardestQuestions(quiz_id, limit=5)` | JOINs `lms_question_response` with `lms_question_field_data`; calculates `correct_responses / total_responses` per question; sorts ascending (hardest first); returns top N |

**Table dependency note:** `getHardestQuestions()` queries `lms_question_response` with columns `field_quiz`, `field_question`, `field_is_correct`. The `lms_question_response` entity is defined in `lms_quiz` (class `QuestionResponse`) — but the actual boolean field is named **`field_correct`**, not `field_is_correct`. The raw SQL expression `SUM(CASE WHEN qr.field_is_correct = 1 ...)` references a non-existent DB column and will throw a SQL error at runtime. **BUG: column name mismatch.**

---

## Controllers

### `CourseReportController` (as-built)

- `build(Request)`: Reads `search`/`status` query params; calls `CourseReportManager::getCourseStats()`; returns `lms_analytics_course_report` render array.
- `export(Request)`: Same query with `limit=10000`; returns `StreamedResponse` as CSV with **UTF-8 BOM** header.

### `UserReportController` (as-built)

- `build(Request)`: Reads `search`/`inactive_days`; returns `lms_analytics_user_report` render array.
- `export(Request)`: CSV export with date formatting.

### `QuizReportController` (as-built)

- `buildList(Request)`: Returns `lms_analytics_quiz_report_list` render array.
- `buildDetail(int $quiz_id)`: Fetches distribution + hardest questions; returns `lms_analytics_quiz_report_detail` render array; throws `NotFoundHttpException` if quiz not found.
- `exportItemAnalysis(int $quiz_id)`: CSV export of item analysis (question-level pass rates).

---

## Database Tables Queried (raw SQL)

| Table | Source module | Purpose in queries |
|---|---|---|
| `lms_user_course` | [lms_progress](../C05-modules/lms_progress.md) | Enrollment and completion tracking |
| `lms_order` | [lms_order](../C05-modules/lms_order.md) | Order status / total |
| `lms_order_item` | [lms_order](../C05-modules/lms_order.md) | Order item price / course link |
| `lms_order__field_items` | [lms_order](../C05-modules/lms_order.md) | Join table for order items |
| `lms_course_field_data` | [lms_course](../C05-modules/lms_course.md) | Course entity field data |
| `lms_quiz_result` | [lms_quiz](../C05-modules/lms_quiz.md) | Quiz attempt results |
| `lms_quiz_field_data` | [lms_quiz](../C05-modules/lms_quiz.md) | Quiz entity field data |
| `lms_user_certificate` | [lms_certificate](../C05-modules/lms_certificate.md) | Issued certificates |
| `lms_question_response` | [lms_quiz](../C05-modules/lms_quiz.md) | Per-question answer records (`QuestionResponse` entity defined in `lms_quiz`) |
| `lms_question_field_data` | [lms_question](../C05-modules/lms_question.md) | Question entity field data |
| `users_field_data` | Drupal core | User accounts |
| `taxonomy_term_field_data` | Drupal core | Course category name resolution |

---

## Hook Implementations

| Hook | What it does |
|---|---|
| `hook_theme()` | Registers 4 templates: `lms_analytics_course_report`, `lms_analytics_user_report`, `lms_analytics_quiz_report_list`, `lms_analytics_quiz_report_detail` |

---

## Dependencies

| Dependency | Type | Why |
|---|---|---|
| `lms_base` | Internal | Shared base services |
| `@database` | Drupal core service | All report managers use raw SQL via `Connection` |
| `@entity_type.manager` | Drupal core service | Entity loading in service constructors |
| `@current_user` | Drupal core service | Access-scoped filtering in `CourseReportManager` and `QuizAnalyticsManager` |

---

## Templates

| Template | Purpose |
|---|---|
| `lms-analytics-course-report.html.twig` | Course report table with enrollment/completion/revenue columns |
| `lms-analytics-user-report.html.twig` | User activity table with tracking details |
| `lms-analytics-quiz-report-list.html.twig` | Quiz overview list with pass rates |
| `lms-analytics-quiz-report-detail.html.twig` | Per-quiz drill-down: score distribution (Chart.js) + hardest questions |

---

## Known Issues / Debt

| Ref | Severity | Description |
|---|---|---|
| MISLEAD | High | Module description claims "GA4 integration" — **no GA4 code exists**. Description is inaccurate. |
| BUG-COLUMN-MISMATCH | Critical | `getHardestQuestions()` queries `qr.field_is_correct` but the actual boolean field on `lms_question_response` (entity defined in `lms_quiz`) is `field_correct`. This SQL column-name mismatch causes a DB exception at runtime, breaking the quiz detail report entirely. Fix: rename `field_is_correct` → `field_correct` in `QuizAnalyticsManager::getHardestQuestions()`. |
| DEBT | Medium | Quiz report routes use raw `{quiz_id}` integer path parameters instead of upcasted entity parameters — no automatic access check; `NotFoundHttpException` is thrown manually if quiz not found. |
| DEBT | Medium | No caching layer on any report manager. Heavy SQL with sub-queries will degrade on large datasets. Reports should use `CacheBackendInterface` or Drupal render cache tags. |
| DEBT | Low | `CourseReportController::export()` hardcodes `limit=10000` — not paginated. Large exports will exhaust memory on high-enrollment sites. |
