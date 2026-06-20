# C04 Entity - lms_question_type

**Display name:** Question Type
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (**Config Entity** — the bundle definition for `lms_question`)

## Purpose
Defines the 8 question-type bundles. As config (not content), these ship in `config/sync` (`lms_question.question_type.*.yml`) and are stable across installs.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id | bundle machine name (e.g. `single_choice`) |
| label | human label |
| description | type description |
| (config) | per-type rendering/grading hints |

## The 8 bundles
`single_choice`, `multiple_choice`, `true_false`, `short_answer`, `essay`, `fill_blank`, `matching`, `ordering`.

## Relationships
- Is the bundle for `lms_question` (`type`). Each bundle maps to a grading strategy in `lms_quiz` `GradingStrategyFactory`.

## Editorial/data management
Managed as configuration (admin config form / config sync), not by content editors. New types require a code grading strategy + config bundle. See [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md).
