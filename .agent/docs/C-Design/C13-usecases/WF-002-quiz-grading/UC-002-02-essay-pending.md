---
usecase_id: UC-002-02
workflow: WF-002-quiz-grading
type: edge
---

# UC-002-02 — Quiz with Essay Question → Pending Result

**One-line purpose:** When a quiz submission contains at least one essay question, `EssayStrategy` always sets `pending_review=true` and the overall result is set to `Pending` — the student is not Passed or Failed until a manual review is completed. No manual grading UI exists; this use case ends in a blocked state.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Submits a quiz containing an essay question |
| `QuizController::submitQuiz()` | Orchestrates grading and result creation |
| `EssayStrategy` | Always returns `is_correct=false, score=0.0, pending_review=true` |
| `GradingStrategyFactory` | Returns `EssayStrategy` for `essay` bundle |
| Instructor / Admin | Would perform manual review — **no UI exists** |

---

## Preconditions

- Student is authenticated and meets attempt/prerequisite conditions (same as UC-002-01).
- `lms_quiz.field_questions` contains at least one `lms_question` with bundle `essay`.
- `quiz_results` vocabulary has a `Pending` term (or `submitQuiz()` creates it on first essay submission).

---

## Trigger

Student submits `POST /quiz/{lms_quiz}/submit` for a quiz that includes an essay question.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student submits a quiz containing one or more essay questions |
| 2 | **When** `QuizController::submitQuiz()` iterates questions and calls `GradingStrategyFactory::getStrategy('essay')` |
| 3 | **Then** `EssayStrategy::grade($question, $text_submission)` is called |
| 4 | **Then** `EssayStrategy` always returns `['is_correct' => false, 'score' => 0.0, 'text_answer' => $text_submission, 'answer_ids' => [], 'pending_review' => true]` |
| 5 | **Then** a `lms_question_response` entity is created with `field_pending_review=true`, `field_text_answer={student text}`, `field_correct=false`, `field_score=0.0` |
| 6 | **When** all responses are processed, `submitQuiz()` checks if any response has `pending_review=true` |
| 7 | **Then** `submitQuiz()` queries `quiz_results` taxonomy for the term labelled `Pending`; if not found, creates it |
| 8 | **Then** a `lms_quiz_result` entity is created with `field_result={Pending term}`, `field_score={partial score from objective questions}` |
| 9 | **Then** because `field_result != Passed`, `QuizPassedEvent` is NOT dispatched |
| 10 | **Then** no certificate is issued; no enrollment side-effects occur |
| 11 | **Then** student is redirected to `/quiz-result/{lms_quiz_result}` which shows the pending state |
| 12 | **Then** an instructor or admin would need to grade the essay via a manual UI — **this UI does not exist (AC-018 is blocked)** |
| 13 | **Then** the `lms_quiz_result` remains in `Pending` state indefinitely until manually resolved outside normal application flows |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_question_response` | `field_pending_review` | `true` |
| `lms_question_response` | `field_text_answer` | student's essay text |
| `lms_question_response` | `field_correct`, `field_score` | `false`, `0.0` |
| `lms_quiz_result` | `field_result` | `Pending` taxonomy term |
| `lms_quiz_result` | `field_score` | partial percentage from non-essay questions only |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Pending term auto-create | No log | `submitQuiz()` silently creates the Pending term if missing |
| No quiz_result notification | None | `QuizPassedEvent` not dispatched → `NotificationEventSubscriber` does not fire |
| Pending result visible to student | `/quiz-result/{id}` render | Shows `lms_quiz_result` theme with `result_label = Pending` |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-017](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-017-essay-questions-are-always-queued-for-manual-review) — Essay always queued for review | TEST-017 |
| [AC-018](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-018-essay-manual-grading-interface--blocked--not-implemented) — Manual grading UI (blocked) | TEST-018 |

---

## Notes

- **Manual grading is fully unimplemented.** There is no route, controller, or form for an instructor to view pending essays and enter a score. The `lms_quiz_result` entity stays `Pending` with no transition mechanism in the app.
- **Certificate never issued for essays:** Because `QuizPassedEvent` is only dispatched when `field_result = Passed`, a quiz that contains any essay question can never trigger auto-certificate issuance through the normal flow — even if the objective questions were all answered correctly.
- **Partial score:** `field_score` on the `lms_quiz_result` reflects the percentage from objective questions only; the essay contributes 0. This may mislead students who see a score close to the threshold.
- `field_min_words` and `field_max_words` on the essay question entity are defined but not enforced by `EssayStrategy` — word count validation does not occur server-side.
