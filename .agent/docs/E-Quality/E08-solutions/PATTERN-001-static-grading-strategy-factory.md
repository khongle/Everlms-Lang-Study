---
solution_id: SOL-001
name: Static Grading Strategy Factory
applies_to: "any module that must dispatch to one of N discrete logic variants keyed by a string token (entity bundle, type field, machine name)"
derived_from_issue: «no E02 issue yet — pattern extracted directly from as-built lms_quiz code»
owning_story: lms_quiz
lesson: K04-static-factory-over-plugin-for-stateless-strategies
status: ACTIVE
---

# E08 — Solution: Static Grading Strategy Factory

> One-line purpose: describes why `lms_quiz` uses a plain PHP `match`-based factory instead of Drupal's Plugin Manager, when that is the right call, and what to watch for.

---

## Problem

`lms_quiz` must grade 8 distinct question bundles (`single_choice`, `multiple_choice`, `true_false`, `fill_blank`, `short_answer`, `ordering`, `matching`, `essay`). Each bundle has a different grading algorithm:

- **single_choice** — compare one submitted answer ID against the correct `lms_answer` entity.
- **multiple_choice** — strict set equality: submitted IDs must exactly match all correct IDs.
- **true_false** — compare submitted boolean to `field_correct_answer`.
- **fill_blank / short_answer** — exact string match against `field_correct_answers[]`; respects `field_case_sensitive`.
- **ordering** — strict array-position equality against `field_ordered_items`.
- **matching** — per-pair position equality against `lms_matching_pair` right items.
- **essay** — always `pending_review=TRUE`; no auto score.

A naive approach would be a single `if/elseif` block inside `QuizController::submitQuiz()`. That violates the Open/Closed Principle and makes each variant untestable in isolation.

---

## Solution

`lms_quiz` implements a **static PHP factory + Strategy pattern** with no Drupal Plugin API involvement.

### Structure (as-built)

```
web/modules/custom/lms_quiz/src/Grading/
├── QuestionGradingInterface.php        # Contract
├── GradingStrategyFactory.php          # Static factory — match(bundle) → strategy
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

### Interface contract

```php
// QuestionGradingInterface
interface QuestionGradingInterface {
    public function grade(Question $question, mixed $user_submission): array;
    // returns: ['is_correct' => bool, 'score' => float, 'text_answer' => string,
    //           'answer_ids' => int[], 'pending_review' => bool]

    public function getMaxScore(Question $question): float;
}
```

### Factory dispatch

```php
// GradingStrategyFactory (static final class — NOT a Drupal service)
public static function getStrategy(string $bundle): ?QuestionGradingInterface {
    return match($bundle) {
        'single_choice'   => new SingleChoiceStrategy(),
        'multiple_choice' => new MultipleChoiceStrategy(),
        'true_false'      => new TrueFalseStrategy(),
        'fill_blank'      => new FillBlankStrategy(),
        'short_answer'    => new ShortAnswerStrategy(),
        'ordering'        => new OrderingStrategy(),
        'matching'        => new MatchingStrategy(),
        'essay'           => new EssayStrategy(),
        default           => null,
    };
}
```

Unknown bundles return `null` — callers should guard with `if ($strategy === null) { continue; }`.

### Call sites

| Location | Usage |
|----------|-------|
| `web/modules/custom/lms_quiz/src/Controller/QuizController.php` `submitQuiz()` | Calls `GradingStrategyFactory::getStrategy($question->bundle())` for each question in the submission loop |
| `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` `submitQuiz()` | Same call — uses static import `use Drupal\lms_quiz\Grading\GradingStrategyFactory;` — **not declared in lms_mobile_api.info.yml dependencies** (as-built defect) |

### Grading behaviour table

| Strategy | Partial credit | `pending_review` | Binary rule |
|----------|---------------|-----------------|-------------|
| SingleChoiceStrategy | No | FALSE | One correct answer ID match |
| MultipleChoiceStrategy | No | FALSE | Exact set equality (array_diff both directions) |
| TrueFalseStrategy | No | FALSE | bool('1'/'0') vs `field_correct_answer` |
| FillBlankStrategy | No | FALSE | trimmed string in `field_correct_answers[]`; optional case-ignore |
| ShortAnswerStrategy | No | FALSE | Same as FillBlank |
| OrderingStrategy | No | FALSE | Strict positional array equality |
| MatchingStrategy | No | FALSE | Each pair right-item at correct position |
| EssayStrategy | N/A | TRUE (always) | score=0.0 always; requires manual review |

**All auto-graded strategies are strictly binary (all-or-nothing).** No partial credit is implemented for multi-select or ordering questions.

---

## When to apply

Use a **static factory + strategy** (this pattern) when ALL of the following are true:

1. The strategy classes are **stateless** — they hold no injected services, no mutable state.
2. The set of variants is **closed and small** (8–15 max) — it changes rarely and only by the module owner (no third-party extensibility needed).
3. The bundle/type token is a **compile-time-stable string** that maps 1:1 to a class.
4. You want **zero DI container overhead** — strategies are `new`-ed on demand, not registered as services.

---

## When NOT to apply

| Situation | Use instead |
|-----------|-------------|
| Strategy needs injected Drupal services (HTTP client, entity manager, config) | Drupal Plugin Manager — see [PATTERN-002](./PATTERN-002-plugin-provider-discovery.md) |
| Third-party modules must register new variants without touching factory code | Drupal Plugin Manager with `@annotation` discovery |
| Variant metadata (labels, capabilities, enabled/disabled) must be stored or admin-configurable | Drupal Plugin Manager + config form |
| More than ~15 variants or variants added at a high rate | Consider plugin discovery to avoid constant factory churn |

---

## Trade-offs

- **Cost:** Every call to `GradingStrategyFactory::getStrategy()` allocates a new strategy object (`new SingleChoiceStrategy()`). For a 50-question quiz this is 50 allocations per submission — negligible in PHP, but worth noting if strategies ever grow heavy constructors.
- **Risk:** Adding a new `lms_question` bundle **requires editing two files**: the `lms_question` config install YAML and the `GradingStrategyFactory` match expression. Missing the factory update causes silent `null` returns — the question gets skipped with no score and no error.
- **Operational impact:** No monitoring hook. If a new bundle is deployed without a matching factory case, failed gradings are silent (no exception, no log entry). Add a `watchdog`/logger call when `$strategy === null` to surface this.
- **Alternatives considered:** Drupal Plugin Manager was evaluated and rejected because all 8 strategies are stateless value objects with no service dependencies. Plugin Manager adds annotation scanning, cache warming, and DI container overhead for no concrete benefit in this case. The static factory also makes the full bundle→class mapping visible in one file, which aids onboarding.

---

## References

- D03 Story: [`D-Development/D03-stories/lms_quiz.md`](../../D-Development/D03-stories/lms_quiz.md)
- C05 Module spec: [`C-Design/C05-modules/lms_quiz.md`](../../C-Design/C05-modules/lms_quiz.md)
- Contrast with plugin-based pattern: [`PATTERN-002-plugin-provider-discovery.md`](./PATTERN-002-plugin-provider-discovery.md)
- Source files:
  - `web/modules/custom/lms_quiz/src/Grading/QuestionGradingInterface.php`
  - `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php`
  - `web/modules/custom/lms_quiz/src/Grading/Strategy/` (8 classes)
  - `web/modules/custom/lms_quiz/src/Controller/QuizController.php` — `submitQuiz()`
  - `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` — `submitQuiz()`
- Known defect: `lms_mobile_api` uses this factory without declaring `lms_quiz` in its `info.yml` dependencies — tracked in [`C-Design/C05-modules/lms_mobile_api.md`](../../C-Design/C05-modules/lms_mobile_api.md)
