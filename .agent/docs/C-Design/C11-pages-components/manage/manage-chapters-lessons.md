---
page: manage-chapters-lessons
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Chapters & Lessons (Edit)

Chapter and lesson editing in the workspace. There are no standalone list pages for chapters or lessons — they are managed inline via the course edit form (see [`manage-courses.md`](manage-courses.md)). The workspace provides dedicated direct-edit routes for each entity when navigating out of the curriculum tree.

Two direct-edit routes exist:
- `/workspace/chapters/{lms_chapter}/admin-edit`
- `/workspace/lessons/{lms_lesson}/admin-edit`

Both delegate to Drupal entity forms (not custom controller methods) registered via `_entity_form` in `lms_course.routing.yml`. The edit form mode `admin_edit` is registered on both entities by `lms_course.module::hook_entity_type_alter()`.

---

## Page A: Chapter Edit — `/workspace/chapters/{lms_chapter}/admin-edit`

**Canonical URL:** `/workspace/chapters/{lms_chapter}/admin-edit` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin.chapter.edit`
**Solution:** Drupal core `_entity_form` handler using `lms_chapter.admin_edit` form mode; form class `src/Form/ChapterForm.php`; rendered inside `lms_theme` admin layout (Claro admin theme is NOT used for these custom workspace routes — `lms_theme` renders them).
**Owner module:** `lms_course` (route definition + `hook_entity_type_alter` registering `admin_edit` form); `lms_chapter` (entity + form class)
**Primary source:** `lms_chapter` entity (existing)
**Editable source:** `lms_chapter` entity fields — `name`, `field_description`, `field_lessons`, `weight`, `status`
**Editor role:** `administer lms_course` or `edit lms_course` (access via `entity_access:lms_chapter.update` → `ChapterAccessControlHandler`: requires `edit lms_course`)
**Editor management UI:** `/workspace/chapters/{lms_chapter}/admin-edit` (this page); accessible via "Edit" link in the curriculum tree on the course edit page
**Theme template:** Drupal default entity form rendering (no dedicated custom template; uses `lms_admin_entity_form` or core form rendering depending on context); the `_entity_form` route bypasses `AdminController` wrapper
**Access:** Role-gated: `entity_access:lms_chapter.update`
**Cache:** No-cache
**Related entities:** [`lms_chapter`](../../C04-entities/), [`lms_lesson`](../../C04-entities/), [`lms_course`](../../C04-entities/)
**Notes:** `ChapterAccessControlHandler` delegates to lms_course permissions: `view lms_course` for view, `edit lms_course` for update/delete, `create lms_course` for create. The `admin_edit` form display is registered externally by `lms_course.module`. No list_builder is defined on `lms_chapter` — there is no `/workspace/chapters` list page. The `/manage/chapters` collection route is provided by `AdminHtmlRouteProvider` but is not linked from the workspace navigation.

---

## Page B: Lesson Edit — `/workspace/lessons/{lms_lesson}/admin-edit`

**Canonical URL:** `/workspace/lessons/{lms_lesson}/admin-edit` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin.lesson.edit`
**Solution:** Drupal core `_entity_form` handler using `lms_lesson.admin_edit` form mode; form class `src/Form/LessonForm.php` (fully implemented with conditional field visibility via `#states`).
**Owner module:** `lms_course` (route + `hook_entity_type_alter`); `lms_lesson` (entity + form class)
**Primary source:** `lms_lesson` entity (existing)
**Editable source:** All `lms_lesson` base fields — conditionally shown by `field_type` value (via `#states` in `LessonForm`)
**Editor role:** `administer lms_course` or `edit lms_course` (access via `entity_access:lms_lesson.update` → `LessonAccessControlHandler`)
**Editor management UI:** `/workspace/lessons/{lms_lesson}/admin-edit` (this page); also inline via IEF in course edit
**Theme template:** `_entity_form` route — uses Drupal form rendering; no dedicated custom Twig template for the standalone lesson edit page
**Access:** Role-gated: `entity_access:lms_lesson.update`
**Cache:** No-cache
**Related entities:** [`lms_lesson`](../../C04-entities/), [`lms_chapter`](../../C04-entities/), [`lms_quiz`](../../C04-entities/)
**Notes:** `LessonForm` uses `#states` to show/hide fields by `field_type`:
- `video` or `mixed`: shows `field_video_url`, `field_video_file`
- `quiz` or `mixed`: shows `field_quiz`
- `h5p` or `mixed`: shows `field_h5p_embed`
- `scorm` or `mixed`: shows `field_scorm_package`
- `pptx` or `mixed`: shows `field_pptx_attachment`
- `zoom`: shows `field_zoom_meeting_id`, `field_zoom_schedule`

SCORM extraction (`_lms_lesson_extract_scorm()`) is triggered automatically via `hook_entity_insert` / `hook_entity_update` — no manual step required. After save, `LessonForm::save()` redirects to the edit-form (not the collection).

---

## Components (visual order, top-to-bottom)

> Global chrome is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Shared workspace chrome
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig` (if rendered inside the workspace shell); for `_entity_form` routes, the workspace shell may not be applied automatically
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static
- **Backed by fields / config keys:** Hardcoded nav links
- **Editable source:** computed/read-only
- **Editor role / permission:** Developer
- **Editor management UI:** Edit Twig file
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** n/a
- **View mode / Projection:** Left sidebar
- **Region / Placement:** `.app-sidebar`
- **Notes:** `_entity_form` routes use core routing; whether the workspace shell template is applied depends on theme template suggestions. If no theme suggestion wraps it, the form renders in the standard theme layout (lms_theme page template) without the sidebar.
- **How to implement (EN):** Add a theme suggestion or template override for `page--workspace-chapters--*.html.twig` to wrap `_entity_form` output in the workspace shell. Alternatively, replace `_entity_form` with a controller-based approach (like `AdminController::courseEditForm()`) to embed the form in the workspace template.
- **How to implement (VI):** Thêm theme suggestion hoặc template override để bọc form entity trong shell workspace; hoặc thay `_entity_form` bằng controller nhúng form vào template workspace.
- **Solution Architect Request:** —

### 2. Chapter Edit Form (Page A)

- **Scope:** Page A (chapter edit)
- **Implementation mechanism:** Drupal entity form — `ChapterForm` (standard `ContentEntityForm`)
- **Plugin / Module:** `lms_chapter`
- **Source content type / Entity:** `lms_chapter` entity
- **Backed by fields / config keys:** `lms_chapter.name` (string, required), `lms_chapter.field_description` (text_long), `lms_chapter.field_lessons` (entity_reference→lms_lesson, IEF widget), `lms_chapter.weight` (integer), `lms_chapter.status` (boolean)
- **Editable source:** `lms_chapter` entity fields
- **Editor role / permission:** `edit lms_course`
- **Editor management UI:** `/workspace/chapters/{lms_chapter}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (name, field_description translatable)
- **Edit smoke:** Edit chapter name → save → verify name updated in course curriculum
- **View mode / Projection:** Standard Drupal entity form
- **Region / Placement:** Main content area
- **Notes:** `ChapterForm::save()` adds a status message and redirects to the collection (which is `/manage/chapters`). In the workspace context, the user would expect to return to the course edit page — this is a UX gap. The `admin_edit` form display mode is configured via `lms_course_update_10002`.
- **How to implement (EN):** The `lms_chapter` entity ships with `ChapterForm` as the default form class. The `admin_edit` form mode is registered by `lms_course.module::hook_entity_type_alter()`. Configure the form display at `/admin/structure/lms_chapter/form-display/admin_edit` to show/hide fields appropriate for admin context.
- **How to implement (VI):** Entity `lms_chapter` dùng `ChapterForm` mặc định; mode `admin_edit` được đăng ký bởi `lms_course.module`; cấu hình hiển thị form tại `/admin/structure/lms_chapter/form-display/admin_edit`.
- **Solution Architect Request:** —

### 3. Lesson Edit Form (Page B)

- **Scope:** Page B (lesson edit)
- **Implementation mechanism:** Drupal entity form — `LessonForm` (custom `ContentEntityForm` with `#states` conditional logic)
- **Plugin / Module:** `lms_lesson`
- **Source content type / Entity:** `lms_lesson` entity
- **Backed by fields / config keys:** `lms_lesson.name`, `lms_lesson.field_type` (list_string: video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed), `lms_lesson.field_content`, `lms_lesson.field_video_url`, `lms_lesson.field_video_file`, `lms_lesson.field_quiz`, `lms_lesson.field_h5p_embed`, `lms_lesson.field_scorm_package`, `lms_lesson.field_pptx_attachment`, `lms_lesson.field_zoom_meeting_id`, `lms_lesson.field_zoom_schedule`, `lms_lesson.field_attachments`, `lms_lesson.field_duration`, `lms_lesson.field_free_preview`, `lms_lesson.field_drip_days`, `lms_lesson.field_comments`, `lms_lesson.weight`, `lms_lesson.status`
- **Editable source:** `lms_lesson` entity fields; media fields managed via file upload widgets
- **Editor role / permission:** `edit lms_course`
- **Editor management UI:** `/workspace/lessons/{lms_lesson}/admin-edit`; also inline IEF in course edit
- **Revision / moderation:** No
- **Translation source:** Content translation (name, field_content translatable)
- **Edit smoke:** Change `field_type` to "video" → verify video URL and file fields appear; set type to "quiz" → verify quiz reference field appears
- **View mode / Projection:** Drupal entity form with conditional field groups
- **Region / Placement:** Main content area
- **Notes:** SCORM package upload triggers automatic extraction (`_lms_lesson_extract_scorm()` in `lms_lesson.module`) on `hook_entity_insert`/`hook_entity_update`. The extracted files land at `public://scorm_extracted/{lesson_id}/`. PDF and PPTX have file fields but no server-side conversion logic (viewer depends on browser or embed). `field_drip_days` enables content drip — lessons unlock N days after enrollment.
- **How to implement (EN):** `LessonForm::buildForm()` iterates field visibility: add `#states` attribute to each type-specific field referencing the `field_type` select element value. SCORM extraction is automatic via module hooks. After save, redirect to `edit-form` to keep editor on the lesson page.
- **How to implement (VI):** `LessonForm::buildForm()` thêm `#states` cho từng trường phụ thuộc vào giá trị `field_type`; trích xuất SCORM tự động qua hook; sau lưu chuyển về form edit bài học.
- **Solution Architect Request:** —

### 4. Lesson Type Field Group (conditional — Page B)

- **Scope:** Page B (lesson edit) — sub-component of the lesson form
- **Implementation mechanism:** `#states` API on individual fields in `LessonForm::buildForm()`; no separate field group module required
- **Plugin / Module:** `lms_lesson`
- **Source content type / Entity:** `lms_lesson` entity fields
- **Backed by fields / config keys:** `field_type` (the controlling field); all type-specific fields as listed above
- **Editable source:** `lms_lesson` entity fields
- **Editor role / permission:** `edit lms_course`
- **Editor management UI:** Inline in lesson form
- **Revision / moderation:** No
- **Translation source:** N/A (field visibility is logic, not content)
- **Edit smoke:** Select each `field_type` value → verify correct subset of fields is shown
- **View mode / Projection:** Dynamic field visibility within form
- **Region / Placement:** Within lesson edit form
- **Notes:** The `mixed` type shows fields from all other types simultaneously. H5P embed (`field_h5p_embed`) is also shown for any field with `h5p` in its name, handled by `LessonForm::buildForm()` compatibility logic.
- **How to implement (EN):** In `buildForm()`, after calling `parent::buildForm()`, iterate over form elements; for each type-specific field, set `$form['field_video_url']['#states'] = ['visible' => [':input[name="field_type"]' => ['value' => 'video']]]` (and `mixed`). Apply equivalent `#states` to all other type-specific fields.
- **How to implement (VI):** Trong `buildForm()`, sau khi gọi `parent`, thêm `#states` cho từng trường phụ thuộc giá trị của `field_type` select; trường `mixed` hiển thị tất cả các nhóm field cùng lúc.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar | Nav links | Static template | Hardcoded | Dev edits Twig | Developer | No | No | None | Visual check |
| Chapter Edit Form | Chapter name, description, lesson list (IEF), weight, status | `lms_chapter` entity | name, field_description, field_lessons, weight, status | `/workspace/chapters/{id}/admin-edit` | `edit lms_course` | No | Yes | No-cache | Edit name → check in course |
| Lesson Edit Form | Name, type selector, type-specific media/embed fields, attachments, duration, drip, quiz ref, comments toggle, status | `lms_lesson` entity | All lms_lesson base fields | `/workspace/lessons/{id}/admin-edit` | `edit lms_course` | No | Yes | No-cache | Change field_type → verify field visibility |
| Lesson Type Field Group | Conditional field subset (video URL/file, quiz ref, H5P embed, SCORM package, PPTX, Zoom meeting/schedule) | `lms_lesson` entity | type-specific fields | Inline in lesson form | `edit lms_course` | No | No (logic) | No-cache | Select each type → verify fields |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| `field_type` allowed values (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed) | Defined in entity `baseFieldDefinitions()` as `list_string` options; requires code change to add new types | Architecture decision (entity field definition) | Revisit if new lesson types are needed |
| SCORM launch URL detection logic | PHP implementation in `_lms_lesson_extract_scorm()` parsing `imsmanifest.xml` | Developer convention | Update if non-standard SCORM 2004 manifests are used |

## Implementation status

| Component | Status | Notes |
|---|---|---|
| Chapter edit route | DONE | `lms_course.admin.chapter.edit` → `_entity_form: lms_chapter.admin_edit` |
| Chapter Edit Form | DONE | `ChapterForm` saves; `admin_edit` form mode registered via `lms_course_update_10002` |
| Lesson edit route | DONE | `lms_course.admin.lesson.edit` → `_entity_form: lms_lesson.admin_edit` |
| Lesson Edit Form | DONE | `LessonForm` with full `#states` conditional logic |
| Lesson Type Field Group | DONE | `#states` API; all 9 types handled including `h5p` compatibility check |
| SCORM auto-extract | DONE | `hook_entity_insert`/`update` → `_lms_lesson_extract_scorm()` using PHP `ZipArchive` |
| Workspace shell wrapping | UNCONFIRMED | `_entity_form` routes may render without the workspace sidebar; needs theme suggestion verification |
