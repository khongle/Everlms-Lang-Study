---
usecase_id: UC-002-01
workflow: WF-002-quiz-grading
type: happy
---

# UC-002-01 — Objective Quiz Submission → Auto-Grade → Pass

**One-line purpose:** Student submits a quiz containing only objective questions (single_choice, multiple_choice, true_false, fill_blank, short_answer, ordering, matching); the system grades each answer via strategy classes, computes a percentage score, determines Pass/Fail, persists the result, and dispatches `QuizPassedEvent` on pass.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user taking a quiz |
| `QuizController::takeQuiz()` | Renders the quiz take page |
| `QuizController::submitQuiz()` | Processes POST submission, grades, persists |
| `GradingStrategyFactory` | Static factory mapping question bundle to strategy class |
| 7 objective strategy classes | `SingleChoiceStrategy`, `MultipleChoiceStrategy`, `TrueFalseStrategy`, `FillBlankStrategy`, `ShortAnswerStrategy`, `OrderingStrategy`, `MatchingStrategy` |
| `QuizPassedEvent` | Symfony event dispatched on pass |
| `NotificationEventSubscriber` | (`lms_notification`) — listens for `QuizPassedEvent` |

---

## Preconditions

- Student is authenticated.
- `lms_quiz` entity is published (`status=1`).
- `field_questions` references at least one `lms_question` entity; none has bundle `essay`.
- If `field_take_quiz_number > 0`: student has fewer than that many `lms_quiz_result` entities for this quiz.
- All prerequisite quizzes (from `field_prerequisite_quizzes`, added by `lms_quiz_update_10001`) are already passed by this student.
- `quiz_results` taxonomy vocabulary exists with `Passed` and `Failed` terms.

---

## Trigger

Student visits `/quiz/{lms_quiz}/take` (GET), reads questions, then submits form to `POST /quiz/{lms_quiz}/submit`.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student navigates to `/quiz/{lms_quiz}/take` |
| 2 | **When** `QuizController::takeQuiz()` runs |
| 3 | **Then** attempt count is checked against `field_take_quiz_number`; prerequisite quizzes are verified (must have a `lms_quiz_result` with `field_result` term label `Passed`); if all pass, continues |
| 4 | **Then** questions are loaded from `field_questions`; if `field_question_count > 0`, list is randomized and truncated |
| 5 | **Then** per question type, choice arrays, matching pairs, or ordered items are prepared as view-model arrays |
| 6 | **Then** `lms_quiz_take` theme is returned with quiz, questions, and `field_time_limit` |
| 7 | **Given** student answers all questions and submits the form |
| 8 | **When** `POST /quiz/{lms_quiz}/submit` hits `QuizController::submitQuiz()` |
| 9 | **Then** for each question, the submitted answer is extracted from POST data by question type |
| 10 | **When** `GradingStrategyFactory::getStrategy($question->bundle())` is called |
| 11 | **Then** the matching strategy class is returned and `grade($question, $submission)` is called |
| 12 | **Then** each strategy returns `['is_correct' => bool, 'score' => float, 'text_answer' => string, 'answer_ids' => array, 'pending_review' => false]` |
| 13 | **Then** a `lms_question_response` entity is created per question with `field_correct`, `field_score`, `field_text_answer`, `field_pending_review=false` |
| 14 | **Then** percentage score is calculated: `(sum of earned points / sum of max points) * 100` |
| 15 | **Then** score is compared to `field_threshold` (default 70): `score >= threshold` → Passed term; else Failed term |
| 16 | **Then** a `lms_quiz_result` entity is created: `uid={student}`, `field_quiz={quiz}`, `field_score={pct}`, `field_result={Passed term}`, `field_responses=[response entities]`, `field_start_time={now-60}`, `field_end_time={now}` |
| 17 | **When** result term = Passed |
| 18 | **Then** `QuizPassedEvent(quizResult, quiz, user)` is dispatched via `\Drupal::service('event_dispatcher')->dispatch()` |
| 19 | **When** `NotificationEventSubscriber::onQuizPassed()` fires (if `lms_notification` is installed) |
| 20 | **Then** `lms_notification.manager->dispatch('quiz_result', ...)` creates an in-app `lms_notification` for the student |
| 21 | **Then** `CertificateEventListener::onQuizPassed()` fires (if `lms_certificate` is installed) — see [UC-003-01](../WF-003-certificate-issuance/UC-003-01-auto-issue-on-pass.md) |
| 22 | **Then** student is redirected to `/quiz-result/{lms_quiz_result}` |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_question_response` | `field_question`, `field_quiz`, `field_correct`, `field_score`, `field_text_answer`, `field_answers`, `field_pending_review` | created per question |
| `lms_quiz_result` | `field_quiz`, `field_score`, `field_result`, `field_responses`, `field_start_time`, `field_end_time`, `uid` | created |
| `lms_notification` | `field_type=quiz_result`, `field_channel=[in_app]` | created (if module installed) |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Quiz passed event | `lms_quiz.passed` Symfony event | Dispatched by `submitQuiz()` synchronously in request |
| In-app notification | `lms_notification` entity | `quiz_result` type; created by `NotificationEventSubscriber` |
| No Drupal log on grade | None | Grading strategies do not log; only the event dispatch is observable |
| `field_start_time` inaccuracy | Field value | Always `request_time - 60`; does not reflect actual start — known bug |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-013](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-013-quiz-take-page-loads-with-correct-questions-and-question-types) — Take page loads with correct questions | TEST-013 |
| [AC-014](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-014-prerequisite-quizzes-are-enforced-before-allowing-a-quiz-attempt) — Prerequisites enforced | TEST-014 |
| [AC-015](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-015-maximum-attempt-limit-is-enforced) — Max attempt limit enforced | TEST-015 |
| [AC-016](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-016-auto-grading-is-strictly-binary-all-or-nothing-for-all-objective-question-types) — Binary grading for objective types | TEST-016 |

---

## Notes

- **No partial credit:** All 7 objective strategies use strict all-or-nothing comparison. Multiple choice requires exact set equality; ordering requires exact sequence equality; matching requires all pairs correct.
- **GradingStrategyFactory is a static PHP class**, not a Drupal Plugin. There is no `src/Plugin/` directory in `lms_quiz` or `lms_question` for grading.
- **start_time bug:** `field_start_time` is always `$request_time - 60`. The quiz take template stores draft answers in `localStorage` but no server-side start time tracking exists.
- **Result template hardcodes threshold at 70:** `lms-quiz-result.html.twig` uses `score >= 70` for display styling independently of the quiz's actual `field_threshold`. If a quiz has a different threshold, display may mismatch the stored result term.
