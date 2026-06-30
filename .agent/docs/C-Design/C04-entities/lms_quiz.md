# C04 Entity - lms_quiz

**Display name:** Quiz
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
An assessment composed of ordered questions, with a pass threshold, optional time limit, attempt limits, and prerequisites. Attached to a lesson/course; passing the final quiz triggers certificate issuance.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, langcode, default_langcode | keys + translation |
| uid | author |
| name | quiz title |
| field_body | formatted intro/instructions |
| field_course | ref → lms_course (owning course; certificate issuance source) |
| field_threshold | pass percentage |
| field_time_limit | time limit (minutes) |
| field_display_right_answer | show correct answers after submit |
| field_form_style | rendering style |
| field_question_count | number of questions to present |
| field_take_quiz_number | max attempts |
| field_questions | **multi-ref → lms_question** |
| field_prerequisite_quizzes | **multi-ref → lms_quiz** (gating) |
| created, changed | timestamps |

## Relationships
- N→1 `lms_course` (`field_course`) for final assessment ownership and certificate issuance.
- N→N `lms_question` (`field_questions`); self-ref prerequisites.
- May also be referenced by `lms_lesson.field_quiz`; produces `lms_quiz_result`.

## Editorial/data management
Instructors build quizzes via the Quiz entity form, attaching questions and setting threshold/limits. All copy in fields. Cache invalidated on save. See [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md), [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
