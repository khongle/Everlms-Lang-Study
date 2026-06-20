---
page: manage-quizzes
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Quizzes

**Canonical URL:** `/workspace/quizzes` *(per [`../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_quizzes`
**Solution:** Custom controller (`AdminController::quizList`) renders `lms_admin_quizzes` Twig template; sub-routes for create/edit use `lms_admin_entity_form` wrapper template.
**Owner module:** `lms_course` (controller/routing); entity owned by [`lms_quiz`](../../C05-modules/lms_quiz.md)
**Primary source:** `entity:lms_quiz`
**Editable source:** `lms_quiz` entity — fields: `name`, `field_body`, `field_questions`, `field_threshold`, `field_time_limit`, `field_display_right_answer`, `field_form_style`, `field_question_count`, `field_take_quiz_number`, `field_prerequisite_quizzes`, `status`
**Editor role:** Instructor / Administrator
**Editor management UI:** `/workspace/quizzes` (list), `/workspace/quizzes/create` (create), `/workspace/quizzes/{id}/edit` (edit)
**Revision / moderation:** Not revisionable; no workflow.
**Forms:** `QuizForm` (Drupal\lms_quiz\Form\QuizForm) — used for both create and edit; handled by `AdminController::quizCreateForm` and `AdminController::quizEditForm`
**Theme template:** `lms_course/templates/lms-admin-quizzes.html.twig` (list); `lms_course/templates/lms-admin-entity-form.html.twig` (create/edit)
**Access:** Role-gated: `administer lms_course` (list, create); `entity_access:lms_quiz.update` (edit)
**Cache:** Per-user; no anonymous caching. Invalidated by `lms_quiz_list` tags on quiz entity save/delete.
**Translation:** Monolingual EN
**Related entities:** [`lms_quiz`](../../C04-entities/01-content-entities.md), [`lms_question`](../../C04-entities/01-content-entities.md), [`lms_quiz_result`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/manage/quizzes.html`](../../C10-prototypes/html/manage/quizzes.html)
**Notes:** The workspace quiz UI is entirely custom — it bypasses Drupal core admin views. Quiz questions are managed inline via `inline_entity_form` (contrib required). `field_prerequisite_quizzes` was added via `lms_quiz_update_10001`. Sub-routes `/workspace/quizzes/create` and `/workspace/quizzes/{id}/edit` share the `lms_admin_entity_form` wrapper.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Global chrome (workspace)
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig` included inside `lms-admin-quizzes.html.twig`
- **Plugin / Module:** `lms_course` (no block plugin — direct include)
- **Source content type / Entity:** Static nav links (workspace menu items)
- **Backed by fields / config keys:** Static template markup; active state driven by current route
- **Editable source:** Static template — developer-owned; no editor-managed source
- **Editor role / permission:** N/A — developer change only
- **Editor management UI:** `«static — edit template»`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** N/A
- **Region / Placement:** Left sidebar
- **Notes:** Shared across all `/workspace/*` pages

### 2. Quiz List Header + Stats Bar

- **Scope:** Page-specific
- **Implementation mechanism:** Twig template section (`lms-admin-quizzes.html.twig`)
- **Plugin / Module:** `lms_course` / `AdminController::quizList`
- **Source content type / Entity:** `lms_quiz` (aggregate counts)
- **Backed by fields / config keys:** COUNT queries on `lms_quiz` table; `status`, `field_time_limit`, `field_take_quiz_number`
- **Editable source:** `lms_quiz` entity fields (indirectly — count reflects saved data)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/quizzes`
- **Revision / moderation:** N/A (read-only aggregate)
- **Translation source:** N/A
- **Edit smoke:** Create/delete a quiz → stat counts change on reload
- **View mode / Projection:** Aggregate summary
- **Region / Placement:** Top of content area
- **Notes:** Stats are computed on each page load; no cache tag invalidation for counters

### 3. Quiz List Table

- **Scope:** Page-specific
- **Implementation mechanism:** Twig template — `lms-admin-quizzes.html.twig`; AJAX partial `lms-admin-courses-table.html.twig` pattern may apply (see note)
- **Plugin / Module:** `lms_course` / `AdminController::quizList`
- **Source content type / Entity:** `lms_quiz` entity
- **Backed by fields / config keys:** `name`, `field_threshold`, `field_time_limit`, `field_question_count`, `field_take_quiz_number`, `status`, `created`; question count via `field_questions` reference
- **Editable source:** `lms_quiz` entity via `/workspace/quizzes/{id}/edit`
- **Editor role / permission:** `administer lms_course`; `entity_access:lms_quiz.update` (edit link)
- **Editor management UI:** `/workspace/quizzes/{id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Edit quiz name → reload list → row shows updated name
- **View mode / Projection:** Table row (id, name, questions, threshold, time, status, actions)
- **Region / Placement:** Main content area
- **Notes:** Edit/delete action links per row. Table may reload via HTMX partial on filter; exact AJAX implementation in `lms-admin-quizzes.html.twig`.

### 4. Create Quiz Button / Form

- **Scope:** Page-specific
- **Implementation mechanism:** Link to `/workspace/quizzes/create` → `AdminController::quizCreateForm` → `QuizForm` wrapped in `lms-admin-entity-form.html.twig`
- **Plugin / Module:** `lms_course` + `lms_quiz`
- **Source content type / Entity:** `lms_quiz` (new entity)
- **Backed by fields / config keys:** All `lms_quiz` base fields; `field_questions` uses `inline_entity_form` widget referencing `lms_question` entities
- **Editable source:** `lms_quiz` entity
- **Editor role / permission:** `entity_create_access:lms_quiz`
- **Editor management UI:** `/workspace/quizzes/create`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Fill form → submit → quiz appears in list
- **View mode / Projection:** Form
- **Region / Placement:** Modal or full page (per `lms-admin-entity-form.html.twig` context)
- **Notes:** `QuizForm` handles 7 form-op variants via the same class. Prerequisite quizzes widget references `lms_quiz` itself (self-reference). `inline_entity_form` contrib required for question widget.

### 5. Edit Quiz Form

- **Scope:** Page-specific
- **Implementation mechanism:** `AdminController::quizEditForm` → `QuizForm` wrapped in `lms-admin-entity-form.html.twig`; route `/workspace/quizzes/{lms_quiz}/edit`
- **Plugin / Module:** `lms_course` + `lms_quiz`
- **Source content type / Entity:** `lms_quiz` (existing entity, upcasted)
- **Backed by fields / config keys:** Same as Create; entity is upcasted from route param
- **Editable source:** `lms_quiz` entity via form
- **Editor role / permission:** `entity_access:lms_quiz.update`
- **Editor management UI:** `/workspace/quizzes/{id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Change `field_threshold` → save → reload edit form → value persists
- **View mode / Projection:** Form
- **Region / Placement:** Full page with sidebar
- **Notes:** Same `QuizForm` class as create. Grading uses `GradingStrategyFactory` (static PHP factory in `lms_quiz/src/Grading/`) — quiz form itself has no grading logic.

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar Nav | Nav links, active highlight | Static template | Static markup | Developer only | N/A | N/A | N/A | None | N/A |
| Stats Bar | Total quizzes, active count, avg questions | `lms_quiz` aggregate | `status`, `field_questions` | `/workspace/quizzes` | `administer lms_course` | No | EN only | Per-request | Verify counts match DB |
| Quiz List Table | Quiz name, question count, threshold, time limit, status, created date, edit/delete links | `entity:lms_quiz` | `name`, `field_threshold`, `field_time_limit`, `field_question_count`, `status`, `created` | `/workspace/quizzes/{id}/edit` | `administer lms_course` | No | EN only | Per-request | Edit name → verify in list |
| Create Form | All quiz fields incl. inline question widget | `entity:lms_quiz` new | All `lms_quiz` base fields | `/workspace/quizzes/create` | `entity_create_access:lms_quiz` | No | EN only | Per-request | Create → appears in list |
| Edit Form | All quiz fields (pre-filled) | `entity:lms_quiz` existing | All `lms_quiz` base fields | `/workspace/quizzes/{id}/edit` | `entity_access:lms_quiz.update` | No | EN only | Per-request | Edit threshold → persists |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Sidebar nav link labels | Developer-owned workspace chrome | Architecture decision — workspace UI is code-only | Revisit if workspace nav becomes config-driven |
| Grading strategy labels (single_choice, etc.) | Enum in `GradingStrategyFactory` / PHP `match` | Code-level constant; no editor involvement | N/A |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Workspace Sidebar Nav | DONE | — | Shared include across workspace |
| Stats Bar | DONE | — | Rendered in `lms-admin-quizzes.html.twig` |
| Quiz List Table | DONE | — | `AdminController::quizList` is fully implemented |
| Create Quiz Form | DONE | — | `AdminController::quizCreateForm` → `QuizForm` |
| Edit Quiz Form | DONE | — | `AdminController::quizEditForm` → `QuizForm` |
