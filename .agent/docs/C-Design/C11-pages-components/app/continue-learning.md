---
page: Continue Learning (redirect)
mode: as-built
owner_module: lms_progress
route_group: Learn/App
adequacy: NOT_RUN
---

# Continue Learning — (no dedicated route; resolved by `UserPortalController`)

"Continue Learning" is not a standalone page. It is a computed URL — a direct deep-link to the learner's next incomplete lesson — resolved by `UserPortalController::dashboard()` and `UserPortalController::myCourses()`. The link appears as a CTA button on the Dashboard and My Courses pages and routes directly to the Lesson Viewer.

---

## Header Block

| Property | Value |
|---|---|
| **Canonical URL** | `/learn/{lms_course}/{lms_lesson}` (resolved at runtime per user) |
| **Route name** | `lms_progress.lesson_view` (destination route) |
| **Solution** | Computed URL — no dedicated route or controller; resolved inside `UserPortalController::dashboard()` and `UserPortalController::myCourses()` |
| **Owner module** | `lms_progress` |
| **Primary source** | `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` → `dashboard()` / `myCourses()` |
| **Editable source** | `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` (button label/style); `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` |
| **Editor role** | Admin (template/theme) |
| **Editor management UI** | n/a |
| **Theme template** | n/a (rendered as anchor tag inside `lms_dashboard` / `lms_my_courses` templates) |
| **Access** | `_user_is_logged_in: TRUE` (inherited from source pages) |
| **Cache** | Per-user (same as Dashboard / My Courses) |
| **Related entities** | `lms_user_course`, `lms_user_lesson`, `lms_lesson`, `lms_course` |

---

## Resolution Logic

`UserPortalController::dashboard()` and `::myCourses()` both run the following resolution algorithm (duplicated — known code-quality issue):

```
1. Load all lms_user_course WHERE uid = current_user, sorted by changed DESC.
2. For each lms_user_course (starting with most recent):
   a. Load lms_course → lms_chapter[] (field_chapters) → lms_lesson[] (field_lessons).
   b. For each lesson in curriculum order:
      - Load lms_user_lesson WHERE uid = current, field_lesson = lesson_id.
      - If lms_user_lesson does not exist OR field_status != 'completed':
        → Set continue_url = /learn/{course_id}/{lesson_id}. STOP.
3. If all lessons completed → continue_url points to first lesson (or is omitted — «unverified»).
```

The resolved URL is stored in the `course_summary` array passed to the template, e.g. `course_summary[0]['continue_url']`.

---

## Components (Visual Order)

This is a button/link component, not a page. It appears in two locations:

| Location | Host page | Component | Template variable |
|---|---|---|---|
| Dashboard — hero strip | `/dashboard` | "Continue Learning →" button on the continue-learning card | `course_summary[0]['continue_url']` |
| Dashboard — in-progress course card grid | `/dashboard` | "Continue" button per card | `course_summary[n]['continue_url']` |
| My Courses — In Progress tab | `/my-courses` | "Continue Learning" button per card | `courses[n]['continue_url']` |

---

## Visible Content (on the Dashboard "continue card")

| Item | Source entity / field | Notes |
|---|---|---|
| Course thumbnail | `lms_course.field_thumbnail` | Shown as card image |
| "In Progress" status badge | `lms_user_course.field_status` | |
| Chapter + lesson label | traversed curriculum | e.g. "Chapter 3: CSS Flexbox — Lesson 3.2" |
| Course title | `lms_course.name` | |
| Progress bar | `lms_user_course.field_progress` (decimal) | Visual width |
| Progress % label | `lms_user_course.field_progress` | e.g. "65% complete" |
| "Continue Learning →" button target | Resolved URL `/learn/{course_id}/{lesson_id}` | |

---

## Implementation Status

| Item | Status |
|---|---|
| URL resolution algorithm (next incomplete lesson) | as-built |
| Continue learning card on Dashboard | as-built |
| Per-card "Continue" button in In-Progress grid | as-built |
| Per-card "Continue Learning" button in My Courses | as-built |
| Code duplication: resolution logic duplicated in `dashboard()` and `myCourses()` | known issue — refactor to shared private method recommended |

---

## Related Pages

- [dashboard.md](./dashboard.md) — primary host of the continue-learning card
- [my-courses.md](./my-courses.md) — secondary host (per-card CTAs)
- [learn-lesson-viewer.md](./learn-lesson-viewer.md) — destination page
