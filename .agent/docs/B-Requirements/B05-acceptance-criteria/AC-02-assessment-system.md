---
feature: F-002
status: DRAFT
---

# B05 — Acceptance Criteria for F-002 — Assessment System

> Feature source: `../B03-features/02-assessment-system.md`
> Implementing modules: `lms_quiz`, `lms_question`
> Implementation maturity: **as-built** — quiz engine, grading strategies, result persistence, and event dispatch are all fully implemented. Known bugs noted per criterion.

---

## AC-013: Quiz take page loads with correct questions and question types

**Priority:** Must
**Given** a published `lms_quiz` entity exists with `field_questions` referencing `lms_question` entities of various bundles
**When** an authenticated user requests `/quiz/{lms_quiz}/take`
**Then** `QuizController::takeQuiz()` returns the `lms_quiz_take` theme render with all questions loaded
**And** choice questions include their `field_choices` options; matching questions include their `field_matching_pairs`; ordering questions include shuffled `field_ordered_items`
**And** if `field_question_count > 0`, the question list is randomized and truncated to that count

**Status:** as-built — `takeQuiz()` is fully implemented including randomization and per-type view-model building.

---

## AC-014: Prerequisite quizzes are enforced before allowing a quiz attempt

**Priority:** Must
**Given** a `lms_quiz` has `field_prerequisite_quizzes` referencing one or more other quizzes
**When** an authenticated user requests `/quiz/{lms_quiz}/take`
**Then** `takeQuiz()` checks whether the user has a `lms_quiz_result` with `field_result` term label `Passed` for each prerequisite quiz
**And** if any prerequisite is not passed, the take page is blocked (the user cannot proceed to submission)

**Status:** as-built — prerequisite check is implemented in `takeQuiz()`. The `field_prerequisite_quizzes` field was added via `lms_quiz_update_10001`.

---

## AC-015: Maximum attempt limit is enforced

**Priority:** Must
**Given** a `lms_quiz` has `field_take_quiz_number > 0`
**When** a user who has already reached that number of `lms_quiz_result` entities for this quiz requests `/quiz/{lms_quiz}/take`
**Then** `takeQuiz()` detects the attempt count via an entity query and does not render the quiz form
**And** the user sees a "maximum attempts reached" message or equivalent blocking response

**Status:** as-built — attempt count check via entity query is implemented in `takeQuiz()`.

---

## AC-016: Auto-grading is strictly binary (all-or-nothing) for all objective question types

**Priority:** Must
**Given** a quiz submission via POST `/quiz/{lms_quiz}/submit` contains answers for `single_choice`, `multiple_choice`, `true_false`, `fill_blank`, `short_answer`, `ordering`, and `matching` question types
**When** `QuizController::submitQuiz()` calls `GradingStrategyFactory::getStrategy($bundle)->grade()` for each question
**Then** each strategy returns `is_correct=true` only when the user's answer exactly matches the stored correct answer(s); any partial match returns `is_correct=false`, `score=0.0`
**And** no partial credit is awarded for any objective type (confirmed: `MultipleChoiceStrategy`, `OrderingStrategy`, and `MatchingStrategy` all use strict all-or-nothing comparison)

**Status:** as-built — all 7 objective strategies are fully implemented with binary grading. No partial credit exists by design.

---

## AC-017: Essay questions are always queued for manual review

**Priority:** Must
**Given** a quiz submission includes an answer for a question with bundle `essay`
**When** `EssayStrategy::grade()` is called
**Then** the strategy returns `is_correct=false`, `score=0.0`, `pending_review=true` and stores the text answer in `field_text_answer`
**And** the resulting `lms_question_response` entity has `field_pending_review=true`
**And** the `lms_quiz_result` entity has `field_result` set to the `Pending` taxonomy term (not `Passed` or `Failed`)

**Status:** as-built — `EssayStrategy` always returns `pending_review=true`; `submitQuiz()` maps this to the `Pending` term (creating it if missing).

---

## AC-018: Essay manual grading interface — blocked — not implemented

**Priority:** Must
**Given** a `lms_quiz_result` entity has `field_result = Pending` due to an essay question awaiting review
**When** an instructor or admin navigates to a grading interface to enter a score for the essay response and submit
**Then** the `lms_question_response.field_teacher_feedback` and `field_score` fields are saved; the `lms_quiz_result.field_result` term transitions from `Pending` to `Passed` or `Failed` based on the updated score; the student receives a notification

**Status:** **blocked — not implemented.** No dedicated essay grading UI, grading form, or route exists anywhere in the codebase. The `field_teacher_feedback` field is defined on `lms_question_response` but no controller reads or writes it via a public route. Instructors cannot currently grade essays through the system. This is a known gap.

---

## AC-019: Quiz percentage score is calculated and persisted correctly

**Priority:** Must
**Given** a quiz with N questions, each worth `field_points` points, and a submitted response set
**When** `submitQuiz()` processes the submission
**Then** the score percentage = (sum of earned points / sum of max points) × 100 is stored in `lms_quiz_result.field_score` as a decimal with precision 5, scale 2
**And** the `field_result` term is `Passed` if `score >= field_threshold`, `Failed` if `score < field_threshold` and no essay is pending, or `Pending` if any essay response has `field_pending_review=true`

**Status:** as-built — score calculation and result term assignment are implemented in `submitQuiz()`.

---

## AC-020: QuizPassedEvent is dispatched on a passing result

**Priority:** Must
**Given** a quiz submission results in `field_result = Passed`
**When** `submitQuiz()` determines the result
**Then** `QuizPassedEvent` (event name `lms_quiz.passed`) is dispatched with the `QuizResult`, `Quiz`, and `AccountInterface` as payload
**And** any registered event subscribers (e.g. `CertificateEventListener`, `NotificationEventSubscriber`) receive the event and can act on it

**Status:** as-built — `QuizPassedEvent` is dispatched in `submitQuiz()` when result = Passed.

---

## AC-021: Quiz result page shows per-question breakdown

**Priority:** Must
**Given** a `lms_quiz_result` entity exists for the current user
**When** the user requests `/quiz-result/{lms_quiz_result}`
**Then** `QuizController::viewResult()` renders the `lms_quiz_result` theme with score percentage, pass/fail label, and a per-response breakdown including the user's answer, the correct/incorrect status, and any `field_teacher_feedback`
**And** pending essay responses are displayed with a "pending review" indicator

**Status:** as-built — `viewResult()` is fully implemented. Note: the result template hardcodes a 70% pass threshold display fallback independent of the quiz's actual `field_threshold` value; this is a cosmetic inconsistency in the template, not in the stored data.

---

## AC-022: My quiz results page aggregates history and summary stats

**Priority:** Must
**Given** the authenticated user has at least one `lms_quiz_result` entity
**When** the user requests `/my-quiz-results`
**Then** `QuizController::myResults()` loads all results for the current user, classifies each as `passed`, `review` (score within 15% below threshold but >= 50%), or `retry`, and returns a summary containing total, passed, review, retry, average_score, and best_score
**And** the `lms_my_quiz_results` template renders the summary stats and a result list with per-row state icons

**Status:** as-built — `myResults()` is fully implemented.

---

## AC-023: Quiz start time is tracked from page load

**Priority:** Should
**Given** a user loads the quiz take page and later submits
**When** the `lms_quiz_result` entity is created
**Then** `field_start_time` reflects the time the quiz page was first loaded, not an arbitrary offset

**Status:** **blocked — not implemented (known bug).** `submitQuiz()` hardcodes `field_start_time = request_time - 60` (a 1-minute estimate). The quiz take page does not record a server-side start timestamp. `field_start_time` values are therefore inaccurate for all existing results.

---

## AC-024: Draft answers are auto-saved in the browser during a quiz

**Priority:** Should
**Given** a user is answering a quiz on the take page
**When** the user changes an answer but has not submitted the form
**Then** the `lms-quiz-take.html.twig` JavaScript writes the current answer state to `localStorage` under a quiz-specific key
**And** if the page is refreshed before submission, the previously entered answers are restored from `localStorage`

**Status:** as-built — localStorage draft auto-save is implemented in the inline JavaScript within `lms-quiz-take.html.twig`.

---

## AC-025: Time-limited quiz auto-submits when the countdown reaches zero

**Priority:** Must
**Given** a `lms_quiz` has `field_time_limit > 0`
**When** the quiz take page renders and the countdown (stored in `localStorage`) reaches zero
**Then** the quiz form is automatically submitted by the client-side JavaScript countdown handler
**And** the submission is processed by `POST /quiz/{lms_quiz}/submit` as a normal submission

**Status:** as-built — countdown timer and auto-submit are implemented in `lms-quiz-take.html.twig` JavaScript.

---

## Local traceability (F-002)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-013 | T-013 | quiz-take / lms_quiz | PENDING |
| AC-014 | T-014 | quiz-prereq / lms_quiz | PENDING |
| AC-015 | T-015 | quiz-max-attempts / lms_quiz | PENDING |
| AC-016 | T-016 | quiz-grading-binary / lms_quiz | PENDING |
| AC-017 | T-017 | quiz-essay-pending / lms_quiz | PENDING |
| AC-018 | T-018 | quiz-essay-grading-ui / lms_quiz | PENDING |
| AC-019 | T-019 | quiz-score-persist / lms_quiz | PENDING |
| AC-020 | T-020 | quiz-passed-event / lms_quiz | PENDING |
| AC-021 | T-021 | quiz-result-page / lms_quiz | PENDING |
| AC-022 | T-022 | my-quiz-results / lms_quiz | PENDING |
| AC-023 | T-023 | quiz-start-time / lms_quiz | PENDING |
| AC-024 | T-024 | quiz-draft-save / lms_quiz | PENDING |
| AC-025 | T-025 | quiz-auto-submit / lms_quiz | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
