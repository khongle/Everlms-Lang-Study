---
page: course-detail
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Course Detail

**Canonical URL:** `/courses/{lms_course}` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.detail`
**Solution:** Custom controller (`CoursePublicController::detail`) loads the course entity plus all related entities (chapters, lessons, instructor, ratings, FAQs); computes enrollment state and "continue learning" URL; renders via `lms-course-detail.html.twig` with tabbed layout (Overview, Curriculum, Instructor, Reviews, FAQ) and sticky purchase sidebar.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** `lms_course` entity (param converter: `entity:lms_course`)
**Editable source:** `lms_course` entity fields; nested `lms_chapter` → `lms_lesson` curriculum; `lms_rating` (reviews); `lms_faq` (course scope); `lms_membership_plan` (membership check)
**Editor role:** administrator, manager, instructor (own course)
**Editor management UI:** `/workspace/courses/{id}/edit` (course fields); `/workspace/chapters/{id}/admin-edit` (chapter); `/workspace/lessons/{id}/admin-edit` (lesson)
**Revision / moderation:** No
**Forms:** Rating form (shown when user is enrolled, renders `lms_rating` entity form); Wishlist toggle (AJAX POST to `/api/lms/wishlist/toggle`)
**Theme template:** `web/modules/custom/lms_course/templates/lms-course-detail.html.twig` *(per [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Public — `_permission: 'access content'`; enrollment/rating/wishlist actions require authentication
**Cache:** Per-user (enrollment + wishlist status differ); invalidated by `lms_course:{id}`, `lms_chapter_list`, `lms_lesson_list`, `lms_rating_list`, `lms_faq_list`
**Translation:** Monolingual EN (course name, description are translatable fields)
**Related entities:** [`lms_course`](../../C04-entities/01-content-entities.md), [`lms_chapter`](../../C04-entities/01-content-entities.md), [`lms_lesson`](../../C04-entities/01-content-entities.md), [`lms_rating`](../../C04-entities/07-communication-entities.md), [`lms_faq`](../../C04-entities/01-content-entities.md), [`lms_membership_plan`](../../C04-entities/04-commerce-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/public/course-detail.html`](../../C10-prototypes/html/public/course-detail.html)
**Notes:** `CoursePublicController::detail` computes `continue_url` (finds next incomplete lesson) for enrolled users. Membership-type courses trigger a membership check via `hook_entity_access`. Free-preview lessons are marked `field_free_preview=TRUE` and show a "Preview" link even without enrollment.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Course Hero Banner — *Banner khóa học*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block; fields read directly from `lms_course` entity passed to template
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `lms_course` entity
- **Backed by fields / config keys:** `name`, `field_short_description`, `field_thumbnail`, `field_banner`, `field_rating`, `field_level`, `field_category`, `uid` (instructor), enrollment count (computed), `field_bestseller`, `field_tags`
- **Editable source:** `lms_course` entity at `/workspace/courses/{id}/edit`
- **Editor role / permission:** manager, administrator — `administer lms_course` or `edit lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (`name`, `field_short_description`)
- **Edit smoke:** Update `field_short_description` → reload `/courses/{id}` → updated subtitle visible in hero
- **View mode / Projection:** Full-width banner with dark overlay on `field_banner` image
- **Region / Placement:** Top of page, full-width, below navbar
- **Notes:** Breadcrumb: Home > Courses > [Course Name]. Instructor name links to `/instructor/{uid}`.
- **How to implement (EN):** Render hero from `lms_course` entity fields; overlay `field_banner`; show category badge, level badge, rating stars, enrollment count, instructor link.
- **How to implement (VI):** Render hero từ fields entity `lms_course`; overlay `field_banner`; hiển thị badge category, level, stars rating, số học viên, link instructor.
- **Solution Architect Request:**

### 2. Tab Navigation — *Thanh tab điều hướng*

- **Scope:** Page-specific
- **Implementation mechanism:** Client-side tab toggle (JS `showTab()` in template); tab IDs: `tab-overview`, `tab-curriculum`, `tab-instructor`, `tab-reviews`, `tab-faq`
- **Plugin / Module:** `lms_course` (template JS)
- **Source content type / Entity:** Static UI chrome
- **Backed by fields / config keys:** N/A
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Sticky tab bar
- **Region / Placement:** Below hero, above tab content area
- **Notes:** Tabs: Overview | Curriculum | Instructor | Reviews | FAQ. Default active: Overview.
- **How to implement (EN):** Render 5 anchor links; inline JS toggles `.active` class and shows/hides `.tab-content` divs.
- **How to implement (VI):** Render 5 anchor link; JS nội tuyến toggle class `.active` và show/hide div `.tab-content`.
- **Solution Architect Request:**

### 3. Overview Tab — *Tab tổng quan*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block; long description fields rendered with markup
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `lms_course` entity
- **Backed by fields / config keys:** `field_description`, `field_what_learn`, `field_prerequisites`, `field_who_for`
- **Editable source:** `lms_course` entity at `/workspace/courses/{id}/edit`
- **Editor role / permission:** manager, administrator — `edit lms_course` or `administer lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (all description fields are translatable)
- **Edit smoke:** Update `field_what_learn` → reload → "What you'll learn" checklist updated
- **View mode / Projection:** Full-width long-form content with bullet lists
- **Region / Placement:** Left column tab area (beside sticky sidebar)
- **Notes:** Rendered with `text_long` formatter supporting full HTML. "What you'll learn" section uses checklist icon styling.
- **How to implement (EN):** Output `field_description`, `field_what_learn` (as bullet list), `field_prerequisites`, `field_who_for` in the overview tab.
- **How to implement (VI):** Xuất `field_description`, `field_what_learn` (dạng bullet list), `field_prerequisites`, `field_who_for` trong tab overview.
- **Solution Architect Request:**

### 4. Curriculum Tab — *Tab chương trình học*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig nested loop over `field_chapters` → `field_lessons`; accordion expand/collapse; free-preview lesson links
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `lms_chapter` entities (via `lms_course.field_chapters`) → `lms_lesson` entities (via `lms_chapter.field_lessons`)
- **Backed by fields / config keys:** `lms_chapter.name`, `lms_chapter.weight`; `lms_lesson.name`, `lms_lesson.field_type`, `lms_lesson.field_duration`, `lms_lesson.field_free_preview`, `lms_lesson.weight`
- **Editable source:** `lms_chapter` at `/workspace/chapters/{id}/admin-edit`; `lms_lesson` at `/workspace/lessons/{id}/admin-edit`
- **Editor role / permission:** manager, administrator — `edit lms_course` or `administer lms_course`
- **Editor management UI:** `/workspace/chapters/{id}/admin-edit`, `/workspace/lessons/{id}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (`lms_chapter.name`, `lms_lesson.name`)
- **Edit smoke:** Update chapter name → reload → curriculum accordion shows updated chapter title
- **View mode / Projection:** Accordion (one row per chapter; expanded shows lesson list)
- **Region / Placement:** Left column tab area
- **Notes:** Lessons show type icon, duration, "Preview" link if `field_free_preview=TRUE`. Non-enrolled users see preview links; enrolled users see `/learn/{course}/{lesson}` links. Drip-scheduled lessons (`field_drip_days > 0`) are indicated as locked.
- **How to implement (EN):** Load `field_chapters` (sorted by `weight`); for each chapter load `field_lessons` (sorted by weight); render as accordion; check `field_free_preview` for preview links.
- **How to implement (VI):** Load `field_chapters` (sort theo `weight`); mỗi chapter load `field_lessons` (sort theo weight); render dạng accordion; check `field_free_preview` cho link preview.
- **Solution Architect Request:**

### 5. Instructor Tab — *Tab giảng viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block using instructor user entity fields loaded by `detail()`
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `user` entity (instructor, via `lms_course.field_instructor`)
- **Backed by fields / config keys:** `user.name`, user profile picture, user bio fields, computed instructor stats (rating average, student count, course count)
- **Editable source:** User profile edit at `/user/{uid}/edit`
- **Editor role / permission:** instructor (own profile) — `administer users` or self-edit
- **Editor management UI:** `/user/{uid}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Update instructor bio → reload course detail → Instructor tab shows updated bio
- **View mode / Projection:** Instructor card (avatar, name, bio, stats: rating / students / courses / reviews)
- **Region / Placement:** Left column tab area (tab-instructor)
- **Notes:** Stats are computed from `lms_rating` and `lms_user_course` aggregates. Links to `/instructor/{uid}` for full profile page.
- **How to implement (EN):** Load instructor user entity from `field_instructor`; compute stats via DB aggregation; render as profile card in tab.
- **How to implement (VI):** Load user entity instructor từ `field_instructor`; tính stats qua DB aggregation; render dạng profile card trong tab.
- **Solution Architect Request:**

### 6. Reviews Tab — *Tab đánh giá*

- **Scope:** Page-specific
- **Implementation mechanism:** Load approved `lms_rating` entities for this course; show rating distribution bars; if enrolled, show rating submission form
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course` / `lms_rating`
- **Source content type / Entity:** `lms_rating` entities (filtered by course, `field_approved=1`)
- **Backed by fields / config keys:** `lms_rating.field_rating`, `lms_rating.field_review`, `lms_rating.uid`, `lms_rating.created`; rating distribution (computed per star level)
- **Editable source:** `lms_rating` approval at `/workspace/ratings`
- **Editor role / permission:** administrator — `administer lms_course` (approval); authenticated enrolled user (submit rating)
- **Editor management UI:** `/workspace/ratings` (approve/reject/feature)
- **Revision / moderation:** No
- **Translation source:** N/A (user content)
- **Edit smoke:** Approve a rating → reload course detail → review appears in Reviews tab
- **View mode / Projection:** Rating summary (average, distribution bars) + review cards; rating form at bottom for enrolled users
- **Region / Placement:** Left column tab area (tab-reviews)
- **Notes:** Rating form shown only if `current_user` is enrolled in the course. `lms_rating` requires approval before public display.
- **How to implement (EN):** Query `lms_rating` for `entity_id={course_id}` and `field_approved=1`; compute distribution; render review cards; show form if enrolled.
- **How to implement (VI):** Query `lms_rating` với `entity_id={course_id}` và `field_approved=1`; tính phân phối sao; render review card; hiển thị form nếu đã đăng ký.
- **Solution Architect Request:**

### 7. FAQ Tab — *Tab câu hỏi thường gặp*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `lms_faq` with `field_scope=course` and `field_course={id}`; render as accordion
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `lms_faq` entities (`field_scope=course`, `field_course={id}`)
- **Backed by fields / config keys:** `lms_faq.question`, `lms_faq.field_answer`, `lms_faq.weight`, `lms_faq.field_category`
- **Editable source:** `lms_faq` CRUD at `/workspace/content/faqs`
- **Editor role / permission:** administrator, manager — `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs`
- **Revision / moderation:** No
- **Translation source:** Content translation
- **Edit smoke:** Create FAQ with `field_scope=course`, `field_course={id}` → reload → FAQ appears in course FAQ tab
- **View mode / Projection:** Accordion list
- **Region / Placement:** Left column tab area (tab-faq)
- **Notes:** Uses same `lms_faq` entity as homepage FAQs, distinguished by `field_scope` value.
- **How to implement (EN):** Query `lms_faq` where `field_scope='course'` and `field_course=course_id`; sort by weight; render as accordion.
- **How to implement (VI):** Query `lms_faq` với `field_scope='course'` và `field_course=course_id`; sort theo weight; render thành accordion.
- **Solution Architect Request:**

### 8. Purchase / Enroll Sidebar — *Sidebar mua/đăng ký khóa học*

- **Scope:** Page-specific
- **Implementation mechanism:** Sticky right-column Twig block; enrollment state computed in `detail()` controller; CTA varies by state
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`; `lms_wishlist` (wishlist toggle); `lms_order` (checkout link)
- **Source content type / Entity:** `lms_course` entity + `lms_user_course` (enrollment check) + `lms_user_membership` (membership check)
- **Backed by fields / config keys:** `lms_course.field_price`, `lms_course.field_price_type`, `lms_course.field_thumbnail` (preview image); computed enrollment state; computed `continue_url`
- **Editable source:** `lms_course.field_price`, `lms_course.field_price_type` at `/workspace/courses/{id}/edit`
- **Editor role / permission:** manager, administrator — `edit lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Interface translation (button labels)
- **Edit smoke:** Change `field_price_type` from paid → free → reload → "Enroll Free" button appears
- **View mode / Projection:** Sticky card with: course thumbnail preview, price (or "Free"), enrollment CTA, wishlist icon button, what's included list
- **Region / Placement:** Right sticky column (320px wide, `top: 76px`)
- **Notes:** CTA states: (1) Free course → "Enroll for Free" → `/courses/{id}/enroll-free`; (2) Paid course → "Buy Now" → `/checkout/{id}`; (3) Membership course (not member) → "Get Membership" → `/pricing`; (4) Membership course (is member) → "Enroll Free"; (5) Already enrolled → "Continue Learning" → `continue_url`. Wishlist toggle via AJAX POST `/api/lms/wishlist/toggle`.
- **How to implement (EN):** Check enrollment (`lms_user_course`), membership (`lms_user_membership`), and `field_price_type`; render appropriate CTA button; wishlist button triggers AJAX.
- **How to implement (VI):** Kiểm tra enrollment (`lms_user_course`), membership (`lms_user_membership`), và `field_price_type`; render nút CTA phù hợp; nút wishlist trigger AJAX.
- **Solution Architect Request:**

### 9. Related Courses Section — *Phần khóa học liên quan*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `lms_course` by same category; limit 3–4; render as course card row
- **Plugin / Module:** `CoursePublicController::detail` → `lms_course`
- **Source content type / Entity:** `lms_course` entities (same `field_category` as current course)
- **Backed by fields / config keys:** `lms_course.field_category` (filter), same card fields as catalog
- **Editable source:** `lms_course` entity at `/workspace/courses`
- **Editor role / permission:** manager, administrator
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation
- **Edit smoke:** Assign same category to additional courses → reload detail → they appear in related section
- **View mode / Projection:** `lms_course_card` (3–4 columns)
- **Region / Placement:** Below tab content, before footer
- **Notes:** Related section renders below main tabs. Implementation confirmed in prototype but `detail()` implementation details «unverified» — listed in routing.yml as implemented.
- **How to implement (EN):** Query `lms_course` where `field_category={same term}` and `id != {current_id}`; limit 4; render as card row.
- **How to implement (VI):** Query `lms_course` với `field_category={cùng term}` và `id != {id hiện tại}`; limit 4; render thành hàng card.
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Course Hero Banner | Title, short description, thumbnail/banner, rating, level badge, category badge, enrollment count, instructor name, tags, bestseller tag | `lms_course` entity | `name`, `field_short_description`, `field_thumbnail`, `field_banner`, `field_rating`, `field_level`, `field_category`, `field_bestseller`, `field_tags`, `uid` | `/workspace/courses/{id}/edit` | manager / `edit lms_course` | No | Content translation | `lms_course:{id}` | Edit short_description → reload → updated in hero |
| Tab Navigation | Tab labels (Overview, Curriculum, Instructor, Reviews, FAQ) | static UI chrome | N/A | N/A | N/A | No | Interface translation | (page cache) | N/A |
| Overview Tab | Long description, what you'll learn bullets, prerequisites, who it's for | `lms_course` entity | `field_description`, `field_what_learn`, `field_prerequisites`, `field_who_for` | `/workspace/courses/{id}/edit` | manager / `edit lms_course` | No | Content translation | `lms_course:{id}` | Edit `field_what_learn` → reload → updated |
| Curriculum Tab | Chapter names, lesson list (name, type icon, duration, preview link) | `lms_chapter`, `lms_lesson` | `name`, `weight`; `name`, `field_type`, `field_duration`, `field_free_preview` | `/workspace/chapters/{id}/admin-edit`, `/workspace/lessons/{id}/admin-edit` | manager / `edit lms_course` | No | Content translation | `lms_chapter_list`, `lms_lesson_list` | Edit chapter name → reload → updated in accordion |
| Instructor Tab | Instructor avatar, name, bio, stats (rating, students, courses, reviews) | `user` entity | `name`, picture, bio; computed stats | `/user/{uid}/edit` | instructor / self-edit | No | N/A | `user:{uid}` | Update bio → reload → updated in Instructor tab |
| Reviews Tab | Avg rating, star distribution bars, review cards (reviewer, stars, text, date), rating form | `lms_rating` entities + form | `field_rating`, `field_review`, `uid`, `created`; computed distribution | `/workspace/ratings` (approve) | admin / `administer lms_course` (approve); enrolled user (submit) | No | N/A | `lms_rating_list` | Approve rating → reload → appears in Reviews tab |
| FAQ Tab | Q&A accordion (question, answer) | `lms_faq` entities (`field_scope=course`) | `question`, `field_answer`, `weight` | `/workspace/content/faqs` | manager / `administer lms_course` | No | Content translation | `lms_faq_list` | Add course FAQ → reload → appears in FAQ tab |
| Purchase Sidebar | Price/Free badge, enrollment CTA button, what's included list, wishlist icon | `lms_course` + enrollment state | `field_price`, `field_price_type`; `lms_user_course` (computed) | `/workspace/courses/{id}/edit` | manager / `edit lms_course` | No | Interface translation | per-user context | Change price_type → reload → CTA button changes |
| Related Courses | Course card row (thumbnail, title, price, rating) | `lms_course` (same category) | Same as catalog card fields | `/workspace/courses/{id}/edit` | manager / `administer lms_course` | No | Content translation | `lms_course_list` | Add course in same category → reload → appears |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "What you'll learn" section heading | UI chrome label | Template default | Interface-translatable |
| Tab labels (Overview, Curriculum, etc.) | UI chrome | Template default | Interface-translatable |
| "Enroll for Free" / "Buy Now" button text (fallback) | UI chrome | Template default | Interface-translatable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Course Hero Banner | DONE | — | Fields from `lms_course` entity; breadcrumb via `LmsCourseBreadcrumbBuilder` |
| Tab Navigation | DONE | — | Client-side JS tab toggle in template |
| Overview Tab | DONE | — | `field_description`, `field_what_learn`, `field_prerequisites`, `field_who_for` |
| Curriculum Tab | DONE | — | Nested `field_chapters` → `field_lessons` with `field_free_preview` check |
| Instructor Tab | DONE | — | Instructor user entity + computed stats |
| Reviews Tab | DONE | — | Approved `lms_rating` entities; rating form for enrolled users |
| FAQ Tab | DONE | — | `lms_faq` with `field_scope=course` + `field_course={id}` filter |
| Purchase Sidebar | DONE | — | 5-state CTA logic; wishlist AJAX; `continue_url` computed |
| Related Courses | DONE | — | Same-category `lms_course` query (implementation «unverified» in controller tail) |
