# F-006 Acceptance Criteria - Quiz & assessment engine

## Core criteria
1. **Given** a quiz with mixed question types, **when** a learner submits, **then** each objective question is graded by its `GradingStrategyFactory` strategy and a `lms_quiz_result` is stored.
2. **Given** a graded attempt, **when** the score is computed, **then** pass/fail is decided against the quiz threshold and shown to the learner.
3. **Given** all 8 question types, **when** authored, **then** each renders correctly and (for objective types) auto-grades; essay/short-answer are flagged for manual review.
4. **Given** a quiz with prerequisites (`field_prerequisite_quizzes`), **when** prerequisites are unmet, **then** the quiz is locked.
5. **Given** a passing attempt on a course's final quiz, **when** stored, **then** `QuizPassedEvent` fires (feeds WF-003).

## Evidence expected
- Modules: [lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_question](../../C-Design/C05-modules/lms_question.md).
- Test: per-strategy grading unit tests (8); pass-threshold + event-fire kernel test (E03).
