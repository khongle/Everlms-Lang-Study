---
page: quiz-result
mode: as-built
owner_module: lms_quiz
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Quiz Result

**Canonical URL:** `/quiz-result/{lms_quiz_result}` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_quiz.result`
**Solution:** Custom controller (`QuizController::viewResult`) upcasts `lms_quiz_result` entity, builds response review array, returns `lms_quiz_result` theme render array.
**Owner module:** [`lms_quiz`](../../C05-modules/lms_quiz.md)
**Primary source:** `entity:lms_quiz_result` (upcasted route param `{lms_quiz_result}`)
**Editable source:** `lms_quiz` entity (pass threshold, show-correct-answers flag); `lms_question_response` entities (teacher feedback); `taxonomy_term:quiz_results` vocabulary (Passed/Failed/Pending labels)
**Editor role:** Instructor (`administer lms_course`) for feedback; system for result creation
**Editor management UI:** `/manage/quiz-results/{id}` (admin entity canonical)
**Revision / moderation:** Not revisionable
**Forms:** — (read-only result view; no inline form)
**Theme template:** `web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** No-cache — per-user result entity
**Translation:** Monolingual EN
**Related entities:** [`lms_quiz_result`](../../C04-entities/README.md), [`lms_quiz`](../../C04-entities/README.md), [`lms_question_response`](../../C04-entities/README.md), `taxonomy_term:quiz_results`
**Prototype reference:** [`../../C10-prototypes/html/learn/quiz-result.html`](../../C10-prototypes/html/learn/quiz-result.html)
**Notes:** Template displays pass/fail using `score >= 70` as a fallback threshold (hardcoded in template), independent of the quiz entity's actual `field_threshold` — known bug. Essay responses always show `pending_review=TRUE`; no manual grading UI exists yet (target-state). "Continue Learning" link is hardcoded to `/user/portal` in template — should resolve to the enrollments page.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. Score Circle / Result Hero

- **Scope:** Page-specific
- **Implementation mechanism:** Twig variable `score` rendered as CSS animated circle; pass/fail styling derived from `result_label` and hardcoded `>= 70` check in template
- **Plugin / Module:** `lms_quiz` — `lms-quiz-result.html.twig`
- **Source content type / Entity:** `lms_quiz_result`
- **Backed by fields / config keys:** `field_score` (decimal %, 0-100), `field_result` (term ref: Passed/Failed/Pending), quiz `field_threshold`
- **Editable source:** `lms_quiz_result` entity (system-created on submit); `lms_quiz.field_threshold` (editable by instructor)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/quizzes/{id}/edit` (for threshold); result is read-only post-creation
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Adjust `field_threshold` → retake quiz → confirm pass/fail determination
- **View mode / Projection:** Computed render variables: `score`, `result`, `result_label`
- **Region / Placement:** Top hero section
- **Notes:** BUG — template uses hardcoded `>= 70` for CSS class; actual grading uses `field_threshold`. These can diverge. Pass = green circle; Fail = red; Pending = amber.
- **How to implement (EN):** Pass `score` (float), `result` (term entity), `result_label` (term name string), and `quiz.field_threshold` to the template. Template should use `quiz.field_threshold` (not a hardcoded 70) to determine CSS class.
- **How to implement (VI):** Truyền `score` (float), `result` (term entity), `result_label` (tên term), và `quiz.field_threshold` vào template. Template nên dùng `quiz.field_threshold` (không hardcode 70) để xác định class CSS.
- **Solution Architect Request:** —

### 2. Per-Response Breakdown

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over `responses` array (built by controller from `lms_question_response` entities)
- **Plugin / Module:** `lms_quiz` — `QuizController::viewResult` + `lms-quiz-result.html.twig`
- **Source content type / Entity:** `lms_question_response`
- **Backed by fields / config keys:** `field_question` (lms_question ref), `field_correct` (bool), `field_score`, `field_text_answer`, `field_pending_review`, `field_teacher_feedback`, `field_answers` (selected choice refs)
- **Editable source:** `field_teacher_feedback` on `lms_question_response` (editable by instructor); all other fields are system-set on submit
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/manage/quiz-results/{result_id}` → inline response edit (no dedicated form; admin entity form only)
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Add teacher feedback to essay response → reload result page → confirm feedback shown
- **View mode / Projection:** Custom array per response: `question_text`, `is_correct`, `score`, `pending`, `choices_view`, `text_answer`, `teacher_feedback`
- **Region / Placement:** Main content area, below hero
- **Notes:** Shows tick/cross/clock icon per response. When `field_display_right_answer=TRUE` on quiz, correct choices highlighted. Pending essay responses show amber state with "Awaiting Review" label.
- **How to implement (EN):** Controller loads `field_responses` → builds response array with question text, choice labels, correctness. Template iterates with `{% for response in responses %}`.
- **How to implement (VI):** Controller load `field_responses` → xây dựng mảng response với text câu hỏi, nhãn lựa chọn, đúng/sai. Template lặp qua `{% for response in responses %}`.
- **Solution Architect Request:** —

### 3. Teacher Feedback Display (per essay response)

- **Scope:** Page-specific
- **Implementation mechanism:** Conditional Twig block within response loop
- **Plugin / Module:** `lms_quiz` — `lms-quiz-result.html.twig`
- **Source content type / Entity:** `lms_question_response.field_teacher_feedback`
- **Backed by fields / config keys:** `field_teacher_feedback` (text_long)
- **Editable source:** `lms_question_response` entity
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/manage/quiz-results/{id}` (admin entity form)
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Write feedback on essay response → reload page → confirm feedback text visible
- **View mode / Projection:** Plain text block, shown only when `field_teacher_feedback` is non-empty
- **Region / Placement:** Within per-response card
- **Notes:** No dedicated instructor grading UI exists. Feedback must be entered via `/manage/quiz-results` admin form — not learner-facing.
- **How to implement (EN):** `{% if response.teacher_feedback %}` block within the response card.
- **How to implement (VI):** Block `{% if response.teacher_feedback %}` trong thẻ response.
- **Solution Architect Request:** —

### 4. Action Links (Continue Learning / Retake)

- **Scope:** Page-specific
- **Implementation mechanism:** Static anchor tags in template
- **Plugin / Module:** `lms_quiz` — `lms-quiz-result.html.twig`
- **Source content type / Entity:** Static / computed
- **Backed by fields / config keys:** Hardcoded `/user/portal` for "Continue Learning" (known bug)
- **Editable source:** computed/read-only (static link, not entity-backed)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Button row below responses
- **Region / Placement:** Bottom of page content area
- **Notes:** BUG — "Continue Learning" links to `/user/portal` which is not a valid route in this codebase (portal is at `/my-courses`). Should link to `/my-courses` or the originating lesson.
- **How to implement (EN):** Replace hardcoded `/user/portal` with `path('lms_progress.my_courses')`. If quiz came from a lesson context, resolve and pass the lesson URL from the controller.
- **How to implement (VI):** Thay link hardcoded `/user/portal` bằng `path('lms_progress.my_courses')`. Nếu quiz đến từ bài học, truyền URL bài học từ controller.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Score Circle / Result Hero | Score %, pass/fail label, result badge | `lms_quiz_result`, `taxonomy_term:quiz_results` | `field_score`, `field_result`, `result_label` | `/manage/quiz-results/{id}` (read-only post-creation) | system-created; `administer lms_course` to view | N/A | Monolingual EN | no-cache / per-user | Submit quiz → confirm score displayed |
| Per-Response Breakdown | Question text, selected answer(s), correct/incorrect indicator, score per question | `lms_question_response`, `lms_question` | `field_question`, `field_correct`, `field_score`, `field_answers`, `field_text_answer`, `field_pending_review` | `/manage/quiz-results/{id}` | `administer lms_course` | N/A | Monolingual EN | no-cache | Submit quiz → confirm each response shown |
| Teacher Feedback | Instructor's written feedback on essay responses | `lms_question_response.field_teacher_feedback` | `field_teacher_feedback` | `/manage/quiz-results/{id}` | `administer lms_course` | N/A | Monolingual EN | no-cache | Add feedback → reload → confirm visible |
| Action Links | "Continue Learning" and "Retake Quiz" buttons | Static template chrome | Hardcoded `/user/portal` (bug) | computed/read-only | N/A | N/A | N/A | N/A | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Pass/fail threshold in template (`score >= 70`) | Bug — should use `quiz.field_threshold`; template uses hardcoded value | Known bug — see E02-issues | Fix: pass `quiz.field_threshold` to template and use it for CSS class determination |
| "Continue Learning" URL (`/user/portal`) | Hardcoded in template; `/user/portal` is not a valid route | Known bug — see E02-issues | Fix: replace with `path('lms_progress.my_courses')` |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Score Circle / Result Hero | DONE | — | As-built; styling works; threshold display bug logged |
| Per-Response Breakdown | DONE | — | As-built; all response types rendered |
| Teacher Feedback Display | DONE | — | As-built; conditional display; no dedicated grading UI |
| Action Links | DONE (with bugs) | — | "Continue Learning" links to invalid route; retake functional |
| Essay pending review flow | PARTIAL | — | Display works; no instructor grading UI in learner/workspace area |
