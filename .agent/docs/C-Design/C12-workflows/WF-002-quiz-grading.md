---
workflow: WF-002
slug: quiz-grading
mode: as-built
b04_source: B04/WF-002-quiz-grading.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-002 Quiz Submit & Grading

**Trigger:** Student GET `/quiz/{lms_quiz}/take` to open a quiz; POST `/quiz/{lms_quiz}/submit` to submit answers.
**Outcome:** `lms_quiz_result` entity persisted with `field_score`, `field_result` term, and per-question `lms_question_response` entities. `QuizPassedEvent` dispatched if result = Passed, enabling WF-003.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-002-quiz-grading.md`](../../B-Requirements/B04-business-workflows/WF-002-quiz-grading.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Student (authenticated) | Steps 1–3, 11 (view result) | `_user_is_logged_in: TRUE`; `view lms_course` |
| System | Steps 4–10 (grading, persistence, event) | — |
| Instructor | Manual essay grading — **no route exists; planned/stub** | `edit lms_course` (assumed, not yet enforced) |

## Entry Points

| URL | Page (C11) | Method | Form | Feature Flag |
|---|---|---|---|---|
| `/quiz/{lms_quiz}/take` | quiz-take | GET | — (render array) | — |
| `/quiz/{lms_quiz}/submit` | — | POST | form data keyed by question ID | — |
| `/quiz-result/{lms_quiz_result}` | quiz-result | GET | — | — |
| `/my-quiz-results` | my-quiz-results | GET | — | — |
| `/manage/quiz-results` | admin quiz-results | GET | — (AdminHtmlRouteProvider) | `administer lms_course` |

## Entities Touched

| Entity | Operation | Fields Written | State Transition |
|---|---|---|---|
| `lms_question_response` | CREATE (one per question) | `field_question`, `field_quiz`, `field_answers`, `field_correct`, `field_score`, `field_text_answer`, `field_pending_review`, `field_teacher_feedback` | — |
| `lms_quiz_result` | CREATE | `field_quiz`, `field_score`, `field_result` (taxonomy_term), `field_responses`, `field_start_time`, `field_end_time`, `uid` | — → `Passed` / `Failed` / `Pending` |
| `lms_quiz` | READ (attempt count, prereqs, questions) | — | — |
| `lms_question` | READ (bundle, fields per type) | — | — |
| `lms_answer` | READ (for single/multiple_choice) | — | — |
| `lms_matching_pair` | READ (for matching) | — | — |

## State Transitions

```
lms_quiz_result.field_result (taxonomy_term — quiz_results vocabulary):

  [quiz opened]
       │
       ▼  POST /quiz/{quiz}/submit
  [grading in progress]
       │
       ├──▶ Passed   (score >= field_threshold AND no pending_review)
       │       └──▶ QuizPassedEvent dispatched → WF-003, WF-notification
       │
       ├──▶ Failed   (score < field_threshold AND no pending_review)
       │
       └──▶ Pending  (any lms_question_response.field_pending_review = TRUE)
               └──▶ manual instructor grading (STUB — no route)
                       └──▶ re-evaluate → Passed or Failed (PLANNED)
```

State guards:

| Transition | Trigger | Guard | Side Effects |
|---|---|---|---|
| → `Passed` | `submitQuiz()` score calc | `score >= field_threshold` AND `pending_review_count = 0` | Dispatches `lms_quiz.passed` (`QuizPassedEvent`); `NotificationEventSubscriber` creates `quiz_result` in-app notification |
| → `Failed` | `submitQuiz()` score calc | `score < field_threshold` AND `pending_review_count = 0` | No event dispatched |
| → `Pending` | `submitQuiz()` detects essay | Any `EssayStrategy.pending_review = TRUE` | No event dispatched; `QuizPassedEvent` blocked; certificate blocked |

## Grading Architecture

```
QuizController::submitQuiz()
  │
  ├── For each question in $quiz->field_questions:
  │     GradingStrategyFactory::getStrategy($question->bundle())
  │       └── PHP match → one of 8 Strategy classes
  │             strategy->grade($question, $user_submission)
  │               └── returns ['is_correct', 'score', 'text_answer', 'answer_ids', 'pending_review']
  │             CREATE lms_question_response entity
  │
  ├── score = (sum awarded_points / sum max_points) * 100
  │
  ├── determine quiz_results term: Passed / Failed / Pending
  │
  └── CREATE lms_quiz_result
        dispatch QuizPassedEvent if Passed
```

**Architecture note:** `GradingStrategyFactory` is a **static PHP class** using `match`. It is NOT a Drupal Plugin manager. There is no `src/Plugin/` directory in lms_question or lms_quiz. No DI container involvement in the grading layer.

## Grading Strategy Reference

| Bundle | Strategy Class | Grading Logic | Partial Credit | `pending_review` |
|---|---|---|---|---|
| `single_choice` | `SingleChoiceStrategy` | Submitted answer ID = single `lms_answer` with `field_correct=TRUE` | No — binary | FALSE |
| `multiple_choice` | `MultipleChoiceStrategy` | Submitted IDs must exactly equal correct IDs (bidirectional `array_diff`) | No — strict binary | FALSE |
| `true_false` | `TrueFalseStrategy` | Submitted `'1'`/`'0'` vs `field_correct_answer` boolean | No — binary | FALSE |
| `fill_blank` | `FillBlankStrategy` | Trimmed text in `field_correct_answers` list; respects `field_case_sensitive` | No | FALSE |
| `short_answer` | `ShortAnswerStrategy` | Same logic as FillBlankStrategy; exact match | No | FALSE |
| `ordering` | `OrderingStrategy` | Strict positional array comparison vs `field_ordered_items` | No — all positions | FALSE |
| `matching` | `MatchingStrategy` | `submitted[i]` must equal `expected_pairs[i]['right']` for all pairs | No — all pairs | FALSE |
| `essay` | `EssayStrategy` | Always: `is_correct=FALSE`, `score=0.0`, `pending_review=TRUE`; stores text | N/A | TRUE (always) |

## Modules Involved

| Module | Responsibility in This Workflow |
|---|---|
| `lms_quiz` | `QuizController` (take/submit/viewResult/myResults); `GradingStrategyFactory`; 8 strategy classes; `QuizPassedEvent`; `lms_quiz_result` + `lms_question_response` entity definitions; templates |
| `lms_question` | `lms_question` entity (8 bundles); `lms_answer`; `lms_matching_pair`; `QuestionAccessControlHandler` |
| `lms_notification` | `NotificationEventSubscriber` subscribes to `QuizPassedEvent`; dispatches `quiz_result` in-app notification to student |
| `lms_certificate` | `CertificateEventListener` subscribes to `QuizPassedEvent`; triggers WF-003 |

## Permissions

| Step | Public (anon) | Student (auth) | Manager | Admin |
|---|---|---|---|---|
| 1. GET take quiz | — | `view lms_course` | same | same |
| 3. POST submit | — | `_user_is_logged_in` | same | same |
| 11. View result | — | own result only (QuizAccessControlHandler) | same | `edit lms_course` |
| View `/my-quiz-results` | — | own results | own results | all |
| Admin `/manage/quiz-results` | — | — | `edit lms_course` | `administer lms_course` |

Access logic: `QuizAccessControlHandler::checkAccess()`: view → `view lms_course`; update/delete → `edit lms_course`.

## Attempt Limit & Prerequisite Guards (in takeQuiz())

| Guard | Check | Failure Outcome |
|---|---|---|
| Max attempts | Entity query: `lms_quiz_result` count for `uid + field_quiz` | Error render array if count >= `field_take_quiz_number` (when > 0) |
| Prerequisite quizzes | For each `field_prerequisite_quizzes[i]`: query `lms_quiz_result` where `uid`, `field_quiz=prereq`, `field_result=Passed` | Error render listing unmet prerequisites |
| Random question selection | When `field_question_count > 0`: `array_rand` of `field_questions` | Subset of questions shown; order shuffled |

## Notifications

| Trigger | Recipient | Channel | Template |
|---|---|---|---|
| `QuizPassedEvent` | Student | in-app (`lms_notification` entity, type=`quiz_result`) | NotificationEventSubscriber |
| Essay `Pending` result | — | None dispatched | — |
| `Failed` result | — | None dispatched | — |

> Email and push channels for quiz_result notification are stubs in `NotificationManager::dispatch()` — logs only.

## Cache Invalidation

| Step | Cache Tag(s) | Search Index | List Views |
|---|---|---|---|
| `submitQuiz()` creates `lms_quiz_result` | No explicit tag invalidation (as-built) | — | `/manage/quiz-results` (dynamic, AdminHtmlRouteProvider) |
| `myResults()` | No cache headers set in controller; renders fresh per request | — | — |

> **Gap**: quiz result list at `/my-quiz-results` has no explicit cache control in the controller (facts do not show `#cache` on the render array). Renders fresh each request, which is acceptable.

## Operations Queues

| Page | Filters | Owner Role |
|---|---|---|
| `/manage/quiz-results` | — (AdminHtmlRouteProvider default list) | `administer lms_course` |
| `/manage/quizzes` | — (AdminHtmlRouteProvider default list) | `administer lms_course` |

## Editorial / Management Surfaces

| Entity / Content | Create/Edit UI | Role / Permission | State | Translation | Visible C11 Surfaces |
|---|---|---|---|---|---|
| `lms_quiz` | `/manage/quizzes/add`, `/manage/quizzes/{id}/edit` | `create lms_course` / `edit lms_course` | `status` boolean (published) | translatable | `/manage/quizzes` |
| `lms_question` | `/manage/questions/add/{type}`, `/manage/questions/{id}/edit` | `create lms_course` / `edit lms_course` | — | translatable | `/manage/questions` |
| `lms_quiz_result` | View/delete only at `/manage/quiz-results` | `administer lms_course` | `field_result` (taxonomy term) | — | `/manage/quiz-results` |

## Failure Modes

| Failure | Detection | Handling |
|---|---|---|
| Attempt limit reached | `takeQuiz()` entity query count check | Error render array; no redirect to take page |
| Prerequisite not passed | `takeQuiz()` loads prerequisite results | Error render array listing unmet prereqs |
| Unknown question bundle in factory | `GradingStrategyFactory::getStrategy()` returns `null` for unknown bundle | `submitQuiz()` skips question (no response created, no score awarded) |
| DB write fail on `lms_quiz_result` | Drupal entity save exception | 500 error page; student must retry (duplicate prevention note: no unique constraint on quiz+user; could create duplicate results) |
| Essay present → Pending | `EssayStrategy.pending_review=TRUE` | `QuizPassedEvent` not dispatched; no certificate; no in-app notification; student sees Pending on result page |
| Instructor grading route | Not implemented | Pending results remain pending indefinitely — **stub/planned** |
| Template threshold bug | `lms-quiz-result.html.twig` uses hardcoded `score >= 70` | Visual pass/fail styling may be wrong if `field_threshold != 70` — **bug, as-built** |
| `field_start_time` inaccuracy | `submitQuiz()` sets `field_start_time = request_time - 60` | Elapsed time always recorded as 60 seconds regardless of actual time taken — **bug, as-built** |

## Acceptance (B05)

- [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/) — `lms_quiz_result` persisted with correct score and result term; one `lms_question_response` per question; `QuizPassedEvent` dispatched iff result=Passed; result viewable at `/quiz-result/{id}`

## Implementation Status

| Step | Status | Notes |
|---|---|---|
| takeQuiz() — attempt limit, prereqs, randomization | DONE | As-built |
| GradingStrategyFactory + 8 strategies | DONE | Static PHP factory; all strategies implemented |
| lms_question_response creation | DONE | As-built |
| lms_quiz_result creation | DONE | As-built; start_time bug documented |
| QuizPassedEvent dispatch | DONE | As-built |
| Instructor essay grading route | PLANNED | No route or form in lms_quiz |
| Timer server-side enforcement | PLANNED | Client-side only via localStorage |
