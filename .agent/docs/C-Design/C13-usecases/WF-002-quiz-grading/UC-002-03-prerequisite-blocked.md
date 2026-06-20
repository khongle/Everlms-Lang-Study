---
usecase_id: UC-002-03
workflow: WF-002-quiz-grading
type: permission
---

# UC-002-03 — Student Attempts Quiz Without Passing Prerequisite → Blocked

**One-line purpose:** A student tries to take a quiz whose `field_prerequisite_quizzes` references one or more quizzes the student has not yet passed. `QuizController::takeQuiz()` detects the unmet prerequisite and blocks the attempt.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user who has not passed a prerequisite quiz |
| `QuizController::takeQuiz()` | Performs prerequisite check before rendering the quiz |
| `lms_quiz_result` entity storage | Queried to find Passed results for prerequisite quizzes |

---

## Preconditions

- Student is authenticated.
- Target `lms_quiz` has `field_prerequisite_quizzes` (added by `lms_quiz_update_10001`) referencing at least one other `lms_quiz` entity.
- Student does NOT have a `lms_quiz_result` entity for the prerequisite quiz with `field_result` taxonomy term label `Passed`.
- `quiz_results` taxonomy vocabulary has a `Passed` term.

---

## Trigger

Student navigates to `GET /quiz/{lms_quiz}/take` for a quiz with an unmet prerequisite.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student navigates to `/quiz/{lms_quiz}/take` |
| 2 | **When** `QuizController::takeQuiz()` runs |
| 3 | **Then** `takeQuiz()` loads `field_prerequisite_quizzes` for the target quiz |
| 4 | **Then** for each prerequisite quiz, an entity query checks for a `lms_quiz_result` where `uid={student}`, `field_quiz={prereq_id}`, and the referenced `field_result` taxonomy term has label `Passed` |
| 5 | **When** no such passing result is found for at least one prerequisite |
| 6 | **Then** `takeQuiz()` blocks rendering of the quiz take form |
| 7 | **Then** the student receives a response indicating the prerequisite must be completed first (the exact response — redirect, message, or render array — is determined by the controller logic; no 403 is thrown; it returns a themed page or messenger message) |
| 8 | **Then** no `lms_quiz_result` or `lms_question_response` entities are created |
| 9 | **When** student later passes the prerequisite quiz (UC-002-01) |
| 10 | **Then** the prerequisite check will pass on the next attempt, and the student can take the target quiz |

---

## Entities Mutated

None — the block happens before any submission or result creation.

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Prerequisite check | Entity query on `lms_quiz_result` | Executed synchronously in `takeQuiz()` on every GET request |
| No log on block | None | No Drupal log is written when a prerequisite blocks a student |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-014](../../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md#ac-014-prerequisite-quizzes-are-enforced-before-allowing-a-quiz-attempt) — Prerequisite check enforced | TEST-014 |

---

## Notes

- The prerequisite check is implemented in `takeQuiz()` but only on the GET take page. The POST `submitQuiz()` route does NOT re-check prerequisites — if a student manually POSTs to `/quiz/{id}/submit` without visiting the take page, the prerequisite is bypassed. This is a security gap.
- `field_prerequisite_quizzes` was added by update hook `lms_quiz_update_10001`. Quizzes created before this update ran will have no field storage for this field and cannot have prerequisites set via the UI.
- The `field_prerequisite_quizzes` field is self-referential (`target_type=lms_quiz`) with unlimited cardinality — circular prerequisites are possible and would not be detected; they would permanently block all students.
- Max attempt limit (`field_take_quiz_number`) is checked at the same point in `takeQuiz()`; both conditions are independent.
