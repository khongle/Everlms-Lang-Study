---
page: Dashboard
mode: as-built
owner_module: lms_progress
route_group: Learn/App
adequacy: NOT_RUN
---

# Dashboard — `/dashboard`

Authenticated learner landing page. Shows welcome greeting, a "continue learning" card for the most recent in-progress course, summary stat tiles, a grid of in-progress course cards, and a recent-activity feed. All data is computed by `UserPortalController::dashboard()` from `lms_user_course` / `lms_user_lesson` records for the current user.

---

## Header Block

| Property | Value |
|---|---|
| **Canonical URL** | `/dashboard` |
| **Route name** | `lms_progress.dashboard` |
| **Solution** | Drupal 11 custom page |
| **Owner module** | `lms_progress` |
| **Primary source** | `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` → `dashboard()` |
| **Editable source** | `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` |
| **Editor role** | Admin (template/theme); no learner-editable content |
| **Editor management UI** | n/a |
| **Theme template** | `lms_dashboard` hook → `lms-dashboard.html.twig` (lms_progress module) |
| **Access** | `_user_is_logged_in: TRUE` |
| **Cache** | Per-user (cache tags: `lms_user_course_list`, `lms_user_lesson_list`; no explicit max-age set) |
| **Related entities** | `lms_user_course`, `lms_user_lesson`, `lms_user_certificate`, `lms_course`, `lms_lesson` |

---

## Components (Visual Order)

| # | Component | Editable source | Notes |
|---|---|---|---|
| 1 | **Authenticated navbar** | `lms_theme/templates/` | Logo, nav links (My Learning, My Courses), notification bell (`lms_notification_bell_block`), user avatar/name |
| 2 | **Hero greeting strip** | `lms-dashboard.html.twig` | "Welcome back, {name}" heading; streak badge (not implemented — template placeholder); "You have N courses in progress" subtext; "View all courses" link to `/my-courses` |
| 3 | **Continue learning card** | `lms-dashboard.html.twig` | Shows the most recent in-progress `lms_user_course`; renders course thumbnail, chapter/lesson label, progress bar, % complete, "Continue Learning" button to `/learn/{course}/{lesson}`. Controller resolves next incomplete lesson via `lms_user_lesson` scan. |
| 4 | **Stat tiles row** | `lms-dashboard.html.twig` | Four tiles implemented: Courses Enrolled, In Progress, Completed, Certificates — passed as `stats` variable with keys `enrolled`, `in_progress`, `completed`, `certificates` (confirmed in `UserPortalController::dashboard()`). Hours Studied and Quiz Average are not stored and not implemented (no corresponding fields or computed values). |
| 5 | **In-progress course cards grid** | `lms-dashboard.html.twig` | Grid of `lms_course_card`-style cards for enrolled courses with `in_progress` status; each shows thumbnail, title, lesson position, progress bar %, "Continue" button. Passed as `course_summary` variable (array). |
| 6 | **Recent activity feed** | `lms-dashboard.html.twig` | Passed as `recent_courses` variable. Contains the full `enrolled_courses` array — every enrolled course with keys: `course` (entity), `title`, `progress` (float 0–100), `status` (`in_progress`/`completed`/`not_started`), `enrolled_at`, `url` (continue URL), `thumbnail_url`, `category`. Not filtered or limited — all enrollments are passed (confirmed in `UserPortalController::dashboard()`). `course_summary` is a separate aggregate-counts array (`all`, `in_progress`, `completed`, `not_started`). |
| 7 | **Footer** | `lms_theme/templates/` | Shared site footer |

---

## Visible Content Inventory

| Item | Source entity / field | Controller variable | Notes |
|---|---|---|---|
| Greeting user name | `user.field_first_name` / `getDisplayName()` | resolved in `dashboard()` | |
| Courses enrolled count | `COUNT(lms_user_course WHERE uid=current)` | `stats['enrolled']` | |
| Courses in-progress count | `COUNT(lms_user_course WHERE field_status=in_progress)` | `stats['in_progress']` | |
| Courses completed count | `COUNT(lms_user_course WHERE field_status=completed)` | `stats['completed']` | |
| Certificates count | `COUNT(lms_user_certificate WHERE uid=current)` | `stats['certificates']` | |
| Continue learning — course title | `lms_course.name` | `course_summary[0]['title']` | First in-progress course |
| Continue learning — progress % | `lms_user_course.field_progress` | `course_summary[0]['progress']` | decimal 0–100 |
| Continue learning — next lesson URL | `/learn/{course_id}/{lesson_id}` | `course_summary[0]['continue_url']` | Controller scans `lms_user_lesson` for first non-completed lesson |
| In-progress card — thumbnail | `lms_course.field_thumbnail` | per card in `course_summary` | |
| In-progress card — progress bar | `lms_user_course.field_progress` | per card | |
| In-progress card — lesson label | `lms_lesson.name` (current) | per card | |
| Recent courses | all enrolled `lms_user_course` records for current user | `recent_courses` | Full array of all enrollments; no limit or recency filter applied (confirmed in `UserPortalController::dashboard()`). Each entry: `title`, `progress`, `status`, `enrolled_at`, `url` (continue URL), `thumbnail_url`, `category`. |

**Not implemented (prototype-only):** streak badge, hours-studied stat, quiz-average stat — no corresponding stored fields or computed values in the controller.

---

## Implementation Status

| Item | Status |
|---|---|
| Route + controller method | as-built |
| Template `lms-dashboard.html.twig` | as-built |
| `stats` computation (enrolled/in_progress/completed/certificates) | as-built |
| Continue learning card + next-lesson URL resolution | as-built |
| Course card grid (`course_summary`) | as-built |
| Streak badge | target-state (no stored field) |
| Hours-studied stat | target-state (no stored field) |
| Quiz-average stat | target-state (no stored field) |
| Code duplication: `dashboard()` shares near-identical "find next lesson" logic with `myCourses()` | known issue |
