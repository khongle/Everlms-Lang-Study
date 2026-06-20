---
module: lms_question
service: question-bank
route-groups: [Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: lms_question

**Directory:** `web/modules/custom/lms_question/`
**Responsibility:** Question bank — defines the `lms_question` content entity (8 bundles via config entity), the `lms_answer` and `lms_matching_pair` child entities, and their admin CRUD routes; contains no services, no grading logic, and no templates.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| Manage (admin CRUD) | `/manage` | `AdminHtmlRouteProvider` (auto-generated) | `/manage/questions`, `/manage/questions/add/{type}`, `/manage/questions/{lms_question}`, `/manage/questions/{lms_question}/edit`, `/manage/questions/{lms_question}/delete` |

No `lms_question.routing.yml` exists — all routes come from entity link definitions processed by `AdminHtmlRouteProvider`.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_question` entity | Content entity | 8-bundle question bank; consumed by `lms_quiz` for quiz assembly and grading |
| `lms_answer` entity | Content entity | Answer options for `single_choice` / `multiple_choice` bundles |
| `lms_matching_pair` entity | Content entity | Left/right pair for `matching` bundle |
| `QuestionAccessControlHandler` | Access handler | Shared by all three entities; delegates to `lms_base` course permissions |

No Drupal services registered. No Plugin API types. No custom interfaces beyond what `ContentEntityBase` provides.

---

## Entities

### `lms_question` — Content Entity (Bundled)

| Property | Value |
|----------|-------|
| Entity type ID | `lms_question` |
| Base table | `lms_question` / `lms_question_field_data` |
| Translatable | Yes |
| Admin permission | `administer lms_course` |
| Bundle entity type | `lms_question_type` (config entity) |
| Bundle label | Question Type |
| Entity keys | id, uuid, bundle=type, label=name, langcode |
| Form handler | `Drupal\lms_question\Form\QuestionForm` |
| Access handler | `Drupal\lms_question\Access\QuestionAccessControlHandler` |
| Route provider | `AdminHtmlRouteProvider` |
| Inline form | `Drupal\inline_entity_form\Form\EntityInlineForm` (requires `inline_entity_form` contrib) |
| Views | `EntityViewsData` |

**Entity links:**
- canonical: `/manage/questions/{lms_question}`
- add-form: `/manage/questions/add/{type}`
- edit-form: `/manage/questions/{lms_question}/edit`
- delete-form: `/manage/questions/{lms_question}/delete`
- collection: `/manage/questions`

**Base fields:**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| name | string | 1 | Required, translatable, max_length=1024, label='Question' |
| field_body | text_long | 1 | Translatable, label='Details' |
| field_points | integer | 1 | Default=1, label='Points' |
| field_explanation | text_long | 1 | Translatable; shown to learner after submission |
| field_difficulty | entity_reference | 1 | → `taxonomy_term` bundle `quiz_difficulty` |
| field_choices | entity_reference | UNLIMITED | → `lms_answer`; used by `single_choice`, `multiple_choice` |
| field_correct_answer | boolean | 1 | Used by `true_false` |
| field_correct_answers | string | UNLIMITED | max_length=512; used by `fill_blank`, `short_answer` |
| field_case_sensitive | boolean | 1 | Default=FALSE; used by `fill_blank`, `short_answer` |
| field_matching_pairs | entity_reference | UNLIMITED | → `lms_matching_pair`; used by `matching` |
| field_ordered_items | string | UNLIMITED | max_length=512; used by `ordering` |
| field_min_words | integer | 1 | Default=0; used by `essay` |
| field_max_words | integer | 1 | Default=0; used by `essay` |
| created / changed | standard | 1 | — |

**Important:** All bundle-specific fields are defined on the base entity — there is no per-bundle field storage separation. `QuestionForm` does not conditionally hide irrelevant fields per bundle; all fields are always rendered in the edit form.

---

### `lms_question_type` — Config Entity (Bundle Definition)

| Property | Value |
|----------|-------|
| Entity type ID | `lms_question_type` |
| Type | Config entity |
| Config prefix | `question_type` |
| Bundle of | `lms_question` |
| Admin permission | `administer lms_course` |
| Class | `ConfigEntityBundleBase` (no custom handlers) |
| Entity keys | id (string), label (string) |
| Config export | id, label, description |

**8 bundles installed via `config/install/`:**

| Config file | id | Label | Description |
|-------------|----|-------|-------------|
| `lms_question.question_type.essay.yml` | `essay` | Essay | Essay question type |
| `lms_question.question_type.fill_blank.yml` | `fill_blank` | Fill Blank | Fill Blank question type |
| `lms_question.question_type.matching.yml` | `matching` | Matching | Matching question type |
| `lms_question.question_type.multiple_choice.yml` | `multiple_choice` | Multiple Choice | Multiple Choice question type |
| `lms_question.question_type.ordering.yml` | `ordering` | Ordering | Ordering question type |
| `lms_question.question_type.short_answer.yml` | `short_answer` | Short Answer | Short Answer question type |
| `lms_question.question_type.single_choice.yml` | `single_choice` | Single Choice | Single Choice question type |
| `lms_question.question_type.true_false.yml` | `true_false` | True False | True False question type |

Config schema defined in `config/schema/lms_question.schema.yml` for `lms_question.question_type.*`.

---

### `lms_answer` — Content Entity

| Property | Value |
|----------|-------|
| Entity type ID | `lms_answer` |
| Base table | `lms_answer` |
| Admin permission | `administer lms_course` |
| Form handlers | `ContentEntityForm`, `ContentEntityDeleteForm` |
| Access handler | `Drupal\lms_question\Access\QuestionAccessControlHandler` |

**Base fields:**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| name | string | 1 | Required, max_length=1024, label='Answer Text' |
| field_body | text_long | 1 | label='Details' |
| field_correct | boolean | 1 | Default=FALSE, label='Correct Answer' |

Used by `single_choice` and `multiple_choice` bundles via `lms_question.field_choices`.

---

### `lms_matching_pair` — Content Entity

| Property | Value |
|----------|-------|
| Entity type ID | `lms_matching_pair` |
| Base table | `lms_matching_pair` |
| Admin permission | `administer lms_course` |
| Form handlers | `ContentEntityForm`, `ContentEntityDeleteForm` |
| Access handler | `Drupal\lms_question\Access\QuestionAccessControlHandler` |

**Base fields:**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| field_left_item | string | 1 | Required, max_length=512 |
| field_right_item | string | 1 | Required, max_length=512 |
| weight | integer | 1 | Default=0 |

Used by the `matching` bundle via `lms_question.field_matching_pairs`.

---

## Bundle-to-Field Mapping

The table below shows which base fields are meaningful per bundle. All fields exist on every entity regardless of bundle (the form does not hide irrelevant fields).

| Bundle | Relevant fields |
|--------|----------------|
| `single_choice` | field_choices (→ lms_answer with field_correct) |
| `multiple_choice` | field_choices (→ lms_answer, multiple field_correct=TRUE) |
| `true_false` | field_correct_answer |
| `fill_blank` | field_correct_answers (list), field_case_sensitive |
| `short_answer` | field_correct_answers (list), field_case_sensitive |
| `ordering` | field_ordered_items (correct order) |
| `matching` | field_matching_pairs (→ lms_matching_pair, left/right) |
| `essay` | field_min_words, field_max_words |

All bundles: name, field_body, field_points, field_explanation, field_difficulty.

---

## Access Control

**`src/Access/QuestionAccessControlHandler.php`** — shared by `lms_question`, `lms_answer`, `lms_matching_pair`.

| Method | Permission check |
|--------|-----------------|
| `checkAccess()` — view | `view lms_course` |
| `checkAccess()` — update / delete | `edit lms_course` |
| `checkAccess()` — other | `neutral()` |
| `checkCreateAccess()` | `create lms_course` |

All permissions are delegated to `lms_base`; `lms_question` defines no permissions of its own (`lms_question.permissions.yml` absent).

---

## Form Layer

**`src/Form/QuestionForm.php`** — minimal wrapper extending `ContentEntityForm`. Overrides only `save()` to add a status message and redirect to the collection URL (`/manage/questions`). No field-hiding or bundle-conditional logic.

---

## Internal Structure

```
web/modules/custom/lms_question/
├── lms_question.info.yml
├── src/
│   ├── Access/
│   │   └── QuestionAccessControlHandler.php   # shared by all 3 entities
│   ├── Entity/
│   │   ├── Question.php                        # lms_question content entity
│   │   ├── QuestionType.php                    # lms_question_type config entity
│   │   ├── Answer.php                          # lms_answer content entity
│   │   └── MatchingPair.php                    # lms_matching_pair content entity
│   └── Form/
│       └── QuestionForm.php                    # minimal ContentEntityForm wrapper
├── config/
│   ├── install/
│   │   ├── lms_question.question_type.essay.yml
│   │   ├── lms_question.question_type.fill_blank.yml
│   │   ├── lms_question.question_type.matching.yml
│   │   ├── lms_question.question_type.multiple_choice.yml
│   │   ├── lms_question.question_type.ordering.yml
│   │   ├── lms_question.question_type.short_answer.yml
│   │   ├── lms_question.question_type.single_choice.yml
│   │   └── lms_question.question_type.true_false.yml
│   └── schema/
│       └── lms_question.schema.yml
```

No `.module` file, no `.routing.yml`, no `.services.yml`, no `.permissions.yml`, no `.install` file, no `templates/` directory.

---

## Dependencies

| Dependency | Type | Why |
|------------|------|-----|
| `lms_base` | Internal module | Provides permissions used by access handler; base entity infrastructure |
| `inline_entity_form` | Contrib module | `inline_entity_form` handler registered on `lms_question` entity for embedding in parent forms (e.g., quiz builder) |

No circular dependencies. `lms_question` → `lms_base`. `lms_quiz` → `lms_question` (not the reverse).

---

## Taxonomy Dependencies

| Vocabulary | Used on | Notes |
|------------|---------|-------|
| `quiz_difficulty` | `lms_question.field_difficulty` | Must exist; not installed by this module |

---

## Grading Note

`lms_question` contains **no grading logic**. No `src/Plugin/` directory exists. The grading strategy pattern (8 strategy classes + `GradingStrategyFactory`) lives entirely in `lms_quiz`. See [lms_quiz](./lms_quiz.md) for the grading subsystem.

---

## Known Issues / Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-01 | Low | `QuestionForm` shows all bundle-specific fields for every bundle with no conditional visibility; UX is cluttered for instructors |
| DEBT-02 | Low | No question versioning — editing a question after `lms_quiz_result` entities exist can invalidate historical grading context |
| DEBT-03 | Low | `lms_answer` and `lms_matching_pair` have no entity links / no standalone admin UI — only accessible via inline entity form or direct URL |

---

## Cross-Links

- Entities: [Assessment entities](../C04-entities/01-content-entities.md)
- Quiz engine: [lms_quiz](./lms_quiz.md)
- Entity-module map: [C03](../C03-entity-module-map.md)
