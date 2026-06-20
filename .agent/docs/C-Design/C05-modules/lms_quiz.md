---
module: lms_quiz
service: quiz-engine
route-groups: [Public, Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: lms_quiz

**Directory:** `web/modules/custom/lms_quiz/`
**Responsibility:** Quiz engine — presents quizzes to learners, grades submissions via a static strategy factory, stores attempt results, and dispatches `QuizPassedEvent` for downstream subscribers.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| Public (learner) | `/quiz` | `QuizController` | `/quiz/{lms_quiz}/take`, `/quiz/{lms_quiz}/submit` (POST), `/quiz-result/{lms_quiz_result}`, `/my-quiz-results` |
| Manage (admin CRUD) | `/manage` | AdminHtmlRouteProvider (auto) | `/manage/quizzes`, `/manage/quizzes/add`, `/manage/quizzes/{lms_quiz}/edit`, `/manage/quizzes/{lms_quiz}/delete`, `/manage/quiz-results`, `/manage/quiz-results/{lms_quiz_result}` |

All learner routes require `_user_is_logged_in: TRUE`. Route parameters `{lms_quiz}` and `{lms_quiz_result}` are entity-upcasted.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `QuizPassedEvent` | Event class | Dispatched when a learner passes a quiz; carries `QuizResult`, `Quiz`, `AccountInterface`. Event name: `lms_quiz.passed` |
| `QuestionGradingInterface` | Interface | Contract for all 8 grading strategy classes: `grade(Question, mixed): array`, `getMaxScore(Question): float` |
| `GradingStrategyFactory` | Static factory | `getStrategy(string $bundle): ?QuestionGradingInterface` — resolves bundle slug to strategy via PHP `match` |

No Drupal services registered (`lms_quiz.services.yml` absent). The factory is a plain static PHP class, not a service container entry.

---

## Entities

### `lms_quiz` — Content Entity

| Property | Value |
|----------|-------|
| Base table | `lms_quiz` / `lms_quiz_field_data` |
| Translatable | Yes |
| Admin permission | `administer lms_course` |
| Entity keys | id, uuid, label=name, langcode, owner=uid, published=status |
| Form handler | `Drupal\lms_quiz\Form\QuizForm` (7 form-op aliases → same class) |
| Access handler | `Drupal\lms_quiz\Access\QuizAccessControlHandler` |
| Route provider | `AdminHtmlRouteProvider` |
| Traits | `EntityOwnerTrait` → implements `EntityOwnerInterface` |
| Views | `EntityViewsData` |

**Base fields:**

| Field | Type | Cardinality | Default | Notes |
|-------|------|-------------|---------|-------|
| name | string | 1 | — | Required, translatable, max_length=255 |
| field_body | text_long | 1 | — | Instructions (translatable) |
| field_questions | entity_reference | UNLIMITED | — | → `lms_question` |
| field_threshold | integer | 1 | 70 | Pass threshold (%) |
| field_time_limit | integer | 1 | 0 | Minutes; 0 = unlimited |
| field_display_right_answer | boolean | 1 | TRUE | Show correct answers after submit |
| field_form_style | list_string | 1 | `one_page` | Allowed: `one_page`, `question_step` |
| field_question_count | integer | 1 | 0 | Random selection count; 0 = use all |
| field_take_quiz_number | integer | 1 | 0 | Max attempts; 0 = unlimited |
| field_prerequisite_quizzes | entity_reference | UNLIMITED | — | → `lms_quiz` (self-ref); added via `lms_quiz_update_10001` |
| status | boolean | 1 | TRUE | Published |
| created / changed / uid | standard | 1 | — | — |

---

### `lms_quiz_result` — Content Entity

| Property | Value |
|----------|-------|
| Base table | `lms_quiz_result` |
| Translatable | No |
| Admin permission | `administer lms_course` |
| Access handler | `QuizAccessControlHandler` |
| Route provider | `AdminHtmlRouteProvider` |
| Traits | `EntityOwnerTrait` |

**Base fields:**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| field_quiz | entity_reference | 1 | Required → `lms_quiz` |
| field_score | decimal | 1 | precision=5, scale=2; stores percentage 0–100 |
| field_result | entity_reference | 1 | → taxonomy_term `quiz_results` (Passed / Failed / Pending) |
| field_responses | entity_reference | UNLIMITED | → `lms_question_response` |
| field_start_time | timestamp | 1 | **Bug:** hardcoded to `request_time - 60` in `submitQuiz` |
| field_end_time | timestamp | 1 | Set to `request_time` on submit |
| created / uid | standard | 1 | — |

---

### `lms_question_response` — Content Entity

| Property | Value |
|----------|-------|
| Base table | `lms_question_response` |
| Translatable | No |
| Admin permission | `administer lms_course` |
| Access handler | `QuizAccessControlHandler` |
| No route provider | No admin UI routes generated |

**Base fields:**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| field_question | entity_reference | 1 | Required → `lms_question` |
| field_quiz | entity_reference | 1 | → `lms_quiz` |
| field_answers | entity_reference | UNLIMITED | → `lms_answer`; for choice questions |
| field_correct | boolean | 1 | Default FALSE |
| field_score | decimal | 1 | precision=5, scale=2 |
| field_text_answer | text_long | 1 | For essay / short_answer types |
| field_pending_review | boolean | 1 | Default FALSE; TRUE = manual grading needed |
| field_teacher_feedback | text_long | 1 | Teacher annotation after manual review |

---

## Grading System

### Architecture

The grading subsystem uses a **static PHP factory + strategy pattern**. It is NOT a Drupal Plugin API system — no annotations, no plugin discovery, no `@Plugin`/`#[Plugin]` attributes. The factory is called directly from `QuizController::submitQuiz()`.

```
src/Grading/
├── QuestionGradingInterface.php      # grade() + getMaxScore()
├── GradingStrategyFactory.php        # static getStrategy(string $bundle)
└── Strategy/
    ├── SingleChoiceStrategy.php
    ├── MultipleChoiceStrategy.php
    ├── TrueFalseStrategy.php
    ├── FillBlankStrategy.php
    ├── ShortAnswerStrategy.php
    ├── OrderingStrategy.php
    ├── MatchingStrategy.php
    └── EssayStrategy.php
```

### `QuestionGradingInterface`

```php
interface QuestionGradingInterface {
    public function grade(Question $question, mixed $user_submission): array;
    // Returns: ['is_correct'=>bool, 'score'=>float, 'text_answer'=>string,
    //           'answer_ids'=>array, 'pending_review'=>bool]

    public function getMaxScore(Question $question): float;
}
```

### `GradingStrategyFactory`

Static class — `getStrategy(string $bundle): ?QuestionGradingInterface`

| Bundle | Strategy class |
|--------|----------------|
| `single_choice` | `SingleChoiceStrategy` |
| `multiple_choice` | `MultipleChoiceStrategy` |
| `true_false` | `TrueFalseStrategy` |
| `fill_blank` | `FillBlankStrategy` |
| `short_answer` | `ShortAnswerStrategy` |
| `ordering` | `OrderingStrategy` |
| `matching` | `MatchingStrategy` |
| `essay` | `EssayStrategy` |

Unknown bundle → returns `null`.

### Strategy Implementations (all as-built, DONE)

| Strategy | Grading logic | Partial credit | `pending_review` |
|----------|---------------|----------------|------------------|
| `SingleChoiceStrategy` | Submitted answer ID matches the one `lms_answer` where `field_correct=TRUE` | No | FALSE |
| `MultipleChoiceStrategy` | Strict: submitted ID set must exactly equal correct ID set (bidirectional `array_diff`) | No | FALSE |
| `TrueFalseStrategy` | Submitted `'1'`/`'0'` compared to `field_correct_answer` | No | FALSE |
| `FillBlankStrategy` | Trimmed text matched against `field_correct_answers` list; respects `field_case_sensitive` | No | FALSE |
| `ShortAnswerStrategy` | Same logic as `FillBlankStrategy`; exact match with optional case sensitivity | No | FALSE |
| `OrderingStrategy` | Strict array comparison: submitted order must exactly equal `field_ordered_items` | No | FALSE |
| `MatchingStrategy` | Each `submitted[i]` compared to `expected_pairs[i]['right']`; all must match | No | FALSE |
| `EssayStrategy` | Always: `is_correct=FALSE`, `score=0.0`, `pending_review=TRUE`; stores text answer for manual review | N/A | TRUE (always) |

**All auto-graded strategies are strictly binary (all-or-nothing). No partial credit is implemented for any type.** Essay grading is always manual; no manual-grading workflow UI exists.

---

## Quiz-Take Flow (`QuizController`)

**Injected services:** `EntityTypeManagerInterface`, `AccountProxyInterface`.

### `takeQuiz(Quiz $lms_quiz)` — GET `/quiz/{lms_quiz}/take`

1. Check max attempts: entity query on `lms_quiz_result` by `uid + field_quiz`; if count ≥ `field_take_quiz_number` (and limit > 0), block.
2. Check prerequisite quizzes: for each `field_prerequisite_quizzes` reference, verify learner has a `lms_quiz_result` with `field_result` term = "Passed".
3. Load questions from `field_questions`; if `field_question_count > 0`, shuffle and slice to that count.
4. Build question view-model per type: choices array for `single_choice`/`multiple_choice`, pairs for `matching`, shuffled `field_ordered_items` for `ordering`.
5. Return `lms_quiz_take` theme render array with `quiz`, `questions`, `time_limit`.

### `submitQuiz(Quiz $lms_quiz, Request $request)` — POST `/quiz/{lms_quiz}/submit`

1. Extract POST data keyed by question type.
2. For each question: call `GradingStrategyFactory::getStrategy($bundle)->grade($question, $submission)`.
3. Create and save a `lms_question_response` entity per question with graded results.
4. Calculate percentage score: `(total_score / max_score) * 100`.
5. Determine result term:
   - Score ≥ `field_threshold` → "Passed"
   - Any `pending_review` response → "Pending" (creates term if absent)
   - Otherwise → "Failed"
6. Create and save `lms_quiz_result` entity. **Note:** `field_start_time` is hardcoded to `request_time - 60` (1-minute estimate — not actual start time).
7. If result = "Passed": dispatch `QuizPassedEvent($result, $quiz, $account)`.
8. Redirect to `/quiz-result/{result_id}`.

### `viewResult(QuizResult $lms_quiz_result)` — GET `/quiz-result/{lms_quiz_result}`

Loads quiz, result term, and all `field_responses`. Builds response arrays with choices, text answer, teacher feedback, and pending state. Returns `lms_quiz_result` theme render array.

### `myResults()` — GET `/my-quiz-results`

Queries all `lms_quiz_result` for current user. Classifies each result:
- **passed** — result term = "Passed"
- **review** — score within 15% below threshold AND score ≥ 50
- **retry** — all other failures

Computes summary stats: total, passed, review, retry, average_score, best_score. Returns `lms_my_quiz_results` theme render array.

---

## `QuizPassedEvent`

**File:** `src/Event/QuizPassedEvent.php`

| Property | Value |
|----------|-------|
| Event name constant | `QuizPassedEvent::EVENT_NAME = 'lms_quiz.passed'` |
| Constructor | `(QuizResult $result, Quiz $quiz, AccountInterface $user)` |
| Getters | `getQuizResult()`, `getQuiz()`, `getUser()` |

Dispatched by `QuizController::submitQuiz()` on a "Passed" result. No subscriber is registered within `lms_quiz` itself. The primary subscriber is in `lms_certificate` (certificate auto-issuance) — see `../lms_certificate.md`.

---

## Templates

All templates embed large inline `<style>` blocks (dark GitHub-style theme, `#0d1117`) and load Font Awesome 6.4.0 from CDN.

| Template file | Theme hook | Variables | Notes |
|---------------|------------|-----------|-------|
| `lms-quiz-take.html.twig` | `lms_quiz_take` | `quiz`, `questions[]`, `time_limit` | Renders all 8 question types; vanilla JS: localStorage draft auto-save, drag-and-drop ordering, countdown timer (localStorage-based), confirmation modal |
| `lms-quiz-result.html.twig` | `lms_quiz_result` | `quiz`, `result`, `result_label`, `score`, `responses[]` | Score circle (green/red); per-response breakdown; teacher feedback; "Continue Learning" link hardcoded to `/user/portal`. **Bug:** pass/fail display threshold hardcoded to 70, ignores `field_threshold` |
| `lms-my-quiz-results.html.twig` | `lms_my_quiz_results` | `results[]`, `summary` | Hero stats (total/passed/average/best); result rows with state icons |

---

## Hook Implementations

| Hook | File | What it does |
|------|------|-------------|
| `hook_theme()` | `lms_quiz.module` | Registers `lms_quiz_take`, `lms_quiz_result`, `lms_my_quiz_results` theme hooks with their variable definitions |

---

## Update Hooks

| Hook | What it does |
|------|-------------|
| `lms_quiz_update_10001()` | Adds `field_prerequisite_quizzes` base field (entity_reference → lms_quiz, UNLIMITED) to existing entity storage via `entityDefinitionUpdateManager` |

---

## Internal Structure

```
web/modules/custom/lms_quiz/
├── lms_quiz.info.yml
├── lms_quiz.routing.yml              # 4 learner routes
├── lms_quiz.module                   # hook_theme()
├── lms_quiz.install                  # lms_quiz_update_10001
├── src/
│   ├── Access/
│   │   └── QuizAccessControlHandler.php
│   ├── Controller/
│   │   └── QuizController.php        # takeQuiz, submitQuiz, viewResult, myResults
│   ├── Entity/
│   │   ├── Quiz.php                  # lms_quiz content entity
│   │   ├── QuizResult.php            # lms_quiz_result content entity
│   │   └── QuestionResponse.php      # lms_question_response content entity
│   ├── Event/
│   │   └── QuizPassedEvent.php
│   ├── Form/
│   │   └── QuizForm.php              # minimal wrapper; 7 form-op aliases
│   └── Grading/
│       ├── QuestionGradingInterface.php
│       ├── GradingStrategyFactory.php
│       └── Strategy/
│           ├── SingleChoiceStrategy.php
│           ├── MultipleChoiceStrategy.php
│           ├── TrueFalseStrategy.php
│           ├── FillBlankStrategy.php
│           ├── ShortAnswerStrategy.php
│           ├── OrderingStrategy.php
│           ├── MatchingStrategy.php
│           └── EssayStrategy.php
└── templates/
    ├── lms-quiz-take.html.twig
    ├── lms-quiz-result.html.twig
    └── lms-my-quiz-results.html.twig
```

---

## Dependencies

| Dependency | Type | Why |
|------------|------|-----|
| `lms_base` | Internal module | Provides base permissions (`view/edit/create lms_course`), shared entity infrastructure |
| `lms_question` | Internal module | Provides `lms_question`, `lms_answer`, `lms_matching_pair` entities consumed by grading strategies |

No circular dependencies. `lms_question` → `lms_base`; `lms_quiz` → `lms_question` → `lms_base`.

---

## Taxonomy Dependencies

The module relies on two vocabularies that must exist but are **not** installed by `lms_quiz` itself:

| Vocabulary | Used for | Required terms |
|------------|----------|----------------|
| `quiz_results` | `lms_quiz_result.field_result` | Passed, Failed, Pending (Pending created on-demand in `submitQuiz` if absent) |
| `quiz_difficulty` | `lms_question.field_difficulty` (in `lms_question`) | — |

Both vocabularies are assumed to be installed by `lms_base` or manual setup.

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-01 | Medium | `field_start_time` in `QuizResult` hardcoded to `request_time - 60`; actual quiz duration not tracked |
| BUG-02 | Medium | `lms-quiz-result.html.twig` uses hardcoded `score >= 70` for pass/fail display; ignores `field_threshold` |
| BUG-03 | Low | No concurrent-submit guard — double-submit can create two `lms_quiz_result` entities for one attempt |
| DEBT-01 | Low | All auto-graded strategies are strictly binary; no partial credit for MultipleChoice/Ordering/Matching |
| DEBT-02 | Low | No manual-grading workflow UI for Essay responses (`pending_review=TRUE` has no teacher dashboard) |

---

## Cross-Links

- Entities: [Assessment entities](../C04-entities/01-content-entities.md)
- Question bank: [lms_question](./lms_question.md)
- Entity-module map: [C03](../C03-entity-module-map.md)
