---
page: instructors
mode: target-state
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Instructors Directory

**Canonical URL:** No dedicated route — instructors are currently accessible via `/instructor/{user}` (individual profile) only. A `/instructors` listing page exists as a C10 prototype but has **no corresponding route or controller** in the codebase.
**Route name:** _(no route registered — prototype/planned only)_
**Solution (target-state):** Add a new route `lms_course.instructors` at `/instructors` with a new `CoursePublicController::instructors` method; load all users with the `instructor` role; display as a card grid with featured instructor spotlight; render via a new `lms-instructors.html.twig` template.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** `user` entity (role: instructor)
**Editable source:** `user` profile fields; `lms_course` (instructor's courses for counts)
**Editor role:** administrator
**Editor management UI:** `/workspace/users` (user management); `/user/{uid}/edit` (profile)
**Revision / moderation:** No
**Forms:** — (read-only directory)
**Theme template:** _(planned)_ `web/modules/custom/lms_course/templates/lms-instructors.html.twig`
**Access:** Public — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `user_list`, `lms_course_list`
**Translation:** Monolingual EN
**Related entities:** [`user`](../../C04-entities/01-content-entities.md), [`lms_course`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/public/instructors.html`](../../C10-prototypes/html/public/instructors.html)
**Notes:** The C10 prototype at `html/public/instructors.html` shows a featured instructor spotlight, an instructor grid (avatar, name, specialty, course count, student count, rating, social links), and a "Become an Instructor" CTA. No route exists in the current codebase — this page is prototype-only (target-state). The individual instructor profile at `/instructor/{user}` IS implemented (as-built); see [instructor-profile.md](instructor-profile.md).

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Page Hero — *Banner trang giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block with aggregate stats (instructor count, student count, course count)
- **Plugin / Module:** _(planned)_ `CoursePublicController::instructors` → `lms_course`
- **Source content type / Entity:** computed — `count(user:instructor)`, `count(lms_course)`, aggregated `lms_user_course` student count
- **Backed by fields / config keys:** DB aggregation queries
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** No
- **Translation source:** Interface translation
- **Edit smoke:** N/A (computed stats update automatically)
- **View mode / Projection:** Hero with headline, subtitle, 3 stat pills (instructors, students, courses)
- **Region / Placement:** Top of content area
- **Notes:** Prototype shows "320+ Expert Instructors", "45,000+ Students Taught", "1,200+ Courses Available" as stats.
- **How to implement (EN):** In new `instructors()` controller method, query counts; pass to template hero block.
- **How to implement (VI):** Trong method controller `instructors()` mới, query các count; truyền vào block hero trong template.
- **Solution Architect Request:**

### 2. Featured Instructor Spotlight — *Giảng viên nổi bật*

- **Scope:** Page-specific
- **Implementation mechanism:** Load a single featured instructor (user with `field_featured_instructor=TRUE` or highest rating); render as a wide highlight card
- **Plugin / Module:** _(planned)_ `CoursePublicController::instructors` → `lms_course`
- **Source content type / Entity:** `user` entity (instructor role, highest rating or featured flag)
- **Backed by fields / config keys:** `user.name`, user picture, user bio, computed stats (rating average, student count, course count, review count)
- **Editable source:** User profile at `/user/{uid}/edit`; featured flag (if added to user entity)
- **Editor role / permission:** administrator — `administer users`
- **Editor management UI:** `/workspace/users/{uid}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Set featured instructor flag → reload → spotlight card shows correct instructor
- **View mode / Projection:** Full-width highlight card (avatar, name, specialty, bio excerpt, stats, CTA to profile + courses)
- **Region / Placement:** Below hero
- **Notes:** Prototype shows a bordered card with accent color indicating "Featured" status. A `field_featured_instructor` boolean on the `user` entity would be needed (not yet implemented).
- **How to implement (EN):** Query users with instructor role where `field_featured=1` (or fallback to highest rated); load stats; render as wide card.
- **How to implement (VI):** Query user có role instructor với `field_featured=1` (hoặc fallback sang người có rating cao nhất); load stats; render thành wide card.
- **Solution Architect Request:**

### 3. Instructor Grid — *Lưới giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over instructor users; each card links to `/instructor/{uid}`
- **Plugin / Module:** _(planned)_ `CoursePublicController::instructors` → `lms_course`
- **Source content type / Entity:** `user` entities (role: instructor)
- **Backed by fields / config keys:** `user.name`, user picture, user bio (excerpt), computed: course count, student count, rating average; social links (if on user entity)
- **Editable source:** User profile edit at `/workspace/users/{uid}/admin-edit` or `/user/{uid}/edit`
- **Editor role / permission:** administrator — `administer users`; instructor — self-edit
- **Editor management UI:** `/workspace/users/{uid}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Update instructor avatar → reload `/instructors` → updated avatar in grid card
- **View mode / Projection:** Card grid (avatar, name, specialty, rating stars, course count, student count, social links, "View courses" CTA); 4 columns desktop, 2 tablet, 1 mobile
- **Region / Placement:** Main content area below featured spotlight
- **Notes:** "View courses" button links to `/courses?instructor={uid}` (requires instructor filter support in catalog — «unverified» if implemented). Pagination may be needed for large instructor sets.
- **How to implement (EN):** Query users with role `instructor`; load per-user stats; render as responsive card grid; each card links to `/instructor/{uid}`.
- **How to implement (VI):** Query user có role `instructor`; load stats từng user; render dạng card grid responsive; mỗi card link đến `/instructor/{uid}`.
- **Solution Architect Request:**

### 4. "Become an Instructor" CTA — *Phần kêu gọi trở thành giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Static Twig block with configurable copy
- **Plugin / Module:** _(planned)_ `lms_course` (template)
- **Source content type / Entity:** `lms_theme.settings` (copy) or template defaults
- **Backed by fields / config keys:** Template defaults ("Share your expertise. Earn on your terms.")
- **Editable source:** `lms_theme.settings` or template
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No
- **Translation source:** Config / Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Full-width CTA section (headline, body, CTA button linking to registration or a contact form)
- **Region / Placement:** Below instructor grid, before footer
- **Notes:** Prototype copy: "Join 320+ instructors earning a living by teaching what they love. Keep 70% of every sale." — marketing copy, not entity-backed; should be moved to theme settings for editability.
- **How to implement (EN):** Render full-width section with configurable copy from theme settings; CTA links to `/contact` or a dedicated instructor application form (planned).
- **How to implement (VI):** Render section full-width với nội dung có thể chỉnh qua theme settings; CTA link đến `/contact` hoặc form đăng ký giảng viên (planned).
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Headline, subtitle, stat pills (instructor count, student count, course count) | computed DB counts | `count(user:instructor)`, aggregated counts | N/A | N/A | No | Interface translation | `user_list`, `lms_course_list` | N/A (planned page) |
| Featured Instructor Spotlight | Avatar, name, specialty, bio excerpt, stats (rating, students, courses, reviews), CTA links | `user` entity + computed stats | `name`, picture, bio; computed aggregates | `/workspace/users/{uid}/admin-edit` | administrator / `administer users` | No | N/A | `user:{uid}` | Set featured flag → reload → spotlight updates |
| Instructor Grid | Instructor cards (avatar, name, specialty, rating, course count, student count, CTA) | `user` entities (instructor role) + computed stats | `name`, picture, bio; computed per-user stats | `/workspace/users/{uid}/admin-edit` | administrator / self-edit | No | N/A | `user_list` | Update profile → reload → updated card |
| Become an Instructor CTA | Headline, body copy, CTA button | `lms_theme.settings` or template | copy keys or defaults | `/admin/appearance/settings/lms_theme` | administrator | No | Config / Interface translation | (page cache) | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "70% of every sale" in CTA copy | Marketing placeholder in prototype | Prototype default | Move to `lms_theme.settings` before production |
| Stat pill copy format ("320+ Expert Instructors") | UI chrome template default | Prototype default | Revisit when `/instructors` route is implemented |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Hero | PLANNED | — | No route or controller exists; `CoursePublicController::instructors()` method to be created |
| Featured Instructor Spotlight | PLANNED | — | Requires `field_featured_instructor` on user entity; not yet implemented |
| Instructor Grid | PLANNED | — | Requires new `instructors()` controller method and `lms-instructors.html.twig` template |
| Become an Instructor CTA | PLANNED | — | Static section; easy to add once route exists |
