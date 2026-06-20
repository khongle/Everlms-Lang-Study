---
page: My Courses
mode: as-built
owner_module: lms_progress
route_group: Learn/App
adequacy: NOT_RUN
---

# My Courses — `/my-courses`

Enrolled-course library for the authenticated learner. Lists all enrolled courses in a tabbed grid (In Progress / Completed / Wishlist). Each card shows thumbnail, instructor, title, lesson position, progress bar, and a primary CTA. Computed by `UserPortalController::myCourses()`.

---

## Header Block

| Property | Value |
|---|---|
| **Canonical URL** | `/my-courses` |
| **Route name** | `lms_progress.my_courses` |
| **Solution** | Drupal 11 custom page |
| **Owner module** | `lms_progress` |
| **Primary source** | `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` → `myCourses()` |
| **Editable source** | `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` |
| **Editor role** | Admin (template/theme) |
| **Editor management UI** | n/a |
| **Theme template** | `lms_my_courses` hook → `lms-my-courses.html.twig` (lms_progress module) |
| **Access** | `_user_is_logged_in: TRUE` |
| **Cache** | Per-user (cache tags implied by entity loads; no explicit max-age) |
| **Related entities** | `lms_user_course`, `lms_course`, `lms_lesson`, `lms_chapter`, `lms_user_lesson`, `lms_wishlist` (separate module) |

---

## Components (Visual Order)

| # | Component | Editable source | Notes |
|---|---|---|---|
| 1 | **Authenticated navbar** | `lms_theme/templates/` | Same as all authenticated pages; notification bell, user avatar |
| 2 | **Page header** | `lms-my-courses.html.twig` | "My Courses" H1; subtitle "All your enrolled, completed, and wishlist courses in one place." |
| 3 | **Tab strip** | `lms-my-courses.html.twig` | Three tabs: "In Progress (N)", "Completed (N)", "Wishlist (N)". Tab counts derived from `summary` variable. Wishlist data sourced from `lms_wishlist` module (separate `lms_wishlist` entity — cross-module data). |
| 4 | **Course card grid — In Progress** | `lms-my-courses.html.twig` | `lms_course_card`-style cards for `field_status=in_progress` courses. Each card: thumbnail (gradient fallback), instructor name, course title, "Lesson X of N" label, progress bar + %, "Continue Learning" button → `/learn/{course}/{lesson}`. |
| 5 | **Course card grid — Completed** | `lms-my-courses.html.twig` | Same card layout; primary CTA becomes "Get Certificate" → `/my-certificates` (or direct download if `lms_user_certificate` exists). |
| 6 | **Course card grid — Wishlist** | `lms-my-courses.html.twig` | Cards for wishlisted courses (data from `lms_wishlist` entities). CTA: "Enroll" → `/courses/{id}/enroll-free` or `/checkout/{id}`. |
| 7 | **Empty state** | `lms-my-courses.html.twig` | Shown per tab when no courses; links to `/courses` catalog. |
| 8 | **Footer** | `lms_theme/templates/` | Shared site footer |

---

## Visible Content Inventory

| Item | Source entity / field | Controller variable | Notes |
|---|---|---|---|
| In-progress courses | `lms_user_course WHERE field_status=in_progress` | `courses` (filtered) | `myCourses()` loads all `lms_user_course` for user |
| Completed courses | `lms_user_course WHERE field_status=completed` | `courses` (filtered) | Same load, filtered by status |
| Course thumbnail | `lms_course.field_thumbnail` | per card | Falls back to gradient in template |
| Instructor name | `lms_course.field_instructor` → `user.name` | per card | |
| Progress % | `lms_user_course.field_progress` | per card | decimal 0–100 |
| Lesson position label | derived from curriculum scan | per card | "Lesson X of N" — same logic as `dashboard()` |
| Continue URL | `/learn/{course_id}/{lesson_id}` | per card `continue_url` | Next incomplete `lms_user_lesson` |
| Wishlist courses | `lms_wishlist WHERE uid=current` | `courses` (wishlist tab) | Cross-module: `lms_wishlist` entity |
| Summary counts (tab badges) | computed from loaded arrays | `summary` | e.g. `summary['in_progress']`, `summary['completed']` |

---

## Implementation Status

| Item | Status |
|---|---|
| Route + controller method | as-built |
| Template `lms-my-courses.html.twig` | as-built |
| In-progress + completed tabs (course cards, progress, CTA) | as-built |
| Wishlist tab (cross-module lms_wishlist data) | as-built (data available; tab rendering depends on template) |
| Empty state per tab | as-built (template-level) |
| Code duplication: `myCourses()` duplicates "find next lesson" logic from `dashboard()` | known issue — [see related issues](../../../../E-Quality/E02-issues/) |

---

## Related Pages

- [dashboard.md](./dashboard.md) — shares controller logic
- [learn-lesson-viewer.md](./learn-lesson-viewer.md) — destination of "Continue Learning" CTAs
- [my-certificates.md](./my-certificates.md) — destination of "Get Certificate" CTAs
