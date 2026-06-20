# D02 — Epic Spec: Assessment Engine

**Epic ID:** E-03  
**Maps to draft:** G-ASSESSMENT  
**Features contributed to:** F-020 (Quiz Taking), F-021 (Question Bank), F-022 (Grading), F-023 (Results)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Assessment Service (lms_quiz, lms_question)  
**Dependencies:** E-01 (Foundation), E-02 (Content — lms_lesson.field_quiz reference)  
**Estimated Duration:** 2 weeks  
**Status:** COMPLETE (as-built — all entities, grading strategies, and quiz flow implemented; two known bugs documented below)

---

## Purpose

Deliver the complete quiz and assessment engine: the Quiz entity with configurable settings (threshold, time limit, attempt limit, prerequisite quizzes, question randomisation, form style), the Question bank with 8 bundles (single_choice, multiple_choice, true_false, fill_blank, short_answer, ordering, matching, essay) backed by Answer and MatchingPair sub-entities, a static-factory grading system (GradingStrategyFactory + 8 strategy classes), QuizResult and QuestionResponse storage entities, the full quiz-take/submit/result controller flow, and the `lms_quiz.passed` event that chains into the Certificate and Notification Epics.

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-03-01 | lms_question — Question entity (8 bundles), Answer, MatchingPair | module | modules-assessment.md; [C05-modules/lms_question.md](../../C-Design/C05-modules/lms_question.md) | lms_question | as-built | E-01 | Must |
| S-03-02 | lms_quiz — Quiz entity + QuizResult + QuestionResponse | module | modules-assessment.md; [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md) | lms_quiz | as-built | S-03-01 | Must |
| S-03-03 | GradingStrategyFactory + 8 binary grading strategies | module | modules-assessment.md; [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md) | lms_quiz | as-built | S-03-01, S-03-02 | Must |
| S-03-04 | QuizController — take, submit, result, my-results + templates | page | modules-assessment.md; [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md) | lms_quiz | as-built | S-03-02, S-03-03 | Must |
| S-03-05 | QuizPassedEvent dispatch + subscriber wiring | module | modules-assessment.md; [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md) | lms_quiz | as-built | S-03-04 | Must |

---

## Build Order Within Epic

1. **S-03-01** — lms_question entity family (Question, Answer, MatchingPair, 8 QuestionType configs)
2. **S-03-02** — lms_quiz entity family (Quiz, QuizResult, QuestionResponse); depends on S-03-01
3. **S-03-03** — GradingStrategyFactory + 8 strategy classes; depends on S-03-01, S-03-02
4. **S-03-04** — QuizController take/submit/result/my-results + templates; depends on S-03-02, S-03-03
5. **S-03-05** — QuizPassedEvent dispatch; depends on S-03-04 (event dispatched in submitQuiz)

---

## Story Details

### S-03-01 — lms_question — Question entity (8 bundles), Answer, MatchingPair
**As-built.** Three entities: `lms_question` (bundled; bundle entity type `lms_question_type`, a ConfigEntity), `lms_answer`, `lms_matching_pair`. 8 QuestionType configs installed via `config/install/`: essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false. All 8 bundle-specific fields are defined on the base entity (no per-bundle field separation — QuestionForm shows all fields regardless of bundle). `inline_entity_form` handler registered (requires contrib). `QuestionAccessControlHandler` delegates to lms_course permissions. There is **no Drupal Plugin API** for question handlers — grading is static factory in lms_quiz. Source: [C05-modules/lms_question.md](../../C-Design/C05-modules/lms_question.md).

### S-03-02 — lms_quiz entity family: Quiz, QuizResult, QuestionResponse
**As-built.** `lms_quiz`: fields include `field_questions` (→ lms_question, unlimited), `field_threshold` (default 70%), `field_time_limit` (minutes; 0=unlimited), `field_take_quiz_number` (max attempts; 0=unlimited), `field_question_count` (randomisation; 0=use all), `field_form_style` (one_page/question_step), `field_prerequisite_quizzes` (self-reference, added in update_10001), `field_display_right_answer`. `lms_quiz_result`: stores `field_score` (decimal 0-100%), `field_result` (taxonomy_term:quiz_results — terms: Passed/Failed/Pending), `field_responses` (→ lms_question_response, unlimited). **Known bug:** `field_start_time` on QuizResult always set to `$now - 60` (hardcoded 1-min estimate) — not tracked from page load. Source: [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md).

### S-03-03 — GradingStrategyFactory + 8 binary grading strategies
**As-built.** Architecture: static PHP factory (no Drupal Plugin API, no DI container) + Strategy pattern. `GradingStrategyFactory::getStrategy(string $bundle)` returns strategy via PHP `match`. `QuestionGradingInterface::grade()` returns `['is_correct'=>bool, 'score'=>float, 'text_answer'=>string, 'answer_ids'=>array, 'pending_review'=>bool]`. All auto-graded types are **strictly binary** (all-or-nothing — no partial credit for MultipleChoice, Ordering, or Matching). Essay strategy always returns `is_correct=FALSE, pending_review=TRUE`. Source: [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md).

| Bundle | Strategy | Partial credit |
|--------|----------|----------------|
| single_choice | SingleChoiceStrategy | No |
| multiple_choice | MultipleChoiceStrategy | No (strict exact match) |
| true_false | TrueFalseStrategy | No |
| fill_blank | FillBlankStrategy | No (exact match; case sensitivity configurable) |
| short_answer | ShortAnswerStrategy | No (same as fill_blank) |
| ordering | OrderingStrategy | No (strict order match) |
| matching | MatchingStrategy | No (all pairs must match) |
| essay | EssayStrategy | N/A (manual review; always pending_review=TRUE) |

### S-03-04 — QuizController: take, submit, result, my-results + templates
**As-built.** Routes: `/quiz/{lms_quiz}/take` (GET), `/quiz/{lms_quiz}/submit` (POST), `/quiz-result/{lms_quiz_result}` (GET), `/my-quiz-results` (GET). All require login. `takeQuiz`: checks max attempts via entity query, checks prerequisite quizzes (requires Passed result), randomises questions if `field_question_count > 0`, builds per-type view model. `submitQuiz`: dispatches GradingStrategyFactory per question, creates QuestionResponse + QuizResult entities, dispatches `QuizPassedEvent` on pass. `myResults`: classifies results as passed/review/retry (review = score within 15% below threshold but ≥50%). Templates: `lms-quiz-take.html.twig` (8-type quiz UI, localStorage draft save, drag-and-drop ordering, countdown timer), `lms-quiz-result.html.twig`, `lms-my-quiz-results.html.twig`. **Known bug:** `lms-quiz-result.html.twig` uses hardcoded `score >= 70` for pass/fail display independent of actual `field_threshold`. Source: [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md).

### S-03-05 — QuizPassedEvent dispatch + subscriber wiring
**As-built.** `QuizPassedEvent` (EVENT_NAME: `lms_quiz.passed`) dispatched by `QuizController::submitQuiz()` when result term = 'Passed'. No subscriber in lms_quiz itself. Subscribers in other modules: `CertificateEventListener` (lms_certificate — see E-06) and `NotificationEventSubscriber` (lms_notification — see E-04). Both subscribers guard with `class_exists(QuizPassedEvent::class)` for optional coupling. Source: [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md).

---

## Affected Modules

| Module | Role in Epic |
|--------|-------------|
| lms_question | Question bank: 3 entities (Question, Answer, MatchingPair), 8 QuestionType configs, QuestionAccessControlHandler |
| lms_quiz | Quiz + result entities, GradingStrategyFactory, QuizController, QuizPassedEvent |

---

## Known Issues (log to E02-issues/)

| Issue | Severity | Story | Description |
|-------|----------|-------|-------------|
| Quiz start_time hardcoded | Low | S-03-02 | `field_start_time` always set to `$now - 60` instead of being tracked from page load |
| Result template hardcoded threshold | Medium | S-03-04 | `lms-quiz-result.html.twig` uses `score >= 70` for pass/fail display, ignores quiz `field_threshold` |
| QuestionForm shows all bundle fields | Low | S-03-01 | All 8 bundle-specific fields are always visible regardless of selected bundle — no conditional logic |

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-020 | Student can take a quiz with all 8 question types | F-020 | S-03-04 |
| AC-021 | Quiz auto-graded; essay routes to pending_review | F-022 | S-03-03, S-03-04 |
| AC-022 | Prerequisite quiz check blocks taking quiz until dependency passed | F-020 | S-03-04 |
| AC-023 | QuizPassedEvent fired on successful pass; certificate issued (chain) | F-022 | S-03-05 |
| AC-024 | My Quiz Results page shows pass/review/retry classification | F-023 | S-03-04 |
| AC-025 | Max attempts enforcement blocks re-take when limit reached | F-020 | S-03-04 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-020 | lms_quiz.passed event → CertificateEventListener (E-06) | S-03-05 | Event subscriber in lms_certificate |
| INT-021 | lms_quiz.passed event → NotificationEventSubscriber (E-04) | S-03-05 | Event subscriber in lms_notification |
| INT-022 | lms_lesson.field_quiz → lms_quiz (E-02 link) | S-03-02 | field_quiz on lms_lesson points to lms_quiz entity |
| INT-023 | QuizResult → ProgressApiController::markComplete (E-04) | S-03-04 | Progress module checks for quiz completion but does NOT auto-trigger certs |
| INT-024 | lms_analytics.quiz_analytics queries lms_question_response | S-03-02 | Raw SQL joins against lms_question_response; assumes field_is_correct column (verify) |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story; grading strategies must each have unit tests for pass/fail cases
- **Cross-Story integration tests:** End-to-end take → submit → result cycle for each of the 8 question types; verify QuizPassedEvent is dispatched and received by downstream subscribers
- **Cross-Story Integration Test Owner:** `S-03-04` (QuizController orchestrates grading, result creation, event dispatch)
- **Acceptance tests:** AC-020 through AC-025

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_PARTIAL_CREDIT | S-03-03 | Enable partial-credit grading for MultipleChoice/Ordering/Matching | High | Disabled by default; enable per-quiz when implemented |

---

## CP4 Readiness Criteria

- [x] Every Story in this Epic has status: DONE (all as-built)
- [x] G1-G4 passed for every Story (per draft G-ASSESSMENT exit criteria)
- [x] Cross-Story integration tests within Epic passing
- [x] D07 code-map delta complete for all files touched in this Epic
- [x] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] Known issues logged in E02-issues/ (start_time bug, hardcoded threshold in template, QuestionForm shows all fields)
- [x] Every Story that touched code/config/UI lists relevant D00-conventions/ files
