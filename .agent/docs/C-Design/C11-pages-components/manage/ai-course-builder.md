---
page: ai-course-builder
mode: as-built
owner_module: lms_ai
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: AI Course Builder

**Canonical URL:** `/manage/courses/ai-builder` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_ai.course_builder_form`
**Solution:** Custom controller-less form route — `CourseBuilderForm` (FormBase with AJAX submit) rendered via `_form` route key; no dedicated Twig template (Drupal default form rendering).
**Owner module:** [`lms_ai`](../../C05-modules/lms_ai.md)
**Primary source:** Form input (user-provided topic, audience, tone)
**Editable source:** `lms_course` entity (created on submit); `lms_lesson` entities (created + queued); `lms_ai.settings` config (provider, API key)
**Editor role:** Administrator, Manager, Instructor
**Editor management UI:** This page IS the management UI; course result editable at `/workspace/courses/{id}/edit`
**Revision / moderation:** N/A for the form; generated course entity: not revisionable
**Forms:** `Drupal\lms_ai\Form\CourseBuilderForm`
**Theme template:** Drupal default form wrapper (no custom Twig template for this route)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache (form page, per-user)
**Translation:** Monolingual EN
**Related entities:** [`lms_course`](../../C04-entities/01-content-entities.md), [`lms_lesson`](../../C04-entities/01-content-entities.md)
**Prototype reference:** No C10 prototype for this page.
**Notes:** Action link "✨ Build with AI" on the course collection page (`entity.lms_course.collection`) points here (weight: -10, defined in `lms_ai.links.action.yml`). No rollback — partial failures leave orphaned entities. See security risk: AI API keys stored in plaintext config YAML («secret in config — see security risk»).

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md).

### 1. Page Title — *Tiêu đề trang*

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal core `#type: page_title` block; route title set to "Build Course with AI"
- **Plugin / Module:** Drupal core
- **Source content type / Entity:** Route definition (`lms_ai.routing.yml` `_title`)
- **Backed by fields / config keys:** Route `_title: 'Build Course with AI'`
- **Editable source:** Route YAML (`web/modules/custom/lms_ai/lms_ai.routing.yml`)
- **Editor role / permission:** Developer
- **Editor management UI:** computed/read-only (route definition)
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** N/A (static string)
- **View mode / Projection:** H1 heading
- **Region / Placement:** `content` region, above form
- **Notes:** —
- **How to implement (EN):** Title set in routing.yml `_title` key.
- **How to implement (VI):** Tiêu đề được đặt trong routing.yml tại khoá `_title`.
- **Solution Architect Request:** —

---

### 2. AI Course Builder Form — *Form xây dựng khoá học bằng AI*

- **Scope:** Page-specific (primary)
- **Implementation mechanism:** `Drupal\lms_ai\Form\CourseBuilderForm` (FormBase); `_form` route key; AJAX submit via `generateAjax` callback
- **Plugin / Module:** `lms_ai` → `src/Form/CourseBuilderForm.php`
- **Source content type / Entity:** Form input → generates `lms_course` + `lms_lesson` entities; calls `CourseBuilderService::generateCourseStructure()`
- **Backed by fields / config keys:**
  - Form fields: `topic` (textfield, required), `audience` (textfield), `tone` (select: professional/casual/storytelling)
  - Config: `lms_ai.settings` → `default_provider`, `openai.api_key`, `openai.model`
  - Output: `lms_course.name`, `lms_course.field_description`, `lms_lesson.name`, lesson queue items
- **Editable source:** `lms_ai.settings` config at `/admin/config/lms/ai` (API keys, model selection)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/admin/config/lms/ai` for AI provider config; `/workspace/courses/{id}/edit` for generated course
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Fill topic "Python for Beginners" → submit → verify redirect to `/workspace/courses/{id}/edit` with draft course
- **View mode / Projection:** Standard Drupal form render
- **Region / Placement:** `content` region
- **Notes:**
  - `CourseBuilderService::generateCourseStructure(topic, targetAudience, tone)` pipeline:
    1. Calls OpenAI `generateJson()` with course schema.
    2. Creates `lms_course` entity (status=0 draft).
    3. For each lesson: creates `lms_lesson` (status=0, placeholder content); queues `lms_ai_content_generator` queue item.
    4. Returns Course entity; `generateAjax()` redirects to course edit URL on success.
  - **No rollback**: orphaned entities remain on partial failure.
  - Queue worker `CourseContentGenerator` processes lesson content asynchronously (cron, 60s time limit).
  - API provider: defaults to OpenAI (gpt-4o); configurable. Gemini also supported.
  - Security: API key stored in `lms_ai.settings` YAML (plaintext) — «secret in config — see security risk».
- **How to implement (EN):** Form defined in `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php`. Configure AI API key at `/admin/config/lms/ai`. Queue cron must run for lesson content generation to complete.
- **How to implement (VI):** Form được định nghĩa tại `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php`. Cấu hình API key tại `/admin/config/lms/ai`. Cron phải chạy để nội dung bài học được sinh ra.
- **Solution Architect Request:** —

---

### 3. Generation Progress / Status Messages — *Thông báo tiến trình*

- **Scope:** Page-specific (AJAX response)
- **Implementation mechanism:** Drupal AJAX `AjaxResponse` from `generateAjax()` — success: `RedirectCommand` to course edit; failure: `HtmlCommand` injecting error messages into `#messages` wrapper
- **Plugin / Module:** `lms_ai` → `CourseBuilderForm::generateAjax()`
- **Source content type / Entity:** System messages (Drupal messenger), computed from AI response
- **Backed by fields / config keys:** computed/read-only
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Provide invalid API key → submit → observe error message
- **View mode / Projection:** Drupal status messages
- **Region / Placement:** Above form, in `content` region
- **Notes:** No progress bar or streaming — single AJAX call with blocking AI request (60s timeout). On OpenAI timeout, user sees generic error.
- **How to implement (EN):** AJAX response in `CourseBuilderForm::generateAjax()`.
- **How to implement (VI):** Phản hồi AJAX trong `CourseBuilderForm::generateAjax()`.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Title | "Build Course with AI" | Route `_title` | Static | computed/read-only | Developer | N/A | Interface translation | N/A | N/A |
| AI Course Builder Form | Topic field, Audience field, Tone select, Submit button | Form input → `lms_course` + `lms_lesson` entities; `lms_ai.settings` config | topic, audience, tone; `openai.api_key`, `openai.model` | `/admin/config/lms/ai` (provider config); `/workspace/courses/{id}/edit` (result) | `administer lms_course` | N/A | EN only | no-cache | Fill topic → submit → observe redirect to course edit |
| Status Messages | Error / success inline messages | computed/read-only (AJAX response) | — | computed/read-only | N/A | N/A | Interface | no-cache | Invalid API key → observe error |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Tone options (professional/casual/storytelling) | Defined in PHP form; tied to AI prompt engineering | D00 code convention | When prompt strategy changes |
| "✨ Build with AI" action link label | `lms_ai.links.action.yml` static string | D00 code convention | On UX copy update |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| AI Course Builder Form | DONE | | `CourseBuilderService` + `CourseContentGenerator` queue worker fully implemented |
| No-rollback on failure | RISK | — | Orphaned entities on partial AI pipeline failure; no saga/compensation |
| Lesson content generation (queue) | DONE | — | Queue worker processes asynchronously via Drupal cron |
