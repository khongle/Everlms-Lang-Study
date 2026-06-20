---
page: manage-faq
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage FAQ (List, Create, Edit, View)

FAQ management in the workspace. FAQs are backed by the `lms_faq` custom entity (defined in `lms_faq` module; routes and controllers provided by `lms_course`). The workspace exposes four operations: list, add, edit, view — all served by `WorkspaceContentController` in `lms_course`.

**Architectural note:** `lms_faq` is a minimal module (only `lms_faq.info.yml` + `src/Entity/Faq.php`). It has no custom routes, no custom form class, and no custom access handler. All workspace CRUD is delegated to `lms_course`'s `WorkspaceContentController`. The entity uses `ContentEntityForm` (core default) and `EntityAccessControlHandler` (core default — no custom access policy beyond admin_permission = `administer lms_course`).

---

## Page A: FAQ List — `/workspace/content/faqs` *(via generic content list)*

**Canonical URL:** `/workspace/content/{content_type}` with `content_type=faqs` — resolves to `WorkspaceContentController::contentList()` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.workspace_content.list`
**Solution:** `WorkspaceContentController::contentList()` detects `content_type=faqs`, queries `lms_faq` entity storage, renders `lms_admin_content` template with AJAX-capable table partial (`lms_admin_content_table`).
**Owner module:** `lms_course` — `src/Controller/WorkspaceContentController::contentList()`
**Primary source:** `lms_faq` entity storage
**Editable source:** `lms_faq` entities; edit at `/workspace/content/faqs/{id}/edit`
**Editor role:** `administer lms_course`
**Editor management UI:** `/workspace/content/faqs/{lms_faq}/edit`
**Theme template:** `lms_course/templates/lms-admin-content.html.twig` (hook: `lms_admin_content`); partial: `lms-admin-content-table.html.twig` (hook: `lms_admin_content_table`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache
**Related entities:** [`lms_faq`](../../C04-entities/), `taxonomy_term` (faq_category), `lms_course`

---

## Page B: FAQ Create — `/workspace/content/faqs/add`

**Canonical URL:** `/workspace/content/faqs/add` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.workspace_faq.add`
**Solution:** `WorkspaceContentController::faqCreateForm()` creates a new `lms_faq` entity and embeds the entity form in the `lms_admin_entity_form` workspace wrapper template.
**Owner module:** `lms_course` — `src/Controller/WorkspaceContentController::faqCreateForm()`
**Primary source:** New `lms_faq` entity (unsaved)
**Editable source:** `lms_faq` fields — `question`, `field_answer`, `field_scope`, `field_course`, `field_category`, `weight`
**Editor role:** `administer lms_course`
**Editor management UI:** `/workspace/content/faqs/add` (this page)
**Theme template:** `lms_course/templates/lms-admin-entity-form.html.twig` (hook: `lms_admin_entity_form`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache
**Related entities:** [`lms_faq`](../../C04-entities/), `taxonomy_term` (faq_category), `lms_course`

---

## Page C: FAQ Edit — `/workspace/content/faqs/{lms_faq}/edit`

**Canonical URL:** `/workspace/content/faqs/{lms_faq}/edit` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.workspace_faq.edit`
**Solution:** `WorkspaceContentController::faqEditForm()` loads the `lms_faq` entity and embeds the entity form in the `lms_admin_entity_form` wrapper. Uses core `ContentEntityForm` (no custom form class on `lms_faq`).
**Owner module:** `lms_course` — `src/Controller/WorkspaceContentController::faqEditForm()`
**Primary source:** `lms_faq` entity (existing)
**Editable source:** All `lms_faq` base fields
**Editor role:** `administer lms_course`
**Editor management UI:** `/workspace/content/faqs/{lms_faq}/edit` (this page)
**Theme template:** `lms_course/templates/lms-admin-entity-form.html.twig` (hook: `lms_admin_entity_form`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache
**Related entities:** [`lms_faq`](../../C04-entities/), `taxonomy_term` (faq_category), `lms_course`

---

## Page D: FAQ View — `/workspace/content/faqs/{lms_faq}/view`

**Canonical URL:** `/workspace/content/faqs/{lms_faq}/view` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.workspace_faq.view`
**Solution:** `WorkspaceContentController::faqView()` calls the private `generatePremiumPreview()` helper which auto-detects image fields and renders all entity fields with fallbacks; wrapped in `lms_admin_entity_view` template.
**Owner module:** `lms_course` — `src/Controller/WorkspaceContentController::faqView()`
**Primary source:** `lms_faq` entity (existing)
**Editable source:** `lms_faq` entity (read-only preview; edit at Page C)
**Editor role:** `administer lms_course`
**Editor management UI:** Link to `/workspace/content/faqs/{id}/edit`
**Theme template:** `lms_course/templates/lms-admin-entity-view.html.twig` (hook: `lms_admin_entity_view`)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache
**Related entities:** [`lms_faq`](../../C04-entities/)
**Notes:** `generatePremiumPreview()` iterates all entity fields; renders each with text/entity_reference type fallbacks. There is no dedicated FAQ view template — the generic entity view wrapper is used.

---

## Components (visual order, top-to-bottom)

> Global chrome is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Shared across all workspace pages
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
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
- **Notes:** FAQ management is typically accessible via a "Content" or "FAQs" link in the sidebar.
- **How to implement (EN):** Include `lms-admin-sidebar.html.twig` in the content list and form templates.
- **How to implement (VI):** Nhúng sidebar dùng chung vào các template danh sách và form.
- **Solution Architect Request:** —

### 2. Page Topbar (List page — Page A)

- **Scope:** Page A (FAQ list)
- **Implementation mechanism:** Twig block in `lms-admin-content.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static title; "+ Add FAQ" button links to `/workspace/content/faqs/add`
- **Backed by fields / config keys:** Static
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** n/a
- **View mode / Projection:** Topbar strip
- **Region / Placement:** Top of `.app-main`
- **Notes:** Title dynamically set based on `content_type` param — for FAQs shows "Manage FAQs" or similar.
- **How to implement (EN):** `WorkspaceContentController::contentList()` passes a `content_type_label` variable to the template based on the `content_type` route param.
- **How to implement (VI):** Controller truyền nhãn loại nội dung vào template để hiển thị tiêu đề phù hợp.
- **Solution Architect Request:** —

### 3. FAQ Data Table (Page A)

- **Scope:** Page A (FAQ list)
- **Implementation mechanism:** Twig table from `content_items` array passed by `WorkspaceContentController::contentList()`; AJAX partial `lms_admin_content_table`; bulk action support via POST to `lms_course.workspace_content.bulk`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_faq` entity storage
- **Backed by fields / config keys:** `lms_faq.question` (label), `lms_faq.field_scope`, `lms_faq.field_category`, `lms_faq.field_course`, `lms_faq.weight`
- **Editable source:** `lms_faq` entities; edit at `/workspace/content/faqs/{id}/edit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** Row-level Edit, View, Delete links
- **Revision / moderation:** No
- **Translation source:** Content translation (question, field_answer translatable)
- **Edit smoke:** Create a FAQ → verify it appears in list with correct scope and category
- **View mode / Projection:** HTML table
- **Region / Placement:** Main content area
- **Notes:** `lms_faq` has NO `status` field — there is no publish/unpublish for FAQs. Bulk actions (publish/unpublish) that exist for node content types are NOT applicable here. The `bulkAction()` method uses direct DB update for nodes — it is not called for `lms_faq`. Rows show: Question (truncated), Scope badge (global/course/lesson), Category (faq_category term), Course (if scope=course), Weight, Actions.
- **How to implement (EN):** `WorkspaceContentController::contentList()` queries `lms_faq` storage (not node storage) when `content_type=faqs`. Load `field_scope`, `field_category`, `field_course` for each FAQ. Pass as `content_items` to template.
- **How to implement (VI):** Khi `content_type=faqs`, controller query `lms_faq` storage; tải scope, category, course cho mỗi FAQ; truyền vào template danh sách.
- **Solution Architect Request:** —

### 4. FAQ Create/Edit Form (Pages B and C)

- **Scope:** Page B (create) and Page C (edit)
- **Implementation mechanism:** Drupal core `ContentEntityForm` (no custom form class on lms_faq); embedded in `lms_admin_entity_form` workspace wrapper by `faqCreateForm()` / `faqEditForm()`
- **Plugin / Module:** `lms_course` (controller + wrapper template); `lms_faq` (entity fields)
- **Source content type / Entity:** `lms_faq` entity (new or existing)
- **Backed by fields / config keys:**
  - `lms_faq.question` (string, max 512, required) — the entity label
  - `lms_faq.field_answer` (text_long, required) — rich text answer body
  - `lms_faq.field_scope` (list_string: global/course/lesson, default=global) — determines display context
  - `lms_faq.field_course` (entity_reference→lms_course) — shown when scope=course or lesson
  - `lms_faq.field_category` (entity_reference→taxonomy_term, bundle=faq_category) — FAQ category grouping
  - `lms_faq.weight` (integer, default=0) — display order
- **Editable source:** `lms_faq` entity fields as above
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs/add` (create) or `/workspace/content/faqs/{id}/edit` (edit)
- **Revision / moderation:** No (`lms_faq` has no `status` field and is not revisionable)
- **Translation source:** Content translation (question, field_answer translatable)
- **Edit smoke (create):** Fill question + answer + scope → save → verify appears in FAQ list
- **Edit smoke (edit):** Change answer text → save → verify updated on course detail page FAQ accordion
- **View mode / Projection:** Drupal entity form in workspace wrapper
- **Region / Placement:** Main content area (within `lms_admin_entity_form` template)
- **Notes:**
  - `field_scope=global` → FAQ appears on homepage (loaded by `CoursePublicController::homepage()`).
  - `field_scope=course` → FAQ appears on course detail page filtered by `field_course` reference.
  - `field_scope=lesson` → Reserved scope (not currently consumed in as-built frontend controllers).
  - No `status` field: FAQs are always visible once created. No draft/unpublish capability.
  - No custom form class: `ContentEntityForm` core default. No AJAX or conditional field logic implemented (unlike `LessonForm`). Consider adding `#states` to show `field_course` only when scope ≠ global.
  - `field_category` options come from the `faq_category` taxonomy vocabulary.
- **How to implement (EN):** `faqCreateForm()` creates a new `lms_faq` entity via entity type manager, calls `getFormObject('default')`, builds the Drupal form, and returns it inside `lms_admin_entity_form` render array. `faqEditForm()` loads the existing entity and does the same. No custom form class is needed unless conditional field visibility is added.
- **How to implement (VI):** `faqCreateForm()` tạo entity `lms_faq` mới, gọi form object mặc định, nhúng vào wrapper template. `faqEditForm()` tải entity hiện có. Không cần form class tùy chỉnh trừ khi cần ẩn/hiện field theo scope.
- **Solution Architect Request:** —

### 5. FAQ Preview (Page D)

- **Scope:** Page D (FAQ view)
- **Implementation mechanism:** `WorkspaceContentController::faqView()` → `generatePremiumPreview()` → `lms_admin_entity_view` wrapper template
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_faq` entity
- **Backed by fields / config keys:** All `lms_faq` fields rendered iteratively
- **Editable source:** `lms_faq` entity (read-only in this view; edit link provided)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** Edit button links to `/workspace/content/faqs/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A (read-only preview)
- **Edit smoke:** Verify question and answer text are displayed correctly in preview
- **View mode / Projection:** Generic entity preview (`lms_admin_entity_view` wrapper)
- **Region / Placement:** Main content area
- **Notes:** `generatePremiumPreview()` auto-detects image fields and renders each field with text/entity_reference fallbacks. This is a generic preview helper — not a styled FAQ-specific template. The preview shows raw field output rather than the styled accordion seen on the public course page.
- **How to implement (EN):** `faqView()` loads the entity; calls `generatePremiumPreview($entity)` which iterates `$entity->getFields()`, renders each field value, and returns a render array. Wrap in `lms_admin_entity_view` template with an "Edit" link.
- **How to implement (VI):** `faqView()` tải entity; gọi `generatePremiumPreview()` lặp qua các trường và render; bọc trong template view với liên kết chỉnh sửa.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar | Nav links | Static template | Hardcoded | Developer | Developer | No | No | None | Visual |
| Topbar | "Manage FAQs" title, "+ Add FAQ" button | Static + route param | content_type_label | N/A | N/A | No | No | None | Static |
| FAQ Data Table | Question (truncated), scope badge, category, course ref, weight, actions | `lms_faq` entity storage | question, field_scope, field_category, field_course, weight | `/workspace/content/faqs/{id}/edit` | `administer lms_course` | No | Yes | No-cache | Create FAQ → check list |
| FAQ Form (create/edit) | Question input, answer rich text, scope select, course ref (conditional), category ref, weight | `lms_faq` entity | question, field_answer, field_scope, field_course, field_category, weight | `/workspace/content/faqs/add` or `/{id}/edit` | `administer lms_course` | No | Yes | No-cache | Edit answer → reload course detail → verify FAQ text |
| FAQ Preview | All FAQ fields rendered | `lms_faq` entity | All fields | `/workspace/content/faqs/{id}/edit` (link) | `administer lms_course` | No | No | No-cache | Verify question + answer visible |
| faq_category terms | Category dropdown options in form | `taxonomy_term` (faq_category vocabulary) | term.name | `/admin/structure/taxonomy/manage/faq_category/overview` | `administer taxonomy` | No | No | Per-vocabulary tag | Add term → check form dropdown |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| `field_scope` allowed values (global, course, lesson) | Defined in entity `baseFieldDefinitions()` as list_string options | Architecture decision | Revisit if new FAQ scopes are needed (e.g. per-quiz scope) |
| No publish/unpublish | `lms_faq` entity has no `status` field — visibility is all-or-nothing | Known limitation of entity design | Add `status` field if draft FAQs are needed |
| `field_scope=lesson` not consumed on frontend | Scope reserved but `CoursePublicController` does not render lesson-scoped FAQs | Known gap | Implement when lesson-level FAQ rendering is required |

## Implementation status

| Component | Status | Notes |
|---|---|---|
| FAQ list route + controller | DONE | `lms_course.workspace_content.list` with `content_type=faqs` param |
| FAQ Data Table | DONE | `lms_admin_content` / `lms_admin_content_table` templates |
| FAQ Create route + controller | DONE | `lms_course.workspace_faq.add` → `faqCreateForm()` |
| FAQ Edit route + controller | DONE | `lms_course.workspace_faq.edit` → `faqEditForm()` |
| FAQ View route + controller | DONE | `lms_course.workspace_faq.view` → `faqView()` → `generatePremiumPreview()` |
| FAQ form (ContentEntityForm) | DONE | Core default; no custom form class on lms_faq |
| Conditional field_course visibility | NOT_IMPLEMENTED | `field_course` always shown in form regardless of scope; `#states` not applied |
| lesson-scope FAQ frontend rendering | NOT_IMPLEMENTED | CoursePublicController loads global + course-scoped FAQs only |
