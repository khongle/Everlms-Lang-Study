# C06 — API: Assessment (Quiz Take / Submit / Result)

> **Documentation mode:** as-built — all endpoints exist in the codebase as of 2026-06-20.
>
> Covers both the web quiz flow (HTML + POST) and the mobile quiz JSON API. The web quiz submit (`/quiz/{id}/submit`) accepts form POST data and returns a redirect; the mobile quiz submit (`/api/v1/quiz/{id}/submit`) is a proper JSON endpoint.
>
> **Modules:** [lms_quiz](../C05-modules/lms_quiz.md), [lms_question](../C05-modules/lms_question.md), [lms_mobile_api](../C05-modules/lms_mobile_api.md)
> **Entities:** [lms_quiz, lms_quiz_result, lms_question_response, lms_question](../C04-entities/01-content-entities.md)

---

## API Overview

| Attribute | Web (Learner) | Mobile API |
|-----------|---------------|------------|
| Base path | `/quiz/`, `/quiz-result/` | `/api/v1/quiz/` |
| Authentication | Drupal session (logged_in) | Bearer token (controller-level) |
| Consumers | Browser (lms_theme) | Flutter app v1.0.10+33 |
| Submit format | `application/x-www-form-urlencoded` (HTML form POST) | `application/json` |

**Grading architecture:** All grading uses a static PHP factory `GradingStrategyFactory` (NOT a Drupal Plugin manager). Eight question bundles each have a dedicated `*Strategy` class implementing `QuestionGradingInterface`. All auto-graded strategies use strict binary scoring (all-or-nothing — no partial credit). Essay questions always return `pending_review = true`.

**Event on pass:** `QuizController::submitQuiz()` dispatches `QuizPassedEvent` (event name: `lms_quiz.passed`) when result is Passed. `lms_certificate` subscribes to this event and auto-issues a certificate by traversing Quiz → Lesson → Chapter → Course.

---

## Web Quiz Flow (HTML + Redirect)

The web quiz flow is **not a pure JSON API** — it renders HTML pages and uses form POST with redirect. It is documented here for completeness and mobile parity comparison.

### GET /quiz/{lms_quiz}/take

**Route name:** `lms_quiz.take`
**Module:** lms_quiz
**Controller:** `web/modules/custom/lms_quiz/src/Controller/QuizController.php` → `takeQuiz(Quiz $lms_quiz)`
**Authentication:** `_user_is_logged_in: TRUE`
**Response type:** HTML (Drupal render array via `lms_quiz_take` theme hook)

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `lms_quiz` | integer | `lms_quiz` entity ID (upcasted by Drupal param converter) |

**Business logic (as-built):**
1. Checks attempt count against `field_take_quiz_number` (0 = unlimited).
2. Checks prerequisite quizzes via `field_prerequisite_quizzes` — requires each prerequisite to have a `lms_quiz_result` with `field_result` taxonomy term = "Passed".
3. Loads questions from `field_questions`; if `field_question_count > 0`, randomly selects that many questions.
4. Builds per-bundle view models:
   - `single_choice` / `multiple_choice`: loads `lms_answer` entities from `field_choices` (with `field_correct` hidden from user).
   - `matching`: loads `lms_matching_pair` entities from `field_matching_pairs`; shuffles right-side items.
   - `ordering`: loads `field_ordered_items` strings; shuffles for display.
   - `true_false`, `fill_blank`, `short_answer`, `essay`: text input only.
5. Returns theme render array with `quiz`, `questions[]`, `time_limit` variables.

**Template:** `lms-quiz-take.html.twig` — includes vanilla JS for countdown timer (localStorage-based), draft auto-save, drag-and-drop ordering, and pre-submit confirmation modal.

---

### POST /quiz/{lms_quiz}/submit

**Route name:** `lms_quiz.submit`
**Module:** lms_quiz
**Controller:** `QuizController::submitQuiz(Quiz $lms_quiz, Request $request)`
**Authentication:** `_user_is_logged_in: TRUE`
**Methods:** POST only

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `lms_quiz` | integer | `lms_quiz` entity ID |

**Request Body (form-encoded from HTML form):**

The POST data structure depends on question bundle. `QuizController::submitQuiz()` reads from `$request->request->all()`:

| Field name pattern | Bundle(s) | Value |
|--------------------|-----------|-------|
| `responses[{question_id}]` | single_choice, true_false | Single answer ID or `"1"` / `"0"` |
| `responses[{question_id}][]` | multiple_choice | Array of selected answer IDs |
| `responses[{question_id}]` | fill_blank, short_answer, essay | Text string |
| `responses[{question_id}][]` | ordering | Array of item strings in submitted order |
| `matching[{question_id}][{pair_index}]` | matching | Right-side value for each pair index |

**Business logic (as-built):**
1. For each question in `field_questions`, reads response from POST data.
2. Calls `GradingStrategyFactory::getStrategy($bundle)` → strategy instance.
3. Calls `strategy->grade($question, $user_submission)` → `['is_correct', 'score', 'text_answer', 'answer_ids', 'pending_review']`.
4. Creates `lms_question_response` entity per question (saves immediately).
5. Sums scores, calculates percentage: `($total_score / $max_score) * 100`.
6. Determines result term: `>= field_threshold` → "Passed", `== 0 && essay_pending` → "Pending", else → "Failed". Creates "Pending" term in `quiz_results` vocabulary if missing.
7. Creates `lms_quiz_result` entity with `field_score`, `field_result`, `field_responses`, `field_start_time` (hardcoded: `request_time - 60` — **known bug**), `field_end_time`.
8. If Passed: dispatches `QuizPassedEvent`.
9. Redirects to `/quiz-result/{lms_quiz_result}`.

**Known bugs:**
- `field_start_time` is hardcoded to `now - 60` seconds — does not reflect actual time spent.
- No double-submit guard — concurrent submissions create duplicate `lms_quiz_result` records.
- No server-side CSRF token validation beyond Drupal's default form token (HTML form only).

**Response:** HTTP 302 redirect to `/quiz-result/{result_id}`. Not JSON.

---

### GET /quiz-result/{lms_quiz_result}

**Route name:** `lms_quiz.result`
**Module:** lms_quiz
**Controller:** `QuizController::viewResult(QuizResult $lms_quiz_result)`
**Authentication:** `_user_is_logged_in: TRUE`
**Response type:** HTML (Drupal render array via `lms_quiz_result` theme hook)

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `lms_quiz_result` | integer | `lms_quiz_result` entity ID |

**Security gap:** No ownership check — any logged-in user can view any result if they know the ID.

**Data rendered:** Quiz name, result term label (Passed/Failed/Pending), score percentage, per-question breakdown (question text, submitted answer, correct answer if `field_display_right_answer = true`, teacher feedback if essay, pending flag).

**Template note:** `lms-quiz-result.html.twig` uses hardcoded `score >= 70` for pass/fail display color — does not read the quiz's actual `field_threshold`. This is independent of the grading logic (cosmetic bug).

---

### GET /my-quiz-results

**Route name:** `lms_quiz.my_results`
**Module:** lms_quiz
**Controller:** `QuizController::myResults()`
**Authentication:** `_user_is_logged_in: TRUE`
**Response type:** HTML (Drupal render array via `lms_my_quiz_results` theme hook)

Queries all `lms_quiz_result` entities owned by the current user. Classifies each result:
- `passed` — result term = "Passed"
- `review` — score within 15% below threshold AND score >= 50%
- `retry` — everything else

Computes summary stats: total, passed, review, retry, average_score, best_score.

---

## Mobile Assessment API

### GET /api/v1/quiz/{quizId}

**Route name:** `lms_mobile_api.quiz.detail`
**Module:** lms_mobile_api
**Controller:** `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` → `getQuiz()`
**Authentication:** Bearer token (controller-enforced)

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `quizId` | integer | `lms_quiz` entity ID |

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Response (200):**
```json
{
  "id": 10,
  "title": "string",
  "instructions": "string — field_body (HTML)",
  "time_limit": 30,
  "threshold": 70,
  "max_attempts": 3,
  "show_correct_answers": true,
  "question_count": 5,
  "questions": [
    {
      "id": 100,
      "bundle": "single_choice | multiple_choice | true_false | fill_blank | short_answer | matching | ordering | essay",
      "text": "string — name field",
      "points": 1,
      "choices": [
        { "id": 200, "text": "string" }
      ],
      "pairs": [
        { "index": 0, "left": "string", "right_shuffled": "string" }
      ],
      "ordered_items": ["string"],
      "min_words": 0,
      "max_words": 0
    }
  ]
}
```

**Notes:**
- `choices` only populated for `single_choice` and `multiple_choice` bundles. `field_correct` is NOT included (hidden from learner).
- `pairs` only populated for `matching`; `right_shuffled` is the right-side item (shuffled position, not the correct match).
- `ordered_items` only populated for `ordering` (shuffled).
- `time_limit = 0` means unlimited.
- `max_attempts = 0` means unlimited.
- `question_count = 0` means use all questions.

**Error Responses:**
| Status | When |
|--------|------|
| 401 | Missing/invalid bearer token |
| 404 | Quiz not found or unpublished |

---

### POST /api/v1/quiz/{quizId}/submit

**Route name:** `lms_mobile_api.quiz.submit`
**Module:** lms_mobile_api
**Controller:** `MobileLearningController::submitQuiz()`
**Authentication:** Bearer token (controller-enforced)
**Methods:** POST

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `quizId` | integer | `lms_quiz` entity ID |

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |
| Content-Type | application/json | Yes |

**Request Body:**
```json
{
  "responses": {
    "100": "200",
    "101": ["201", "203"],
    "102": "1",
    "103": "Paris",
    "104": ["Item A", "Item B", "Item C"],
    "105": { "0": "Match B", "1": "Match A" },
    "106": "Essay text here"
  },
  "time_taken": 300
}
```

**Response field format per bundle:**
| Bundle | `responses[question_id]` value type | Example |
|--------|--------------------------------------|---------|
| `single_choice` | String — answer entity ID | `"200"` |
| `multiple_choice` | Array of answer IDs | `["201", "203"]` |
| `true_false` | `"1"` (true) or `"0"` (false) | `"1"` |
| `fill_blank` | String — text answer | `"Paris"` |
| `short_answer` | String — text answer | `"string"` |
| `ordering` | Array of item strings in submitted order | `["Item A", "Item C", "Item B"]` |
| `matching` | Object: pair_index → right-side value | `{"0": "B", "1": "A"}` |
| `essay` | String — essay text | `"long text..."` |

**Response (200):**
```json
{
  "result_id": 55,
  "score": 80.0,
  "passed": true,
  "result_label": "Passed",
  "pending_review": false,
  "threshold": 70
}
```

**Notes:**
- `pending_review: true` when any essay question is present (graded as `pending_review` by `EssayStrategy` — always returns score=0 and pending=true; the overall result becomes "Pending").
- `passed` reflects the `field_result` taxonomy term ("Passed" = true, all others = false).
- Grading uses the same `GradingStrategyFactory` as the web flow. Same known bug: `field_start_time` hardcoded to `now - 60`.

**Error Responses:**
| Status | When |
|--------|------|
| 400 | Missing required fields or malformed JSON |
| 401 | Missing/invalid bearer token |
| 404 | Quiz not found |
| 409 | Max attempts reached |

---

### GET /api/v1/quiz/results/{resultId}

**Route name:** `lms_mobile_api.quiz.result`
**Module:** lms_mobile_api
**Controller:** `MobileLearningController::getQuizResult()`
**Authentication:** Bearer token (controller-enforced)

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `resultId` | integer | `lms_quiz_result` entity ID |

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Response (200):**
```json
{
  "id": 55,
  "quiz_id": 10,
  "quiz_title": "string",
  "score": 80.0,
  "passed": true,
  "result_label": "Passed | Failed | Pending",
  "pending_review": false,
  "threshold": 70,
  "responses": [
    {
      "question_id": 100,
      "question_text": "string",
      "bundle": "single_choice",
      "is_correct": true,
      "score": 1.0,
      "pending_review": false,
      "teacher_feedback": "string | null",
      "submitted_answer_ids": [200],
      "submitted_text": null
    }
  ],
  "created": "2026-06-20T10:00:00Z"
}
```

**Error Responses:**
| Status | When |
|--------|------|
| 401 | Missing/invalid bearer token |
| 403 | Result belongs to a different user |
| 404 | Result not found |

---

### GET /api/v1/quiz/my-results

**Route name:** `lms_mobile_api.quiz.my_results`
**Module:** lms_mobile_api
**Controller:** `MobileLearningController::listMyQuizResults()`
**Authentication:** Bearer token (controller-enforced)

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `per_page` | integer | 20 | Items per page |

**Response (200):**
```json
{
  "results": [
    {
      "id": 55,
      "quiz_id": 10,
      "quiz_title": "string",
      "score": 80.0,
      "passed": true,
      "result_label": "Passed",
      "created": "2026-06-20T10:00:00Z"
    }
  ],
  "summary": {
    "total": 10,
    "passed": 6,
    "failed": 3,
    "pending": 1,
    "average_score": 72.5,
    "best_score": 95.0
  },
  "total": 10,
  "page": 1,
  "per_page": 20
}
```

---

## Grading Strategy Reference

All grading is strictly binary (all-or-nothing per question). No partial credit for multi-select or ordering.

| Bundle | Strategy Class | `is_correct` Logic | `pending_review` |
|--------|----------------|--------------------|-----------------|
| `single_choice` | `SingleChoiceStrategy` | Submitted ID matches the one `lms_answer` where `field_correct=TRUE` | FALSE |
| `multiple_choice` | `MultipleChoiceStrategy` | Submitted IDs exactly equal correct IDs (array diff both ways must be empty) | FALSE |
| `true_false` | `TrueFalseStrategy` | Submitted `"1"/"0"` matches `field_correct_answer` | FALSE |
| `fill_blank` | `FillBlankStrategy` | Trimmed text matches any entry in `field_correct_answers`; respects `field_case_sensitive` | FALSE |
| `short_answer` | `ShortAnswerStrategy` | Same logic as FillBlankStrategy | FALSE |
| `ordering` | `OrderingStrategy` | Submitted order array exactly equals `field_ordered_items` | FALSE |
| `matching` | `MatchingStrategy` | All submitted pair values match corresponding `lms_matching_pair.field_right_item` values | FALSE |
| `essay` | `EssayStrategy` | Always `is_correct=FALSE`, `score=0.0`; stores text; requires instructor review | TRUE (always) |

**Factory location:** `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php` (static class, `match` expression, no Drupal service injection).

---

## API Route Summary

| Method | Path | Route Name | Auth | Description |
|--------|------|------------|------|-------------|
| GET | `/quiz/{lms_quiz}/take` | `lms_quiz.take` | Session | Web: render quiz-taking page |
| POST | `/quiz/{lms_quiz}/submit` | `lms_quiz.submit` | Session | Web: submit quiz answers → redirect |
| GET | `/quiz-result/{lms_quiz_result}` | `lms_quiz.result` | Session | Web: view quiz result page |
| GET | `/my-quiz-results` | `lms_quiz.my_results` | Session | Web: list all my quiz results |
| GET | `/api/v1/quiz/{quizId}` | `lms_mobile_api.quiz.detail` | Bearer | Mobile: quiz detail + questions |
| POST | `/api/v1/quiz/{quizId}/submit` | `lms_mobile_api.quiz.submit` | Bearer | Mobile: submit quiz answers → JSON result |
| GET | `/api/v1/quiz/results/{resultId}` | `lms_mobile_api.quiz.result` | Bearer | Mobile: quiz result detail |
| GET | `/api/v1/quiz/my-results` | `lms_mobile_api.quiz.my_results` | Bearer | Mobile: list my quiz results |

---

## Known Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| `field_start_time` hardcoded | Bug | `QuizResult.field_start_time` is always set to `now - 60` seconds; does not track real elapsed time |
| No double-submit guard | Risk | Concurrent POST requests can create duplicate `lms_quiz_result` records; no DB lock or idempotency key |
| No ownership check on web result view | Security | `GET /quiz-result/{id}` has no check that the result belongs to the current user |
| Template uses hardcoded threshold | Bug | `lms-quiz-result.html.twig` uses `score >= 70` for pass/fail color; ignores `field_threshold` |
| Essay always fails grading | By design | `EssayStrategy` always returns `score=0` and `pending_review=TRUE`; instructor must manually review and update `field_teacher_feedback` and `field_correct` on the `lms_question_response` entity (no dedicated instructor review UI/endpoint exists) |
| No input schema validation | Risk | Web submit reads raw POST fields; no server-side type/range validation |
