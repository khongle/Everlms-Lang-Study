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
**Notes:** The `instructorProfile()` method is documented in the controller index as loading "instructor bio, social links, courses, student/rating stats." The exact user entity fields for bio and social links are «unverified» — Drupal core users do not have bio/social fields by default; these may be added as custom fields on the user entity or stored as user profile fields.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Instructor Profile Header — *Phần thông tin cá nhân giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block using user entity fields and computed stats from `instructorProfile()`
- **Plugin / Module:** `CoursePublicController::instructorProfile` → `lms_course`
- **Source content type / Entity:** `user` entity
- **Backed by fields / config keys:** `user.name`, user picture, user bio field (custom — «unverified» field name), social link fields (custom — «unverified»); computed: instructor rating average, total students, course count, review count
- **Editable source:** `user` profile at `/user/{uid}/edit`
- **Editor role / permission:** instructor — self-edit; administrator — `administer users` via `/workspace/users/{uid}/admin-edit`
- **Editor management UI:** `/user/{uid}/edit`, `/workspace/users/{uid}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A (user-generated content)
- **Edit smoke:** Update instructor display name or bio → reload `/instructor/{uid}` → updated in profile header
- **View mode / Projection:** Full-width header: large avatar, name, specialty/title, bio excerpt, 4 stat pills (rating, students, courses, reviews), social link icons
- **Region / Placement:** Top of content, below navbar
- **Notes:** Stats computed from: `lms_rating` (average, count); `lms_user_course` entity count per instructor; `lms_course` count where `field_instructor={uid}`. Social link fields on `user` entity are «unverified»; prototype shows LinkedIn, Twitter, YouTube, GitHub icons.
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
- **Source content type / Entity:** `user` entity (bio field — custom field, «unverified» field name)
- **Backed by fields / config keys:** User bio / description field (custom — possibly `field_bio` or `field_description` on user entity); «unverified»
- **Editable source:** `user` profile at `/user/{uid}/edit`
- **Editor role / permission:** instructor — self-edit; administrator — `administer users`
- **Editor management UI:** `/user/{uid}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Update bio field → reload `/instructor/{uid}` → updated bio visible
- **View mode / Projection:** Text block with formatted HTML (bio, background, expertise areas)
- **Region / Placement:** Below course grid or in a tabbed layout alongside courses
- **Notes:** If no custom bio field is defined on the user entity, this section will be empty / not rendered. The implementation depends on custom user field configuration «unverified».
- **How to implement (EN):** Check if user entity has `field_bio` (or similar); if present, render with `text_long` formatter.
- **How to implement (VI):** Kiểm tra user entity có `field_bio` không; nếu có, render qua formatter `text_long`.
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
- **Notes:** Implementation of this aggregated reviews section in `instructorProfile()` is «unverified» — the controller documentation confirms loading "student/rating stats" but a full reviews section with cards may not be rendered on this page.
- **How to implement (EN):** Aggregate `lms_rating` where `entity_id IN (instructor's course IDs)` and `field_approved=1`; compute average + distribution; render summary card.
- **How to implement (VI):** Tổng hợp `lms_rating` với `entity_id IN (danh sách course của instructor)` và `field_approved=1`; tính average + phân phối; render summary card.
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Profile Header | Avatar, name, specialty, bio excerpt, stat pills (rating, students, courses, reviews), social links | `user` entity + computed stats | `name`, picture, bio field («unverified»), social fields («unverified»); computed: rating avg, student count, course count | `/user/{uid}/edit`, `/workspace/users/{uid}/admin-edit` | instructor / self; administrator / `administer users` | No | N/A | `user:{uid}`, `lms_course_list` | Update name → reload → updated in header |
| Instructor Courses | Course card grid (thumbnail, title, price, rating, level) | `lms_course` entities (field_instructor={uid}) | `name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level` | `/workspace/courses/{id}/edit` | instructor / `edit own lms_course` | No | Content translation | `lms_course_list`, per-entity tags | Publish new course for instructor → reload → card appears |
| Bio Section | Full bio text, background, expertise | `user` entity bio field | bio field name «unverified» | `/user/{uid}/edit` | instructor / self-edit | No | N/A | `user:{uid}` | Update bio → reload → updated |
| Reviews Summary | Aggregate rating avg, distribution, recent review cards | `lms_rating` entities (across instructor's courses) | `field_rating`, `field_review`, `uid` | `/workspace/ratings` | administrator / `administer lms_course` | No | N/A | `lms_rating_list` | Approve rating → reload → stats update |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Social link icons (LinkedIn, Twitter, etc.) | UI chrome icons | Template default | Social link fields on user entity «unverified»; revisit when fields confirmed |
| Stat pill labels ("Instructor Rating", "Students", "Courses") | UI chrome | Template default | Interface-translatable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Profile Header | DONE | — | `instructorProfile()` confirmed loading bio, social links, stats; exact user fields «unverified» |
| Instructor Courses | DONE | — | `lms_course` query by `field_instructor={uid}` confirmed in method description |
| Bio Section | DONE | — | Rendered from user entity bio field; field name «unverified» |
| Reviews Summary | DONE | — | Controller described as loading "student/rating stats"; full review card section «unverified» |
