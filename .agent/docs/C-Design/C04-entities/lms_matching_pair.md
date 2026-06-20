# C04 Entity - lms_matching_pair

**Display name:** Matching Pair
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity)

## Purpose
A left/right pair for a `matching`-type question.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_left_item | left side (prompt) |
| field_right_item | right side (match) |
| weight | display order |

## Relationships
- Referenced by `lms_question.field_matching_pairs`.

## Editorial/data management
Created inline with a matching question. Grading via the matching strategy in `GradingStrategyFactory`. See [lms_question](lms_question.md).
