---
scope: assessment-entities
mode: as-built
entities: [lms_quiz, lms_quiz_result, lms_question_response, lms_question, lms_answer, lms_matching_pair, lms_question_type]
last_updated: 2026-06-20
---

# C04 — Assessment Entities

Covers the seven assessment-layer entities: **lms_quiz**, **lms_quiz_result**, **lms_question_response**, **lms_question** (with 8 bundles), **lms_answer**, **lms_matching_pair**, and the **lms_question_type** config entity.

**Mode:** as-built — specs mirror current code and DB schema as of 2026-06-20.

Cross-references:
- Owner modules: [`lms_quiz`](../C05-modules/lms_quiz.md), [`lms_question`](../C05-modules/lms_question.md)
- Content entities: [`01-content-entities.md`](01-content-entities.md)
- Entity-module map: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)

### Grading architecture (critical context)

Quiz grading uses a **static PHP factory + strategy pattern** — NOT a Drupal Plugin API system. `GradingStrategyFactory::getStrategy(string $bundle)` in `lms_quiz/src/Grading/` returns the matching `QuestionGradingInterface` implementation via PHP `match`. All 8 strategies are **strictly binary** (full score or 0; no partial credit). Essay always sets `pending_review=TRUE` for manual grading. On quiz pass, `QuizController::submitQuiz()` dispatches `QuizPassedEvent` (`lms_quiz.passed`).

---

## Entity 1 — lms_quiz (Quiz)

---
entity: lms_quiz
mode: as-built
owner_module: lms_quiz
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_quiz` (base) + `lms_quiz_field_data` (translatable data) + junction tables  
**Description:** A quiz assigned to a Lesson (via `lms_lesson.field_quiz`). Configures question pool, pass threshold, time limit, attempt count, and display options. `QuizController` handles take/submit/result flows.  
**Owner module:** [`lms_quiz`](../C05-modules/lms_quiz.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/manage/quizzes` (collection), `/workspace/quizzes/{id}/edit` (workspace admin)  
**Create / Edit / Delete forms:** `lms_course.admin_quiz.create`, `lms_course.admin_quiz.edit`, entity delete  
**Display modes:** No YAML entity_view_display defined for lms_quiz; rendered via `lms_quiz_take`, `lms_quiz_result`, `lms_my_quiz_results` theme hooks  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Content translation; `name` and `field_body` translatable  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_quiz.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_quiz.uuid` | Unique |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_quiz.langcode` | Translation key |
| 4 | `uid` | Owner | entity_reference → user | 1 | No | current user | No | users | `lms_quiz_field_data.uid` | Quiz creator; via EntityOwnerTrait |
| 5 | `name` | Quiz Name | string | 1 | Yes | — | Yes | — | `lms_quiz_field_data.name` | max_length=255; entity label |
| 6 | `status` | Published | boolean | 1 | No | TRUE | No | — | `lms_quiz_field_data.status` (not in field_data) | Published flag; stored via base field |
| 7 | `field_body` | Instructions | text_long | 1 | No | — | Yes | — | `lms_quiz_field_data.field_body__value/format` | Shown to learner before quiz start |
| 8 | `field_questions` | Questions | entity_reference → lms_question | unlimited | No | — | No | lms_question | `lms_quiz__field_questions` junction table | Ordered question pool |
| 9 | `field_threshold` | Pass Threshold | integer | 1 | No | 70 | No | — | `lms_quiz_field_data.field_threshold` | Pass score %; integer 0-100 |
| 10 | `field_time_limit` | Time Limit | integer | 1 | No | 0 | No | — | `lms_quiz_field_data.field_time_limit` | Minutes; 0=unlimited (entity definition uses minutes; DB column is `int`) |
| 11 | `field_display_right_answer` | Show Correct Answers | boolean | 1 | No | TRUE | No | — | `lms_quiz_field_data.field_display_right_answer` | Show correct answers after submit |
| 12 | `field_form_style` | Form Style | list_string | 1 | No | one_page | No | one_page, question_step | `lms_quiz_field_data.field_form_style` | Display all questions vs paginated |
| 13 | `field_question_count` | Random Question Count | integer | 1 | No | 0 | No | — | `lms_quiz_field_data.field_question_count` | 0=use all questions; >0=random draw N |
| 14 | `field_take_quiz_number` | Max Attempts | integer | 1 | No | 0 | No | — | `lms_quiz_field_data.field_take_quiz_number` | 0=unlimited attempts |
| 15 | `field_prerequisite_quizzes` | Prerequisite Quizzes | entity_reference → lms_quiz | unlimited | No | — | No | lms_quiz (self) | `lms_quiz__field_prerequisite_quizzes` junction table | Added via `lms_quiz_update_10001`; requires passing these quizzes first; currently empty in DB |
| 16 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_quiz_field_data.created` | Unix timestamp |
| 17 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_quiz_field_data.changed` | Unix timestamp |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` (owner) | M:1 | `lms_quiz_field_data.uid` → `users.uid` | No cascade |
| `lms_question` | M:M | `lms_quiz__field_questions.field_questions_target_id` | No DB cascade |
| `lms_quiz` (self) | M:M | `lms_quiz__field_prerequisite_quizzes` | No DB cascade |
| `lms_lesson` | 1:M (reverse) | `lms_lesson_field_data.field_quiz` → `lms_quiz.id` | No DB cascade |
| `lms_quiz_result` | 1:M | `lms_quiz_result.field_quiz` → `lms_quiz.id` | No DB cascade |

### State machine

No formal state machine. `status` boolean (published/unpublished) is the only lifecycle flag.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/manage/quizzes` (collection), `/manage/quizzes/add`, `/manage/quizzes/{id}/edit`, `/workspace/quizzes`, `/workspace/quizzes/create`, `/workspace/quizzes/{id}/edit` |
| Editor role / permission | `administer lms_course` (`_entity_create_access: lms_quiz`, `_entity_access: lms_quiz.update`) |
| Form display | `QuizForm` (minimal ContentEntityForm wrapper). `AdminController::quizEditForm()` patches `inline_entity_form_complex` settings on the fly for the `field_questions` widget. 7 form op variants all use the same `QuizForm` class. |
| View displays / view modes | No YAML entity_view_display. Public rendering via `lms_quiz_take` theme hook at `/quiz/{id}/take`. |
| Revision / moderation | Not revisionable. |
| Translation | `name` and `field_body` translatable. |
| Preview | No preview route. |
| Cache invalidation | `lms_quiz:{id}` tag. |
| Smoke verification | Create quiz with threshold=80 → submit answers scoring 85% → verify `lms_quiz_result.field_result = Passed` term. |

### Business rules

- `field_question_count > 0`: `QuizController::takeQuiz()` randomly draws N questions from `field_questions` pool; order randomized.
- Prerequisite check: `takeQuiz()` loads `field_prerequisite_quizzes`; for each, queries `lms_quiz_result` for current user filtered by `field_result` term = "Passed". Quiz is blocked until all prerequisites passed.
- `field_take_quiz_number > 0`: `takeQuiz()` counts prior `lms_quiz_result` rows for user + quiz; blocks if at limit.
- On pass: `submitQuiz()` dispatches `QuizPassedEvent`; `lms_certificate` module's event subscriber auto-issues certificate by traversing Quiz → Lesson → Chapter → Course (via `field_certificate_template`).
- **Bug:** `field_start_time` on QuizResult is hardcoded to `request_time - 60` in `submitQuiz()` — actual quiz start time is not tracked.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_quiz` | ~82 |
| `lms_quiz_field_data` | ~82 (+ translations) |
| `lms_quiz__field_questions` | proportional to questions per quiz |
| `lms_quiz__field_prerequisite_quizzes` | 0 (empty) |

---

## Entity 2 — lms_quiz_result (Quiz Result)

---
entity: lms_quiz_result
mode: as-built
owner_module: lms_quiz
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
---

**Storage:** `lms_quiz_result` (flat base table) + `lms_quiz_result__field_responses` junction  
**Description:** Immutable record of one quiz attempt by one learner. Created by `QuizController::submitQuiz()`. Drives certificate issuance and progress reporting.  
**Owner module:** [`lms_quiz`](../C05-modules/lms_quiz.md)  
**Mode:** as-built  
**Content kind:** transactional  
**Editor owned:** false (system-created; admin may view/delete via `/manage/quiz-results`)  
**Management UI:** `/manage/quiz-results` (collection), `/manage/quiz-results/{id}` (canonical)  
**Create / Edit / Delete forms:** No add/edit form routes exposed; ContentEntityDeleteForm for delete  
**Display modes:** Learner view via `lms_quiz_result` theme hook at `/quiz-result/{id}`; admin list at `/manage/quiz-results`  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Not translatable (single flat table, no langcode)  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_quiz_result.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_quiz_result.uuid` | Unique |
| 3 | `uid` | Student | entity_reference → user | 1 | No | current user | No | users | `lms_quiz_result.uid` | Quiz taker; via EntityOwnerTrait |
| 4 | `field_quiz` | Quiz | entity_reference → lms_quiz | 1 | Yes | — | No | lms_quiz | `lms_quiz_result.field_quiz` | Which quiz was taken |
| 5 | `field_score` | Score | decimal (5,2) | 1 | No | 0 | No | — | `lms_quiz_result.field_score` | Percentage 0.00–100.00 |
| 6 | `field_result` | Result | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (quiz_results) | `lms_quiz_result.field_result` | Terms: Passed / Failed / Pending (Pending created on-the-fly if missing) |
| 7 | `field_responses` | Responses | entity_reference → lms_question_response | unlimited | No | — | No | lms_question_response | `lms_quiz_result__field_responses` junction table | Per-question grading detail |
| 8 | `field_start_time` | Start Time | timestamp | 1 | No | — | No | — | `lms_quiz_result.field_start_time` | **Bug:** hardcoded to `request_time - 60`; not the real start time |
| 9 | `field_end_time` | End Time | timestamp | 1 | No | — | No | — | `lms_quiz_result.field_end_time` | Unix timestamp of submission |
| 10 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_quiz_result.created` | Unix timestamp |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| `uid + field_quiz` | `uid, field_quiz` | B-Tree | User's attempts per quiz (attempt count check) |
| `field_result` | `field_result` | B-Tree | Passed/failed filtering |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `lms_quiz_result.uid` → `users.uid` | No cascade |
| `lms_quiz` | M:1 | `lms_quiz_result.field_quiz` → `lms_quiz.id` | No DB cascade |
| `taxonomy_term` (quiz_results) | M:1 | `lms_quiz_result.field_result` → `taxonomy_term_data.tid` | No cascade |
| `lms_question_response` | 1:M | `lms_quiz_result__field_responses.field_responses_target_id` | No cascade; responses belong to this result |
| `lms_user_certificate` | 1:M (reverse) | `lms_user_certificate.field_quiz_result` → `lms_quiz_result.id` | No cascade |

### State machine

Result state is expressed via `field_result` taxonomy term:

```
(grading) → Passed
         → Failed
         → Pending  (when any essay question exists in the quiz)
```

Transition is determined at submit time by `QuizController::submitQuiz()`. No formal state workflow.

### Business rules

- Score calculation: sum of `lms_question_response.field_score` / total possible points × 100. If `score >= field_threshold` → result = Passed; else Failed; if any response has `field_pending_review=TRUE` → result = Pending.
- `field_result` terms (Passed/Failed/Pending) must exist in `quiz_results` vocabulary. `submitQuiz()` creates "Pending" term on-the-fly if missing.
- On Passed: `QuizPassedEvent` dispatched; certificate subscriber in `lms_certificate` issues `lms_user_certificate`.
- **Bug:** `field_start_time` is always `request_time - 60` (one-minute estimate); real quiz start is not tracked from when `/quiz/{id}/take` was loaded.
- **Bug (template):** `lms-quiz-result.html.twig` uses hardcoded threshold 70 for pass/fail color display, ignoring `quiz.field_threshold`.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_quiz_result` | ~94 |
| `lms_quiz_result__field_responses` | proportional to questions per quiz |

---

## Entity 3 — lms_question_response (Question Response)

---
entity: lms_question_response
mode: as-built
owner_module: lms_quiz
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
---

**Storage:** `lms_question_response` (flat base table) + `lms_question_response__field_answers` junction  
**Description:** Captures a learner's answer to one question within a quiz attempt. Created programmatically by `QuizController::submitQuiz()` per question. Stores auto-graded result or flags essay for manual review.  
**Owner module:** [`lms_quiz`](../C05-modules/lms_quiz.md)  
**Mode:** as-built  
**Content kind:** transactional  
**Editor owned:** false (system-created; instructors may add `field_teacher_feedback`)  
**Management UI:** No dedicated CRUD UI (no entity links, no route_provider in annotation). Viewed via `QuizController::viewResult()`.  
**Create / Edit / Delete forms:** No UI routes generated  
**Display modes:** Rendered inline within `lms_quiz_result` theme hook  
**Revision / moderation:** Not revisionable; not translatable  
**Translation:** Not translatable  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_question_response.id` | PK |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_question_response.uuid` | Unique |
| 3 | `field_question` | Question | entity_reference → lms_question | 1 | Yes | — | No | lms_question | `lms_question_response.field_question` | Which question was answered |
| 4 | `field_quiz` | Quiz | entity_reference → lms_quiz | 1 | No | — | No | lms_quiz | `lms_question_response.field_quiz` | Denormalized quiz reference |
| 5 | `field_answers` | Selected Answers | entity_reference → lms_answer | unlimited | No | — | No | lms_answer | `lms_question_response__field_answers` junction | Populated for single_choice, multiple_choice |
| 6 | `field_correct` | Correct | boolean | 1 | No | FALSE | No | — | `lms_question_response.field_correct` | Auto-grading result flag |
| 7 | `field_score` | Score | decimal (5,2) | 1 | No | 0 | No | — | `lms_question_response.field_score` | Points earned for this question |
| 8 | `field_text_answer` | Text Answer | text_long | 1 | No | — | No | — | `lms_question_response.field_text_answer__value/format` | Essay / short_answer submission |
| 9 | `field_pending_review` | Pending Review | boolean | 1 | No | FALSE | No | — | `lms_question_response.field_pending_review` | TRUE for essay; awaiting manual grading |
| 10 | `field_teacher_feedback` | Teacher Feedback | text_long | 1 | No | — | No | — | `lms_question_response.field_teacher_feedback__value/format` | Instructor comment on essay response |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| `field_question` | `field_question` | B-Tree | Lookup responses per question |
| `field_quiz` | `field_quiz` | B-Tree | Responses per quiz |
| `field_pending_review` | `field_pending_review` | B-Tree | Find essays awaiting grading |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_question` | M:1 | `lms_question_response.field_question` → `lms_question.id` | No cascade |
| `lms_quiz` | M:1 | `lms_question_response.field_quiz` → `lms_quiz.id` | No cascade |
| `lms_answer` | M:M | `lms_question_response__field_answers.field_answers_target_id` | No cascade |
| `lms_quiz_result` | M:1 (owner) | `lms_quiz_result__field_responses.field_responses_target_id` | Linked via quiz_result junction |

### Business rules

- Created one-per-question by `QuizController::submitQuiz()` via `GradingStrategyFactory::getStrategy($bundle)->grade(...)`.
- `field_correct` and `field_score` are set by the grading strategy (binary; no partial credit except for essay which sets both to 0/FALSE + `field_pending_review=TRUE`).
- `field_teacher_feedback` entry point: no dedicated teacher grading UI route exists in the current codebase. The field is defined and stored in DB but there is no controller action or form that allows an instructor to populate it. Manual essay grading path is not implemented.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_question_response` | ~182 |
| `lms_question_response__field_answers` | proportional to multiple-choice questions |

---

## Entity 4 — lms_question (Question)

---
entity: lms_question
mode: as-built
owner_module: lms_question
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_question` (base, with `type` bundle column) + `lms_question_field_data` (translatable data) + 4 junction tables for multi-value fields  
**Description:** A question item in the question bank. Bundled by `lms_question_type` config entity (8 bundles). All bundle-specific fields are declared at base entity level (no per-bundle YAML field storage). Bundle context is used only for form display and grading strategy routing.  
**Owner module:** [`lms_question`](../C05-modules/lms_question.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/manage/questions` (collection), `/manage/questions/add/{type}` (create by bundle), `/manage/questions/{id}/edit`  
**Create / Edit / Delete forms:** `entity.lms_question.add_form`, `entity.lms_question.edit_form`, `entity.lms_question.delete_form`  
**Display modes:** No YAML entity_view_display defined; rendered inline in quiz taking UI  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Content translation; `name`, `field_body`, `field_explanation` translatable  
**Replaces / Replaced by:** N/A

### Bundles (8) — defined by lms_question_type config entity

| Bundle ID | Label | Key Fields Used | Grading Strategy | Manual? |
|---|---|---|---|---|
| `single_choice` | Single Choice | `field_choices` (one correct lms_answer) | `SingleChoiceStrategy` | No |
| `multiple_choice` | Multiple Choice | `field_choices` (multiple correct lms_answers) | `MultipleChoiceStrategy` | No |
| `true_false` | True/False | `field_correct_answer` (boolean) | `TrueFalseStrategy` | No |
| `fill_blank` | Fill Blank | `field_correct_answers` (strings), `field_case_sensitive` | `FillBlankStrategy` | No |
| `short_answer` | Short Answer | `field_correct_answers` (strings), `field_case_sensitive` | `ShortAnswerStrategy` | No |
| `matching` | Matching | `field_matching_pairs` (lms_matching_pair refs) | `MatchingStrategy` | No |
| `ordering` | Ordering | `field_ordered_items` (strings in correct order) | `OrderingStrategy` | No |
| `essay` | Essay | `field_min_words`, `field_max_words` | `EssayStrategy` | Yes (always pending) |

### Fields (all bundles — shared base)

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Applicable bundles |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_question.id` | all |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_question.uuid` | all |
| 3 | `type` | Bundle | string | 1 | Yes | — | No | lms_question_type | `lms_question.type` | all (bundle key) |
| 4 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_question.langcode` | all |
| 5 | `name` | Question | string | 1 | Yes | — | Yes | — | `lms_question_field_data.name` | all; max_length=1024; question stem/text |
| 6 | `field_body` | Details | text_long | 1 | No | — | Yes | — | `lms_question_field_data.field_body__value/format` | all; optional extended detail / HTML |
| 7 | `field_points` | Points | integer | 1 | No | 1 | No | — | `lms_question_field_data.field_points` | all; max score for this question |
| 8 | `field_explanation` | Explanation | text_long | 1 | No | — | Yes | — | `lms_question_field_data.field_explanation__value/format` | all; shown to learner after submission |
| 9 | `field_difficulty` | Difficulty | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (quiz_difficulty) | `lms_question_field_data.field_difficulty` | all; optional difficulty tag |
| 10 | `field_choices` | Choices | entity_reference → lms_answer | unlimited | No | — | No | lms_answer | `lms_question__field_choices` junction | single_choice, multiple_choice |
| 11 | `field_correct_answer` | Correct Answer | boolean | 1 | No | — | No | — | `lms_question_field_data.field_correct_answer` | true_false (0=false, 1=true) |
| 12 | `field_correct_answers` | Correct Answers | string | unlimited | No | — | No | — | `lms_question__field_correct_answers` junction | fill_blank, short_answer; max_length=512 per value |
| 13 | `field_case_sensitive` | Case Sensitive | boolean | 1 | No | FALSE | No | — | `lms_question_field_data.field_case_sensitive` | fill_blank, short_answer |
| 14 | `field_matching_pairs` | Matching Pairs | entity_reference → lms_matching_pair | unlimited | No | — | No | lms_matching_pair | `lms_question__field_matching_pairs` junction | matching bundle |
| 15 | `field_ordered_items` | Ordered Items | string | unlimited | No | — | No | — | `lms_question__field_ordered_items` junction | ordering bundle; strings in correct order; max_length=512 |
| 16 | `field_min_words` | Min Words | integer | 1 | No | 0 | No | — | `lms_question_field_data.field_min_words` | essay bundle |
| 17 | `field_max_words` | Max Words | integer | 1 | No | 0 | No | — | `lms_question_field_data.field_max_words` | essay bundle |
| 18 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_question_field_data.created` | all |
| 19 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_question_field_data.changed` | all |

**Important architecture note:** All bundle-specific fields (`field_choices`, `field_correct_answer`, `field_correct_answers`, `field_case_sensitive`, `field_matching_pairs`, `field_ordered_items`, `field_min_words`, `field_max_words`) are defined on the base entity — no per-bundle YAML field.storage. They are physically present on all bundles but semantically relevant only to specific bundles. `QuestionForm` does not hide irrelevant fields per bundle (no `#states` per bundle). See `entities-fields.md` §9 note 2.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| `type` | `lms_question.type` | B-Tree | Filter by question bundle |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |
| `field_difficulty` | `lms_question_field_data.field_difficulty` | B-Tree | Difficulty-filtered question draw |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_question_type` (config) | M:1 (bundle) | `lms_question.type` → config entity id | No cascade |
| `lms_answer` | 1:M | `lms_question__field_choices.field_choices_target_id` | No DB cascade |
| `lms_matching_pair` | 1:M | `lms_question__field_matching_pairs.field_matching_pairs_target_id` | No DB cascade |
| `taxonomy_term` (quiz_difficulty) | M:1 | `lms_question_field_data.field_difficulty` | No cascade |
| `lms_quiz` | M:M (reverse) | `lms_quiz__field_questions.field_questions_target_id` | No DB cascade |
| `lms_question_response` | 1:M (reverse) | `lms_question_response.field_question` | No cascade |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/manage/questions` (collection), `/manage/questions/add/{type}` (per-bundle add), `/manage/questions/{id}/edit`, `/manage/questions/{id}/delete` |
| Editor role / permission | `administer lms_course` (`create lms_course`, `edit lms_course`) |
| Form display | `QuestionForm` (minimal ContentEntityForm wrapper — overrides only `save()`). Inline entity form handler registered (`inline_entity_form`), allowing question editing from within quiz admin form. |
| View displays / view modes | No YAML view display. Rendered inline in `lms-quiz-take.html.twig` by `QuizController::takeQuiz()` which builds per-bundle question view-model arrays. |
| Revision / moderation | Not revisionable. |
| Translation | `name`, `field_body`, `field_explanation` translatable. |
| Preview | No preview route. |
| Cache invalidation | `lms_question:{id}` tag. |
| Smoke verification | Add true_false question, set `field_correct_answer=TRUE` → take quiz → submit TRUE → verify `lms_question_response.field_correct=1`. |

### Business rules

- Grading is entirely in `lms_quiz/src/Grading/`: `GradingStrategyFactory::getStrategy($bundle)` → strategy → `grade()`.
- All grading is binary (all-or-nothing). No partial credit for multi-correct or ordering/matching.
- Essay: `EssayStrategy::grade()` always returns `is_correct=FALSE, score=0.0, pending_review=TRUE`; text is stored in `field_text_answer` on `lms_question_response`.
- `field_min_words` / `field_max_words` on essay: collected in entity but NOT enforced by `EssayStrategy` (no word-count validation in grading code).
- `inline_entity_form` handler allows creating/editing questions directly within the quiz admin form.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_question` | ~336 |
| `lms_question_field_data` | ~336 (+ translations) |
| `lms_question__field_choices` | substantial (most questions are choice-type) |
| `lms_question__field_correct_answers` | moderate (fill_blank + short_answer) |
| `lms_question__field_matching_pairs` | small (~39 matching_pair rows total) |
| `lms_question__field_ordered_items` | small |

---

## Entity 5 — lms_answer (Answer)

---
entity: lms_answer
mode: as-built
owner_module: lms_question
content_kind: editorial
editor_owned: true
revisionable: false
translatable: false
moderation_workflow: null
---

**Storage:** `lms_answer` (flat single base table — no _field_data split, no langcode)  
**Description:** A single answer option for a choice-type question (`single_choice`, `multiple_choice`). Marked correct or incorrect. Referenced by `lms_question.field_choices`.  
**Owner module:** [`lms_question`](../C05-modules/lms_question.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true (typically created inline via `inline_entity_form` within question editing)  
**Management UI:** No standalone CRUD routes; managed inline within `lms_question` admin form  
**Create / Edit / Delete forms:** `ContentEntityForm` / `ContentEntityDeleteForm` (no custom form class)  
**Display modes:** Rendered inline within quiz UI  
**Revision / moderation:** Not revisionable  
**Translation:** Not translatable (no langcode, no _field_data table)  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_answer.id` | PK |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_answer.uuid` | Unique |
| 3 | `name` | Answer Text | string | 1 | Yes | — | No | — | `lms_answer.name` | max_length=1024; displayed as the answer option label |
| 4 | `field_body` | Details | text_long | 1 | No | — | No | — | `lms_answer.field_body__value/format` | Extended rich-text detail (optional) |
| 5 | `field_correct` | Correct Answer | boolean | 1 | No | FALSE | No | — | `lms_answer.field_correct` | 1=this is a correct answer option |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| `field_correct` | `field_correct` | B-Tree | Grading strategy lookups |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_question` | M:1 (parent) | `lms_question__field_choices.field_choices_target_id` → `lms_answer.id` | No DB cascade |
| `lms_question_response` | M:M | `lms_question_response__field_answers.field_answers_target_id` | Records learner selections |

### Business rules

- `field_correct = TRUE` marks this option as a correct answer. A question may have 0, 1, or multiple correct answers.
- `SingleChoiceStrategy`: expects exactly 1 answer with `field_correct=TRUE`; validates submitted ID against correct answer ID.
- `MultipleChoiceStrategy`: collects all answer IDs where `field_correct=TRUE`; strict match (all must match, no extras).
- No admin UI for standalone answer management; answers are edited inline within the question form via `inline_entity_form`.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_answer` | ~816 |

---

## Entity 6 — lms_matching_pair (Matching Pair)

---
entity: lms_matching_pair
mode: as-built
owner_module: lms_question
content_kind: editorial
editor_owned: true
revisionable: false
translatable: false
moderation_workflow: null
---

**Storage:** `lms_matching_pair` (flat single base table)  
**Description:** One left/right pair for a `matching` type question. Multiple pairs form a matching set referenced by `lms_question.field_matching_pairs`.  
**Owner module:** [`lms_question`](../C05-modules/lms_question.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true (managed inline within question form)  
**Management UI:** No standalone CRUD routes  
**Create / Edit / Delete forms:** `ContentEntityForm` / `ContentEntityDeleteForm` (no custom form class)  
**Display modes:** Rendered inline in quiz UI  
**Revision / moderation:** Not revisionable  
**Translation:** Not translatable (flat table, no langcode)  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_matching_pair.id` | PK |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_matching_pair.uuid` | Unique |
| 3 | `field_left_item` | Left Item | string | 1 | Yes | — | No | — | `lms_matching_pair.field_left_item` | max_length=512; prompt shown on left side |
| 4 | `field_right_item` | Right Item | string | 1 | Yes | — | No | — | `lms_matching_pair.field_right_item` | max_length=512; correct match for left item |
| 5 | `weight` | Weight | integer | 1 | No | 0 | No | — | `lms_matching_pair.weight` | Display order of pair |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| `weight` | `weight` | B-Tree | Sort order |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_question` (matching) | M:1 (parent) | `lms_question__field_matching_pairs.field_matching_pairs_target_id` | No DB cascade |

### Business rules

- `MatchingStrategy::grade()`: iterates submitted answer array; compares each `submitted[i]` against `expected_pairs[i]['right']`; all pairs must match for full score.
- Grading is positional (delta-based) — pair order matters.
- `weight` controls the display and comparison order; pairs are rendered shuffled in the quiz UI (left items shuffled, right items shuffled separately).

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_matching_pair` | ~38 |

---

## Entity 7 — lms_question_type (Question Type Config Entity)

---
entity: lms_question_type
mode: as-built
owner_module: lms_question
content_kind: config
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
---

**Storage:** Drupal config system (`key_value` store, prefix `lms_question.question_type.*`); no custom DB table  
**Description:** The bundle definition config entity for `lms_question`. One instance per question type (8 shipped by `lms_question/config/install/`). Managed via Drupal config import/export; not editable from admin UI at runtime.  
**Owner module:** [`lms_question`](../C05-modules/lms_question.md)  
**Mode:** as-built  
**Content kind:** config  
**Editor owned:** false (config-managed; changed via config sync YAML)  
**Management UI:** Config entity — no standard admin UI exposed; managed via `drush config:import`  
**Config prefix:** `lms_question.question_type`  
**Bundle of:** `lms_question`  
**Replaces / Replaced by:** N/A

### Properties (exported in YAML)

| Property | Type | Required | Notes |
|---|---|---|---|
| `id` | string | Yes | Machine name (e.g. `essay`, `fill_blank`) |
| `label` | string (label) | Yes | Human-readable name |
| `description` | string | No | Default = empty string |

### Shipped instances (config/install/)

| Config file | id | label | description |
|---|---|---|---|
| `lms_question.question_type.essay.yml` | `essay` | Essay | Essay question type. |
| `lms_question.question_type.fill_blank.yml` | `fill_blank` | Fill Blank | Fill Blank question type. |
| `lms_question.question_type.matching.yml` | `matching` | Matching | Matching question type. |
| `lms_question.question_type.multiple_choice.yml` | `multiple_choice` | Multiple Choice | Multiple Choice question type. |
| `lms_question.question_type.ordering.yml` | `ordering` | Ordering | Ordering question type. |
| `lms_question.question_type.short_answer.yml` | `short_answer` | Short Answer | Short Answer question type. |
| `lms_question.question_type.single_choice.yml` | `single_choice` | Single Choice | Single Choice question type. |
| `lms_question.question_type.true_false.yml` | `true_false` | True False | True False question type. |

### Class hierarchy

```
ConfigEntityBundleBase (Drupal core)
  └── QuestionType (lms_question/src/Entity/QuestionType.php)
        Properties: $id (string), $label (string), $description (string)
        No custom handlers beyond core defaults
```

Config schema: `lms_question/config/schema/lms_question.schema.yml` defines `lms_question.question_type.*` with id (string), label (label), description (text).

### Relationships

| Related | Type | Notes |
|---|---|---|
| `lms_question` | 1:M | This config entity is the bundle provider; `lms_question.type` references the `id` of an `lms_question_type` instance |

### Business rules

- Config entities are immutable at runtime (managed via CMI). Adding a new question type requires creating a YAML file and running `drush config:import`.
- No UI routes generated (`ConfigEntityBundleBase` with no handlers).
- All 8 types are installed by `config/install/` YAMLs on module install.
- The `GradingStrategyFactory` in `lms_quiz` uses `match` on the question `type` string to select the grading strategy — adding a new bundle requires updating the factory too.

---

## Grading Strategy Reference

| Bundle | Strategy Class | Input fields read | Pass logic | Essay/Manual |
|---|---|---|---|---|
| `single_choice` | `SingleChoiceStrategy` | `field_choices` (lms_answer where `field_correct=TRUE`) | Submitted ID == correct answer ID | No |
| `multiple_choice` | `MultipleChoiceStrategy` | `field_choices` (all lms_answers where `field_correct=TRUE`) | Submitted IDs exactly equal correct IDs (strict array diff) | No |
| `true_false` | `TrueFalseStrategy` | `field_correct_answer` (boolean) | Submitted bool ('1'/'0') == `field_correct_answer` | No |
| `fill_blank` | `FillBlankStrategy` | `field_correct_answers` (strings), `field_case_sensitive` | Trimmed match against any correct answer string | No |
| `short_answer` | `ShortAnswerStrategy` | `field_correct_answers` (strings), `field_case_sensitive` | Same as FillBlank | No |
| `ordering` | `OrderingStrategy` | `field_ordered_items` (strings) | Submitted order array == `field_ordered_items` exactly | No |
| `matching` | `MatchingStrategy` | `field_matching_pairs` (lms_matching_pair; positional) | All pairs match positionally | No |
| `essay` | `EssayStrategy` | `field_min_words`, `field_max_words` (not enforced) | Always `pending_review=TRUE`; score=0 until teacher grades | Yes (always) |

**Architecture note:** `GradingStrategyFactory` is a static PHP class with no Drupal container registration (`lms_quiz` has no `.services.yml`). No partial credit exists for any bundle.
