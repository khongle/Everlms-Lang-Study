---
feature: F-016
status: DRAFT
---

# B05 — Acceptance Criteria for F-016 — Essential Enhancements

> Per-feature Given/When/Then criteria for the essential platform enhancements described in [B03 F-016](../B03-features/16-essential-enhancements.md). Enhancement areas grounded in the current codebase: `lms_analytics` (internal reporting, CSV export), `lms_note` (student notes per lesson), `lms_wishlist` (already covered by F-013 AC-061–064), `lms_bundle` (already covered by F-013 AC-065–067), `lms_zoom` (hook_cron). Items with no existing implementation (abandoned cart recovery email, social login web, email drip campaigns, instructor revenue dashboard, audit log, SEO sitemap module) are marked **target-state/planned**.

---

## AC-091: Student notes — create and auto-save per lesson (as-built)

**Priority:** Must
**Given** a logged-in student is on a lesson view page and the `lms_note` entity type is available
**When** the student types in the notes panel and the note is saved (via the `api/lms/note/save` endpoint or the Drupal form handler)
**Then** an `lms_note` entity is created or updated with the student's UID, the lesson entity reference, the note body text, and the current timestamp
**And** the note is retrievable via `api/lms/note/get` for the same user/lesson combination on subsequent page loads

## AC-092: Student notes — delete (as-built)

**Priority:** Must
**Given** a logged-in student has a note for a specific lesson
**When** the student deletes the note (via `api/lms/note/delete` or equivalent UI action)
**Then** the `lms_note` entity is removed from the database
**And** subsequent GET requests for that user/lesson combination return no note

## AC-093: Analytics — course report with access control (as-built)

**Priority:** Must
**Given** a user with `administer lms_course` permission visits `/admin/lms/reports/courses`
**When** the page loads and `CourseReportManager::getCourseStats()` executes
**Then** the report shows all published and draft courses with per-course columns: enrollments, active learners (enrolled minus completed), completed, completion rate (%), and revenue from completed orders
**And** the revenue column is calculated as SUM of `lms_order_item.field_price` for orders with status `completed` joined to the course
**Given** a user WITHOUT `administer lms_course` and WITHOUT `manager edit lms_course` roles accesses the endpoint
**Then** the SQL query adds `c.uid = {current_user_id}` and only that instructor's own courses appear in the results

## AC-094: Analytics — course report CSV export (as-built)

**Priority:** Must
**Given** a user with `administer lms_course` permission visits `/admin/lms/reports/courses/export`
**When** the `CourseReportController::export()` handler executes
**Then** a `StreamedResponse` is returned with Content-Type `text/csv`
**And** the response body includes a UTF-8 BOM followed by a header row and up to 10,000 data rows matching the course stats query
**And** the downloaded file can be opened in Excel/LibreOffice without character encoding issues

## AC-095: Analytics — user report with inactive filter (as-built)

**Priority:** Must
**Given** an admin with `administer users` visits `/admin/lms/reports/users`
**When** the admin filters by `inactive_days` (e.g., 30)
**Then** `UserReportManager::getOverviewList()` applies `access < (now - 30 days)` to `users_field_data`
**And** the resulting list shows per-user: enrolled count, completed count, quiz attempts, average quiz score, certificate count, and an inline drill-down of per-course and per-quiz detail

## AC-096: Analytics — quiz report item analysis (as-built)

**Priority:** Must
**Given** an admin with `administer lms_quiz` visits `/admin/lms/reports/quizzes/{quiz_id}`
**When** `QuizReportController::buildDetail()` executes
**Then** the page displays a score distribution chart (bins: 0–20, 21–40, 41–60, 61–80, 81–100) calculated by `QuizAnalyticsManager::getScoreDistribution()`
**And** the page lists the top 5 hardest questions ranked by ascending correct-response ratio from `lms_question_response`
**And** a `NotFoundHttpException` is thrown if the quiz_id does not correspond to an existing `lms_quiz` entity

## AC-097: Analytics — quiz item analysis CSV export (as-built)

**Priority:** Should
**Given** an admin is on the quiz detail report page
**When** the admin requests `/admin/lms/reports/quizzes/{quiz_id}/export`
**Then** a `StreamedResponse` CSV is returned with question-level item analysis (question title, total responses, correct responses, correct-response rate)
**And** the CSV includes a UTF-8 BOM and is correctly encoded

## AC-098: Social login — mobile app (as-built)

**Priority:** Must
**Given** a user opens the EverLMS Flutter app and has a Google or Facebook account
**When** the user taps the Google Sign-In or Facebook login button on the sign-in screen
**Then** the native social auth SDK initiates the OAuth2 flow and, on success, sends the resulting token to the backend social auth endpoint (`/api/v1/auth/social-register` or legacy `lms-social-auth`)
**And** a Drupal user account is created or linked, and the app receives a bearer token for subsequent API calls
**Note:** Social login on the Drupal web frontend (social_auth module) is **target-state/planned** — no `social_auth_*` Drupal modules are currently enabled on the web side.

## AC-099: Abandoned cart recovery email (target-state/planned)

**Priority:** Should
**Given** a logged-in user adds a course to their cart (order with status `cart`) but does not complete checkout
**When** 1 hour has elapsed and a cron job runs
**Then** a reminder email is sent to the user with the cart contents and a call-to-action link
**And** subsequent reminders are sent at the 24-hour and 72-hour marks, with an optional coupon in the 72-hour email
**And** `field_reminder_sent` on the `lms_order` entity is updated to prevent duplicate sends
**Note:** This criterion is **not met** by the current code. `lms_order` does not have `field_reminder_sent`; the cron-based reminder logic does not exist. Implementation requires an update hook and a cron handler in `lms_order`.

## AC-100: Email drip campaigns (target-state/planned)

**Priority:** Should
**Given** a new user registers on the platform
**When** the welcome sequence is enabled by an admin
**Then** the user receives a 3-email welcome sequence at configurable intervals (e.g., day 0, day 3, day 7)
**And** inactive users (no login for 7/14/30 days) receive a re-engagement nudge email
**And** an admin WYSIWYG editor allows customization of each template
**Note:** This criterion is **not met**. `lms_notification` provides in-app channels only; email and push notification channels are stubs. A dedicated drip-campaign module (`lms_email_campaign`) or extension to `lms_notification` is needed.

## AC-101: Instructor revenue dashboard (target-state/planned)

**Priority:** Should
**Given** an instructor is logged in to the management portal
**When** the instructor navigates to their revenue dashboard page
**Then** the page displays: total earnings to date, earnings for the current month, number of enrolled students, new enrollments this period, and completion rate for their courses
**And** the instructor can filter by 7d / 30d / 90d / custom date range and export a CSV report
**Note:** This criterion is **not met**. No dedicated instructor revenue dashboard route or controller exists. The existing `lms_analytics` course report requires `administer lms_course` — it is not scoped to individual instructors as a self-service view.

## AC-102: SEO metadata — course pages (target-state/planned)

**Priority:** Must
**Given** 200 published courses exist on the platform
**When** a search engine crawler or social sharing bot requests a course detail page
**Then** the page `<head>` contains: `<title>` with the course name, `<meta name="description">` with the course description truncated to ≤160 characters, Open Graph `og:title`/`og:description`/`og:image`/`og:url`, and a `<link rel="canonical">` element
**And** `/sitemap.xml` exists and lists URLs for all published courses, published blog posts, published category pages, and static pages
**Note:** Drupal core's Metatag and Simple XML Sitemap contributed modules are **not listed** in the current composer.json or enabled modules. This criterion is target-state/planned.

---

## Local traceability (F-016)

| AC ID | Scenario | Implementing Module | Status |
|-------|----------|---------------------|--------|
| AC-091 | Student notes — create/save | lms_note / lms_mobile_api | as-built |
| AC-092 | Student notes — delete | lms_note / lms_mobile_api | as-built |
| AC-093 | Course report with access control | lms_analytics | as-built |
| AC-094 | Course report CSV export | lms_analytics | as-built |
| AC-095 | User report with inactive filter | lms_analytics | as-built |
| AC-096 | Quiz report item analysis | lms_analytics | as-built |
| AC-097 | Quiz item analysis CSV export | lms_analytics | as-built |
| AC-098 | Social login — mobile app | lms_mobile_api / Flutter app | as-built |
| AC-099 | Abandoned cart recovery email | lms_order (planned) | planned |
| AC-100 | Email drip campaigns | lms_notification / new module (planned) | planned |
| AC-101 | Instructor revenue dashboard | lms_analytics (planned) | planned |
| AC-102 | SEO metadata + sitemap | contrib modules (planned) | planned |

> Cross-feature traceability lives in `E05-traceability.md`.
