---
id: F-002
name: Assessment System
priority: Must
status: APPROVED
parent-requirements: [BR-002]
owning_modules: [lms_quiz, lms_question]
primary_source: C05
story_anchors: []
---

# F-002 — Assessment System

## User story

As a **learner**, I want to take quizzes with a variety of question types and receive immediate feedback on my score, so that I can assess my understanding and progress toward course completion and certification.

As an **instructor or administrator**, I want to create quizzes with configurable pass thresholds, time limits, and attempt limits, so that I can reliably measure learner competency.

## Description

The assessment system delivers a complete quiz engine with a question bank supporting eight distinct question types. It is implemented across two modules: `lms_question` (entity definitions for the question bank) and `lms_quiz` (quiz configuration, the quiz-taking UI, grading, and result tracking).

Grading is implemented as a static PHP factory (`GradingStrategyFactory`) with eight strategy classes — not a Drupal Plugin API system. Seven of the eight types are strictly binary (all-or-nothing, no partial credit). Essay questions always route to `pending_review = TRUE` and require manual grading. On passing a quiz, `QuizPassedEvent` (`lms_quiz.passed`) is dispatched; downstream subscribers (notably `lms_certificate`) react to this event.

Quizzes are linked to lessons via `lms_lesson.field_quiz` and also act as standalone assessments accessible at `/quiz/{lms_quiz}/take`. The quiz result detail page and "My Quiz Results" history page are fully implemented. `lms_quiz.module` registers a `hook_theme()` for the three public-facing quiz templates.

## In scope

- `lms_quiz` entity with fields: name, instructions, questions (reference, unlimited), pass threshold (%), time limit (minutes, 0=unlimited), display-right-answer flag, form style (one_page / question_step), random question count (0=all), max attempts (0=unlimited), prerequisite quizzes (self-reference, added via update_10001), status.
- `lms_question` entity with 8 bundles defined by `lms_question_type` config entity: `essay`, `fill_blank`, `matching`, `multiple_choice`, `ordering`, `short_answer`, `single_choice`, `true_false`. All bundles share one base field set.
- Supporting entities: `lms_answer` (choice text + correct flag), `lms_matching_pair` (left/right item + weight).
- `lms_quiz_result` entity: score (decimal %), result taxonomy term (Passed/Failed/Pending from `quiz_results` vocabulary), per-response references, start/end time, owner.
- `lms_question_response` entity: per-question grading record with fields for selected answers, text answer, correct flag, score, pending-review flag, teacher feedback.
- `GradingStrategyFactory` static factory + 8 strategy classes (all as-built; strictly binary scoring for 7 types; essay always `pending_review`).
- Quiz-taking route (`/quiz/{lms_quiz}/take`): prerequisite-quiz check, max-attempts check, optional question randomisation, all 8 question types rendered in `lms-quiz-take.html.twig` with inline JS (localStorage auto-save draft, drag-and-drop ordering, countdown timer, confirmation modal).
- Quiz submission route (`/quiz/{lms_quiz}/submit`, POST): grading via factory, QuestionResponse + QuizResult entity creation, `QuizPassedEvent` dispatch on pass, redirect to result page.
- Result view (`/quiz-result/{lms_quiz_result}`): score circle, per-response breakdown (correct/incorrect/pending icons), teacher feedback display.
- "My Quiz Results" page (`/my-quiz-results`): history list with summary stats (total, passed, average score, best score); state classification (passed / review / retry).
- Admin CRUD for quizzes (`/manage/quizzes`, `/manage/quiz-results`) via auto-generated `AdminHtmlRouteProvider` routes.
- Admin workspace quiz list (`/workspace/quizzes`) via `AdminController::quizList()`.
- `inline_entity_form` handler on `lms_question` for embedding question forms within quiz forms.
- `quiz_difficulty` vocabulary for question difficulty tagging.
- `quiz_results` vocabulary for Passed/Failed/Pending result terms.

## Out of scope

- Drupal Plugin API-based question handler system — grading uses a plain static factory; no `src/Plugin/` directory in either module.
- Partial credit for MultipleChoice, Ordering, or Matching — all seven auto-graded types are strictly binary.
- Manual grading UI (teacher feedback form) — `field_teacher_feedback` field exists on `lms_question_response` and is view/form configurable, but there is no dedicated grading queue page or instructor-facing grading workflow beyond the field itself.
- Quiz analytics dashboard (pass/fail rate, score distribution) — `AdminController::quizList()` has a hardcoded `pending_grading = 5` mock; real analytics are deferred to `lms_analytics`.
- Bulk import of questions from CSV/JSON — not implemented.
- Score override by admin — not implemented.
- CSV/Excel export of quiz results — not implemented.
- SCORM and H5P tracking scores — these are handled at the lesson/progress layer, not by the quiz engine.

## Acceptance summary

A learner can take a published quiz linked to a lesson, submit answers, and see an immediate result page showing pass/fail status and per-question feedback (where configured). Essay responses are marked pending. On passing, a `QuizPassedEvent` fires, enabling downstream certificate issuance. Full Given/When/Then lives in `B05-acceptance-criteria/F-002.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Quiz definitions (name, instructions, threshold, settings) | Instructors, Admins | manager (administer lms_course), administrator | `/workspace/quizzes`, `/manage/quizzes` | Quiz form uses `admin_edit` / `manager_edit` / etc. modes |
| Question bank (all 8 types) | Instructors, Admins | manager, administrator | `/manage/questions` (auto-generated entity routes) | All bundle-specific fields present on all bundles; form does not conditionally hide irrelevant fields |
| Quiz results / learner feedback | Admins | administrator | `/manage/quiz-results` | No RBAC-limited instructor-only result view |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 | Lessons reference quizzes via `lms_lesson.field_quiz`; quiz type lessons use this feature |
| Feature | F-003 | `QuizPassedEvent` triggers certificate issuance; lesson completion may depend on quiz pass |
| Workflow | WF-002 | Quiz-taking and grading workflow |
| Workflow | WF-003 | Certificate issuance triggered by `QuizPassedEvent` |

## Backing modules and entities

| Module | Role | Maturity |
|---|---|---|
| `lms_question` | Question bank entities (Question, Answer, MatchingPair), 8 bundle config, QuestionAccessControlHandler | as-built |
| `lms_quiz` | Quiz entity, QuizResult, QuestionResponse, QuizController, GradingStrategyFactory + 8 strategies, QuizPassedEvent, templates | as-built |

| Entity | Key fields | Maturity |
|---|---|---|
| `lms_quiz` | name, field_questions, field_threshold (default 70), field_time_limit, field_display_right_answer, field_form_style, field_question_count, field_take_quiz_number, field_prerequisite_quizzes | as-built |
| `lms_question` | name, field_body, field_points, field_explanation, field_difficulty, field_choices (→lms_answer), field_correct_answer, field_correct_answers, field_case_sensitive, field_matching_pairs (→lms_matching_pair), field_ordered_items, field_min_words, field_max_words | as-built |
| `lms_question_type` | Config entity — 8 bundles (essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false) | as-built |
| `lms_answer` | name (answer text), field_body, field_correct | as-built |
| `lms_matching_pair` | field_left_item, field_right_item, weight | as-built |
| `lms_quiz_result` | field_quiz, field_score (%), field_result (taxonomy_term quiz_results), field_responses, field_start_time, field_end_time | as-built (bug: field_start_time hardcoded to request_time−60) |
| `lms_question_response` | field_question, field_quiz, field_answers, field_correct, field_score, field_text_answer, field_pending_review, field_teacher_feedback | as-built |

## Priority matrix

| Capability | Priority | Maturity |
|---|---|---|
| Quiz CRUD + settings (threshold, time limit, attempts, display) | Must | as-built |
| All 8 question types (entity + grading strategies) | Must | as-built |
| Quiz-taking UI (timer, auto-save, all type renderers, drag-drop ordering) | Must | as-built |
| Auto-grading for 7 question types (strictly binary) | Must | as-built |
| Essay → pending_review (manual grading flag) | Must | as-built |
| QuizPassedEvent dispatch on pass | Must | as-built |
| Result page (score, per-question breakdown, feedback) | Must | as-built |
| My Quiz Results history | Should | as-built |
| Prerequisite quizzes (`field_prerequisite_quizzes`) | Should | as-built |
| Random question pool (`field_question_count`) | Should | as-built |
| Question bank centralized reuse | Should | as-built (entity level) |
| Partial credit scoring | Could | planned — not implemented |
| Manual grading queue / instructor feedback UI | Could | planned — field exists, UI not built |
| Quiz analytics dashboard (real data) | Could | stub — pending_grading hardcoded to 5 |
| CSV/Excel result export | Could | planned — not implemented |
| Bulk question import (CSV/JSON) | Could | planned — not implemented |

## Known bugs

| Bug | Severity | Location |
|---|---|---|
| `field_start_time` on `lms_quiz_result` always set to `request_time − 60` (hardcoded 1-minute estimate, not real start tracking) | Low | `QuizController::submitQuiz()` |
| Result template uses hardcoded `score >= 70` for pass/fail display colour, ignoring quiz's actual `field_threshold` | Low | `lms-quiz-result.html.twig` |
| `quizList()` shows `pending_grading = 5` (mock, not real query) | Low | `AdminController::quizList()` |

## Open questions

- [ ] Should partial credit be added for MultipleChoice, Ordering, and Matching types? If yes, `GradingStrategyFactory` strategies need modification.
- [ ] Where does the `quiz_difficulty` and `quiz_results` vocabulary installation live? Neither `lms_quiz` nor `lms_question` installs these terms — they must be seeded externally.
- [ ] Manual grading queue for essay responses: which role reviews them and via which UI path?
