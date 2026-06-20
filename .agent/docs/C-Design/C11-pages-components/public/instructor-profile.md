---
page: instructor-profile
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Instructor Profile

**Canonical URL:** `/instructor/{user}` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.instructor_profile`
**Solution:** Custom controller (`CoursePublicController::instructorProfile`) loads the `user` entity (param converter `entity:user`); aggregates instructor bio, social links, courses, and computed student/rating stats; renders via `lms-instructor-profile.html.twig`.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** `user` entity (instructor)
**Editable source:** `user` entity profile fields (bio, picture, social links); `lms_course` entities for instructor's course list
**Editor role:** instructor (own profile), administrator
**Editor management UI:** `/user/{uid}/edit` (self-edit); `/workspace/users/{uid}/admin-edit` (admin edit)
**Revision / moderation:** No
**Forms:** — (read-only public profile page)
**Theme template:** `web/modules/custom/lms_course/templates/lms-instructor-profile.html.twig` *(per [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Public — `_permission: 'access content'`; requires the `{user}` to have instructor role
**Cache:** Per-user context (for follow/wishlist state if applicable); invalidated by `user:{uid}`, `lms_course_list`
**Translation:** Monolingual EN
**Related entities:** [`user`](../../C04-entities/01-content-entities.md), [`lms_course`](../../C04-entities/01-content-entities.md), [`lms_rating`](../../C04-entities/07-communication-entities.md)
**Prototype reference:** _(no dedicated `instructor-profile.html` prototype; `instructors.html` shows card format as reference)_ [`../../C10-prototypes/html/public/instructors.html`](../../C10-prototypes/html/public/instructors.html)
**Notes:** The `instructorProfile()` method loads instructor bio, social links, courses, and computed student/rating stats. Confirmed custom user entity fields (source: `CoursePublicController::instructorProfile()` lines 1540–1619): `field_bio` (bio), `field_job_title` (specialty/title), `user_picture` (avatar), `field_linkedin`, `field_twitter`, `field_youtube`, `field_website` (social links). Reviews count is hardcoded to `0` with a `// Placeholder` comment — review aggregation is not yet implemented.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Instructor Profile Header — *Phần thông tin cá nhân giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block using user entity fields and computed stats from `instructorProfile()`
- **Plugin / Module:** `CoursePublicController::instructorProfile` → `lms_course`
- **Source content type / Entity:** `user` entity
- **Backed by fields / config keys:** `user.name`, `user_picture`, `field_bio`, `field_job_title`, `field_linkedin`, `field_twitter`, `field_youtube`, `field_website`; computed: instructor rating average, total students, course count, review count (reviews hardcoded `0` — stub)
- **Editable source:** `user` profile at `/user/{uid}/edit`
- **Editor role / permission:** instructor — self-edit; administrator — `administer users` via `/workspace/users/{uid}/admin-edit`
- **Editor management UI:** `/user/{uid}/edit`, `/workspace/users/{uid}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A (user-generated content)
- **Edit smoke:** Update instructor display name or bio → reload `/instructor/{uid}` → updated in profile header
- **View mode / Projection:** Full-width header: large avatar, name, specialty/title, bio excerpt, 4 stat pills (rating, students, courses, reviews), social link icons
- **Region / Placement:** Top of content, below navbar
- **Notes:** Stats computed from: `lms_rating` (average, count); `lms_user_course` entity count per instructor; `lms_course` count where `field_instructor={uid}`. Social link fields confirmed: `field_linkedin`, `field_twitter`, `field_youtube`, `field_website`. Note: prototype mockup shows a GitHub icon but the actual code maps `field_website` (generic URL) — there is no `field_github` field. Reviews count is hardcoded to `0` (placeholder) — review aggregation is not yet implemented.
- **How to implement (EN):** Load user entity; compute stats via entity queries (courses, students, ratings); render header with avatar, bio, stats, and social icons.
- **How to implement (VI):** Load user entity; tính stats qua entity queries (courses, students, ratings); render header với avatar, bio, stats, và icon mạng xã hội.
- **Solution Architect Request:**

### 2. Instructor Courses Section — *Phần danh sách khóa học của giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `lms_course` where `field_instructor={uid}` and `status=1`; render as course card grid using `lms-course-card.html.twig`
- **Plugin / Module:** `CoursePublicController::instructorProfile` → `lms_course`
- **Source content type / Entity:** `lms_course` entities (filtered by instructor)
- **Backed by fields / config keys:** `lms_course.name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level`, `field_category`, `field_instructor`, `field_duration`
- **Editable source:** `lms_course` entity at `/workspace/courses/{id}/edit`
- **Editor role / permission:** instructor (own courses) — `edit own lms_course`; administrator — `administer lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (course name)
- **Edit smoke:** Publish a new course assigned to this instructor → reload `/instructor/{uid}` → course appears in instructor's course grid
- **View mode / Projection:** `lms_course_card` — card grid (3–4 columns desktop, 2 tablet, 1 mobile)
- **Region / Placement:** Below profile header (main content area)
- **Notes:** Section heading typically "Courses by {instructor name}" or "All Courses". Pagination may apply for instructors with many courses.
- **How to implement (EN):** Query `lms_course` with `field_instructor.target_id = {uid}` and `status=1`; build card arrays via `buildCourseCards()`; render in grid.
- **How to implement (VI):** Query `lms_course` với `field_instructor.target_id = {uid}` và `status=1`; tạo mảng card qua `buildCourseCards()`; render dạng grid.
- **Solution Architect Request:**

### 3. About / Bio Section — *Phần giới thiệu chi tiết*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block rendering full instructor bio field with markup
- **Plugin / Module:** `CoursePublicController::instructorProfile` → `lms_course`
- **Source content type / Entity:** `user` entity (bio field — confirmed `field_bio`)
- **Backed by fields / config keys:** `field_bio` (text_long) on user entity; `field_job_title` (plain text)
- **Editable source:** `user` profile at `/user/{uid}/edit`
- **Editor role / permission:** instructor — self-edit; administrator — `administer users`
- **Editor management UI:** `/user/{uid}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Update bio field → reload `/instructor/{uid}` → updated bio visible
- **View mode / Projection:** Text block with formatted HTML (bio, background, expertise areas)
- **Region / Placement:** Below course grid or in a tabbed layout alongside courses
- **Notes:** Confirmed: `field_bio` is defined on the user entity and accessed in `instructorProfile()` (line 1554). `field_job_title` is also loaded (line 1555).
- **How to implement (EN):** Load `field_bio` with `text_long` formatter; render `field_job_title` as specialty subtitle.
- **How to implement (VI):** Load `field_bio` qua formatter `text_long`; render `field_job_title` làm subtitle chuyên môn.
- **Solution Architect Request:**

### 4. Student Reviews Summary — *Tóm tắt đánh giá học viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Aggregate `lms_rating` entities across all of this instructor's courses; display rating distribution and recent reviews
- **Plugin / Module:** `CoursePublicController::instructorProfile` → `lms_course` / `lms_rating`
- **Source content type / Entity:** `lms_rating` entities (approved, across instructor's courses)
- **Backed by fields / config keys:** `lms_rating.field_rating`, `lms_rating.field_review`, `lms_rating.uid`, `lms_rating.entity_id` (course)
- **Editable source:** `lms_rating` approval at `/workspace/ratings`
- **Editor role / permission:** administrator — `administer lms_course`
- **Editor management UI:** `/workspace/ratings`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Approve a rating on one of instructor's courses → reload profile → rating count/average updates
- **View mode / Projection:** Rating summary (aggregate average + star count) + 2–3 recent review cards
- **Region / Placement:** Below bio section
- **Notes:** The reviews count in `instructorProfile()` is hardcoded to `0` with a `// Placeholder` comment (line 1615 of `CoursePublicController.php`). No real `lms_rating` aggregation is performed for the instructor profile. This section is a stub — the rating average and student count may be computed, but the review card section is not implemented.
- **How to implement (EN):** Aggregate `lms_rating` where `entity_id IN (instructor's course IDs)` and `field_approved=1`; compute average + distribution; render summary card.
- **How to implement (VI):** Tổng hợp `lms_rating` với `entity_id IN (danh sách course của instructor)` và `field_approved=1`; tính average + phân phối; render summary card.
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Profile Header | Avatar, name, specialty, bio excerpt, stat pills (rating, students, courses, reviews=0 stub), social links | `user` entity + computed stats | `user_picture`, `name`, `field_bio`, `field_job_title`, `field_linkedin`, `field_twitter`, `field_youtube`, `field_website`; computed: rating avg, student count, course count; reviews hardcoded `0` | `/user/{uid}/edit`, `/workspace/users/{uid}/admin-edit` | instructor / self; administrator / `administer users` | No | N/A | `user:{uid}`, `lms_course_list` | Update name → reload → updated in header |
| Instructor Courses | Course card grid (thumbnail, title, price, rating, level) | `lms_course` entities (field_instructor={uid}) | `name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level` | `/workspace/courses/{id}/edit` | instructor / `edit own lms_course` | No | Content translation | `lms_course_list`, per-entity tags | Publish new course for instructor → reload → card appears |
| Bio Section | Full bio text, background, expertise | `user` entity bio field | `field_bio` (text_long), `field_job_title` | `/user/{uid}/edit` | instructor / self-edit | No | N/A | `user:{uid}` | Update bio → reload → updated |
| Reviews Summary | Aggregate rating avg, distribution, recent review cards | `lms_rating` entities (across instructor's courses) — STUB (reviews count = 0 hardcoded) | `field_rating`, `field_review`, `uid` | `/workspace/ratings` | administrator / `administer lms_course` | No | N/A | `lms_rating_list` | Not applicable (stub) |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Social link icons (LinkedIn, Twitter, YouTube, Website) | UI chrome icons rendered from confirmed fields: `field_linkedin`, `field_twitter`, `field_youtube`, `field_website` | Confirmed from `CoursePublicController.php` line 1558 | No `field_github` field exists; prototype mockup showing GitHub icon is incorrect |
| Stat pill labels ("Instructor Rating", "Students", "Courses") | UI chrome | Template default | Interface-translatable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Profile Header | DONE | — | Confirmed: `field_bio`, `field_job_title`, `user_picture`, `field_linkedin`, `field_twitter`, `field_youtube`, `field_website`; reviews count hardcoded `0` |
| Instructor Courses | DONE | — | `lms_course` query by `field_instructor={uid}` confirmed |
| Bio Section | DONE | — | Confirmed: `field_bio` (text_long) and `field_job_title` loaded at lines 1554–1555 |
| Reviews Summary | NOT IMPLEMENTED (stub) | — | `reviews` is hardcoded to `0` with `// Placeholder` comment at line 1615; no real rating aggregation |
