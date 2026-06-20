---
id: TEST-002
type: Kernel
priority: P0 — Must
ac: AC-013, AC-016, AC-017
feature: F-002 (Assessment System)
module: lms_quiz, lms_question
service: N/A (GradingStrategyFactory is a static PHP class, no DI)
status: NOT_YET_AUTOMATED
---

# TEST-002 — Quiz Auto-Grading

> Verifies the static `GradingStrategyFactory` + 8 strategy classes for all auto-graded question types, QuizResult persistence, pass/fail determination, and `QuizPassedEvent` dispatch. This is the most complex unit boundary in EverLMS — grading is pure PHP with no Drupal Plugin API.

**Story:** [D03 lms_quiz](../../D-Development/D03-stories/lms_quiz.md)
**AC:** [AC-013 — Quiz take page loads (B05 AC-02)](../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md) · [AC-016 — Auto-grading binary correctness (B05 AC-02)](../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md) · [AC-017 — Essay pending review (B05 AC-02)](../../B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md)
**C12 Workflow:** [WF-002 Quiz Grading](../../C-Design/C12-workflows/WF-002-quiz-grading.md)
**C05 Module:** [lms_quiz](../../C-Design/C05-modules/lms_quiz.md) · [lms_question](../../C-Design/C05-modules/lms_question.md)

---

## TC-002-A: SingleChoiceStrategy — correct answer

**Layer:** Kernel (needs entity storage to create `lms_question` + `lms_answer`)
**Given:**
- A `lms_question` entity of bundle `single_choice` exists with two `lms_answer` entities via `field_choices`; one answer has `field_correct = TRUE`
- The user submits the ID of the correct answer

**When:** `GradingStrategyFactory::getStrategy('single_choice')->grade($question, $submitted_answer_id)` is called

**Then:**
- Returns `['is_correct' => TRUE, 'score' => <field_points>, 'pending_review' => FALSE, 'answer_ids' => [<correct_id>], 'text_answer' => '']`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Correct answer ID submitted | `is_correct=TRUE`, `score=field_points` |
| Wrong answer ID submitted | `is_correct=FALSE`, `score=0` |
| Null / missing submission | `is_correct=FALSE`, `score=0` |

---

## TC-002-B: MultipleChoiceStrategy — strict all-or-nothing

**Layer:** Kernel
**Given:**
- A `single_choice` question with 3 choices, 2 marked `field_correct = TRUE`
- User submits exactly the 2 correct IDs

**When:** `MultipleChoiceStrategy::grade()` runs

**Then:**
- `is_correct=TRUE` when submitted IDs exactly match correct IDs (both array_diff directions are empty)
- `is_correct=FALSE` when submitted set is missing one correct ID (partial submission)
- `is_correct=FALSE` when submitted set has one extra wrong ID

### Test Data

| Input | Expected Output |
|-------|----------------|
| Exactly `{correct_1, correct_2}` | `is_correct=TRUE`, `score=field_points` |
| Only `{correct_1}` (partial) | `is_correct=FALSE`, `score=0` |
| `{correct_1, correct_2, wrong_1}` | `is_correct=FALSE`, `score=0` |

---

## TC-002-C: TrueFalseStrategy

**Layer:** Kernel
**Given:** A `true_false` question with `field_correct_answer = TRUE`

**When:** `TrueFalseStrategy::grade()` called with `'1'` vs `'0'`

**Then:**
- `'1'` → `is_correct=TRUE`
- `'0'` → `is_correct=FALSE`

---

## TC-002-D: FillBlankStrategy — case sensitivity

**Layer:** Kernel
**Given:** A `fill_blank` question with `field_correct_answers = ['Paris', 'paris']` and `field_case_sensitive = FALSE`

**When:** User submits `'PARIS'`

**Then:** `is_correct=TRUE` (case-insensitive match)

**And when** `field_case_sensitive = TRUE` and user submits `'PARIS'`:

**Then:** `is_correct=FALSE`

---

## TC-002-E: EssayStrategy — always pending review

**Layer:** Kernel
**Given:** An `essay` question exists; user submits any non-empty text

**When:** `EssayStrategy::grade()` is called

**Then:**
- `is_correct=FALSE`, `score=0.0`, `pending_review=TRUE`
- `text_answer` contains the submitted text

### Test Data

| Input | Expected Output |
|-------|----------------|
| Any non-empty string | `pending_review=TRUE`, `score=0` |
| Empty string | `pending_review=TRUE`, `score=0` |

---

## TC-002-F: Full quiz submit — QuizPassedEvent dispatched on pass

**Layer:** Kernel
**Given:**
- A `lms_quiz` with `field_threshold = 70`, single `single_choice` question (1 point)
- Student submits the correct answer → score = 100%
- An event subscriber is wired that records dispatched events

**When:** `QuizController::submitQuiz()` processes the POST submission

**Then:**
- A `lms_quiz_result` entity is created with `field_score = 100.00`, `field_result` term label = `Passed`
- `lms_question_response` entity created with `field_correct = TRUE`
- `QuizPassedEvent::EVENT_NAME` (`lms_quiz.passed`) is dispatched exactly once with the correct user + result + quiz

**And when** the student scores 60% (below threshold=70):
- `field_result` term label = `Failed`
- Event is NOT dispatched

### Test Data

| Input | Expected Output |
|-------|----------------|
| Score >= threshold (e.g. 100%) | `Passed` term; `QuizPassedEvent` dispatched |
| Score < threshold (e.g. 60%) | `Failed` term; no event |
| All essay questions | `Pending` term; no event |

---

## TC-002-G: Max attempts enforced

**Layer:** Kernel
**Given:** `field_take_quiz_number = 2` on the quiz; student already has 2 `lms_quiz_result` entities

**When:** `QuizController::takeQuiz()` is called

**Then:** The quiz take page signals attempt limit reached (quiz is blocked or take form is not rendered)

---

## Setup / Teardown (shared)

- Setup: Install `lms_base`, `lms_question`, `lms_quiz`; install entity schemas for `lms_question`, `lms_answer`, `lms_matching_pair`, `lms_quiz`, `lms_quiz_result`, `lms_question_response`; install `quiz_difficulty` and `quiz_results` taxonomy vocabularies and seed `Passed`/`Failed`/`Pending` terms; create a `student` user
- Teardown: Entity storage cleared by KernelTestBase

## Notes

- `GradingStrategyFactory` source: `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php` — static class, no DI container, testable without service container
- Strategy sources: `web/modules/custom/lms_quiz/src/Grading/Strategy/`
- `QuizController` source: `web/modules/custom/lms_quiz/src/Controller/QuizController.php`
- `QuizPassedEvent` source: `web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php`
- Known quirk: `lms_quiz_result.field_start_time` is always hardcoded to `request_time - 60` in `submitQuiz()` — not verifiable from the actual quiz start; do not assert exact start time in tests
- Known quirk: `lms-quiz-result.html.twig` uses a hardcoded `score >= 70` threshold for display — independent of `field_threshold`; this is a template bug but does not affect the grading logic under test
- Expected file: `web/modules/custom/lms_quiz/tests/src/Kernel/GradingStrategyTest.php` (TC-002-A through TC-002-E) + `web/modules/custom/lms_quiz/tests/src/Kernel/QuizSubmitTest.php` (TC-002-F/G)
