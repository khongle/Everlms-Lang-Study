# C04 Entity - lms_answer

**Display name:** Answer (choice option)
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity)

## Purpose
A selectable answer/choice option belonging to a choice-type question (single/multiple choice, true/false).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | option label |
| field_body | formatted option text |
| field_correct | whether this option is correct |

## Relationships
- Referenced by `lms_question.field_choices` / `field_correct_answers`.

## Editorial/data management
Created inline with the question via the question form (inline-entity-style). Cache bubbled to parent question. See [lms_question](lms_question.md).
