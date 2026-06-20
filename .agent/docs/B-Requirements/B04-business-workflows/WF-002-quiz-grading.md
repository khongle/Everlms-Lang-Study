---
id: WF-002
name: Quiz Submit & Grading
status: APPROVED
features: [F-002, F-003]
---

# WF-002 — Quiz Submit & Grading

End-to-end flow from a student opening a quiz through submission, automated grading, result persistence, and downstream event dispatch.

## Trigger

Student clicks "Take Quiz" on a lesson page that has a linked `lms_quiz` entity.

## Actors

| Actor | Role |
|-------|------|
| Student (authenticated) | Takes and submits quiz |
| System | Grading, result persistence, event dispatch |
| Instructor | Manual grading for essay questions (no dedicated route exists yet — see Business Rules) |

## Preconditions

- Student is authenticated.
- `lms_quiz` entity is published.
- Student has not exceeded `field_take_quiz_number` attempts (0 = unlimited).
- All prerequisite quizzes (via `field_prerequisite_quizzes`) have a passing `lms_quiz_result` for this student.

## Happy Path

| Step | Actor | Action | System Response | Modules / Code | Feature |
|------|-------|--------|----------------|----------------|---------|
| 1 | Student | GET `/quiz/{lms_quiz}/take` | `QuizController::takeQuiz()` checks attempt count (entity query on `lms_quiz_result` per user+quiz) and prerequisite passes. Optionally randomizes question order if `field_question_count > 0`. Returns `lms_quiz_take` theme render array. | `lms_quiz` | F-002 |
| 2 | System | Render quiz UI | `lms-quiz-take.html.twig` renders all 8 question types. JS provides: localStorage draft auto-save, drag-and-drop for ordering, countdown timer (localStorage-based), confirm-before-submit modal. | `lms_quiz` templates | F-002 |
| 3 | Student | Answers questions and submits | POST `/quiz/{lms_quiz}/submit` with form data keyed by question ID per type | `lms_quiz` | F-002 |
| 4 | System | `QuizController::submitQuiz()` iterates questions | For each question, calls `GradingStrategyFactory::getStrategy($bundle)` to obtain the correct `QuestionGradingInterface` instance via PHP `match`. | `lms_quiz/src/Grading/GradingStrategyFactory.php` | F-002 |
| 5 | System | Each strategy `grade()` method runs | Returns `['is_correct', 'score', 'text_answer', 'answer_ids', 'pending_review']`. Creates `lms_question_response` entity per question. | `lms_quiz/src/Grading/Strategy/` | F-002 |
| 6 | System | Score calculation | Total awarded points divided by total possible points, multiplied by 100 = percentage score. Compares against `field_threshold` to determine pass/fail. | `QuizController::submitQuiz()` | F-002 |
| 7 | System | Determine result term | Loads `quiz_results` taxonomy term: `Passed` (score >= threshold, no pending_review), `Failed` (score < threshold), or `Pending` (any essay present). Creates `Pending` term if missing. | `QuizController::submitQuiz()` | F-002 |
| 8 | System | Persist `lms_quiz_result` | Entity saved with `field_quiz`, `field_score`, `field_result` (taxonomy term), `field_responses`, `field_start_time` (hardcoded `now - 60`), `field_end_time`. | `lms_quiz_result` entity | F-002 |
| 9 | System | Dispatch `QuizPassedEvent` | If result term = `Passed`: `new QuizPassedEvent($quizResult, $quiz, $account)` dispatched via Symfony event system (event name: `lms_quiz.passed`). | `lms_quiz/src/Event/QuizPassedEvent.php` | F-002, F-003 |
| 10 | System | Redirect | Student redirected to `/quiz-result/{lms_quiz_result}` | `QuizController::submitQuiz()` | F-002 |
| 11 | Student | View result | `QuizController::viewResult()` loads result + responses; renders `lms_quiz_result` theme with score circle, per-response breakdown, teacher feedback, "Continue Learning" link. | `lms_quiz` | F-002 |

## Grading Strategies (as-built)

All strategies are in `web/modules/custom/lms_quiz/src/Grading/Strategy/`.

| Question Type (bundle) | Strategy Class | Logic | Partial Credit | `pending_review` |
|------------------------|---------------|-------|----------------|-----------------|
| `single_choice` | `SingleChoiceStrategy` | Submitted answer ID matches the one `lms_answer` with `field_correct=TRUE` | No — binary | FALSE |
| `multiple_choice` | `MultipleChoiceStrategy` | Submitted IDs must **exactly** equal correct IDs (bidirectional `array_diff`) | No — strict binary | FALSE |
| `true_false` | `TrueFalseStrategy` | Submitted `'1'`/`'0'` compared to `field_correct_answer` boolean | No — binary | FALSE |
| `fill_blank` | `FillBlankStrategy` | Trimmed text matched against `field_correct_answers` list; respects `field_case_sensitive` | No | FALSE |
| `short_answer` | `ShortAnswerStrategy` | Identical logic to `FillBlankStrategy`; exact match | No | FALSE |
| `ordering` | `OrderingStrategy` | Strict positional array comparison against `field_ordered_items` | No — all positions must match | FALSE |
| `matching` | `MatchingStrategy` | Each `submitted[i]` must equal `expected_pairs[i]['right']` | No — all pairs must match | FALSE |
| `essay` | `EssayStrategy` | Always returns `is_correct=FALSE`, `score=0.0`, `pending_review=TRUE`; stores text in `field_text_answer` | N/A | TRUE (always) |

**Note:** `GradingStrategyFactory` is a **static PHP class** (not a Drupal Plugin). It uses PHP `match` to map bundle string → strategy instance. There is no Drupal Plugin API involved.

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 1-E1 | Attempt limit reached (`field_take_quiz_number > 0` and count = limit) | `takeQuiz()` returns error render array | No retry until admin resets count |
| 1-E2 | Prerequisite quiz not passed | `takeQuiz()` returns error listing unmet prerequisites | Student completes prerequisite |
| 7-E1 | Any essay question present | Result term = `Pending`; `QuizPassedEvent` is NOT dispatched; no certificate issued | Instructor must grade manually (no dedicated instructor grading route exists — **stub**) |
| 8-E1 | DB write fails on `lms_quiz_result` | Drupal exception propagates; student sees Drupal error page | Student retries submit |
| 11-E1 | Result page uses hardcoded `score >= 70` threshold in template | Template may show incorrect pass/fail styling if quiz `field_threshold` differs from 70 | **Bug — as-built**; threshold display in `lms-quiz-result.html.twig` does not read `field_threshold` |

## Success Criteria

- `lms_quiz_result` entity persisted with correct `field_score`, `field_result` term, and `field_responses`.
- One `lms_question_response` per question with `field_correct`, `field_score`, `field_pending_review` set.
- `QuizPassedEvent` dispatched if and only if result term = `Passed`.
- Student can view result at `/quiz-result/{id}`.
- Student's attempt history visible at `/my-quiz-results`.

## Business Rules

- **Strict binary grading**: all auto-graded strategies award full points or zero; no partial credit for multi-part questions (multiple choice, ordering, matching).
- **Max attempts**: `field_take_quiz_number=0` means unlimited. Non-zero blocks after N total attempts regardless of pass/fail.
- **Timer**: managed entirely client-side via localStorage countdown. No server-side enforcement; a sufficiently motivated student could submit after time expires by bypassing the JS.
- **Essay queue**: `EssayStrategy` always marks `pending_review=TRUE`. The `QuizPassedEvent` is never dispatched while any response has `pending_review=TRUE`. There is no instructor-facing grading route in `lms_quiz` — this is a **stub/planned** feature.
- **`field_start_time` bug**: `submitQuiz()` sets `field_start_time = request_time - 60` (one-minute hardcoded estimate) instead of tracking actual quiz start time.
- **Randomization**: when `field_question_count > 0`, `takeQuiz()` randomly selects that many questions from `field_questions`; order is shuffled.

## Related Docs

- [WF-003 — Certificate Issuance](./WF-003-certificate-issuance.md) — triggered by `QuizPassedEvent`
- [WF-001 — Enrollment & Learning](./WF-001-enrollment-learning.md) — prerequisite for quiz access
- Source: `web/modules/custom/lms_quiz/src/Controller/QuizController.php`
- Source: `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php`
- Source: `web/modules/custom/lms_quiz/src/Grading/Strategy/` (8 strategy classes)
- Source: `web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php`
