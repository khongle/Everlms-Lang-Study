---
page: quiz-take
mode: as-built
owner_module: lms_quiz
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Take Quiz

**Canonical URL:** `/quiz/{lms_quiz}/take` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_quiz.take`
**Solution:** Custom controller (`QuizController::takeQuiz`) returns a `lms_quiz_take` theme render array; all question rendering + JS logic embedded in a single Twig template.
**Owner module:** [`lms_quiz`](../../C05-modules/lms_quiz.md)
**Primary source:** `entity:lms_quiz` (upcasted route param `{lms_quiz}`)
**Editable source:** `lms_quiz` entity fields; `lms_question` entities referenced via `field_questions`
**Editor role:** Instructor / `administer lms_course`
**Editor management UI:** `/workspace/quizzes/{lms_quiz}/edit`
**Revision / moderation:** Not revisionable — no workflow
**Forms:** Inline HTML `<form>` (not a Drupal Form API form) — submits POST to `lms_quiz.submit` (`/quiz/{lms_quiz}/submit`)
**Theme template:** `web/modules/custom/lms_quiz/templates/lms-quiz-take.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** No-cache — per-user attempt gating; prerequisite/attempt checks done fresh per request
**Translation:** Monolingual EN
**Related entities:** [`lms_quiz`](../../C04-entities/README.md), [`lms_question`](../../C04-entities/README.md), [`lms_quiz_result`](../../C04-entities/README.md)
**Prototype reference:** [`../../C10-prototypes/html/learn/quiz.html`](../../C10-prototypes/html/learn/quiz.html)
**Notes:** Prerequisite quiz check (all `field_prerequisite_quizzes` must have a `Passed` result) and max-attempt check run in controller before rendering. Random question subset drawn when `field_question_count > 0`. `field_start_time` on the resulting `lms_quiz_result` is hardcoded to `now - 60` (known bug — not tracked from page load).

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. Quiz Header Block

- **Scope:** Page-specific
- **Implementation mechanism:** Twig variables rendered inline in `lms-quiz-take.html.twig`
- **Plugin / Module:** `lms_quiz` — `QuizController::takeQuiz`
- **Source content type / Entity:** `lms_quiz`
- **Backed by fields / config keys:** `name`, `field_body` (instructions), `field_time_limit`, `field_threshold`, `field_form_style`, `field_question_count`
- **Editable source:** `lms_quiz` entity
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/quizzes/{lms_quiz}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Edit quiz name/instructions → save → reload quiz page → confirm updated title and instructions visible
- **View mode / Projection:** Computed variables passed to template
- **Region / Placement:** Top of page content area
- **Notes:** `field_time_limit=0` means unlimited; timer is only shown when `field_time_limit > 0`. Timer uses localStorage to survive page refresh.
- **How to implement (EN):** In `QuizController::takeQuiz`, pass `quiz` entity, `time_limit` (from `field_time_limit`), and `questions` array as theme variables.
- **How to implement (VI):** Trong `QuizController::takeQuiz`, truyền entity `quiz`, `time_limit` (từ `field_time_limit`) và mảng `questions` vào biến template.
- **Solution Architect Request:** —

### 2. Countdown Timer (conditional)

- **Scope:** Page-specific
- **Implementation mechanism:** Inline `<script>` in template; localStorage-backed countdown rendered in a `#quiz-timer` div
- **Plugin / Module:** `lms_quiz` — `lms-quiz-take.html.twig`
- **Source content type / Entity:** `lms_quiz.field_time_limit`
- **Backed by fields / config keys:** `field_time_limit` (minutes; 0 = disabled)
- **Editable source:** `lms_quiz` entity field `field_time_limit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/quizzes/{lms_quiz}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Set `field_time_limit > 0` → take quiz → confirm countdown display
- **View mode / Projection:** Rendered only when `time_limit > 0`
- **Region / Placement:** Sticky header or quiz header bar
- **Notes:** Auto-submits form when timer hits zero via JS `form.submit()`.
- **How to implement (EN):** Template checks `{% if time_limit > 0 %}` and renders timer div; JS initialises from `{{ time_limit * 60 }}` seconds stored in `localStorage['quiz_timer_{{ quiz.id }}']`.
- **How to implement (VI):** Template kiểm tra `{% if time_limit > 0 %}` và hiển thị div đếm ngược; JS khởi tạo từ `{{ time_limit * 60 }}` giây, lưu vào `localStorage`.
- **Solution Architect Request:** —

### 3. Question List / Stepper

- **Scope:** Page-specific
- **Implementation mechanism:** Twig `for` loop over `questions` array; JS-controlled multi-step when `field_form_style = question_step`
- **Plugin / Module:** `lms_quiz` — `QuizController::takeQuiz` + `lms-quiz-take.html.twig`
- **Source content type / Entity:** `lms_question` (8 bundles)
- **Backed by fields / config keys:** `name` (question text), `field_body`, `field_points`, `field_choices` (single/multiple_choice), `field_correct_answer` (true_false), `field_correct_answers` (fill_blank/short_answer), `field_matching_pairs` (matching), `field_ordered_items` (ordering), `field_min_words`/`field_max_words` (essay)
- **Editable source:** `lms_question` entities
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/manage/questions/{id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Edit question text → save → take quiz → confirm updated question text
- **View mode / Projection:** Custom view-model array built in controller per bundle type
- **Region / Placement:** Main content area
- **Notes:** All 8 question types rendered by a single template with `{% if %}` branches per bundle. Drag-and-drop ordering uses vanilla JS. Draft auto-save to localStorage keyed `quiz_draft_{{ quiz.id }}`. `field_form_style = one_page` renders all at once; `question_step` shows one at a time via JS.
- **How to implement (EN):** Controller builds `questions` array with `type`, `choices`, `pairs`, `items` etc. per bundle. Template branches on `question.type` to render appropriate input.
- **How to implement (VI):** Controller xây dựng mảng `questions` với `type`, `choices`, `pairs`, `items`... tùy theo bundle. Template phân nhánh theo `question.type` để hiển thị input phù hợp.
- **Solution Architect Request:** —

### 4. Submit Confirmation Modal

- **Scope:** Page-specific
- **Implementation mechanism:** Inline HTML `<dialog>` or overlay div; triggered by JS before form submit
- **Plugin / Module:** `lms_quiz` — `lms-quiz-take.html.twig`
- **Source content type / Entity:** Static UI chrome
- **Backed by fields / config keys:** Static text
- **Editable source:** computed/read-only — static developer-owned UI string
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Modal overlay
- **Region / Placement:** Full-page overlay on submit intent
- **Notes:** Prevents accidental submission; JS intercepts form `submit` event and shows modal; user confirms to proceed.
- **How to implement (EN):** Add a `<div id="confirm-modal">` with confirm/cancel buttons. JS: `form.addEventListener('submit', e => { e.preventDefault(); showModal(); })`. Cancel re-enables form; confirm calls `form.submit()`.
- **How to implement (VI):** Thêm `<div id="confirm-modal">` với nút xác nhận/hủy. JS: `form.addEventListener('submit', e => { e.preventDefault(); showModal(); })`. Hủy → giữ form; xác nhận → `form.submit()`.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Quiz Header Block | Quiz name, instructions, pass threshold, attempt limit, form style | `lms_quiz` entity | `name`, `field_body`, `field_threshold`, `field_take_quiz_number`, `field_form_style` | `/workspace/quizzes/{id}/edit` | `administer lms_course` | N/A | Monolingual EN | no-cache (per-user) | Edit quiz name → reload page → confirm |
| Countdown Timer | Remaining time (MM:SS) | `lms_quiz.field_time_limit` | `field_time_limit` | `/workspace/quizzes/{id}/edit` | `administer lms_course` | N/A | Monolingual EN | localStorage | Set time limit → confirm timer visible |
| Question List | Question text, answer choices/inputs per bundle | `lms_question` (8 bundles) | `name`, `field_body`, `field_choices`, `field_matching_pairs`, `field_ordered_items`, `field_correct_answer`, `field_correct_answers` | `/manage/questions/{id}/edit` | `administer lms_course` | N/A | Monolingual EN | no-cache | Edit question → reload quiz → confirm |
| Submit Confirmation Modal | "Are you sure you want to submit?" static text | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Submit modal confirmation text | Short developer-owned UI chrome; low business value | Developer convention | Revisit if i18n needed |
| "Continue Learning" fallback link (`/user/portal`) in result template | Hardcoded in `lms-quiz-result.html.twig` — known issue | Code bug / tech debt | Fix: resolve from enrollment record |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Quiz Header Block | DONE | — | As-built; all fields populated by controller |
| Countdown Timer | DONE | — | As-built; localStorage-based |
| Question List (all 8 types) | DONE | — | As-built; drag-drop ordering, draft localStorage |
| Submit Confirmation Modal | DONE | — | As-built; inline JS |
| Prerequisite quiz gate | DONE | — | Controller checks `field_prerequisite_quizzes` before rendering |
| Max attempts gate | DONE | — | Controller counts existing `lms_quiz_result` for user+quiz |
| Bug: `field_start_time` hardcoded | BUG | — | `submitQuiz` sets start_time to `now - 60`; see E02-issues |
