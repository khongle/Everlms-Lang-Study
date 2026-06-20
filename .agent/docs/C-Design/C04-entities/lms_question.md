# C04 Entity - lms_question

**Display name:** Question
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity, **bundleable** via `type`, translatable)

## Purpose
A single question. The `type` field is the bundle (8 `lms_question_type` bundles): single_choice, multiple_choice, true_false, short_answer, essay, fill_blank, matching, ordering. Bundle determines which answer fields apply and which grading strategy runs.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, type (bundle), langcode, default_langcode | keys + bundle + translation |
| name | question text/title |
| field_body | formatted question body |
| field_points | score weight |
| field_explanation | formatted answer explanation |
| field_difficulty | difficulty (`quiz_difficulty` taxonomy) |
| field_correct_answer | correct value (true_false / fill_blank) |
| field_case_sensitive | fill_blank matching flag |
| field_min_words, field_max_words | essay/short_answer constraints |
| field_choices | **multi-ref → lms_answer** (choice options) |
| field_correct_answers | **multi** correct option refs |
| field_matching_pairs | **multi-ref → lms_matching_pair** |
| field_ordered_items | **multi** ordered items |
| created, changed | timestamps |

## Relationships
- Bundle of `lms_question_type` (config). Sub-entities: `lms_answer`, `lms_matching_pair`.
- Referenced by `lms_quiz.field_questions`; answered via `lms_question_response`.

## Editorial/data management
Instructors author questions per type via type-specific forms. Grading per bundle via `GradingStrategyFactory`. All copy in fields. See [C16 sheet 01](../C16-item-solutions/01-entities.md).
