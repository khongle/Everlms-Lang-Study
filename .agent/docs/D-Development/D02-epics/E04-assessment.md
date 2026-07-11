# E04 - Assessment & quizzing

**Status:** done · **Phase:** 4

## Goal
Quiz nhiều loại (multi-type) với chấm điểm tự động; đây là certification gate (cổng chứng nhận).

## Scope
- Modules: [lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_question](../../C-Design/C05-modules/lms_question.md)
- Entities: lms_quiz, lms_question(+type), lms_answer, lms_matching_pair, lms_quiz_result, lms_question_response
- Feature: F-006 · Workflow: WF-002 · Emits `QuizPassedEvent`

## Stories
- [E04S01](../D03-stories/E04S01-lms_question.md), [E04S02](../D03-stories/E04S02-lms_quiz.md)

## Dependencies
E03.

## Open work
UI chấm điểm thủ công (manual grading) cho essay (cần verify độ sâu); test cho các grading-strategy tự động (R-006).
