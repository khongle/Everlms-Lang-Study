---
page: analytics-dashboard
mode: as-built
owner_module: lms_analytics
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Analytics Dashboard (Reports)

> Covers three Drupal `/admin` report pages served by `lms_analytics`: Course Report, User Report, and Quiz Report (list + detail). All share the same module, controller pattern, and template structure.

**Canonical URLs:**
- `/admin/lms/reports/courses` — Course Analytics & Report
- `/admin/lms/reports/users` — User Analytics & Report
- `/admin/lms/reports/quizzes` — Quiz Analytics & Report (list)
- `/admin/lms/reports/quizzes/{quiz_id}` — Quiz Item Analysis (detail)

*(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*

**Route names:**
- `lms_analytics.course_report`
- `lms_analytics.user_report`
- `lms_analytics.quiz_report_list`
- `lms_analytics.quiz_report_detail`

**Solution:** Custom controllers return Twig theme arrays (no Layout Builder); raw SQL queries via service classes; CSV export via `StreamedResponse`.
**Owner module:** [`lms_analytics`](../../C05-modules/lms_analytics.md)
**Primary source:** Raw DB queries across `lms_user_course`, `lms_order`, `lms_quiz_result`, `lms_user_certificate`, `lms_question_response`, `users_field_data`
**Editable source:** computed/read-only — all data is aggregated live from transactional tables
**Editor role:** Administrator, Manager (`administer lms_course`); User report: `administer users`; Quiz report: `administer lms_quiz`
**Editor management UI:** computed/read-only (analytics read-only pages)
**Revision / moderation:** N/A
**Forms:** —
**Theme templates:**
- `web/modules/custom/lms_analytics/templates/lms-analytics-course-report.html.twig`
- `web/modules/custom/lms_analytics/templates/lms-analytics-user-report.html.twig`
- `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-list.html.twig`
- `web/modules/custom/lms_analytics/templates/lms-analytics-quiz-report-detail.html.twig`

**Access:** Role-gated:
- Course + Quiz reports: `administer lms_course` / `administer lms_quiz`
- User report: `administer users`

**Cache:** No-cache (admin route, live DB query on every request)
**Translation:** Monolingual EN
**Related entities:** [`lms_course`](../../C04-entities/01-content-entities.md), [`lms_quiz`](../../C04-entities/01-content-entities.md), `user`
**Prototype reference:** No C10 prototype.
**Notes:** Module info.yml describes "GA4 integration" but no GA4 code exists — internal reporting only. CSV exports available for all three report types.

---

## Components (visual order, top-to-bottom)

> Global chrome defined in [`../_common.md`](../_common.md).

### 1. Report Page Title & Sub-navigation — *Tiêu đề trang & điều hướng phụ*

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal route `_title`; sub-nav tabs rendered via Drupal local task links or inline template links
- **Plugin / Module:** `lms_analytics` (routing.yml + controller)
- **Source content type / Entity:** Route definition
- **Backed by fields / config keys:** Route `_title` strings; template-level navigation links between report types
- **Editable source:** computed/read-only
- **Editor role / permission:** Developer
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** H1 + tab/link strip
- **Region / Placement:** `content` region, top
- **Notes:** Three reports are separate routes; navigation between them is template-rendered links.
- **How to implement (EN):** Route titles in `lms_analytics.routing.yml`; inter-report nav in templates.
- **How to implement (VI):** Tiêu đề route trong `lms_analytics.routing.yml`; điều hướng giữa các báo cáo trong template.
- **Solution Architect Request:** —

---

### 2. Filter Bar (search/status inputs) — *Thanh lọc*

- **Scope:** Page-specific
- **Implementation mechanism:** GET query params read by controller; rendered as HTML inputs in Twig template (no Drupal Form API — plain HTML form with GET method)
- **Plugin / Module:** `lms_analytics` controllers: `CourseReportController::build`, `UserReportController::build`, `QuizReportController::buildList`
- **Source content type / Entity:** URL query params (`search`, `status`, `inactive_days`)
- **Backed by fields / config keys:** Controller reads `$request->query->get('search')`, `status`, `inactive_days`
- **Editable source:** computed/read-only (filter state from URL)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Enter search term → observe filtered table rows
- **View mode / Projection:** Inline form in template
- **Region / Placement:** `content` region, above data table
- **Notes:**
  - Course filter: `search` (LIKE on course name), `status` (published/draft).
  - User filter: `search` (LIKE on name/email), `inactive_days` (last_login threshold).
  - Quiz filter: `search` on quiz name (implementation detail in `QuizAnalyticsManager::getQuizOverview`).
- **How to implement (EN):** Template renders a `<form method="GET">` that POSTs to the same URL; controller reads query params from `$request`.
- **How to implement (VI):** Template render form GET; controller đọc query params từ `$request`.
- **Solution Architect Request:** —

---

### 3. Course Analytics Table — *Bảng phân tích khoá học* (Course report only)

- **Scope:** Page-specific
- **Implementation mechanism:** `CourseReportManager::getCourseStats()` raw SQL; rendered via `lms_analytics_course_report` Twig theme
- **Plugin / Module:** `lms_analytics` → `Service/CourseReportManager`, `Controller/CourseReportController`
- **Source content type / Entity:** `lms_course_field_data`, `lms_user_course`, `lms_order`/`lms_order_item`, `taxonomy_term_field_data`
- **Backed by fields / config keys:**
  - Per course: `id`, `title`, `category` (taxonomy), `status`, `price_type`, `enrollments` (COUNT), `active` (enrolled minus completed), `completed` (SUM), `completion_rate` (%), `revenue` (SUM completed orders)
- **Editable source:** computed/read-only
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Enroll a test user in a course → view report → verify enrollment count increments
- **View mode / Projection:** HTML table rendered by `lms-analytics-course-report.html.twig`
- **Region / Placement:** `content` region, main
- **Notes:** Access-controlled at query level: non-admin instructors see only their own courses (filtered `c.uid = current_user`). Pagination: limit/offset via GET params.
- **How to implement (EN):** `CourseReportManager::getCourseStats()` in `web/modules/custom/lms_analytics/src/Service/CourseReportManager.php`. Template: `templates/lms-analytics-course-report.html.twig`.
- **How to implement (VI):** Service class tại `src/Service/CourseReportManager.php`; template `templates/lms-analytics-course-report.html.twig`.
- **Solution Architect Request:** —

---

### 4. User Analytics Table — *Bảng phân tích người dùng* (User report only)

- **Scope:** Page-specific
- **Implementation mechanism:** `UserReportManager::getOverviewList()` raw SQL; rendered via `lms_analytics_user_report` theme
- **Plugin / Module:** `lms_analytics` → `Service/UserReportManager`, `Controller/UserReportController`
- **Source content type / Entity:** `users_field_data`, `lms_user_course`, `lms_quiz_result`, `lms_user_certificate`
- **Backed by fields / config keys:**
  - Per user: `uid`, `name`, `email`, `created`, `last_login`, `enrolled` (COUNT), `completed` (SUM), `quizzes` (COUNT), `avg_score` (AVG), `certs` (COUNT)
  - Expandable: per-user `tracking.courses` and `tracking.quizzes` detail sub-arrays
- **Editable source:** computed/read-only
- **Editor role / permission:** `administer users`
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Create test user → enroll in course → verify in user report
- **View mode / Projection:** HTML table rendered by `lms-analytics-user-report.html.twig`
- **Region / Placement:** `content` region, main
- **Notes:** `inactive_days` filter: `users_field_data.access < (now - inactive_days * 86400)`. Excludes uid=1 (superadmin).
- **How to implement (EN):** `UserReportManager::getOverviewList()` in `src/Service/UserReportManager.php`.
- **How to implement (VI):** Service class tại `src/Service/UserReportManager.php`.
- **Solution Architect Request:** —

---

### 5. Quiz Analytics List + Item Analysis — *Danh sách & phân tích câu hỏi* (Quiz reports)

- **Scope:** Page-specific
- **Implementation mechanism:** `QuizAnalyticsManager` service; two templates for list and detail views
- **Plugin / Module:** `lms_analytics` → `Service/QuizAnalyticsManager`, `Controller/QuizReportController`
- **Source content type / Entity:** `lms_quiz_field_data`, `lms_quiz_result`, `lms_question_response`, `lms_question_field_data`
- **Backed by fields / config keys:**
  - List: per-quiz `id`, `title`, `pass_threshold`, `attempts`, `unique_attempts`, `avg_score`, `pass_rate`, `fail_rate`
  - Detail: `getScoreDistribution()` (5 bins for Chart.js), `getHardestQuestions()` (correct_responses / total_responses per question, top 5 hardest)
- **Editable source:** computed/read-only
- **Editor role / permission:** `administer lms_quiz`
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Submit quiz attempts → observe stats in quiz report
- **View mode / Projection:** `lms-analytics-quiz-report-list.html.twig` / `lms-analytics-quiz-report-detail.html.twig`
- **Region / Placement:** `content` region
- **Notes:** Detail page renders Chart.js score distribution and hardest-questions table. Assumes `lms_question_response` table with `field_quiz`, `field_question`, `field_is_correct` columns.
- **How to implement (EN):** `QuizAnalyticsManager` in `src/Service/QuizAnalyticsManager.php`. Detail at `/admin/lms/reports/quizzes/{quiz_id}`.
- **How to implement (VI):** Service tại `src/Service/QuizAnalyticsManager.php`. Trang chi tiết tại `/admin/lms/reports/quizzes/{quiz_id}`.
- **Solution Architect Request:** —

---

### 6. CSV Export Actions — *Xuất CSV*

- **Scope:** Page-specific
- **Implementation mechanism:** GET link to export route; controller returns `StreamedResponse` with CSV content (UTF-8 BOM)
- **Plugin / Module:** `lms_analytics` → `CourseReportController::export`, `UserReportController::export`, `QuizReportController::exportItemAnalysis`
- **Source content type / Entity:** Same queries as respective list pages with limit=10000
- **Backed by fields / config keys:** Same fields as table columns; formatted dates
- **Editable source:** computed/read-only
- **Editor role / permission:** Same as parent report page
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Click Export → verify CSV downloads with correct headers and data rows
- **View mode / Projection:** `StreamedResponse` (direct download)
- **Region / Placement:** Export link/button above or beside table
- **Notes:** UTF-8 BOM prepended for Excel compatibility.
- **How to implement (EN):** Export routes: `/admin/lms/reports/courses/export`, `/users/export`, `/quizzes/{id}/export`.
- **How to implement (VI):** Route xuất tại `/admin/lms/reports/courses/export`, v.v.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Title & Sub-nav | Report title, report-type navigation tabs | Route `_title` | Static | computed/read-only | Developer | N/A | Interface | N/A | N/A |
| Filter Bar | Search input, status/inactive-days selectors | URL query params | `search`, `status`, `inactive_days` | computed/read-only | N/A | N/A | N/A | no-cache | Enter search → observe filtered results |
| Course Analytics Table | Per-course: title, category, status, enrollments, completions, completion rate, revenue | `lms_course`, `lms_user_course`, `lms_order` | DB aggregates | computed/read-only | `administer lms_course` | N/A | N/A | no-cache | Enroll user → verify count |
| User Analytics Table | Per-user: name, email, enrolled, completed, quizzes, avg score, certs | `users_field_data`, `lms_quiz_result`, `lms_user_certificate` | DB aggregates | computed/read-only | `administer users` | N/A | N/A | no-cache | Create user → enroll → verify |
| Quiz Analytics List | Per-quiz: title, pass threshold, attempts, avg score, pass/fail rate | `lms_quiz_field_data`, `lms_quiz_result` | DB aggregates | computed/read-only | `administer lms_quiz` | N/A | N/A | no-cache | Submit quiz → verify stats |
| Quiz Item Analysis | Score distribution (5 bins), hardest questions (top 5) | `lms_question_response`, `lms_quiz_result` | `field_is_correct`, binned scores | computed/read-only | `administer lms_quiz` | N/A | N/A | no-cache | Submit quiz → verify distribution |
| CSV Export | Downloadable CSV of report data | Same as table | Same fields, formatted | computed/read-only | Same as report | N/A | N/A | no-cache | Click export → verify CSV |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Score distribution bins (0-20, 21-40, …) | Fixed in `QuizAnalyticsManager::getScoreDistribution()` | D00 code convention | When scoring granularity changes |
| CSV column headers | Hardcoded in export controllers | D00 code convention | On data model changes |
| "GA4 integration" (module description) | Module info.yml — no GA4 code implemented | «stub description» | When/if GA4 is implemented |
