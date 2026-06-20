---
page: manage-courses
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Courses (List, Create, Edit)

Three related pages covering the course management lifecycle. All served by `AdminController` in `lms_course`, sharing the workspace shell and `lms_admin_entity_form` wrapper for forms.

---

## Page A: Course List — `/workspace/courses`

**Canonical URL:** `/workspace/courses` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_courses`
**Solution:** Custom controller method `AdminController::courseList()` returning paginated entity query results; AJAX-capable table partial (`ajax=1` query param returns `lms_admin_courses_table` partial for live filter without page reload).
**Owner module:** `lms_course` — `src/Controller/AdminController::courseList()`
**Primary source:** `lms_course` entity storage
**Editable source:** `lms_course` entity; edit at `/workspace/courses/{id}/edit`
**Editor role:** `administer lms_course` (instructor, admin)
**Editor management UI:** `/workspace/courses/{lms_course}/edit`
**Theme template:** `lms_course/templates/lms-admin-courses.html.twig` (hook: `lms_admin_courses`); table partial: `lms-admin-courses-table.html.twig` (hook: `lms_admin_courses_table`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache (per-request; AJAX partial reloads)
**Related entities:** [`lms_course`](../../C04-entities/), `taxonomy_term` (course_category, course_level), `user` (instructor)
**Prototype reference:** [`../../C10-prototypes/html/manage/courses.html`](../../C10-prototypes/html/manage/courses.html)

---

## Page B: Course Create — `/workspace/courses/create`

**Canonical URL:** `/workspace/courses/create` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_course_create`
**Solution:** `AdminController::courseCreateWizard()` returns a `lms_admin_course_create` theme render. This is a wizard entry point — the actual entity create route is at `/manage/courses/add` (via `AdminHtmlRouteProvider`). The wizard page may link into the standard Drupal entity form or inline a CourseForm.
**Owner module:** `lms_course` — `src/Controller/AdminController::courseCreateWizard()`
**Primary source:** New `lms_course` entity (unsaved)
**Editable source:** `lms_course` entity fields — see Course entity fields below
**Editor role:** `administer lms_course`
**Editor management UI:** `/workspace/courses/create` (this page)
**Theme template:** `lms_course/templates/lms-admin-course-create.html.twig` (hook: `lms_admin_course_create`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache
**Related entities:** [`lms_course`](../../C04-entities/)

---

## Page C: Course Edit — `/workspace/courses/{lms_course}/edit`

**Canonical URL:** `/workspace/courses/{lms_course}/edit` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin.course.edit`
**Solution:** `AdminController::courseEditForm()` embeds the `lms_course` entity form (form mode `admin_edit` for admins; `manager_edit` for instructors without full admin) inside the `lms_admin_entity_form` wrapper template. Form class: `src/Form/CourseForm.php`.
**Owner module:** `lms_course` — `src/Controller/AdminController::courseEditForm()`
**Primary source:** `lms_course` entity (existing)
**Editable source:** All `lms_course` base fields and attached fields — see Course entity fields in [`../../C04-entities/`](../../C04-entities/)
**Editor role:** `administer lms_course` (admin_edit form mode) or `edit lms_course` (manager_edit form mode)
**Editor management UI:** `/workspace/courses/{lms_course}/edit` (this page)
**Theme template:** `lms_course/templates/lms-admin-entity-form.html.twig` (hook: `lms_admin_entity_form`)
**Access:** Role-gated: `entity_access:lms_course.update`
**Cache:** No-cache
**Related entities:** [`lms_course`](../../C04-entities/), [`lms_chapter`](../../C04-entities/), [`lms_lesson`](../../C04-entities/), `taxonomy_term`, `lms_certificate_template`
**Prototype reference:** [`../../C10-prototypes/html/manage/course-edit.html`](../../C10-prototypes/html/manage/course-edit.html)
**Notes:** `courseEditForm()` detects current user role and selects either the `admin_edit` or `manager_edit` form display. The `manager_edit` form mode is created via `lms_course_update_10002`. The course edit page in the prototype shows a curriculum tree (chapters + lessons) inside the same page — in implementation this is driven by the `field_chapters` inline entity form widget.

---

## Components (visual order, top-to-bottom)

> Global chrome is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Shared across all workspace pages
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static
- **Backed by fields / config keys:** Hardcoded nav links
- **Editable source:** computed/read-only (developer-owned)
- **Editor role / permission:** Developer only
- **Editor management UI:** Edit `lms_course/templates/lms-admin-sidebar.html.twig`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** n/a
- **View mode / Projection:** Left sidebar
- **Region / Placement:** `.app-sidebar`
- **Notes:** Same component as workspace-dashboard; "Courses" link is active on these pages.
- **How to implement (EN):** Include `lms-admin-sidebar.html.twig` at top of page templates.
- **How to implement (VI):** Nhúng `lms-admin-sidebar.html.twig` vào đầu mỗi template trang workspace.
- **Solution Architect Request:** —

### 2. Page Topbar (List page)

- **Scope:** Page A (course list) only
- **Implementation mechanism:** Twig block in `lms-admin-courses.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static title; "+ Add course" button links to `/workspace/courses/create`
- **Backed by fields / config keys:** Static
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** n/a
- **View mode / Projection:** Topbar strip
- **Region / Placement:** Top of `.app-main`
- **Notes:** "Export" button shown in prototype — not wired in current implementation.
- **How to implement (EN):** Render static heading + link button to create route in topbar div.
- **How to implement (VI):** Hiển thị tiêu đề tĩnh và nút "+ Thêm khóa học" trỏ đến route tạo mới.
- **Solution Architect Request:** —

### 3. Filter Bar (List page)

- **Scope:** Page A (course list)
- **Implementation mechanism:** HTML form with `<select>` and `<input>` elements; submitted as GET params; `AdminController::courseList()` reads `status`, `category`, `instructor`, `search` query params
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Filter options sourced from taxonomy terms (`course_category` vocabulary) and user accounts (instructors)
- **Backed by fields / config keys:** `lms_course.field_category` (taxonomy_term), `lms_course.status`, `lms_course.field_instructor` (user), `lms_course.name` (search)
- **Editable source:** Taxonomy terms managed at `/admin/structure/taxonomy/manage/course_category/overview`; instructor options from user accounts with `create lms_course` permission
- **Editor role / permission:** `administer lms_course` can see and use filters; category terms managed by `administer taxonomy`
- **Editor management UI:** `/admin/structure/taxonomy/manage/course_category/overview`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Add a new taxonomy term → verify it appears in the Category filter dropdown
- **View mode / Projection:** Filter bar (horizontal strip above table)
- **Region / Placement:** Above courses table
- **Notes:** AJAX-capable: when `ajax=1` is appended, controller returns the `lms_admin_courses_table` partial only (for live filtering without full page reload).
- **How to implement (EN):** Build a GET form with selects for category (taxonomy_term options), status (published/unpublished), instructor (user options). On submit, `AdminController::courseList()` applies these as entity query conditions. Add a JS listener to auto-submit on change and replace the table partial via fetch.
- **How to implement (VI):** Tạo form GET với select category, status, instructor và ô tìm kiếm. Controller lọc entity query. JS tự submit và thay thế bảng qua fetch khi thay đổi filter.
- **Solution Architect Request:** —

### 4. Courses Data Table (List page)

- **Scope:** Page A (course list)
- **Implementation mechanism:** Twig table rendered from `courses` array; paginated (Drupal pager); AJAX partial in `lms-admin-courses-table.html.twig`
- **Plugin / Module:** `lms_course` — `AdminController::courseList()`
- **Source content type / Entity:** `lms_course` entity storage
- **Backed by fields / config keys:** `lms_course.field_thumbnail`, `lms_course.name`, `lms_course.field_category`, `lms_course.field_instructor`, `lms_course.status`, `lms_course.changed`; enrollment count from `lms_user_course` join
- **Editable source:** `lms_course` entities; edit at `/workspace/courses/{id}/edit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/courses/{lms_course}/edit` (Edit action); `/workspace/courses` (Publish/Delete row actions)
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Edit a course title → reload list → new title appears in table
- **View mode / Projection:** HTML table with thumbnail column, status badge, action buttons
- **Region / Placement:** Main content area below filter bar
- **Notes:** Columns (from prototype): checkbox, thumbnail, Title+subtitle, Category, Instructor, Students count, Status badge, Updated date, Actions (Edit, Delete). Bulk select with checkbox. Pagination below table.
- **How to implement (EN):** `AdminController::courseList()` queries `lms_course` with filters, paginates (default 20/page). For each course, load `field_thumbnail`, `field_category`, `field_instructor`. Count enrollments from `lms_user_course` table per course. Build render array; pass to `lms_admin_courses` template. AJAX partial is `lms_admin_courses_table`.
- **How to implement (VI):** Controller query `lms_course` có phân trang, tải thumbnail, category, instructor; đếm enroll từ `lms_user_course`; truyền vào template bảng; partial AJAX cho live filter.
- **Solution Architect Request:** —

### 5. Course Create Wizard (Create page)

- **Scope:** Page B (course create)
- **Implementation mechanism:** `AdminController::courseCreateWizard()` returns `lms_admin_course_create` render array. The wizard presents a step-by-step UI for entering course basics before proceeding to the full edit form.
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** New `lms_course` entity (unsaved at wizard step; saved when form submitted)
- **Backed by fields / config keys:** `lms_course.name`, `lms_course.field_category`, `lms_course.field_price_type`, `lms_course.field_thumbnail` (wizard captures minimal fields)
- **Editable source:** `lms_course` entity fields
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/courses/create` (this page) then redirects to edit form
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Complete wizard → verify new course entity created and redirected to edit
- **View mode / Projection:** Step-form wizard card
- **Region / Placement:** Main content area
- **Notes:** `courseCreateWizard()` returns theme render only — the actual entity persistence and form validation happen via `CourseForm` (either directly at `/manage/courses/add` or after the wizard submits there). The wizard page is primarily a UX entry point.
- **How to implement (EN):** `AdminController::courseCreateWizard()` returns `['#theme' => 'lms_admin_course_create']`. The template renders a multi-step or single-form card. On submit, POST to the entity add form or to a custom route that creates the entity and redirects to `/workspace/courses/{id}/edit`.
- **How to implement (VI):** Controller trả về theme render wizard; template hiển thị bước nhập thông tin cơ bản; khi submit tạo entity `lms_course` mới và chuyển hướng đến trang edit đầy đủ.
- **Solution Architect Request:** —

### 6. Course Edit Form (Edit page)

- **Scope:** Page C (course edit)
- **Implementation mechanism:** Drupal entity form embedded in workspace shell via `AdminController::courseEditForm()`; form class `src/Form/CourseForm.php`; wrapped in `lms_admin_entity_form` template
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_course` entity (existing)
- **Backed by fields / config keys:** All `lms_course` base fields: `name`, `field_description`, `field_short_description`, `field_what_learn`, `field_prerequisites`, `field_who_for`, `field_thumbnail`, `field_banner`, `field_chapters` (inline entity form → chapters → lessons), `field_category`, `field_tags`, `field_level`, `field_language`, `field_instructor`, `field_price`, `field_price_type`, `field_duration`, `field_bestseller`, `field_featured`, `field_certificate_template`, `status`
- **Editable source:** `lms_course` entity; form mode `admin_edit` or `manager_edit` depending on role
- **Editor role / permission:** `administer lms_course` (admin_edit) or `edit lms_course` (manager_edit)
- **Editor management UI:** `/workspace/courses/{lms_course}/edit` (this page)
- **Revision / moderation:** No (lms_course is not revisionable)
- **Translation source:** Content translation (translatable fields: name, field_description, etc.)
- **Edit smoke:** Edit course name → save → verify name updated in course list
- **View mode / Projection:** Full entity form with sidebar (lms_admin_entity_form wrapper)
- **Region / Placement:** Main content area + right sidebar (AI panel or metadata sidebar in prototype)
- **Notes:** `courseEditForm()` detects role (`administer lms_course` → `admin_edit` form mode; else `manager_edit`). `field_chapters` uses Inline Entity Form widget, allowing add/edit/remove chapters inline. Each chapter's `field_lessons` is similarly inline. The prototype shows an AI sidebar panel — this corresponds to the `/manage/courses/ai-builder` route in `lms_ai` (separate page, not embedded). Curriculum tree with drag handles is shown in prototype but drag-and-drop reordering is not confirmed in as-built implementation (relies on IEF widget default ordering).
- **How to implement (EN):** `AdminController::courseEditForm()` loads the course entity; detects role; calls `getFormObject('admin_edit')` or `getFormObject('manager_edit')`; builds Drupal form; embeds in `lms_admin_entity_form` template render array with `#attached` form state.
- **How to implement (VI):** Controller tải entity khóa học; phát hiện vai trò; gọi form object với mode phù hợp; nhúng vào template wrapper `lms_admin_entity_form`.
- **Solution Architect Request:** —

### 7. Curriculum Tree (Chapter + Lesson sub-components, Edit page)

- **Scope:** Page C (course edit) — sub-component of the edit form
- **Implementation mechanism:** Inline Entity Form (IEF) widget on `field_chapters`; each chapter IEF expands to show `field_lessons` IEF
- **Plugin / Module:** `lms_course`, `lms_chapter`, `lms_lesson`; requires `inline_entity_form` contrib module
- **Source content type / Entity:** `lms_chapter` entities (via `field_chapters` on `lms_course`); `lms_lesson` entities (via `field_lessons` on `lms_chapter`)
- **Backed by fields / config keys:** `lms_chapter.name`, `lms_chapter.field_description`, `lms_chapter.field_lessons`, `lms_chapter.weight`; `lms_lesson.name`, `lms_lesson.field_type`, `lms_lesson.field_duration`, `lms_lesson.weight`
- **Editable source:** `lms_chapter` and `lms_lesson` entities via IEF in the course form
- **Editor role / permission:** `administer lms_course` or `edit lms_course`
- **Editor management UI:** Inline within course edit form; also directly at `/workspace/chapters/{id}/admin-edit` and `/workspace/lessons/{id}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (chapter/lesson names are translatable)
- **Edit smoke:** Add a chapter + lesson inline → save course → verify chapter and lesson entities created and linked
- **View mode / Projection:** Collapsible tree blocks (chapter header + lesson rows)
- **Region / Placement:** Within edit form main column
- **Notes:** Prototype shows drag handles for reordering. The `weight` field on both `lms_chapter` and `lms_lesson` supports ordering; IEF widget may or may not expose drag handles depending on configuration. Each lesson row shows: icon (by `field_type`), name, duration, type badge, edit/delete actions.
- **How to implement (EN):** Configure `field_chapters` widget as `inline_entity_form_complex`; configure each chapter's `field_lessons` widget as `inline_entity_form_complex`. Enable weight/drag support in form display. `AdminController::quizEditForm()` patches IEF settings programmatically — apply the same pattern to `courseEditForm()` if needed.
- **How to implement (VI):** Cấu hình widget `inline_entity_form_complex` cho `field_chapters` và `field_lessons`; bật sắp xếp theo weight; mỗi hàng lesson hiển thị icon theo loại, tên, thời lượng và nút chỉnh sửa.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar | Nav links | Static template | Hardcoded HTML | Developer | Developer | No | No | None | Visual check |
| Topbar (list) | Title, "+ Add course" button | Static | N/A | N/A | N/A | No | No | None | Static |
| Filter Bar | Category options, status options, instructor options, search text | taxonomy_term (course_category), user accounts | term.name, user.name | `/admin/structure/taxonomy/...` | `administer taxonomy` | No | No | Per-request | Add taxonomy term → check filter |
| Courses Table | Thumbnail, title, category, instructor, student count, status, date, actions | `lms_course`, `lms_user_course` | All lms_course fields + enrollment count | `/workspace/courses/{id}/edit` | `administer lms_course` | No | No | Per-request | Edit title → check list |
| Create Wizard | Step form for new course basics | New `lms_course` (unsaved) | name, category, price_type, thumbnail | `/workspace/courses/create` | `administer lms_course` | No | No | No-cache | Create → verify redirect to edit |
| Edit Form | All course metadata fields | `lms_course` entity | All base fields listed above | `/workspace/courses/{id}/edit` | `administer lms_course` / `edit lms_course` | No | Yes (translatable fields) | No-cache | Edit name → reload → verify |
| Curriculum Tree | Chapter list + lesson rows per chapter | `lms_chapter`, `lms_lesson` via IEF | name, field_type, field_duration, weight | Inline in course edit form; direct: `/workspace/chapters/{id}/admin-edit` | `administer lms_course` | No | Yes (chapter/lesson name) | No-cache | Add chapter inline → save → verify |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "Export" button in topbar | Prototype-only; not wired in as-built | Known gap | Wire when CSV export for courses is needed |
| Lesson type icons (video, text, quiz, scorm) | UI chrome mapped from `field_type` enum | Developer convention (enum-to-icon map in template) | Update template when new lesson types added |

## Implementation status

| Component | Status | Notes |
|---|---|---|
| Sidebar | DONE | Shared include |
| Topbar (list) | DONE | Static |
| Filter Bar | DONE | Status, category, instructor, search filters with AJAX partial |
| Courses Table | DONE | Paginated; HTMX/AJAX partial `lms_admin_courses_table` |
| Create Wizard | DONE | `courseCreateWizard()` returns theme render; form submission path via CourseForm |
| Edit Form (Page C) | DONE | `admin_edit`/`manager_edit` form mode selection working; `CourseForm` saves and redirects to collection |
| Curriculum Tree (IEF) | DONE | `field_chapters` + `field_lessons` IEF widgets; drag-and-drop reorder status unconfirmed |
